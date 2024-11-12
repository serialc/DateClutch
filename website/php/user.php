<?php
// Filename: php/user/user.php
// Purpose: Allows display of user content to users

// if user is logged in
if ($user->getStatus() >= MEMBER_STATUS_BASIC) {
    switch ($req[1]) {
    case 'create':
        include '../php/user/create_poll.php';
        break;

    case 'polls':
        include '../php/user/polls.php';
        break;

    default:
        echo '<div class="row"><div class="col"><h2>Error</h2><p>Unknown user menu option selected.</p></div></div>';
    }
} else {
    echo '<div class="row"><div class="col"><h2>Access denied</h2><p>You likely need to login to complete this action.</p></div></div>';
}

// EOF
