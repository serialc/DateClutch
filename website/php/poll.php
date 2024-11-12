<?php
// Filename: php/poll.php
// Purpose: Displays and process participant interaction with polls

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

if (!empty($req[1])) {

    $submit_error = false;
    // show the processing results
    if (isset($_POST['pname'])) {

        // check for name
        if (empty($_POST['pname'])) {
            printAlert("Name missing");
            $submit_error = true;
        }

        // check for date selection
        if (!isset($_POST['pdate'])) {
            printAlert("Date selection missing");
            $submit_error = true;
        }

        print_r($_POST);
    }

    // show the form clean or with reported errors
    if (!isset($_POST['pname']) or $submit_error) {
        $poll = Poll::fromCode($req[1]);
        $poll->display();
    }
} else {
    echo "<h2>Poll not found</h2>";
}

// EOF
