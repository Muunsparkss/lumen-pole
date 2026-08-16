<?php
session_start();
require_once '../../classes/AdminAuth.php';
require_once '../../classes/Election.php';

if (!AdminAuth::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user = Auth::getCurrentUser();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election = new Election();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (empty($title) || empty($start_date) || empty($end_date)) {
        $message = 'Title, start date, and end date are required';
        $message_type = 'error';
    } else {
        $result = $election->create($title, $description, $start_date, $end_date, $user['id']);
        if ($result['success']) {
            $message = 'Election created successfully! Election ID: ' . $result['election_id'];
            $message_type = 'success';
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
    <title>Create Election - Admin Panel</title>
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
            max-width: 700px;
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

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-card h2 {
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
            min-height: 100px;
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

        .helper-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
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

        <div class="form-card">
            <h2>Create New Election</h2>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="title">Election Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                           placeholder="e.g., Class President Election 2024">
                    <div class="helper-text">Enter the name of the election</div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" 
                              placeholder="Describe what this election is about..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <div class="helper-text">Optional: Provide details about the election</div>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date & Time *</label>
                    <input type="datetime-local" id="start_date" name="start_date" required 
                           value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                    <div class="helper-text">When voting begins</div>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date & Time *</label>
                    <input type="datetime-local" id="end_date" name="end_date" required 
                           value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                    <div class="helper-text">When voting ends</div>
                </div>

                <button type="submit">Create Election</button>
            </form>
        </div>
    </div>
</body>
</html>