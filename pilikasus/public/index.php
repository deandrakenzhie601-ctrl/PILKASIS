<?php

session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Candidate.php';
require_once __DIR__ . '/../src/Vote.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Database connection failed!");
}

// Initialize variables
$current_user = null;
$candidates = [];
$user_vote = null;
$results = null;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user = new User($conn);
    $current_user = $user->getById($_SESSION['user_id']);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new User($conn);
    $auth_user = $user->authenticate($username, $password);

    if ($auth_user) {
        $_SESSION['user_id'] = $auth_user['id'];
        $_SESSION['username'] = $auth_user['username'];
        $_SESSION['role'] = $auth_user['role'];
        header('Location: index.php');
        exit;
    } else {
        $login_error = "Username atau password salah!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    if (!$current_user) {
        header('Location: index.php');
        exit;
    }

    $candidate_id = $_POST['candidate_id'] ?? null;

    $vote = new Vote($conn);
    if ($vote->hasUserVoted($current_user['id'])) {
        $vote_error = "Anda sudah melakukan voting!";
    } else {
        $vote->user_id = $current_user['id'];
        $vote->candidate_id = $candidate_id;
        if ($vote->create()) {
            $vote_success = "Vote berhasil disimpan!";
        }
    }
}

// Get candidates with vote count
$candidate_obj = new Candidate($conn);
$candidates = $candidate_obj->getAll();

// Get user's vote if logged in
if ($current_user) {
    $vote = new Vote($conn);
    $user_vote = $vote->getUserVote($current_user['id']);
}

// Get voting results
$vote = new Vote($conn);
$results = $vote->getResults();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilkasis - Voting App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 50%, #14B8A6 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: url('data:image/svg+xml,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg"><path d="M0,50 Q300,0 600,50 T1200,50 L1200,120 L0,120 Z" fill="%23FDE047" opacity="0.1"/></svg>') repeat-x;
            background-size: auto 100%;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.5);
            max-width: 1200px;
            width: 100%;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::after {
            content: '🌊';
            position: absolute;
            font-size: 80px;
            opacity: 0.1;
            top: -20px;
            right: 50px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.95;
            font-weight: 300;
        }

        .content {
            padding: 40px 30px;
        }

        .user-info {
            text-align: right;
            margin-bottom: 25px;
            font-size: 0.95rem;
            color: #333;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #DC2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #16A34A;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .login-form {
            max-width: 420px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1F2937;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #F9FAFB;
        }

        input:focus {
            outline: none;
            border-color: #0EA5E9;
            background: white;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        h2 {
            color: #1F2937;
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 800;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .candidate-card {
            background: linear-gradient(135deg, #F0F9FF 0%, #ECFDF5 100%);
            border: 2px solid #E0F2FE;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .candidate-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.1), transparent);
            border-radius: 50%;
        }

        .candidate-card:hover {
            border-color: #0EA5E9;
            box-shadow: 0 15px 40px rgba(14, 165, 233, 0.2);
            transform: translateY(-5px);
        }

        .candidate-photo {
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3);
            position: relative;
            z-index: 1;
        }

        .candidate-name {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: #0F172A;
            position: relative;
            z-index: 1;
        }

        .candidate-party {
            color: #64748B;
            margin-bottom: 15px;
            font-size: 0.95rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .vote-count {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #0EA5E9, #06B6D4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .vote-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
            position: relative;
            z-index: 1;
        }

        .vote-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3);
        }

        .vote-btn:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .results {
            background: linear-gradient(135deg, #F0F9FF 0%, #ECFDF5 100%);
            padding: 30px;
            border-radius: 16px;
            margin-top: 40px;
            border: 2px solid #E0F2FE;
        }

        .results h2 {
            margin-bottom: 25px;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
            transition: all 0.3s ease;
        }

        .result-item:hover {
            padding-left: 10px;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-name {
            font-weight: 700;
            color: #0F172A;
            font-size: 1.05rem;
        }

        .result-votes {
            background: linear-gradient(135deg, #0EA5E9 0%, #06B6D4 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
        }

        .logout-btn {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            padding: 10px 20px;
            width: auto;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        p {
            text-align: center;
            margin-top: 25px;
            color: #64748B;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        strong {
            color: #0EA5E9;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗳️ Pilkasis</h1>
            <p>A Minimal PHP OOP Voting App</p>
        </div>

        <div class="content">
            <?php if (!$current_user): ?>
                <!-- Login Form -->
                <div class="user-info">
                    Belum login? Login untuk voting
                </div>

                <?php if (isset($login_error)): ?>
                    <div class="alert alert-error"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <form class="login-form" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <input type="hidden" name="action" value="login">
                    <button type="submit">Login</button>
                </form>

                <p style="text-align: center; margin-top: 20px; color: #666;">
                    <strong>Test Accounts:</strong><br>
                    admin / admin123<br>
                    siswa1 / siswa123
                </p>

            <?php else: ?>
                <!-- Voting Page -->
                <div class="user-info">
                    Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
                    <button class="logout-btn" onclick="window.location='?logout=1'">Logout</button>
                </div>

                <?php if (isset($vote_error)): ?>
                    <div class="alert alert-error"><?php echo $vote_error; ?></div>
                <?php endif; ?>

                <?php if (isset($vote_success)): ?>
                    <div class="alert alert-success"><?php echo $vote_success; ?></div>
                <?php endif; ?>

                <?php if ($user_vote): ?>
                    <div class="alert alert-success">
                        ✓ Anda sudah melakukan voting untuk: <strong><?php 
                        $voted_candidate = array_filter($candidates, function($c) use ($user_vote) {
                            return $c['id'] == $user_vote['candidate_id'];
                        });
                        if (count($voted_candidate) > 0) {
                            echo htmlspecialchars(array_values($voted_candidate)[0]['name']);
                        }
                        ?></strong>
                    </div>
                <?php endif; ?>

                <h2 style="margin-bottom: 20px;">Pilih Kandidat</h2>
                <div class="candidates-grid">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-card">
                            <div class="candidate-photo">📋</div>
                            <div class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></div>
                            <div class="candidate-party"><?php echo htmlspecialchars($candidate['party'] ?? '-'); ?></div>
                            <div class="vote-count"><?php echo $candidate['vote_count']; ?> Suara</div>

                            <?php if (!$user_vote): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="vote">
                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                    <button type="submit" class="vote-btn">Vote</button>
                                </form>
                            <?php else: ?>
                                <button class="vote-btn" disabled>Sudah Vote</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="results">
                    <h2>📊 Hasil Sementara</h2>
                    <?php foreach ($results as $result): ?>
                        <div class="result-item">
                            <div>
                                <span class="result-name"><?php echo htmlspecialchars($result['name']); ?></span>
                                <span style="color: #999; margin-left: 10px;"><?php echo htmlspecialchars($result['party'] ?? '-'); ?></span>
                            </div>
                            <span class="result-votes"><?php echo $result['total_votes']; ?> Suara</span>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
