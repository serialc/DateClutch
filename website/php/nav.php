<?php
// Filename: inc/nav.php
// Purpose: Displays nav menu

namespace frakturmedia\clutch;

// create menu items contextually (not logged in, logged in, user type, admin)
echo <<< END
<div class="justify-content-end" id="navbarSupportedContent">
    <div class="navbar-nav">

END;

// if user is logged in
if ($user->getStatus() >= MEMBER_STATUS_BASIC) {
    // New poll
    echo '        <a href="/user/create" class="nav-link mb-1 text-end" title="Create poll"><button class="btn"><i class="fa fa-plus" aria-hidden="true"></i> New poll</button></a>' . "\n";
    // Edit polls
    echo '        <a href="/user/polls" class="nav-link mb-1 text-end" title="View your polls"><button class="btn"><i class="fa fa-list" aria-hidden="true"></i> My polls</button></a>' . "\n";
    // Logout
    echo '        <a href="/logout" class="nav-link mb-1 text-end" title="Logout"><button class="btn"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</button></a>' . "\n";
} else {
    // Login
    echo '        <a href="/login" class="nav-link mb-1 text-end" title="Login"><button class="btn"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</button></a>' . "\n";
}

// end container div and nav
echo '</div></div>';

// EOF
