<?php
// Filename: php/user/api.php
// Purpose: Only accessed by trusted users

namespace frakturmedia\clutch;

require_once('../php/classes/polls.php');

// transform input stream - with AJAX there is no $_POST (it's empty)
$post = json_decode(file_get_contents('php://input'), true);

switch ($req[1]) {
case 'add_date':
    if (Poll::addDate($user->getId(), $post['pid'], $post['date'])) {
        echo buildResponse("success");
    } else {
        echo buildResponse("failed");
    }
    break;

case 'delete_date':
    if (Poll::deleteDate($user->getId(), $post['pid'], $post['date'])) {
        echo buildResponse("success");
    } else {
        echo buildResponse("failed");
    }
    break;

case 'delete_clutcher':
    if (Poll::deleteClutcher($user->getId(), $post['pid'], $post['date'])) {
        echo buildResponse("success");
    } else {
        echo buildResponse("failed");
    }
    break;

default:
    echo buildResponse("Unexpected API request in php/user/api.php");
}

// EOF
