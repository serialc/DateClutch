<?php
// Filename: php/user/create_poll.php
// Purpose: Form to process new polls

namespace frakturmedia\clutch;

use \DateTime;

$submit_error = false;
if (isset($_POST['ptitle'])) {
    $check_required_fields = [
        'ptitle'=>"Poll title",
        'pdescription'=>"Poll description",
        'pdates'=>"Poll dates"
    ];

    // check that all the required fields are submitted
    foreach ( $check_required_fields as $fieldkey => $fieldvalue ) {
        if (strcmp($_POST[$fieldkey], '') === 0) {
            printAlert($fieldvalue . " missing");
            $submit_error = true;
        }
    }

    $ptitle = filter_input(INPUT_POST, "ptitle", FILTER_SANITIZE_SPECIAL_CHARS);
    $pdescription = htmlspecialchars(filter_input(INPUT_POST, "pdescription"), ENT_QUOTES);

    // try parsing dates to see if they are all valid
    $subdates_dirty = $_POST['pdates'];
    // can be on new lines or CSV (or both)
    // convert all EOL to ',' so we have only one delimiter
    $subdates_csv = str_replace(PHP_EOL, ',', $subdates_dirty);
    // convert CSV to array
    $subdates_array = explode(',', $subdates_csv);

    // remove any empty values and check date validities
    $valid_dates_array = [];
    $today = new \DateTime(date('Y-m-d'));
    foreach ( $subdates_array as $datestr) {
        $datestr = trim($datestr);
        if (empty($datestr)) {
            continue;
        }

        try {
            $req_date = new \DateTime($datestr);
        } catch (\Exception $e) {
            //var_dump($e->getMessage());
            printAlert("Date " . $datestr . " is not valid. Perhaps the format is wrong.");
            $submit_error = true;
            continue;
        }

        if( DateTime::getLastErrors() ){
            printAlert("Date " . $datestr . " is not valid. Perhaps the format is wrong.");
            $submit_error = true;
            continue;
        }

        if ($req_date <= $today) {
            printAlert("Date " . $datestr . " has passed.");
            $submit_error = true;
            continue;
        }

        array_push($valid_dates_array, $req_date);
    }

    // Email parsing/validation
    $valid_notify_array = [];

    // only evaluate emails if there are some
    if(!empty(trim($_POST['pnotifications']))) {
        foreach (explode(',', $_POST['pnotifications']) as $nemail) {
            $san_nemail = filter_var($nemail, FILTER_SANITIZE_EMAIL);
            if (checkEmailValidity($san_nemail)) {
                array_push($valid_notify_array, filter_var($nemail, FILTER_SANITIZE_EMAIL));
            } else {
                printAlert("Email " . $nemail . " is not valid.");
                $submit_error = true;
            }
        }
    }

    // generate random code for poll URL
    $url_code = getRandomCode(64);

    // ok, no errors - add to DB
    if (!$submit_error) {
        $db = new DataBaseConnection();
        if($db->createPoll($user->getId(), $ptitle, $url_code, $pdescription, $valid_dates_array, $valid_notify_array)) {

            $poll_url = 'http://' . $_SERVER['SERVER_NAME'] . '/poll/' . $url_code;
            $poll_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll/' . $url_code;

            // show the results of form processing
            echo "<h2>Poll created</h2>";
            echo '<div class="row"><div class="col fs-5">';
            echo '<div>Visit the <a href="' . $poll_url . '">poll</a></div>';
            echo '<div>Share the poll <button data="' . $poll_url . '" class="btn btn-sm btn-flashes" onclick="CLU.copyUrl(event)">Copy URL</button></div>';
            echo '<div>Edit the <a href="' . $poll_edit_url . '">poll</div>';
            echo "</div></div>";
        } else {
            printAlert("Failed to submit data to DB. Contact the developer.");
            $submit_error = true;
        }
    }
}

// show the form if not a submission or there are errors
if (!isset($_POST['ptitle']) or $submit_error) {
    include('../php/layout/user/new_poll.php');
}

// EOF
