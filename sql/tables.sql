DROP TABLE IF EXISTS member;
CREATE TABLE member (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`userid` varchar(32) UNIQUE NOT NULL,
	`password` varchar(255) NOT NULL,
	`name` varchar(32) NOT NULL,
	`phone` varchar(12) NOT NULL,
	`smdname` varchar(50) NOT NULL,
	`agentcode` varchar(20) NOT NULL,
	`license` varchar(40) NOT NULL,
	`email` varchar(50) NOT NULL,
	`role` enum('Admin','Staff','Agent') NOT NULL,
	`deleted` boolean NOT NULL DEFAULT FALSE,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS schedule;
CREATE TABLE schedule (
	`userid` varchar(32) NOT NULL,
	`sunS` int(2) DEFAULT NULL,
	`sunE` int(2) DEFAULT NULL,
	`monS` int(2) DEFAULT NULL,
	`monE` int(2) DEFAULT NULL,
	`tueS` int(2) DEFAULT NULL,
	`tueE` int(2) DEFAULT NULL,
	`wedS` int(2) DEFAULT NULL,
	`wedE` int(2) DEFAULT NULL,
	`thuS` int(2) DEFAULT NULL,
	`thuE` int(2) DEFAULT NULL,
	`friS` int(2) DEFAULT NULL,
	`friE` int(2) DEFAULT NULL,
	`satS` int(2) DEFAULT NULL,
	`satE` int(2) DEFAULT NULL,
	`num_assigns` int(10) NOT NULL DEFAULT 0,
	`deleted` BOOLEAN NOT NULL DEFAULT FALSE,
	PRIMARY KEY (`userid`),
	FOREIGN KEY (`userid`) REFERENCES member(`userid`) ON DELETE CASCADE
);

DROP TABLE IF EXISTS assignments;
CREATE TABLE assignments (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`postedtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`client` varchar(40) NOT NULL,
	`provider` enum('Blue Cross','Blue Shield','Covered CA','Everest','Health Net','Kaiser','Molina','Nationwide','Sharp','Transamerica','Voya','WFG','Other') NOT NULL DEFAULT 'Other',
	`description` varchar(500) NOT NULL,
	`status` enum('New','Pending','Complete') NOT NULL DEFAULT 'New',
	`assignedto` varchar(32) NOT NULL,
	`createdby` varchar(32) NOT NULL,
	`deleted` boolean NOT NULL DEFAULT FALSE,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`assignedto`) REFERENCES member(`userid`) ON DELETE CASCADE,
	FOREIGN KEY (`createdby`) REFERENCES member(`userid`) ON DELETE CASCADE
);	
	
DROP TABLE IF EXISTS comments;
CREATE TABLE comments (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`taskid` int(11) NOT NULL,
	`userid` varchar(32) NOT NULL,
	`comments` varchar(500) NOT NULL,
	`postedtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`taskid`) REFERENCES assignments(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`userid`) REFERENCES member(`userid`) ON DELETE CASCADE
);	

DROP TABLE IF EXISTS files;
CREATE TABLE files (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`taskid` int(11) NOT NULL,
	`userid` varchar(32) NOT NULL,
	`imgpath` varchar(300) NOT NULL,
	`postedtime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`taskid`) REFERENCES assignments(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`userid`) REFERENCES member(`userid`) ON DELETE CASCADE
);

DROP TABLE IF EXISTS alerts;
CREATE TABLE alerts (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`taskid` int(11) NOT NULL,
	`notefor` varchar(32) NOT NULL,
	`note` varchar(100) NOT NULL,
	`timeofchange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`taskid`) REFERENCES assignments(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`notefor`) REFERENCES member(`userid`) ON DELETE CASCADE
);

CREATE TRIGGER `after_staff_insert`
AFTER INSERT ON `member` 
FOR EACH ROW 
BEGIN
IF (NEW.role LIKE 'Staff') THEN 
INSERT INTO `schedule` (userid) VALUES(NEW.userid);
END IF;
END;
@@

CREATE TRIGGER `after_task_insert`
AFTER INSERT ON `assignments` 
FOR EACH ROW 
BEGIN
UPDATE schedule SET num_assigns = num_assigns + 1 where userid = NEW.assignedto;
INSERT INTO alerts(taskid,notefor,note) VALUES(NEW.id,NEW.assignedto,CONCAT('New task (', NEW.id, ') created.'));
END;
@@

CREATE TRIGGER `after_member_update`
AFTER UPDATE ON `member` 
FOR EACH ROW 
BEGIN
IF (NEW.deleted = true) THEN
UPDATE schedule SET deleted = true WHERE schedule.userid = NEW.userid; 
ELSE UPDATE schedule SET deleted = false WHERE schedule.userid = NEW.userid;
END IF;
END;
@@

INSERT INTO member VALUES(1, "admin", "$2a$10$666c1f490573414398fc8urphq6JRlbPLxQb7Qq/93Ns6GCh8GyC.", 
"Thomas Dang", "000-000-0000", "admin", "admin", "admin", "admin@gmail.com", "Admin", false);


	
	