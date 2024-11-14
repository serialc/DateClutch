<?php
// Filename: php/classes/mailer.php
// Purpose: Handles email sending

namespace frakturmedia\clutch;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private $mail;

    public function __construct()
    {
        // Create an instance; passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        // Mail server settings
        // Enable verbose debug output
        //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        // Send using SMTP
        $this->mail->isSMTP();
        $this->mail->Host       = EMAIL_HOST;
        // Enable SMTP authentication
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = EMAIL_SENDER;
        $this->mail->Password   = EMAIL_PASSWORD;
        // Enable implicit TLS encryption
        if ( EMAIL_PORT === 465 ) {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   // port 465
        } else {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // port 587
        }
        // TCP port to connect to
        // Use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->mail->Port       = EMAIL_PORT;
    }

    public function send($email, $to_name, $subject, $html, $text)
    {
        global $log;

        try {
            // Sender proxy info
            $this->mail->setFrom($reply_to_email, $reply_to_name);

            // Recipients
            $this->mail->addAddress($email, $to_name);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $html;
            $this->mail->AltBody = $text;

            $this->mail->send();
            $log->info("Sent email to $to_name with subject \"$subject\"");
        } catch (Exception $e) {
            $log->error("Failed to send an email. Mailer error: {$this->mail->ErrorInfo}");
            return false;
        }

        return true;
    }

    public function notifyCreator ($name, $title, $clutcher_name, $date, $email)
    {
        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Dear <strong style="color: #A836FF">' . $name .
            '</strong></span>,<p>' . "\n" .
            '<p><stron>' . $clutcher_name . '</strong> just clutched the strong date of <strong style="color: #36FFA8">' . $date . '</strong> for the ' .
            '<span style="color: #FFA836;"><strong>DateClutch</strong></span> poll titled <strong>"' . $title . '"</strong>.</p>' . "\n" .
            '<p style="color: #dee2e6;">Keep up the good work.</p>' . "\n" .
            '</center></body></html>';

        $text = strip_tags($html);

        if( !$this->send($email, $name, $title, $html, $text)) {
                $log->error("Failed to send email notification to " . $email . ' for ' . $title . '.');
        }
    }
    public function sendClutcher ($name, $title, $date, $email, $reply_to_email, $reply_to_name)
    {
        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Thank you <strong style="color: #A836FF">' . $name .
            '</strong></span> for clutching the strong date of <strong style="color: #36FFA8">' . $date . '</strong>.' . "\n" . '<p>Good choice!</p>' . "\n" .
            '<p style="color: #dee2e6;">Your date choice for the ' .
            '<span style="color: #FFA836;"><strong>DateClutch</strong></span> poll titled <strong>"' . $title . '"</strong> has been shared with the creators.</p>' . "\n" .
            '<p style="color: #dee2e6;">We hope that was easy.</p>' . "\n" .
            '<p style="color: #dee2e6;">If you have questions or concerns please contact the creator of the poll, ' . $reply_to_name . ', at the email address ' . $reply_to_email . '.</p>' . "\n" .
            '</center></body></html>';

        $text = strip_tags($html);

        if( !$this->send($email, $name, $title, $html, $text, $reply_to_email, $reply_to_name)) {
                $log->error("Failed to send email notification to " . $email . ' for ' . $title . '.');
        }
    }
}

// EOF
