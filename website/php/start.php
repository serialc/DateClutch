<?php
// Filename: start.php
// Purpose: Only runs once when setting up admin username/password

namespace frakturmedia\clutch;

// Check we have no admin yet
if ($user->count() === 0) {

    // Is the form submitted?
    if (isset($_POST['adminUsername'])) {

        // Process submission
        if ($user->createAdmin(
                filter_input(INPUT_POST, 'adminUsername'),
                filter_input(INPUT_POST, 'adminEmail'),
                filter_input(INPUT_POST, 'inputPassword')
        )) {
            echo "<p>Admin user <strong>" . $user->getName() . "</strong> created.<br>Please log-in.</p>";
        } else {
            printAlert("Failed to create admin!");
        }
    } else {
        // Show admin account creation form
        require_once '../php/layout/create_admin_form.html';
    }
} else {
    echo "<p>Administrator has already been created.</p>";
}

// EOF
