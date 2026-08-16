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

$results = $election_obj->getResults($election_id);
$totalVotes = 0;
foreach ($results as $result) {
    $totalVotes += $result['votes'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Admin Panel</title>
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
            max-width: 900px;
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
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title h2 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .results-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .results-container h3 {
            color: #667eea;
            margin-bottom: 20px;
        }

        .result-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .result-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .result-name {
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }

        .result-votes {
            font-size: 16px;
            color: #667eea;
            font-weight: 600;
        }

        .result-bar {
            width: 100%;
            height: 30px;
            background-color: #f0f0f0;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .result-progress {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            min-width: 30px;
        }

        .result-percentage {
            text-align: right;
            color: #999;
            font-size: 14px;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
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
            <h2><?php echo htmlspecialchars($election['title']); ?> - Results</h2>
            <p>Live voting results for this election</p>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $totalVotes; ?></div>
                <div class="stat-label">Total Votes</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($results); ?></div>
                <div class="stat-label">Candidates</div>
            </div>
        </div>

        <div class="results-container">
            <h3>Vote Distribution</h3>

            <?php if (empty($results)): ?>
                <div class="no-results">
                    <p>No votes cast yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($results as $index => $result): ?>
                    <?php 
                        $percentage = $totalVotes > 0 ? round(($result['votes'] / $totalVotes) * 100, 1) : 0;
                    ?>
                    <div class="result-item">
                        <div class="result-header">
                            <div>
                                <div class="result-name"><?php echo $index + 1; ?>. <?php echo htmlspecialchars($result['name']); ?></div>
                                <?php if ($result['position']): ?>
                                    <small style="color: #999;"><?php echo htmlspecialchars($result['position']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="result-votes"><?php echo $result['votes']; ?> votes</div>
                        </div>
                        
                        <div class="result-bar">
                            <div class="result-progress" style="width: <?php echo $percentage; ?>%">
                                <?php echo $percentage > 10 ? $percentage . '%' : ''; ?>
                            </div>
                        </div>
                        <div class="result-percentage"><?php echo $percentage; ?>%</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>