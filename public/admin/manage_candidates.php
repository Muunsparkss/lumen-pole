<?php
session_start();
require_once '../../classes/AdminAuth.php';
require_once '../../classes/Election.php';

if (!AdminAuth::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user = Auth::getCurrentUser();
$election_obj = new Election();

$election_id = $_GET['id'] ?? null;
if (!$election_id) {
    header('Location: dashboard.php');
    exit;
}

$election = $election_obj->getElectionById($election_id);
if (!$election) {
    header('Location: dashboard.php');
    exit;
}

$candidates = $election_obj->getCandidates($election_id);
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $position = trim($_POST['position'] ?? '');

    if (empty($name)) {
        $message = 'Candidate name is required';
        $message_type = 'error';
    } else {
        $result = $election_obj->addCandidate($election_id, $name, $description, $position);
        if ($result['success']) {
            $message = 'Candidate added successfully!';
            $message_type = 'success';
            // Refresh candidates list
            $candidates = $election_obj->getCandidates($election_id);
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-container h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .page-title {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title h2 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .form-card h3 {
            color: #667eea;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .candidates-list {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .candidates-list h3 {
            color: #667eea;
            margin-bottom: 20px;
        }

        .candidate-item {
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .candidate-item h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .candidate-item p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .candidate-position {
            display: inline-block;
            padding: 3px 10px;
            background: #667eea;
            color: white;
            border-radius: 3px;
            font-size: 12px;
        }

        .candidate-votes {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }

        .no-candidates {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>🗳️ Voting Platform - Admin</h1>
            <div class="user-info">
                <span class="admin-badge">🔐 ADMIN</span>
                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="page-title">
            <h2><?php echo htmlspecialchars($election['title']); ?></h2>
            <p>Add and manage candidates for this election</p>
        </div>

        <div class="content-grid">
            <div class="form-card">
                <h3>Add Candidate</h3>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name">Candidate Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="e.g., John Smith">
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>"
                               placeholder="e.g., President">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  placeholder="Brief description about the candidate..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit">Add Candidate</button>
                </form>
            </div>

            <div class="candidates-list">
                <h3>Candidates (<?php echo count($candidates); ?>)</h3>

                <?php if (empty($candidates)): ?>
                    <div class="no-candidates">
                        <p>No candidates added yet. Add one using the form on the left.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-item">
                            <h4><?php echo htmlspecialchars($candidate['name']); ?></h4>
                            <?php if ($candidate['position']): ?>
                                <span class="candidate-position"><?php echo htmlspecialchars($candidate['position']); ?></span>
                            <?php endif; ?>
                            <?php if ($candidate['description']): ?>
                                <p><?php echo htmlspecialchars($candidate['description']); ?></p>
                            <?php endif; ?>
                            <div class="candidate-votes">
                                📊 <?php echo $candidate['vote_count']; ?> votes
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>