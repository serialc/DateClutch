<?php
// Filename: polls.php
// Purpose: handle poll related tasks

// Need to transition some other code to here

namespace frakturmedia\clutch;

class Poll
{
    # define private variables here
    private $db;
    private $uid;
    private $pid;
    private $title;
    private $code;
    private $description;
    private $dates;

    public function __construct()
    {
        $this->db = new DataBaseConnection();
    }

    public function __destruct()
    {
        unset($this->db);
    }

    public static function getUserList( $uid )
    {
        return (new self())->db->getUserPolls($uid);
    }

    public static function fromCode( $poll_code )
    {
        $instance = new self();
        $instance->retrieveByCode($poll_code);
        return $instance;
    }

    public function retrieveByCode ($poll_code)
    {
        $poll = $this->db->retrievePollFromCode($poll_code);
        $this->fillDetails($poll);
        $poll_dates = $this->db->retrievePollDates($this->pid);
        $this->fillDates($poll_dates);
    }

    private function fillDetails ($db_data)
    {
        $this->pid = $db_data['pid'];
        $this->uid = $db_data['uid'];
        $this->title = $db_data['title'];
        $this->code = $db_data['code'];
        $this->description = $db_data['description'];
    }

    private function fillDates ($pdates)
    {
        $this->dates = [];
        foreach( $pdates as $pd ) {
            $this->dates[$pd['pdate']] = $pd['clutcher'];
        }
    }
        
    public function display ()
    {

        echo '<form action="" method="post">';
        echo '<div class="row"><div class="col">';
        echo '<h2>' . $this->title . '</h2>';

        $Parsedown = new \Parsedown();
        echo $Parsedown->text(htmlspecialchars_decode($this->description));

        // Get name of participant
        echo '<h3>Provide your name and date selection</h3>';
        echo '<div class="col-12 input-group mb-3">' .
            '<span for="pname" class="input-group-text">Name</span>' .
            '<input type="text" class="form-control" autofocus="autofocus" id="pname" name="pname" maxlength="128" value="' . (isset($_POST['pname']) ? $_POST['pname'] : '') . '">' .
            '</div>';

        echo '<div class="col-12 input-group mb-0">' .
            '<span for="pemail" class="input-group-text">Email</span>' .
            '<input type="email" class="form-control" id="pemail" name="pemail" aria-lable="emailHelp" maxlength="128" value="' . (isset($_POST['pemail']) ? $_POST['pemail'] : '') . '">' .
            '</div>' .
            '<divid="emailHelp" class="form-text text-muted fs-6 mb-3">Optional. Not stored. Only used to send you a selection confirmation email.</div>';


        // show available dates
        // convert back to date objects to perform some features with them
        // - only show available dates (not clutched)
        // - only show dates after today
        // - sort remaining dates
        $today = new \DateTime(date('Y-m-d'));
        $dates = [];
        foreach ($this->dates as $datestr => $clutcher) {
            // skip clutched dates
            if (!empty($clutcher)) {
                continue;
            };

            $thisdate = new \DateTime($datestr);
            // skip if today or in past
            if ($thisdate <= $today) {
                continue;
            }
            
            // add to list of valid dates
            array_push($dates, $thisdate);
        }

        # Sorted dates
        sort($dates);

        # show dates
        echo '<div class="row">';
        $year = '';
        $month = '';
        foreach ($dates as $date) {

            $this_year = $date->format('Y');
            if ( strcmp($year, $this_year) !== 0 ) {
                $year = $this_year;
                echo '<h1>' . $year . '</h1>';
            }

            $this_month = $date->format('F');
            if ( strcmp($month, $this_month) !== 0 ) {
                $month = $this_month;
                echo '<h2>' . $month . '</h2>';
            }

            $date_str = $date->format('Y-m-d');
            // was this date selected/checked?
            $checked = (isset($_POST['pdate']) and strcmp($_POST['pdate'], $date_str) == 0) ? 'checked' : '';
            echo '<div class="col-lg-2 col-md-4 col-sm-6 mb-1">' .
                '<input type="radio" class="btn-check" name="pdate" ' .
                'value="' . $date_str . '" id="' . $date_str . '" autocomplete="off" ' .
                $checked . '><label class="btn btn-outline-success w-100" for="' . $date_str . '">' .
            $date->format('l, jS') . '</label></div>';

        }
        echo '</div>';

        # Display the submit button
        echo '<div class="text-end">' .
            '<button type="submit" class="btn mt-3">Submit</button>' .
            '</div>';
        echo '</div></div>';
        echo '</form>';
    }
}
