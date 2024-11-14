<?php
// Filename: php/classes/db.php
// Purpose: Handles all DB access

namespace frakturmedia\clutch;

use Datetime;
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

    public function __destruct()
    {
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
        $sql = "SELECT COUNT(*) AS count FROM " . TABLE_USERS . " WHERE email=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return true;
        }
        return false;
    }

    public function checkExistenceOfUsername($username)
    {
        $sql = "SELECT COUNT(*) AS count FROM " . TABLE_USERS . " WHERE username=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return true;
        }
        return false;
    }

    public function editPoll ($uid, $pid, $title, $description)
    {
        $sql = "UPDATE " . TABLE_POLLS .
            " SET title=?, description=? WHERE uid=? AND pid=?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$title, $description, $uid, $pid]);
        // check the query result and that at least one row was updated
        return ($result and $stmt->rowCount() === 1);
    }

    public function createPoll ($uid, $title, $code, $description, $dates, $emails)
    {
        // add the basic poll info to the table
        $sql = "INSERT INTO " . TABLE_POLLS . " (uid, title, code, description) VALUES (?, ?,?,?)";
        $stmt = $this->conn->prepare($sql);
        if(!$stmt->execute([$uid, $title, $code, $description]) ) {
            return false;
        }

        // get the pid: $this->conn->lastInsertId()
        $pollid = $this->conn->lastInsertId();
        // repackage dates with interspersed pid and dates
        $bound_variables_array = [];
        foreach ($dates as $pdate) {
            // convert the DateTime object to a string of format YYYY-MM-DD
            array_push($bound_variables_array, $pollid, $pdate->format('Y-m-d'));
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

    public function getUserPolls ($uid)
    {
        $sql = "SELECT title, code FROM " . TABLE_POLLS . " WHERE uid=?";
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
}

// EOF
