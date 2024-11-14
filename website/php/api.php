<?php
// Filename: php/api.php
// Purpose: Gatekeeper - checks it is a trusted user

namespace frakturmedia\clutch;

if ($user->getStatus() >= MEMBER_STATUS_BASIC) {
    require_once('../php/user/api.php');
} else {
    echo buildResponse("You don't have access", 401);
}

// EOF
