<?php
// Filename: polls.php
// Purpose: Class file. Handles poll related tasks

namespace frakturmedia\clutch;

class Poll
{
    # define private variables here
    private $db;
    private $uid;
    private $pid;
    private $title;
    private $code;
    private $admin_code;
    private $description;
    private $dates;
    private $enhanced_privacy;
    private $contains_dt;

    private $creator_email;
    private $creator_name;
    private $creator_status;

    public function __construct()
    {
        $this->db = new DataBaseConnection();
    }

    public function __destruct()
    {
        unset($this->db);
    }

    public static function delete ($uid, $pid )
    {
        return (new self())->db->pollDelete($uid, $pid);
    }
    
    public static function addDate ($uid, $pid, $date )
    {
        return (new self())->db->addPollDate($uid, $pid, $date);
    }

    public static function deleteClutcher ( $uid, $pid, $date )
    {
        return (new self())->db->deletePollDateClutcher($uid, $pid, $date);
    }

    public static function deleteDate ( $uid, $pid, $date )
    {
        return (new self())->db->deletePollDate($uid, $pid, $date);
    }

    public static function getUserList( $uid )
    {
        return (new self())->db->getUserPolls($uid);
    }

    public static function fromAdminCode( $admin_poll_code )
    {
        $instance = new self();
        if ($instance->retrieveByCode($admin_poll_code, 'admin')) {
            return $instance;
        }
        return false;
    }

    public static function listForUser( $uid )
    {
        $instance = new self();
        $polls = $instance->getUserList( $uid );

        echo "<h2>Your polls</h2>";
        echo '<div class="row"><div class="col">';
        foreach ($polls as $poll) {
            $poll_url = 'http://' . $_SERVER['SERVER_NAME'] . '/poll/' . $poll['code'];
            $poll_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll/' . $poll['code'];
            $poll_admin_url = 'http://' . $_SERVER['SERVER_NAME'] . '/results/' . $poll['admin_code'];

            echo '<p id="pid_' . $poll['pid'] . '"><a href="' . $poll_url . '">' . $poll['title'] . '</a> - <a href="' . $poll_admin_url . '">Results</a> ';
            echo ' <a href="#/" title="Delete poll" data-bs-toggle="modal" data-bs-target="#mainModal"><i class="fa fa-trash" aria-hidden="true" action="delete_poll" pid="' . $poll['pid'] . '" poll_title="' . $poll['title'] . '"></i></a></p>';
        }
        echo '</div></div>';
    }

    public static function fromCode( $poll_code )
    {
        $instance = new self();
        if ($instance->retrieveByCode($poll_code, 'public')) {
            return $instance;
        }
        return false;
    }

    public function retrieveByPid ($poll_id)
    {
        $poll = $this->db->retrievePollFromPid($poll_code);
    }

    public function retrieveByCode ($poll_code, $type)
    {
        switch ($type) {
        case "public":
            $poll = $this->db->retrievePollFromCode($poll_code);
            break;

        case "admin":
            $poll = $this->db->retrievePollFromAdminCode($poll_code);
            break;
        }

        // if the poll details were retrieved, then also get the dates
        if ($poll !== false) {

            $this->fillDetails($poll);

            $poll_dates = $this->db->retrievePollDates($this->pid);
            $this->fillDates($poll_dates);

            return true;
        }

        echo '<h2>Requested poll does not exist.</h2>';
        echo '<p>Perhaps it once did?<br>Perhaps the link was incorrectly copied?<br>Contact the person whom provided you the link.</p>';
        return false;
    }

    private function fillDetails ($db_data)
    {
        $this->pid = $db_data['pid'];
        $this->uid = $db_data['uid'];
        $this->title = $db_data['title'];
        $this->code = $db_data['code'];
        $this->admin_code = $db_data['admin_code'];
        $this->description = $db_data['description'];
        // convert 0/1 to false/true
        $this->enhanced_privacy = $db_data['privacy'] == 1;
    }

    public function getCleanDate ($date)
    {
        if ($this->contains_dt) {
            // we don't want seconds to appear unless some nut is using them
            return preg_replace('/:00$/', '', $date->format('Y-m-d H:i:s'));
        }
        return $date->format('Y-m-d');
    }

    private function fillDates ($pdates)
    {
        $this->dates = [];
        $this->contains_dt = false;

        foreach( $pdates as $pd ) {
            $this->dates[$pd['pdate']] = $pd['clutcher'];

            // check all the 'dates' are Y-m-d 'dates' at (midnight) or date-time (not midnight)
            $thisdate = new \DateTime($pd['pdate']);
            if ( $thisdate != (new \DateTime($thisdate->format('Y-m-d'))) ) {
                $this->contains_dt = true;
            }
        }
    }
        
    public function display ()
    {
        global $user;

        echo '<form action="" method="post">';
        echo '<div class="row"><div class="col">';

        // check if this poll is being viewed by it's owner/creator
        if ( $user->getId() === $this->uid ) {
            $poll_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll/' . $this->code;
            $poll_dates_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll_results/' . $this->code;

            echo '<p class="text-end accent3">' .
                '<a href="' . $poll_edit_url . '" title="Edit poll details"><i class="fa fa-pencil" aria-hidden="true"></i></a>' .
                ' <a href="' . $poll_dates_edit_url . '" title="Edit dates and results"><i class="fa fa-users" aria-hidden="true"></i></a>' .
                '</p>';
        }
        echo '<h2>' . $this->title . '</h2>';

        $Parsedown = new \Parsedown();
        echo $Parsedown->text(htmlspecialchars_decode($this->description));

        // Get name of participant
        echo '<h3 class="mt-5">Provide your name</h3>';
        echo '<div class="col-12 input-group mb-3">' .
            '<span for="pname" class="input-group-text">Name</span>' .
            '<input type="text" class="form-control" autofocus="autofocus" id="pname" name="pname" maxlength="128" value="' . (isset($_POST['pname']) ? $_POST['pname'] : '') . '">' .
            '</div>';


        // show available dates
        echo '<h3 class="mt-5">Select a date</h3>';
        // convert back to date objects to perform some features with them
        // - only show available dates (not clutched)
        // - only show dates after today
        // - sort remaining dates
        $todaynow = new \DateTime(date('Y-m-d H:i:s'));
        $dates = [];

        // Does the following things:
        // 1 - Only displays date(times) in the future
        // 2 - Only displays date(times) available
        foreach ($this->dates as $dt_str => $clutcher) {

            // skip clutched dates
            if (!empty($clutcher)) {
                continue;
            };

            $thisdate = new \DateTime($dt_str);

            // skip datetime if in past
            if ($thisdate <= $todaynow) {
                continue;
            }

            // add to list of valid dates
            array_push($dates, $thisdate);
        }

        // Sorted dates
        sort($dates);

        // show dates
        echo '<div class="row">';

        if (count($dates) === 0) {
            echo '<div class="col">';
            printAlert("Unfortunately no dates are available. If this is unexpect, contact the person who sent you the poll.");
            echo '</div>';
        }

        $year = '';
        $month = '';

        foreach ($dates as $date) {

            $this_year = $date->format('Y');
            if ( strcmp($year, $this_year) !== 0 ) {
                $year = $this_year;
                echo '<h1 class="mb-0"><small>' . $year . '</small></h1>';
            }

            $this_month = $date->format('F');
            if ( strcmp($month, $this_month) !== 0 ) {
                $month = $this_month;
                echo '<h2 class="mt-2"><small>' . $month . '</small></h2>';
            }

            $date_str = $date->format('Y-m-d H:i:s');

            // was this date selected/checked?
            $checked = (isset($_POST['pdate']) and strcmp($_POST['pdate'], $date_str) == 0) ? 'checked' : '';
            echo '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">' .
                '<input type="radio" class="btn-check" name="pdate" ' .
                'value="' . $date_str . '" id="' . $date_str . '" autocomplete="off" ' .
                $checked . '><label class="btn btn-outline-success w-100" for="' . $date_str . '">' .
            $date->format('l, jS') . ($this->contains_dt ? '<br>' . preg_replace('/:00$/', '', $date->format('H:i:s')) : '') . '</label></div>';

        }
        echo '</div>';

        // retrieve notification email addresses
        echo '<h3 class="mt-5">Would you like a confirmation email?</h3>';
        echo '<div class="col-12 input-group mb-0">' .
            '<span for="pemail" class="input-group-text">Email</span>' .
            '<input type="email" class="form-control" id="pemail" name="pemail" aria-lable="emailHelp" maxlength="128" value="' . (isset($_POST['pemail']) ? $_POST['pemail'] : '') . '">' .
            '</div>' .
            '<divid="emailHelp" class="form-text text-muted fs-6 mb-3">Optional. Not stored. Only used to send you a confirmation email with your date selection.</div>';

        // Display the submit button
        echo '<div class="text-end">' .
            '<button type="submit" class="btn mt-3">Submit</button>' .
            '</div>';
        echo '</div></div>';
        echo '</form>';
    }

    public function displayEditing ()
    {
        global $user;

        // determine if we are in new or edit mode
        $edit_mode = false;
        if ( isset($this->uid) ) {
            $edit_mode = true;
        }

        // check that the user is allowed to edit this poll
        if ( $edit_mode and $user->getId() !== $this->uid ) {
            printAlert("You do not have access to this poll.");
            return;
        }

        $submit_error = false;
        // process if there's a submission
        if (isset($_POST['ptitle'])) {
            $submit_error = $this->parseEditSubmission();
        }

        // show form if we haven't submitted data yet
        // this is for new polls and editing polls
        if (!isset($_POST['ptitle'])) {
            // haven't implemented notifications (the '' parameter) yet 
            $this->displayEditForm($edit_mode, $this->title, $this->description, $this->dates, '', $this->enhanced_privacy);
        }
        // show POSTed form that has errors
        if ($submit_error) {
            $this->displayEditForm($edit_mode, $_POST['ptitle'], $_POST['pdescription'], $_POST['pdates'], '', isset($_POST['enprivacy']) );
        }
    }

    public function displayResponsesEditing ()
    {
        global $user;

        // check that the user is allowed to edit this poll
        if ( $user->getId() !== $this->uid ) {
            return;
        }

        // show the new date creator option
        echo '<div class="row mb-2"><div class="col-12 mb-2">';
        echo '<h3>' . $this->getTitle() . '</h3>';
        echo '<p>Go to the <a href="' . $this->getPublicUrl() . '">poll</a></p>';
        echo '<div class="input-group daterow rounded p-1">';
        $jscode_newdate = "CLU.dateAlter('add_date', {'pid':" . $this->pid . ",'date':document.getElementById('new_date').value})";
        echo '<label for="new_date" class="input-group-text">Add new date</label>' .
            '<input type="datetime-local" class="form-control" id="new_date" name="new_date">' .
            '<button class="btn" type="button" onclick="' . $jscode_newdate . '">Add</button>';
        echo '</div></div></div>';

        // Show response dates and clutchers
        echo '<div class="row mb-2">';
        foreach($this->dates as $date => $clutcher) {

            $thisdate = new \DateTime($date);
            $clean_date = $this->getCleanDate($thisdate);

            echo '<div id="date_' . $clean_date . '"class="col-lg-3 col-md-4 col-sm-6 mb-3"><div class="daterow rounded p-1 h-100">';

            echo '<a href="#/" title="Delete date" data-bs-toggle="modal" data-bs-target="#mainModal"><i class="fa fa-trash" aria-hidden="true" action="delete_date" pid="' . $this->pid . '" date="' . $clean_date . '"></i></a> ' . $clean_date . '<br>';

            if(!empty($clutcher)) {
                echo '<span id="clutcher_' . $clean_date . '"><a href="#/" title="Erase clutcher" data-bs-toggle="modal" data-bs-target="#mainModal"><i class="fa fa-eraser" aria-hidden="true" action="delete_clutcher" pid="' . $this->pid . '" date="' . $clean_date . '" clutcher="' . $clutcher . '"></i></a> ' . $clutcher . '</span>';
            }
            echo '</div></div>';
        }
        echo '</div>';
    }

    private function parseEditSubmission ()
    {
        global $user;

        $submit_error = false;

        $check_required_fields = [
            'ptitle'=>"Poll title",
            'pdescription'=>"Poll description",
            'pdates'=>"Poll dates"
        ];

        // check that all the required fields are submitted
        foreach ( $check_required_fields as $fieldkey => $fieldvalue ) {
            // skip pdates if editing
            if (strcmp($fieldkey, 'pdates') === 0 and !empty($this->pid)) {
                continue;
            }

            if (empty($_POST[$fieldkey])) {
                printAlert($fieldvalue . " missing.");
                $submit_error = true;
            }
        }

        $ptitle = trim(filter_input(INPUT_POST, "ptitle", FILTER_SANITIZE_SPECIAL_CHARS));
        $pdescription = htmlspecialchars(filter_input(INPUT_POST, "pdescription"), ENT_QUOTES);

        // Only new polls submit pdates, not editing
        $valid_dates_array = [];
        if (isset($_POST['pdates'])) {
            $valid_dates_array = $this->validateRawDates();
            if ($valid_dates_array == false) {
                $submit_error = true;
            }
        }

        // Email parsing/validation
        $valid_notify_array = [];

        // only evaluate emails if there are some
        if(isset($_POST['pnotifications']) and !empty(trim($_POST['pnotifications']))) {
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

        // is it an enhanced privacy poll?
        $enhanced_privacy = false;
        if (isset($_POST['enprivacy'])) {
            $enhanced_privacy = true;
        }

        // If everything looks good - copy to instance
        if (!$submit_error) {
            $this->title = $ptitle;
            $this->description = $pdescription;
            $this->enhanced_privacy = $enhanced_privacy;

            // generate random code for poll URL if need
            if (empty($this->code)) {
                $this->code = getRandomCode(64);
                $this->admin_code = getRandomCode(64);
            }

            // If this is a new poll
            if (empty($this->pid)) {
                if($this->db->createPoll($user->getId(), $this->title, $this->code, $this->admin_code, $this->description, $this->enhanced_privacy, $valid_dates_array, $valid_notify_array)) {
                    echo '<h2>Created poll</h2>';
                    $this->showLinks();
                } else {
                    printAlert("Failed to submit data to DB. Contact the developer.");
                    return false;
                }
            } else {
                // If the poll is being edited
                if($this->db->editPoll($user->getId(), $this->pid, $this->title, $this->description, $this->enhanced_privacy)) {
                    printSuccess("Poll updated.");
                    $this->showLinks();
                } else {
                    printAlert("Poll update failed! (Likely because you made no changes)");
                }
            }
        }

        return $submit_error;
    }

    private function displayEditForm ($edit_mode, $ptitle, $pdescription, $pdates, $pnotifications, $enhanced_privacy)
    {
        // form and title input
        if ($edit_mode) {
            echo '<h2>Edit poll</h2>';
        } else {
            echo '<h2>New poll</h2>';
        }

        echo <<< _END
            <form action="" method="post">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="ptitle" class="form-label">Title</label>
                        <input type="text" class="form-control" autofocus="autofocus" id="ptitle" name="ptitle" maxlength="128" aria-describedby="ptitle" value="
        _END;
        echo $ptitle . '"></div>';

        // poll description input
        echo <<< _END
            <div class="col-12 mb-3">
                <label for="pdescription" class="form-label">Description</label>
                <textarea id="pdescription" name="pdescription" class="form-control" aria-label="descriptionHelp" rows="5">
        _END;

        echo $pdescription . '</textarea>';

        echo <<< _END
                <small id="descriptionHelp" class="form-text text-muted">You can enter markdown to format text. What is <a href="https://www.markdowntutorial.com/" target="_blank">markdown</a>?</small>
            </div>
        _END;

        // DATES - show textarea for new polls, editable results for edits
        echo '<div class="col-12 mb-3"><label for="pdates" class="form-label">Dates</label>';

        if ($edit_mode) {
            $poll_responses_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll_results/' . $this->code;
            echo '<div><a href="' . $poll_responses_url . '">Edit dates</a></div>';
        } else {
            // Show textarea input
            echo '<textarea id="pdates" name="pdates" class="form-control mb-0" aria-label="datesHelp" rows="5">' . $pdates . '</textarea>';
            echo '<small id="datesHelp" class="form-text text-muted">Enter either dates (YYYY-MM-DD) or date-times (YYYY-MM-DD HH:MM) in rows or comma-separated.</small>';
        }

        echo '</div>';

        // Notifications - disabled currently
        echo <<< _END
            <div class="col-12 mb-3">
                <label for="pnotifications" class="form-label">Notify</label>
                <input type="text" class="form-control" placeholder="Disabled" id="pnotifications" name="pnotifications" maxlength="256" disabled aria-describedby="notifHelp" value="
        _END;

        echo $pnotifications . '">';

        echo <<< _END
                <small id="notifHelp" class="form-text text-muted">Provide comma separated email addresses of people to be notified when someone submits a poll reponse.</small>
            </div>
        _END;

        // make enhanced privacy available
        echo <<< _END
            <div class="col-12 mt-3 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="enprivacy" value="" name="enprivacy" aria-describedby="notifyEnSecurity"
        _END;
        echo ($enhanced_privacy ? 'checked' : '') . '>';
        echo <<< _END
                    <label class="form-check-label" for="enprivacy">Enhanced privacy</label><br>
                    <small id="notifEnSecurity" class="form-text text-muted">Respondent names will not be stored on the server, only emailed to you.</small>
                </div>
        _END;

        // submit button and closing html elements
        echo <<< _END
                        <div class="text-end">
                            <button type="submit" class="btn mt-3">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        _END;

    }

    public function addClutcher ($clutcher, $date)
    {
        if ($this->enhanced_privacy) {
            return $this->db->setPollClutcher($this->pid, ANONYMOUS_NAME, $date);
        }
        return $this->db->setPollClutcher($this->pid, $clutcher, $date);
    }

    private function validateRawDates ()
    {

        // try parsing dates to see if they are all valid
        $subdates_dirty = $_POST['pdates'];

        // can be on new lines or CSV (or both)
        // convert all EOL to ',' so we have only one delimiter
        $subdates_csv = str_replace(PHP_EOL, ',', $subdates_dirty);
        // convert CSV to array
        $subdates_array = explode(',', $subdates_csv);

        // remove any empty values and check date validities
        $valid_dates_array = [];
        $todaynow = new \DateTime(date('Y-m-d H:i:s'));

        $submit_error = false;
        foreach ( $subdates_array as $datestr) {
            $datestr = trim($datestr);
            if (empty($datestr)) {
                continue;
            }

            try {
                $req_date = new \DateTime($datestr);
            } catch (\Exception $e) {
                printAlert("Date " . $datestr . " is not valid. Perhaps the format is wrong.");
                $submit_error = true;
                continue;
            }

            if( \DateTime::getLastErrors() ){
                printAlert("Date " . $datestr . " is not valid. Perhaps the format is wrong.");
                $submit_error = true;
                continue;
            }

            if ($req_date <= $todaynow) {
                printAlert("Date " . $datestr . " has passed.");
                $submit_error = true;
                continue;
            }

            if (in_array($req_date, $valid_dates_array)) {
                printAlert("Date " . $datestr . " is duplicated. Remove one instance.");
                $submit_error = true;
            }

            array_push($valid_dates_array, $req_date);
        }

        // do not return data, only false if there's a problem
        if ($submit_error) {
            return false;
        }

        return $valid_dates_array;
    }

    private function showLinks ()
    {
        $poll_url = 'http://' . $_SERVER['SERVER_NAME'] . '/poll/' . $this->code;
        $poll_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll/' . $this->code;
        $poll_results_url = 'http://' . $_SERVER['SERVER_NAME'] . '/results/' . $this->admin_code;

        // show the results of form processing
        echo '<h3>' . $this->title . '</h2>';
        echo '<div class="row"><div class="col fs-5">';
        echo '<div>Visit the <a href="' . $poll_url . '">poll</a></div>';
        echo '<div>Share the poll <button data="' . $poll_url . '" class="btn btn-sm btn-flashes" onclick="CLU.copyUrl(event)">Copy URL</button></div>';
        echo '<div>Edit the <a href="' . $poll_edit_url . '">poll</a></div>';
        echo '<div>See the <a href="' . $poll_results_url . '">results</a></div>';
        echo "</div></div>";
    }

    public function getTitle ()
    {
        return $this->title;
    }

    public function isPrivacyMode ()
    {
        return $this->enhanced_privacy;
    }

    public function getPublicCode ()
    {
        return $this->code;
    }

    public function getPublicUrl ()
    {
        return 'http://' . $_SERVER['SERVER_NAME'] . '/poll/' . $this->code;
    }

    private function getDatesString ()
    {
        $dates_str = "";
        foreach ($this->dates as $date => $clutcher) {
            $dates_str .= $date . "\n";
        }
        return trim($dates_str);
    }

    public function getCreatorEmail ()
    {
        if (empty($this->creator_email)) {
            $this->fillCreator();
        }
        return $this->creator_email;
    }
    public function getCreatorName ()
    {
        if (empty($this->creator_name)) {
            $this->fillCreator();
        }
        return $this->creator_name;
    }

    private function fillCreator ()
    {
        $creator = $this->db->getUser($this->uid);
        $this->creator_name = $creator['username'];
        $this->creator_email = $creator['email'];
        $this->creator_status = $creator['status'];
    }

    public function displayResults ()
    {
        echo '<div class="row mb-2">';
        $clutchers_count = 0;
        foreach ($this->dates as $date => $clutcher) {

            // only show dates/dt that have been clutched
            if( !empty($clutcher) ) {

                $thisdate = new \DateTime($date);

                echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-3"><div class="daterow rounded p-2">';
                echo $this->getCleanDate($thisdate) . '<br>' . $clutcher;
                echo '</div></div>';
                $clutchers_count += 1;
            }
        }
        echo '</div>';

        echo '<p>' . $clutchers_count . ' people have clutched a date</p>';
        echo ($clutchers_count === 0) ? '<p class="accent1">Don\'t let that make you sad</p>' : "";
    }
}
