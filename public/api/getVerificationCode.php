<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function sendVerificationCode($email)
{
  $mail = new PHPMailer(true);
  $code = rand(100000, 999999);

  $time = time();
  $time_expire = $time + 300;

  $dateTime_expire = (new DateTime())->setTimeStamp($time_expire);
  $dateTime_expire = $dateTime_expire->format('Y-m-d H:i:s');

  try {
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['MAIL_PORT'];


    $mail->setFrom($_ENV['MAIL_USERNAME'], 'Sign Up To Dashboard App');
    $mail->addAddress($email, '');

    // Nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Verification Code';
    $mail->Body = '<p>Your verification code is: <strong>' . $code . '</strong></p><br><p>expire at: ' . $dateTime_expire . '</p>';
    $mail->AltBody = '';

    $mail->send();

    $conn = Database::getInstance()->getConnection();
    $sql = "INSERT INTO `session.verification` (session_id, email, code, expired) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $code = strval($code);
    $stmt->bind_param("issi", $time, $email, $code, $time_expire);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
      'status' => 'success',
      'message' => 'Verification code sent successfully',
    ]);
  } catch (Exception $e) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo
    ]);
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if (!isset($data['email'])) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Email is required',
    ]);
    return;
  }

  $email = $data['email'];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid email address',
    ]);
    return;
  }

  sendVerificationCode($email);
} else {
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request method',
  ]);
}
