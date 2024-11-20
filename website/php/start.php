<?php
// Filename: php/start.php
// Purpose: Only runs once when setting up admin username/password

namespace frakturmedia\clutch;

// Check we have no admin yet
if ($user->count() === 0) {

    // Is the form submitted?
    if (isset($_POST['username'])) {

        // Process submission
        if ($user->createAdmin(
                filter_input(INPUT_POST, 'username'),
                filter_input(INPUT_POST, 'email'),
                filter_input(INPUT_POST, 'password')
        )) {
            echo "<p>Admin user <strong>" . $user->getName() . "</strong> created.<br>Please log-in.</p>";
        } else {
            printAlert("Failed to create admin!");
        }
    } else {
        // Show admin account creation form
        $user->displayAdminRegistration();
    }
} else {
    echo "<p>Administrator has already been created.</p>";
}

// EOF
