<?php
// Filename: php/pw_reset.php
// Purpose: Shows the email form, new pw form (from link), processes new pw

namespace frakturmedia\clutch;

require_once('../php/classes/mailer.php');

//print_r($req);
//print_r($_POST);

if (isset($req[1])) {
    // user has clicked their link from their email or submitted a new password
    if (isset($_POST['password'])) {
        // handle the submission of new password
        // check the url code 
        $new_passwd = $_POST['password'];
        $code = $req[1];
    
        if ($user->updatePasswordFromCode($new_passwd, $code)) {
            echo '<h2>That worked</h2><p>Try logging in!</p>';
        } else {
            echo '<h2>Game over!</h2><p>That did not work.<br>Perhaps the link timed out.<br>The link only works within 10 minutes of being sent to you.</p>';
            echo '<p><a href="' . $_SERVER['SERVER_NAME'] . '/password_reset">Try again</a>?</p>';
        }
    } else {
        // show the new password submission form
        include('../php/layout/new_pw_form.php');
    }

} else {
    if (isset($_POST['email'])) {
        // check email validity and display results
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $uid = $user->getIdFromEmail($email);

        if ( $uid === false ) {
            // password doesn't exist in database
            $log->info("Password reset requested for email address that does not exist: $email.");
        } else {
            // exists in database
            $log->info("Password reset requested for valid email address: $email.");

            // set the temporary reset code and dt for the user in question
            $ur_code = $user->createResetCode($uid);

            if ($ur_code === false) {
                $log->error("Failed to set user reset code for uid=$uid.");
            } else {
                // send the email with the link
                $reset_url = 'http://' . $_SERVER['SERVER_NAME'] . '/password_reset/' . $ur_code;

                $pwr_email = new Mail();
                $pwr_email->sendPasswordResetUrl($email, $user->getUsernameFromEmail($email), $reset_url);
            }
        }
            
        // Confirm
        echo "<h2>Request received</h2>";
        echo "<p>If we find your account we will email you a password reset link.</p>";
        echo "<p>Check your email, click the link, reset your password.</p>";
    }

    if (!isset($_POST['email'])) {
        // show the email form
        include('../php/layout/pw_reset_email_form.php');
    }
}


// EOF
