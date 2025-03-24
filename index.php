<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="">
        <input type="submit" value="Send email">
    </form>
    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'vendor/autoload.php';  // This line will be used if you installed PHPMailer via Composer.
    
    $mail = new PHPMailer(true);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            //Server settings
            $mail->isSMTP();                                          // Use SMTP
            $mail->Host = 'smtp.gmail.com';                            // Set the SMTP server to use
            $mail->SMTPAuth = true;                                    // Enable SMTP authentication
            $mail->Username = 'justinswenson111@gmail.com';                  // Your Gmail address
            $mail->Password = 'zwpe riuv iqyw cgfn';                     // Use your app password if 2FA is enabled
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable STARTTLS encryption
            $mail->Port = 587;                                         // SMTP port for TLS (587)
        
            //Recipients
            $mail->setFrom('justinswenson111@gmail.com', 'Mailer');          // Sender email address
            $mail->addAddress('justinswenson111@gmail.com', 'Recipient');   // Recipient email address
        
            // Content
            $mail->isHTML(true);                                       // Set email format to HTML
            $mail->Subject = 'Status Update';
            $mail->Body    = 'Your status has been updated.';
            $mail->AltBody = 'This is the plain text version of the email body.';
        
            // Send email
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    
    ?>
</body>
</html>