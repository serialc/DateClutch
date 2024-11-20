<?php

// Handles login and result of login attempt
// Filename: php/login.php

namespace frakturmedia\clutch;

if (isset($_POST['inputPassword'])) {
    /* 
     * there is the login form processing
     * being called in php/preprocess.php
     */ 

    // getName returns false if the user is not logged in
    if (!$user->getName()) {
        // login failed
        printAlert("Incorrect username and/or password!");
    }
}

// show form
include('../php/layout/login_form.html');

// EOF
