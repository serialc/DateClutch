<?php
// Filename: php/poll.php
// Purpose: Displays and process participant interaction with polls

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');
require_once('../php/classes/mailer.php');

if (isset($req[1])) {
    $poll_code = $req[1];
    if (!empty($poll_code)) {

        $poll = Poll::fromCode($poll_code);

        if ($poll === false) {
            $log->warning("Someone tried to access the poll with code '$poll_code' but id doesn't exist");
            return;
        }

        $submit_error = false;
        // show the processing results
        if (isset($_POST['pname'])) {

            // check for name
            if (empty(trim($_POST['pname']))) {
                printAlert("Name missing");
                $submit_error = true;
            }

            // check for date selection
            if (!isset($_POST['pdate'])) {
                printAlert("Date selection missing");
                $submit_error = true;
            } else {
                // check the date
                $today = new \DateTime(date('Y-m-d'));
                try {
                    $req_date = new \DateTime($_POST['pdate']);
                } catch (\Exception $e) {
                    printAlert("Date " . $_POST['pdate'] . " is not valid. This shouldn't happen.");
                    $submit_error = true;
                }
            }

            // sanitize pname, pemail, pdate
            $pname = filter_input(INPUT_POST, "pname", FILTER_SANITIZE_SPECIAL_CHARS);
            // check if a (valid) email has been supplied
            $pemail = filter_input(INPUT_POST, "pemail", FILTER_SANITIZE_EMAIL);
            if (!empty($pemail)) {
                if (checkEmailValidity($pemail)) {
                    $valid_email = $pemail;
                }
            }

            if (!$submit_error) {
                if ($poll->addClutcher($pname, $req_date->format('Y-m-d'))) {
                    // DB update succesfull

                    if (isset($valid_email)) {
                        // email confirmation to clutcher
                        $cemail = new Mail();
                        $cemail->sendClutcher($pname, $poll->getTitle(), $req_date->format('Y-m-d'), $valid_email, $poll->getCreatorEmail(), $poll->getCreatorName(), $poll->isPrivacyMode());
                    }

                    // email the poll owner
                    $poemail = new Mail();
                    $poemail->notifyCreator($poll->getCreatorName(), $poll->getTitle(), $pname, $req_date->format('Y-m-d'), $poll->getCreatorEmail());

                    // provide visual feedback
                    echo "<h2>That's done. Thank you.</h2>";
                } else {
                    echo "<h2>That did not work.</h2><h3>Either try again or contact the person responsible for the poll.</h3>";
                }
            }
        }

        // show the form clean or with reported errors
        if (!isset($_POST['pname']) or $submit_error) {
            $poll->display();
        }
    }
} else {
    echo "<h2>Poll not found</h2>";
}

// EOF
