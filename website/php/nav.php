<?php
// Filename: php/nav.php
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
    echo '<a href="/user/create" class="nav-link mb-1 text-end" title="Create poll"><button class="btn"><i class="fa fa-plus" aria-hidden="true"></i> New poll</button></a>';

    // Edit polls
    echo '<a href="/user/polls" class="nav-link mb-1 text-end" title="View your polls"><button class="btn"><i class="fa fa-list" aria-hidden="true"></i> My polls</button></a>';

    // Account
    echo '<a href="/user/account" class="nav-link mb-1 text-end" title="View account"><button class="btn"><i class="fa fa-user" aria-hidden="true"></i> Account</button></a>';
    echo '</div>';

} else {
    // Login
    echo '        <a href="/login" class="nav-link mb-1 text-end" title="Login"><button class="btn"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</button></a>' . "\n";
}

// end container div and nav
echo '</div></div>';

// EOF
