<?php
// Filename: php/user/user.php
// Purpose: Allows display of user content to users

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

// if user is logged in
if ($user->getStatus() >= MEMBER_STATUS_BASIC) {
    switch ($req[1]) {

    case 'create':
        $poll = new Poll();
        $poll->displayEditing();
        break;

    case 'account':
        include('../php/user/account.php');
        break;

    case 'poll':
        if (isset($req[2])) {
            $poll = Poll::fromCode($req[2]);
            if ($poll) {
                $poll->displayEditing();
            }
        }
        break;

    case 'poll_config':
        if (isset($req[2])) {
            $poll = Poll::fromCode($req[2]);
            if ($poll and $poll->getUid() === $user->getId()) {
                $poll->displayConfiguration();
            } else {
                printAlert("You do not have access to this poll.");        
            }
        }
        break;

    case 'poll_results':
        if (isset($req[2])) {
            $poll = Poll::fromCode($req[2]);
            if ($poll and $poll->getUid() === $user->getId()) {
                $poll->displayResponsesEditing();
            } else {
                printAlert("You do not have access to this poll.");        
            }
        }
        break;

    case 'polls':
        $poll = Poll::listForUser($user->getId());
        break;

    case 'overview':
        $poll = Poll::listPollOverview();
        break;

    default:
        echo '<div class="row"><div class="col"><h2>Error</h2><p>Unknown user menu option selected.</p></div></div>';
    }
} else {
    echo '<div class="row"><div class="col"><h2>Access denied</h2><p>You likely need to login to complete this action.</p></div></div>';
}

// EOF
