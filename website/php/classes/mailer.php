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

        // Reply-to info
        // Don'change to an email from another domain
        // receiving servers won't accept it
        $this->mail->setFrom(EMAIL_REPLYTO, EMAIL_REPLYTONAME);

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

    public function send($email, $to_name, $subject, $html, $text, $anon_logging)
    {
        global $log;

        try {

            // Recipients
            $this->mail->addAddress($email, $to_name);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $html;
            $this->mail->AltBody = $text;

            $this->mail->send();
            $log->info('Sent email to ' . ($anon_logging ? 'someone' : $to_name) . ' with subject "$subject"');
        } catch (Exception $e) {
            $log->error("Failed to send an email. Mailer error: {$this->mail->ErrorInfo}");
            return false;
        }

        return true;
    }

    public function notifyCreator ($name, $title, $clutcher_name, $date, $email)
    {
        global $log;

        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Dear <strong style="color: #A836FF">' . $name .
            '</strong></span>,<p>' . "\n" .
            '<p><stron>' . $clutcher_name . '</strong> just clutched the strong date of <strong style="color: #36FFA8">' . $date . '</strong> for the ' .
            '<span style="color: #FFA836;"><strong>DateClutch</strong></span> poll titled <strong>"' . $title . '"</strong>.</p>' . "\n" .
            '<p style="color: #dee2e6;">Keep up the good work.</p>' . "\n" .
            '</center></body></html>';

        $text = strip_tags($html);

        if( !$this->send($email, $name, $title, $html, $text, false) ) {
                $log->error("Failed to send email notification to " . $email . ' for ' . $title . '.');
        }
    }

    public function sendClutcher ($name, $title, $date, $email, $reply_to_email, $reply_to_name, $privacy_mode)
    {
        global $log;

        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Thank you <strong style="color: #A836FF">' . $name .
            '</strong></span> for clutching the strong date of <strong style="color: #36FFA8">' . $date . '</strong>.' . "\n" . '<p>Good choice!</p>' . "\n" .
            '<p style="color: #dee2e6;">Your date choice for the ' .
            '<span style="color: #FFA836;"><strong>DateClutch</strong></span> poll titled <strong>"' . $title . '"</strong> has been shared with the creators.</p>' . "\n" .
            '<p style="color: #dee2e6;">We hope that was easy.</p>' . "\n" .
            '<p style="color: #dee2e6;">If you have questions or concerns please contact the creator of the poll, ' . $reply_to_name . ', at the email address ' . $reply_to_email . '.</p>' . "\n" .
            '</center></body></html>';

        $text = strip_tags($html);

        if( !$this->send($email, $name, $title, $html, $text, $privacy_mode) ) {
            $log->error("Failed to send email notification to " .
                ($privacy_mode ? '-redacted due to poll privacy mode-' : $email) . ' for ' . $title . '.');
        }
    }

    public function sendPasswordResetUrl ($email, $name, $reset_url)
    {
        global $log;

        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Well hello<strong style="color: #A836FF"> ' . $name .
            '</strong></span>,</p>' . "\n" .
            '<p style="color: #dee2e6;">You, or someone being naughty, has stated that you have forgotten your <span style="color: #FFA836;"><strong>DateClutch</strong></span> password.</p>' . "\n" .
            '<p style="color: #dee2e6;">Ignore this if it was not you.</p>' . "\n" .
            '<p style="color: #dee2e6;">However, if you <emphasis>have</emphasis> forgotten your password, then you should really consider using a password manager (<a href="https://www.mozilla.org/en-US/firefox/features/password-manager/" style="color: #36ffa8">Firefox/Mozilla</a>, a non-profit, provides one freely!).</p>' . "\n" .
            '<p style="color: #dee2e6;">Anyway, <a href="' . $reset_url . '" style="color: #36ffa8">here is your link</a> to reset your password - if you need it.</p>' . "\n" .
            '<p style="color: #dee2e6;"><a href="' . $reset_url . '" style="color: #36ffa8">' . $reset_url . '</a></p>' . "\n" .
            '</center></body></html>';
        $text = strip_tags($html);

        if( !$this->send($email, $name, "Password reset request", $html, $text, false) ) {
                $log->error("Failed to send password reset email to " . $email . '.');
        }
    }

    public function sendInvitation ($name, $email, $code)
    {
        global $user;

        $reg_url = 'http://' . $_SERVER['SERVER_NAME'] . '/register/' . $code;

        $html = '<html><body style="font-size: 1.3em; background-color: #212529; padding: 10%; color: #dee2e6;"><center>' .
            '<p style="color: #dee2e6;">Dear <strong style="color: #A836FF">' . $name .
            '</strong></span>,<p>' . "\n" .
            '<p><stron>' . $user->getName() . '</strong> ' .
            'is inviting you to join <a href="' . $reg_url . '" style="color: #FFA836;"><strong>DateClutch</strong></a> to create date clutching polls.</p>' . "\n" .
            '<p style="color: #dee2e6;"><a href="' . $reg_url . '" style="color: #36ffa8">Here is your link</a> to register - if you wish to.</p>' . "\n" .
            '<p style="color: #dee2e6;"><a href="' . $reg_url . '" style="color: #36ffa8">' . $reg_url . '</a></p>' . "\n" .
            '<p style="color: #dee2e6;">Keep up the good work.</p>' . "\n" .
            '<p style="color: #dee2e6;">P.S. Your registration invitation self-destructs in 10 days.</p>' . "\n" .
            '</center></body></html>';

        $text = strip_tags($html);

        $title = $user->getName() . ' sent you a DateClutch invitation';

        if( !$this->send($email, $name, $title, $html, $text, false) ) {
            $log->error("Failed to send email invitation to " . $email . ' for ' . $title . '.');
        }
        return true;
    }
}

// EOF
