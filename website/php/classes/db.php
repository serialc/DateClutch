<?php
// Filename: php/classes/db.php
// Purpose: Handles all DB access

namespace frakturmedia\clutch;

use PDO;

class DataBaseConnection
{
    # define private variables here
    private $conn;

    # constructor
    public function __construct()
    {
        // create connection, MySQL setup
        try {
            $this->conn = new PDO(
                'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USERNAME,
                DB_PASS,
                [PDO::MYSQL_ATTR_INIT_COMMAND =>"SET NAMES utf8;SET time_zone = '" . TIMEZONE . "'"]
            );

        } catch (PDOException $e) {
            // Database connection failed
            echo "Database connection failed" and die;
        }
    }

    public function __destruct() {
        # php will close the db connection automatically when the process ends
        $this->conn = null;
        //mysqli_close($this->conn);
    }

    public function checkTZ()
    {
        //$this->conn->exec("SET time_zone = '" . date('P') . "'");
        $sql = "SELECT @@global.time_zone, @@session.time_zone";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getNumberOfUsers()
    {
        $sql = "SELECT COUNT(*) AS unumber FROM users";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute();
        if ($result) {
            $usernum = (int) $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['unumber'];
            return $usernum;
        } else {
            error_log(print_r($this->conn->errorInfo(), true));
            error_log("Failed to determine number of users") and die;
        }
    }

    public function listUsers()
    {
        $sql = "SELECT username, usertype, status FROM " . TABLE_USERS . "";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function retrieveUserFromUid($uid)
    {
        $sql = "SELECT * FROM " . TABLE_USERS . " WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid]);

        // if successfull and there is a user with this username
        if ($result && $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function retrieveUserFromEmail($email)
    {
        $sql = "SELECT * FROM " . TABLE_USERS . " WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$email]);

        // if successfull and there is a user with this username
        if ($result && $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function userExists($uname)
    {
        $sql = "SELECT COUNT(*) as number FROM " . TABLE_USERS .
           " WHERE username=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$uname]);
        if ($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['number'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUser($uname, $email, $status, $pwd, $uid)
    {
        $sql = "UPDATE " . TABLE_USERS .
            " SET username=?, email=?, status=?, password=?  WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$uname, $email, $status, $pwd, $uid]);
    }

    public function changeUserStatus($uid, $status)
    {
        $sql = "UPDATE " . TABLE_USERS .
            " SET status=? WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $uid]);
    }

    public function addUser($uname, $email, $status, $pwd)
    {
        $sql = "INSERT INTO " . TABLE_USERS .
            " (username, email, password, status)" .
            " VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$uname, $email, $pwd, $status]);
    }

    public function getUsernameFromEmail($email)
    {
        $sql = "SELECT username FROM " . TABLE_USERS . " WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$email]);

        if ($result && $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC)['username'];
        }
        return false;
    }

    public function getUserIdFromEmail($email)
    {
        $sql = "SELECT uid FROM " . TABLE_USERS . " WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$email]);
        if ($result && $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC)['uid'];
        }
        return false;
    }

    public function checkExistenceOfEmail($email)
    {
        $sql = "SELECT email FROM " . TABLE_USERS . " WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$email]);
        return ($result and $stmt->rowCount() === 1);
    }

    public function checkExistenceOfUsername($username)
    {
        $sql = "SELECT username FROM " . TABLE_USERS . " WHERE username=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$username]);
        return ($result and $stmt->rowCount() === 1);
    }

    public function editPoll ($uid, $pid, $title, $description, $priv_mode)
    {
        $sql = "UPDATE " . TABLE_POLLS .
            " SET title=?, description=?, privacy=? WHERE uid=? AND pid=?";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$title, $description, intval($priv_mode), $uid, $pid]);
        // check the query result and that at least one row was updated
        return ($result and $stmt->rowCount() === 1);
    }

    public function createPoll ($uid, $title, $code, $admin_code, $description, $priv_mode, $dates, $emails)
    {
        // add the basic poll info to the table
        $sql = "INSERT INTO " . TABLE_POLLS . " (uid, title, code, admin_code, description, privacy) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt->execute([$uid, $title, $code, $admin_code, $description, intval($priv_mode)]) ) {
            return false;
        }

        // get the pid: $this->conn->lastInsertId()
        $pollid = $this->conn->lastInsertId();
        // repackage dates with interspersed pid and dates
        $bound_variables_array = [];
        foreach ($dates as $pdate) {
            // convert the DateTime object to a string of format YYYY-MM-DD
            array_push($bound_variables_array, $pollid, $pdate->format('Y-m-d H:i:s'));
        }

        // use pid to associate dates with poll
        $sql = "INSERT INTO " . TABLE_POLLDATES . " (pid, pdate) VALUES " .
            // Need to prep DPO placeholders (?,?), ... 
            trim(str_repeat("(?,?),", count($dates)), ',');
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute($bound_variables_array) ) {
            return false;
        }

        // add notification emails
        // - later

        return true;
    }

    public function retrievePollFromPid ($pid)
    {   
        $sql = "SELECT * FROM " . TABLE_POLLS . " WHERE pid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$pid]);

        if ($result and $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }   

    public function retrievePollFromAdminCode ($code)
    {   
        $sql = "SELECT * FROM " . TABLE_POLLS . " WHERE admin_code=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$code]);

        if ($result and $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function retrievePollFromCode ($code)
    {   
        $sql = "SELECT * FROM " . TABLE_POLLS . " WHERE code=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$code]);

        if ($result and $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function retrievePollDates ($pid)
    {
        $sql = "SELECT pdate, clutcher FROM " . TABLE_POLLDATES . " WHERE pid=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$pid]);
        $polldates = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($polldates, $row);
        }
        return $polldates;
    }

    public function getAllUserPolls ()
    {
        $sql = "SELECT u.username, p.pcount FROM " . TABLE_USERS . " AS u " .
            "JOIN (SELECT uid, COUNT(*) AS pcount FROM " . TABLE_POLLS . " GROUP BY uid) AS p " .
            "ON u.uid=p.uid";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([]);
        $upolls = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($upolls, $row);
        }
        return $upolls;
    }

    public function getUserPolls ($uid)
    {
        $sql = "SELECT title, code, admin_code, pid FROM " . TABLE_POLLS . " WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$uid]);
        $polls = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($polls, $row);
        }
        return $polls;
    }

    public function setPollClutcher ($pid, $clutcher, $date)
    {
        $sql = "UPDATE " . TABLE_POLLDATES .
            " SET clutcher=? WHERE pid=? AND pdate=? AND clutcher IS NULL";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$clutcher, $pid, $date]);
        // check the query result and that at least one row was updated
        return ($result and $stmt->rowCount() === 1);
    }

    public function pollDelete ($uid, $pid)
    {
        // careful here - two tables
        // delete summary table first, if that works then this uid
        // is owner of poll and can delete dates
        $sql = "DELETE FROM " . TABLE_POLLS . " WHERE uid=? AND pid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid, $pid]);

        // check the query result and that at least one row was deleted 
        if ($result and $stmt->rowCount() === 1) {
            // now try deleting the dates and clutchers
            $sql = "DELETE FROM " . TABLE_POLLDATES . " WHERE pid=?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$pid]);

            // there may be no rows of data to delete, so nothing to check
            // except that $result was true
            return $result;
        }
        return false;
    }

    public function deletePollDateClutcher ($uid, $pid, $date)
    {
        $sql = "UPDATE " . TABLE_POLLDATES . " AS pd JOIN " . TABLE_POLLS . " AS p " .
            "ON pd.pid=p.pid SET pd.clutcher = NULL WHERE p.uid=? AND p.pid=? AND pd.pdate=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid, $pid, $date]);

        // check the query result and that at least one row was deleted 
        return ($result and $stmt->rowCount() === 1);
    }

    public function deletePollDate ($uid, $pid, $date)
    {
        $sql = "DELETE pd FROM " . TABLE_POLLDATES . " AS pd JOIN " . TABLE_POLLS . " AS p " .
            "ON pd.pid=p.pid WHERE p.uid=? AND p.pid=? AND pd.pdate=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid, $pid, $date]);

        // check the query result and that at least one row was deleted 
        return ($result and $stmt->rowCount() === 1);
    }

    public function addPollDate ($uid, $pid, $date)
    {
        $sql = "SELECT uid FROM " . TABLE_POLLS . " WHERE uid=? AND pid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid, $pid]);

        // check if uid is owner of poll
        if($result and $stmt->rowCount() === 1) {
            $sql = "INSERT IGNORE INTO " . TABLE_POLLDATES . " (pid, pdate) VALUES (?,?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$pid, $date]);
        }
        return false;
    }

    public function getUser ($uid)
    {
        $sql = "SELECT username, email, status FROM " . TABLE_USERS . " WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$uid]);

        if ($result and $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function setUserResetCode ($uid, $code)
    {
        $sql = "UPDATE " . TABLE_USERS . " SET reset_dt=NOW(), reset_code=? WHERE uid=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$code, $uid]);
    }

    public function updateUserPasswordFromCode ($newpw, $code)
    {
        $sql = "UPDATE " . TABLE_USERS . " SET password=?, reset_code=NULL, reset_dt=NULL " .
            "WHERE reset_code=? AND reset_dt >= NOW() - INTERVAL 10 MINUTE";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$newpw, $code]);
        return ($result and $stmt->rowCount() === 1);
    }

    private function setSetting ( $name, $value )
    {
        $sql = "INSERT INTO " . TABLE_SETTINGS . " (name, value) VALUES (?,?) " .
            "ON DUPLICATE KEY UPDATE name=?, value=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $value, $name, $value]);
    }

    private function getSetting ( $name )
    {
        $sql = "SELECT ? FROM " . TABLE_SETTINGS;
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$name]);
        if ($result and $stmt->rowCount() === 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC)[$name];
        }
        return false;
    }

    public function initializeSettings ()
    {
        // If we add other settings, adapt return so it returns false if any fails
        return $this->setSetting("salt", getRandomCode(32));
    }

    public function createUserInvitation ($code)
    {
        $sql = "INSERT INTO " . TABLE_INVITATIONS . " (code, expires) VALUES (?,NOW() + INTERVAL 10 DAY)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$code]);
        return ($result and $stmt->rowCount() === 1);
    }

    public function evaluateUserInvitation ($code)
    {
        $sql = "DELETE FROM " . TABLE_INVITATIONS . " WHERE " .
            "code=? AND expires > NOW()";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$code]);
        return ($result and $stmt->rowCount() === 1);
    }
}

// EOF
