<?php
// Filename: register.php
// Purpose: People register using an invitation code

namespace frakturmedia\clutch;

// Is the form submitted?
if ( isset($req[1]) and isset($_POST['username']) ) {

    $inv_code = $req[1];

    if (User::evaluateInvitation($inv_code)) {
    
        // Process submission
        if ($user->create(
                filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
                filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                MEMBER_STATUS_CREATOR,
                filter_input(INPUT_POST, 'password')
        )) {
            echo "<p>Account <strong>" . $user->getName() . "</strong> created.<br>Please log-in.</p>";
        } else {
            printAlert("Failed to create account!");
        }
    } else {
        printAlert("Registration failed due to invalid/expired invitation.");
    }

} else {
    // Show admin account creation form
    $user->displayInviteeRegistration();
}

// EOF
