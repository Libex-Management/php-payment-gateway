<?php
include('./qrlib.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = filter_var($_POST["amount"], FILTER_VALIDATE_FLOAT);
    $accountNumber = preg_replace('/[^a-zA-Z0-9]/', '', $_POST["account_number"]);
    $bankName = $_POST["bank_name"];
    $branchCode = $_POST["branch_code"];
    if ($amount <= 0) {
        http_response_code(400);
        echo "Invalid amount";
        exit;
    }

    if (empty($accountNumber)) {
        http_response_code(400);
        echo "Account number is required";
        exit;
    }

    if (empty($bankName)) {
        http_response_code(400);
        echo "Bank name is required";
        exit;
    }

    if (empty($branchCode)) {
        http_response_code(400);
        echo "Branch code is required";
        exit;
    }

    // UPI details (Modify these with your UPI details)
    $upiId = getenv('UPI_ID'); // Use environment variables for sensitive data
    $name = "Your Name";
    $transactionNote = "Payment for XYZ";
    $currency = "INR";

    // Create UPI payment URL
    $upiUrl = "upi://pay?pa=$accountNumber&pn=$name&am=$amount&cu=$currency&tn=$transactionNote&bank=$bankName&branch=$branchCode";

    // Generate QR code
    $tempDir = sys_get_temp_dir();
    $fileName = 'qrcode.png';
    $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
    try {
        QRcode::png($upiUrl, $filePath, QR_ECLEVEL_L, 6);
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error generating QR code: " . $e->getMessage();
        exit;
    }

    // Check if the file exists
    if (!file_exists($filePath)) {
        http_response_code(500);
        echo "Error generating QR code: File not found";
        exit;
    }

    // Display the QR code image
    $qrCodeImage = base64_encode(file_get_contents($filePath));
    $qrCodeSrc = 'data:image/png;base64,' . $qrCodeImage;

    // Store the QR code image in a session variable
    $_SESSION['qrCodeSrc'] = $qrCodeSrc;

    // Redirect to the same page to prevent form resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Check if the QR code image is stored in the session variable
if (isset($_SESSION['qrCodeSrc'])) {
    $qrCodeSrc = $_SESSION['qrCodeSrc'];
    // Do not unset the session variable here
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Payment Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Generate Payment QR Code</h1>
        <form id="paymentForm" action="" method="POST">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>
            <br>
            <label for="account_number">Account Number:</label>
            <input type="number" id="account_number" name="account_number" required>
            <br>
            <label for="bank_name">Bank Name:</label>
            <input type="text" id="bank_name" name="bank_name" required>
            <br>
            <label for="branch_code">Branch Code:</label>
            <input type="text" id="branch_code" name="branch_code" required>
            <br>
            <button type="submit">Generate QR Code</button>
        </form>
        <?php if (isset($qrCodeSrc)) { ?>
            <img src="<?php echo $qrCodeSrc; ?>" alt="QR Code" style="width: 200px; height: 200px;">
        <?php } else { ?>
            <p>No QR code generated yet.</p>
        <?php } ?>
    </div>
</body>
</html>