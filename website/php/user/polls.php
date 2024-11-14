<?php
// Filename: php/user/polls.php
// Purpose: Displays list of polls created by this user

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

$polls = Poll::getUserList($user->getId());

echo "<h2>Your polls</h2>";
echo '<div class="row"><div class="col">';
foreach ($polls as $poll) {
    $poll_url = 'http://' . $_SERVER['SERVER_NAME'] . '/poll/' . $poll['code'];
    $poll_edit_url = 'http://' . $_SERVER['SERVER_NAME'] . '/user/poll/' . $poll['code'];
    $poll_admin_url = 'http://' . $_SERVER['SERVER_NAME'] . '/results/' . $poll['admin_code'];

    echo '<p><a href="' . $poll_url . '">' . $poll['title'] . '</a> [<a href="' . $poll_admin_url . '">Results</a>]</p>';
}
echo '</div></div>';

// EOF
