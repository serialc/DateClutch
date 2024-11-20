<?php
// Filename: php/user/account.php
// Purpose: Displays user's information and some other tools

namespace frakturmedia\clutch;

require_once('../php/classes/mailer.php');

// if user submitted username/email update
if (isset($_POST['email'])) {

    $uname = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    $submit_error = false;

    if (empty($uname)) {
        printAlert("Name cannot be blank.");
        $submit_error = true;
    }

    if (empty($email) or !checkEmailValidity($email)) {
        printAlert("Email not valid.");
        $submit_error = true;
    }

    if (!$submit_error) {
        // update db
        $user->setName($uname);
        $user->setEmail($email);
        $user->save();

        printSuccess("Username and email updated.");
    }
}

// user submitted an invitation
if (isset($_POST['invite_email'])) {

    $iname = filter_input(INPUT_POST, 'invite_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $iemail = filter_input(INPUT_POST, 'invite_email', FILTER_SANITIZE_EMAIL);

    $submit_error = false;

    if (empty($iname)) {
        printAlert("Invitation name cannot be blank.");
        $submit_error = true;
    }

    if (empty($iemail) or !checkEmailValidity($iemail)) {
        printAlert("Invitation email address not valid.");
        $submit_error = true;
    }

    if (!$submit_error) {
        // update db and send email
        $code = User::createInvitation();

        $invite = new Mail();
        if ($invite->sendInvitation($iname, $iemail, $code)) {
            printSuccess("Invitation sent.");
        } else {
            printAlert("Failed to send the invitation.");
        }
    }
}

include('../php/layout/account_form.php');

// EOF
