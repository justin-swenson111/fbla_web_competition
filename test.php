

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // This line will be used if you installed PHPMailer via Composer.

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();                                          // Use SMTP
    $mail->Host = 'smtp.gmail.com';                            // Set the SMTP server to use
    $mail->SMTPAuth = true;                                    // Enable SMTP authentication
    $mail->Username = 'your-email@gmail.com';                  // Your Gmail address
    $mail->Password = 'your-app-password';                     // Use your app password if 2FA is enabled
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable STARTTLS encryption
    $mail->Port = 587;                                         // SMTP port for TLS (587)

    //Recipients
    $mail->setFrom('your-email@gmail.com', 'Mailer');          // Sender email address
    $mail->addAddress('recipient@example.com', 'Recipient');   // Recipient email address

    // Content
    $mail->isHTML(true);                                       // Set email format to HTML
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent via PHPMailer using STARTTLS.';
    $mail->AltBody = 'This is the plain text version of the email body.';

    // Send email
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
