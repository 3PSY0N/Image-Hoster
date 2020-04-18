<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Core\Twig;

class Mailer
{
    /**
     * @param string $destination
     * @param array $params
     * @param array $twigParams
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function sendmail(string $destination, array $params = [], array $twigParams = [])
    {
        $mail  = new PHPMailer(false);
        $twig  = new Twig();
        $flash = new Flash();

        try {

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                    // Enable verbose debug output
            $mail->isSMTP();                                       // Send using SMTP
            $mail->Host       = MAIL_HOST;                         // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                              // Enable SMTP authentication
            $mail->Username   = MAIL_SMTP_ACCOUNT;                 // SMTP username
            $mail->Password   = MAIL_SMTP_PASSWORD;                // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;       // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = MAIL_SMTP_PORT;                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom(MAIL_NOREPLY, MAIL_NOREPLY_NAME);
            $mail->addAddress($destination, $destination);     // Add a recipient

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $params['subject'];
            $mail->Body    = $twig->render('/mails/' . $params['template'] . '.twig', $twigParams);

            $mail->send();
            $flash->setFlash('info', "Email has been sent to<br><b>{$destination}</b><br>Please verify your email address.", null, true);
        } catch (Exception $e) {
            $flash->setFlash('danger', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}", null, false);
        }
    }
}