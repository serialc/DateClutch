-- Create DateClutch tables 

CREATE TABLE users (
    uid MEDIUMINT UNSIGNED AUTO_INCREMENT, 
    username VARCHAR(32) NOT NULL UNIQUE,
    email VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (uid)
);

-- Basic info for a poll
CREATE TABLE polls (
    pid MEDIUMINT UNSIGNED AUTO_INCREMENT,
    uid MEDIUMINT UNSIGNED, 
    title VARCHAR(128) NOT NULL,
    code VARCHAR(64) NOT NULL,
    description TEXT,
    PRIMARY KEY (pid)
);

-- Associate dates with a poll - and participant with a poll date
CREATE TABLE polldates (
    pid MEDIUMINT UNSIGNED NOT NULL,
    pdate DATE NOT NULL,
    clutcher VARCHAR(128),
    PRIMARY KEY (pid, pdate)
);

-- Associate users to a poll for notifications
CREATE TABLE pollusers (
    uid MEDIUMINT UNSIGNED, 
    pid MEDIUMINT UNSIGNED,
    notifications TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (uid, pid)
);
