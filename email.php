<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'vendor/autoload.php';  // This line will be used if you installed PHPMailer via Composer.
    
    $mail = new PHPMailer(true);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $status = $_POST['status'];
        $name = $_POST['applicant'];
        $job = $_POST['job'];
        $company = $_POST['company'];
        $recipient= $_POST['recipient'];

        try {
            //Server settings
            $mail->isSMTP();                                          // Use SMTP
            $mail->Host = 'smtp.gmail.com';                            // Set the SMTP server to use
            $mail->SMTPAuth = true;                                    // Enable SMTP authentication
            $mail->Username = 'nec.careers.az@gmail.com';                  // Your Gmail address
            $mail->Password = 'rtjx oymj rllg zsiu ';                     // Use your app password if 2FA is enabled
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable STARTTLS encryption
            $mail->Port = 587;                                         // SMTP port for TLS (587)
        
            //Recipients
            $mail->setFrom('nec.careers.az@gmail.com', 'NEC careers');          // Sender email address
            $mail->addAddress($recipient, 'Recipient');   // Recipient email address
        
            // Content
            $mail->isHTML(true);                                       // Set email format to HTML
            $mail->Subject = 'Status Update';
            $mail->Body = "Hello $name,<br><br>Your application status at <strong>$job</strong> for <strong>$company</strong> has been updated to <strong>$status</strong>.<br><br>Best regards, NEC Careers Team.";
            $mail->AltBody = 'This is the plain text version of the email body.';
        
            // Send email
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    
    ?>