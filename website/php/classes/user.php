<?php
// Filename: user.php
// Purpose: handle all user related tasks

namespace frakturmedia\clutch;

# Constants
require_once '../php/classes/db.php';

class User
{
    # define private variables here
    private $db;

    private $id;
    private $username;
    private $email;
    private $status;
    private $password_hash;

    # constructor
    public function __construct()
    {
        $this->db = new DataBaseConnection();
    }

    public function __destruct()
    {
        unset($this->db);
    }

    public static function getUsernameFromEmail ($email)
    {
        return (new self())->db->getUsernameFromEmail($email);
    }

    public static function createInvitation ()
    {
        $inv_code = getRandomCode(64);
        if ((new self())->db->createUserInvitation($inv_code)) {
            return $inv_code;
        }
        return false;
    }

    public static function evaluateInvitation ($inv_code)
    {
        return ((new self())->db->evaluateUserInvitation($inv_code));
    }

    public function getId()
    {
        if (!isset($this->id)) {
            return false;
        }
        return $this->id;
    }

    public function getName()
    {
        if (!isset($this->username)) {
            return false;
        }
        return $this->username;
    }

    public function setName ($new_username)
    {
        // if existing name is the same as the new, then just return true
        if ($this->username === $new_username) {
            return;
        }

        // overwrite username in object (not yet in db)
        $this->username = $new_username;
    }

    public function setEmail ($new_email)
    {
        if ($this->email === $new_email) {
            return;
        }

        // overwrite email in object (not yet in db)
        $this->email = $new_email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getStatus()
    {
        if (!isset($this->status)) {
            return false;
        }
        return $this->status;
    }

    public function setStatus ($new_status)
    {
        global $log;

        if (!is_int($new_status)) {
            $log->error("Tried to update status for " . $this->username . " to " . $new_status . " but it is not an integer value.");
            return false;
        }
        $this->status = $new_status;
    }

    public function createAdmin ($username, $email, $pwd): bool
    {
        global $log;

        if ($this->create($username, $email, DEFAULT_ADMIN_STATUS, $pwd))
        {
            // now that the admin is created, initialize the server settings
            if (!$this->db->initializeSettings()) {
                $log->error("Initialization of settings failed.");
            } else {
                $log->info("Initialization of settings succeeded.");
            }

            return true;
        }
        return false;
    }

    public function create($username, $email, $status, $pwd): bool
    {
        $email_lwr = strtolower($email);
        $hashed_pw = $this->hashPassword($pwd);

        if ($this->db->userExists($username)) {
            echo "Username already exists";
            return false;
        } else {
            $this->db->addUser($username, $email_lwr, $status, $hashed_pw);
            $uid = $this->getIdFromEmail($email_lwr);
            $this->loadFromUid($uid);
            return true;
        }
    }

    public function updatePassword($pw)
    {
        $this->password_hash = $this->hashPassword($pw);
    }

    private function hashPassword($pw)
    {
        return password_hash(
            $pw,
            PASSWORD_DEFAULT,
            ['cost' => PASSWORD_HASH_COST]
        );
    }

    // is passed the attributes in an associative array converted to properties
    private function populate($details)
    {
        $this->id = $details['uid'];
        $this->username = $details['username'];
        $this->email = $details['email'];
        $this->status = $details['status'];
        $this->password_hash = $details['password'];
    }

    private function getType()
    {
        // status is an int of range 0-255
        if ($this->status > MEMBER_STATUS_ADMIN) {
            return "Administrator";
        }
        if ($this->status > MEMBER_STATUS_CREATOR) {
            return "Creator";
        }
        if ($this->status > MEMBER_STATUS_BASIC) {
            return "Member";
        }
    }

    public function verify($email, $pw): bool
    {
        $email_lwr = strtolower($email);
        $user_row = $this->db->retrieveUserFromEmail($email_lwr);

        if ($user_row === false) {
            return false;
        }

        if (password_verify($pw, $user_row['password'])) {
            $this->populate($user_row);

            $pw_needs_rehash = password_needs_rehash(
                $this->password_hash,
                PASSWORD_DEFAULT,
                ['cost' => PASSWORD_HASH_COST]
            );
            if ($pw_needs_rehash) {
                $this->password_hash = $this->hashPassword($pw);
                // update password
                $this->save();
            }
            return true;
        }
        return false;
    }

    public function loadFromUid($uid): bool
    {
        $user_row = $this->db->retrieveUserFromUid($uid);

        if ($user_row !== false) {
            $this->populate($user_row);
            return true;
        }
        return false;
    }

    public function save(): bool
    {
        return $this->db->updateUser(
            $this->username,
            $this->email,
            $this->status,
            $this->password_hash,
            $this->id,
        );
    }

    public function update(): bool
    {
        if ($this->save()) {
            $this->loadFromUid($this->id);
            return true;
        }
        return false;
    }

    public function getIdFromEmail($email)
    {
        $email_lwr = strtolower($email);
        return $this->db->getUseridFromEmail($email_lwr);
    }

    public function resetPassword($code, $pw)
    {
        // check the code is in the code table
        // get the uid
        $uid = $this->db->getUidFromPasswordCode($code);

        if ($uid !== false) {

            require_once('../php/classes/user.php');
            // create a User object
            $temp_user = new User();
            $temp_user->loadFromUid($uid);
            // update the password for the user with that uid
            $temp_user->updatePassword($pw);
            // update pw to db
            $temp_user->save();

            // delete code from code table for the user
            $db->deleteUserCode($code);
            return true;
        }
        return false;
    }

    public function updatePasswordFromCode ( $newpw, $code )
    {
        $newpw_hashed = $this->hashPassword($newpw);
        return $this->db->updateUserPasswordFromCode($newpw_hashed, $code);
    }

    public function createResetCode ( $uid )
    {
        $ur_code = getRandomCode(64);
        if ($this->db->setUserResetCode ($uid, $ur_code)) {
            return $ur_code;
        }
        return false;
    }

    public function count()
    {
        return $this->db->getNumberOfUsers();
    }

    public function displayAdminRegistration ()
    {
        echo <<< _END
            <div class="row">
                <div class="col-md-6 col-lg-4">
                    <h2>Create administrator account</h2>
                    <p>To begin using the system, please specify a username and password to manage DateClutch.</p>
                </div>
                <div class="col-md-6 col-lg-4">
                    <form action="/start" method="post">
        _END;

        include('../php/layout/user_registration_form.html');
    }

    public function displayInviteeRegistration ()
    {
        echo <<< _END
            <div class="row">
                <div class="col-lg-6">
                    <h2>Register</h2>
                    <p>Welcome to DateClutch.<br>Let's get you registered.</p>
                    <p>You should have been provided a registration code.</p>
                </div>
                <div class="col-lg-6">
        _END;

        // define the same url as before to keep the invitation code
        // when provided
        echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';

        include('../php/layout/user_registration_form.html');
    }
}

// EOF
