<?php
// Filename: php/poll_results.php
// Purpose: Displays summary of reponses for those with the secret key

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

$admin_poll_code = $req[1];
if (!empty($admin_poll_code)) {
    $poll = Poll::fromAdminCode($admin_poll_code);

    if ($poll === false) {
        return;
    }

    echo '<h2>Poll results</h2>';
    echo '<h3>' . $poll->getTitle() . '</h3>';
    echo '<p>Go to the <a href="' . $poll->getPublicUrl() . '">poll</a></p>';
    
    $poll->displayResults();
}

// EOF
