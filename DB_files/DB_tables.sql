#ITC298_project.sql

SET foreign_key_checks = 0; #turn off constraints temporarily

#since constraints cause problems, drop tables first, working backward
DROP TABLE IF EXISTS hhm_bills;
DROP TABLE IF EXISTS hhm_users;
DROP TABLE IF EXISTS hhm_households;

#household table
CREATE TABLE hhm_households(
    HouseholdID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    HouseholdName VARCHAR(50) NOT NULL,
    HhRentAmount DECIMAL(6,2) NULL,#Rent amount cannot exceed 9999.99
    PRIMARY KEY(HouseholdID)
)ENGINE=INNODB;

#insert test data
INSERT INTO hhm_households VALUES (NULL,'Test House','1500.00');

#users table
CREATE TABLE hhm_users(
    UserID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    LastName VARCHAR(50) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL,#instead of username and email is used
    UserPW VARCHAR(255) NOT NULL,
    UserLevel ENUM('admin','member') DEFAULT 'member',
    UserStatus ENUM('done','not done','pending','not in') DEFAULT 'not in',#user status now handles a user that is not part of a household as "not in"; and a user waiting to be accepted into a household as "pending"
    HouseholdID INT UNSIGNED DEFAULT 0,
    PRIMARY KEY(UserID),
    UNIQUE(Email),
    FOREIGN KEY(HouseholdID) REFERENCES hhm_households(HouseholdID)
)ENGINE=INNODB;


#insert test data
INSERT INTO hhm_users VALUES (NULL,'Santiago','Israel','neoazareth@gmail.com'
,'$2y$10$9fc.0/5XUVAWIPcNthKl6eiLwgZxprAeswdOKcuAUwKtREcWQhLW2','admin','not done',1);
INSERT INTO hhm_users VALUES (NULL,'fghij','abcde','colinhx@gmail.com'
 ,'$2y$10$SLmR2mK1inyRKjOvVOKoWu6NxbIznV0YEPXZZZUz04FbGnUbFn6RC','member','not done',1);
INSERT INTO hhm_users VALUES (NULL,'Zhu','Sicheng','szhu0007@seattlecentral.edu'
 ,'$2y$10$URxNKtO9VWyBxwHf3BlZZOcAUUFY/2v/ZsW6LiiSkv1lFnltWuic.','member','not done',1);

#bills table
CREATE TABLE hhm_bills(
    BillID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    BillAmount DECIMAL(5,2) NOT NULL,
    BillDesc VARCHAR(100) NOT NULL,
    BillCategory ENUM('utility','food','maintenance','other') DEFAULT 'other',
    BillDate DATETIME,
    HouseholdID INT UNSIGNED DEFAULT 0,
    UserID INT UNSIGNED DEFAULT 0,
    PRIMARY KEY(BillID),
    FOREIGN KEY(HouseholdID) REFERENCES hhm_households(HouseholdID) ON DELETE CASCADE,
    FOREIGN KEY(UserID) REFERENCES hhm_users(UserID) ON DELETE CASCADE
)ENGINE=INNODB;

INSERT INTO hhm_bills VALUES (NULL,145.00,'Electric','utility',NOW(),1,1);
INSERT INTO hhm_bills VALUES (NULL,350.00,'W/s/g','utility',NOW(),1,1);
INSERT INTO hhm_bills VALUES (NULL,50.00,'Market','food',NOW(),1,1);


CREATE TABLE hhm_codes(
    CodeID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    CodeNum VARCHAR(255) NOT NULL,
    CodeValid INT UNSIGNED NOT NULL,
    PRIMARY KEY(CodeID)
)ENGINE=INNODB;

SET foreign_key_checks = 1; #turn foreign key check back on