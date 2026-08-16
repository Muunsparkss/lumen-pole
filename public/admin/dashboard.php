<?php
session_start();
require_once '../../classes/AdminAuth.php';
require_once '../../classes/Election.php';

// Check if user is admin
if (!AdminAuth::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user = Auth::getCurrentUser();
$election = new Election();
$allElections = $election->getActiveElections();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Voting Platform</title>
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

        .admin-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .nav-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
        }

        .nav-btn.active {
            background: #764ba2;
        }

        .welcome {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .welcome h2 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .elections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .election-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .election-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .election-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .election-card p {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .election-stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .stat {
            flex: 1;
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }

        .election-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-closed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            transition: transform 0.2s;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
        }

        .btn-candidates {
            background: #764ba2;
            color: white;
        }

        .btn-candidates:hover {
            transform: translateY(-2px);
        }

        .btn-results {
            background: #17a2b8;
            color: white;
        }

        .btn-results:hover {
            transform: translateY(-2px);
        }

        .btn-close {
            background: #dc3545;
            color: white;
        }

        .btn-close:hover {
            transform: translateY(-2px);
        }

        .no-elections {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
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
        <div class="admin-nav">
            <a href="dashboard.php" class="nav-btn active">Dashboard</a>
            <a href="create_election.php" class="nav-btn">Create Election</a>
            <a href="../index.php" class="nav-btn">View as Voter</a>
        </div>

        <div class="welcome">
            <h2>Elections Management</h2>
            <p>Manage all elections, candidates, and view voting results.</p>
        </div>

        <?php if (empty($allElections)): ?>
            <div class="no-elections">
                <h3>No Elections Yet</h3>
                <p><a href="create_election.php" style="color: #667eea;">Create your first election</a></p>
            </div>
        <?php else: ?>
            <div class="elections-grid">
                <?php foreach ($allElections as $elec): ?>
                    <div class="election-card">
                        <div class="election-status status-<?php echo $elec['status']; ?>">
                            <?php echo strtoupper($elec['status']); ?>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($elec['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($elec['description'], 0, 100)); ?></p>
                        
                        <div class="election-stats">
                            <div class="stat">
                                <div class="stat-number"><?php echo $elec['candidate_count']; ?></div>
                                <div class="stat-label">Candidates</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number"><?php echo $elec['total_votes']; ?></div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>

                        <small>Ends: <?php echo date('M d, Y H:i', strtotime($elec['end_date'])); ?></small>
                        
                        <div class="action-buttons">
                            <a href="edit_election.php?id=<?php echo $elec['id']; ?>" class="action-btn btn-edit">Edit</a>
                            <a href="manage_candidates.php?id=<?php echo $elec['id']; ?>" class="action-btn btn-candidates">Candidates</a>
                            <a href="results.php?id=<?php echo $elec['id']; ?>" class="action-btn btn-results">Results</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>