<?php

// Filename: config_template.php
// Purpose: variables that need to be configured

namespace frakturmedia\clutch;

// =============== EDIT and save as php/config.php =================
// DefineDB connection parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'cyrille');
define('DB_NAME', 'clutch');
define('DB_PASS', '');

// Define email parameters
define('EMAIL_HOST', 'mail.your-server.de');
define('EMAIL_SENDER', 'webmaster@digitaltwin.lu');
define('EMAIL_REPLYTO', 'webmaster@digitaltwin.lu');
define('EMAIL_REPLYTONAME', 'Webmaster');
define('EMAIL_PASSWORD', 'FvjHx36lmKQ9WL42');
define('EMAIL_PORT', 587);

// Define if production or development
define('SERVER_IS_PRODUCTION', false);
define('REGISTRATION_ENABLED', true);
// =============== END of define in php/config.php =================

// EOF
