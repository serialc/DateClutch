<?php
// Filename: constants.php
// Purpose: define values that are more general and not sensitive

namespace frakturmedia\clutch;

// logging
define("LOGSDIR", "../logs/");

// TABLES
define("TABLE_USERS", "users");
define("TABLE_POLLS", "polls");
define("TABLE_POLLDATES", "polldates");
define("TABLE_POLLUSERS", "pollusers");
define("TABLE_SETTINGS", "settings");
define("TABLE_INVITATIONS", "invitations");

// member types
define("MEMBER_STATUS_BASIC", 1);
define("MEMBER_STATUS_CREATOR", 16);
define("MEMBER_STATUS_ADMIN", 255);

// user facing variabls
define("ANONYMOUS_NAME", '-private-');

// other
define("PASSWORD_HASH_COST", 10);
define("TIMEZONE", 'CET');
define("DEFAULT_ADMIN_STATUS", 255);
define("CONF_TEMPLATE", "../php/config_template.php");
define("CONF_FILE", "../php/config.php");
//define("ALLOWED_UPLOAD_FILE_TYPES", array("png", "gif", "svg", "jpg", "zip"));


// EOF
