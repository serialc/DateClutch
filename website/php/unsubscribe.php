<?php
// Filename: ../php/unsubscribe.php
// Purpose: Removes email from following a poll

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

$pid = $req[1];
$code = $req[2];

if(Poll::deleteSubscriber ($pid, $code)) {
    print("<h2>You've unsubscribed from the poll!</h2>");
} else {
    print("<h2>Well that didn't work.</h2>");
    print("<p>Perhaps you've already deleted your email?<br>");
    print("Contact your DateClutch administrator if you think there's a problem.</p>");
}

// EOF
