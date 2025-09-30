<?php
// Filename: index.php
// Purpose: Main landing file

namespace frakturmedia\clutch;

$req = explode("/", trim($_SERVER['REQUEST_URI'], "/"));

include('../php/initialization.php');

// prevent further output if this is an api call
if (isset($req[0]) and strcmp($req[0], "api") === 0) {
    return;
}

include('../php/layout/head.php');

echo '<div id="content" class="container mb-5">';

switch ($req[0]) {
case 'login':
    include '../php/login.php';
    break;

case 'register':
    include '../php/register.php';
    break;

case 'start':
    include '../php/start.php';
    break;

case 'user':
    include '../php/user.php';
    break;

case 'poll':
    include '../php/poll.php';
    break;

case 'results':
    include '../php/poll_results.php';
    break;

case 'password_reset':
    include '../php/pw_reset.php';
    break;

case 'unsub':
    include '../php/unsubscribe.php';
    break;

case 'terms_of_use':
    include '../php/layout/terms_of_use.html';
    break;

case 'privacy_policy':
    include '../php/layout/priv_policy.html';
    break;
    
case 'cookies_policy':
    include '../php/layout/cookies_policy.html';
    break;

default:
    include '../php/main.php';
}

echo '</div>';

include '../php/layout/foot.php';

// EOF

