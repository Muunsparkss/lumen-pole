<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Election.php';

if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = Auth::getCurrentUser();
$election_obj = new Election();

$election_id = $_GET['election_id'] ?? null;
if (!$election_id) {
    header('Location: index.php');
    exit;
}

$election = $election_obj->getElectionById($election_id);
if (!$election) {
    header('Location: index.php');
    exit;
}

$candidates = $election_obj->getCandidates($election_id);
$hasVoted = $election_obj->hasUserVoted($election_id, $user['id']);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasVoted) {
    $candidate_id = $_POST['candidate_id'] ?? null;
    
    if (!$candidate_id) {
        $message = 'Please select a candidate';
        $message_type = 'error';
    } else {
        $result = $election_obj->castVote($election_id, $user['id'], $candidate_id);
        if ($result['success']) {
            $message = 'Vote cast successfully! Thank you for voting.';
            $message_type = 'success';
            $hasVoted = true;
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
    <title><?php echo htmlspecialchars($election['title']); ?> - Voting Platform</title>
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
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .election-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .election-header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .election-header p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px;
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

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .candidate-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .candidate-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .candidate-card input[type="radio"] {
            display: none;
        }

        .candidate-card input[type="radio"]:checked ~ .candidate-info {
            color: white;
        }

        .candidate-selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-color: #667eea !important;
        }

        .candidate-selected h3,
        .candidate-selected p {
            color: white !important;
        }

        .candidate-info {
            display: block;
        }

        .candidate-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .candidate-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .vote-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
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

        .voted-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            color: #155724;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>🗳️ Voting Platform</h1>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </header>

    <div class="container">
        <a href="index.php" class="back-link">← Back to Elections</a>

        <div class="election-header">
            <h1><?php echo htmlspecialchars($election['title']); ?></h1>
            <p><?php echo htmlspecialchars($election['description']); ?></p>
            <small>Ends: <?php echo date('M d, Y H:i', strtotime($election['end_date'])); ?></small>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($hasVoted): ?>
            <div class="voted-message">
                <h3>✓ You have already voted in this election</h3>
                <p>Thank you for your participation!</p>
            </div>
        <?php else: ?>
            <form method="POST" class="vote-form">
                <h2 style="margin-bottom: 20px; color: #667eea;">Select Your Candidate</h2>

                <div class="candidates-grid">
                    <?php foreach ($candidates as $candidate): ?>
                        <label class="candidate-card" onclick="this.querySelector('input[type=radio]').checked = true;">
                            <input type="radio" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                            <div class="candidate-info">
                                <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                                <?php if ($candidate['position']): ?>
                                    <p><strong><?php echo htmlspecialchars($candidate['position']); ?></strong></p>
                                <?php endif; ?>
                                <?php if ($candidate['description']): ?>
                                    <p><?php echo htmlspecialchars(substr($candidate['description'], 0, 100)); ?></p>
                                <?php endif; ?>
                                <p><small><?php echo $candidate['vote_count']; ?> votes</small></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-btn">Cast Vote</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Handle candidate card selection
        document.querySelectorAll('.candidate-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.candidate-card').forEach(card => {
                    card.classList.remove('candidate-selected');
                });
                if (this.checked) {
                    this.closest('.candidate-card').classList.add('candidate-selected');
                }
            });
        });
    </script>
</body>
</html>