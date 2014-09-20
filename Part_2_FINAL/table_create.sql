CREATE TABLE Users( 
	userID VARCHAR2(20),
	password VARCHAR2(20) NOT NULL,
	name VARCHAR2(20) NOT NULL,
	age INTEGER, 
	address VARCHAR2(100),
	email VARCHAR2(40) NOT NULL,
	status INTEGER,
	PRIMARY KEY (userID),
	UNIQUE (email),
	CHECK (age > 0 and age < 200),
	CHECK (status = 0 OR status = 1), 
	CHECK (REGEXP_LIKE(email,'^\w+(\.\w+)*+@\w+(\.\w+)+$')));
	

CREATE TABLE Employer(
	companyName VARCHAR2(50),
	description VARCHAR2(250),
	department VARCHAR2(250),
	companySize VARCHAR2(20),
	userID VARCHAR2(20),
	PRIMARY KEY (userID),
	FOREIGN KEY (userID) REFERENCES Users ON DELETE CASCADE);


CREATE TABLE Job_Seeker(
	currentStatus VARCHAR2(50),
	preferJob VARCHAR2(30),
	currentJob VARCHAR2(30),
	userID VARCHAR2(20),
	PRIMARY KEY (userID),
	FOREIGN KEY (userID) REFERENCES Users ON DELETE CASCADE);


CREATE TABLE Resume_Post(
	gpa REAL,
	degree VARCHAR2(20),
	school VARCHAR2(50),
	graduationDate DATE,
	resumeID VARCHAR2(20),
	additionalInfomation VARCHAR2(500),
	userID VARCHAR2(20),
	status INTEGER,
	PRIMARY KEY (resumeID, userID),
	FOREIGN KEY (userID) REFERENCES Job_Seeker ON DELETE CASCADE,
	CHECK (gpa > 0 and gpa < 5),
	CHECK (degree = 'Bachelor' OR degree = 'Doctorate' OR degree = 'Master'),
	CHECK (status = 0 OR status = 1));


CREATE TABLE Job_Post(
	jobID VARCHAR2(20),
	jobTitle VARCHAR2(50) NOT NULL,
	requiredGPA REAL,
	requiredDegree VARCHAR2(20),
	jobDescription VARCHAR2(250) NOT NULL,
	location VARCHAR2(20),
	startDate DATE,
	jobType VARCHAR2(20),
	deadline DATE,
	status INTEGER,
	userID VARCHAR2(20),
	PRIMARY KEY (jobID, userID),
	FOREIGN KEY (userID) REFERENCES Employer ON DELETE CASCADE,
	CHECK (requiredGPA > 0 and requiredGPA < 5),
	CHECK (requiredDegree = 'Bachelor' or requiredDegree = 'Doctorate' or requiredDegree = 'Master'),
	CHECK (status = 0 OR status = 1),
	CHECK (deadline <= startDate));

CREATE TABLE Reference_Recommend(
	jobTitle VARCHAR2(50),
	additionalInformation VARCHAR2(500),
	referenceID VARCHAR2(20),
	relationship VARCHAR2(20) NOT NULL,
	duration INTEGER,
	rating INTEGER NOT NULL, 
	companyName VARCHAR2(50) NOT NULL,
	name VARCHAR2(20),
	email VARCHAR2(40),
	status INTEGER,
	userID VARCHAR2(20),
	PRIMARY KEY (referenceID, userID),
	FOREIGN KEY (userID) REFERENCES Job_Seeker ON DELETE CASCADE,
	CHECK (duration > 0),
	CHECK (rating > 0 and rating <= 10),
	CHECK (status = 0 OR status = 1),
	CHECK (REGEXP_LIKE(email,'^\w+(\.\w+)*+@\w+(\.\w+)+$')));

CREATE TABLE Skill(
	skillTitle VARCHAR2(50) NOT NULL,
	skill_type INTEGER NOT NULL,
	skill_ID VARCHAR2(20),
	skillDescription VARCHAR2(250),
	PRIMARY KEY (skill_ID), 
	UNIQUE(skillTitle, skill_type),
	CHECK (skill_type > 0));

	
CREATE TABLE Job_Require_Skill(
	jobID VARCHAR2(20),
	skill_ID VARCHAR2(20),
	userID VARCHAR2(20),
	knowledgeLevel INTEGER,
	PRIMARY KEY (skill_ID, jobID, userID),
	FOREIGN KEY (skill_ID) REFERENCES Skill (skill_ID),
	FOREIGN KEY (jobID, userID) REFERENCES Job_Post (jobID, userID) ON DELETE CASCADE,
	CHECK (knowledgeLevel > 0 and knowledgeLevel <=5));
	
CREATE TABLE Resume_Have_Skill(
	resumeID VARCHAR2(20),
	skill_ID VARCHAR2(20),
	userID VARCHAR2(20),
	knowledgeLevel INTEGER,
	PRIMARY KEY (resumeID, skill_ID, userID),
	FOREIGN KEY (skill_ID) REFERENCES Skill,
	FOREIGN KEY (resumeID, userID) REFERENCES Resume_Post(resumeID, userID) ON DELETE CASCADE,
	CHECK (knowledgeLevel > 0 and knowledgeLevel <=5));

CREATE TABLE JobSeeker_Apply_Job(
	userID VARCHAR2(20),
	jobID VARCHAR2(20),
	job_post_userID VARCHAR2(20),
	status INTEGER,
	PRIMARY KEY (userID, jobID, job_post_userID),
	FOREIGN KEY (userID) REFERENCES Job_Seeker ON DELETE CASCADE,
	FOREIGN KEY (jobID,job_post_userID) REFERENCES Job_Post(jobID, userID) ON DELETE CASCADE,
	CHECK (status = 0 OR status = 1));

CREATE TABLE Resume_Have_WorkExperience(
	resumeID VARCHAR2(20),
	userID VARCHAR2(20),
	experienceID VARCHAR2(20),
	startDate DATE NOT NULL,
	endDate DATE,
	jobDescription VARCHAR2(100),
	companyName VARCHAR2(50) NOT NULL,
	department VARCHAR2(50),
	jobTitle VARCHAR2(50),
	PRIMARY KEY (resumeID, experienceID, userID),
	FOREIGN KEY (resumeID, userID) REFERENCES Resume_Post(resumeID, userID) ON DELETE CASCADE,
	CHECK(endDate >= startDate));

CREATE OR REPLACE TRIGGER Job_Start_Date_Check
  BEFORE INSERT OR UPDATE 
  	ON Job_Post
  	FOR EACH ROW
BEGIN
  IF(:new.startDate < CURRENT_TIMESTAMP) THEN
    RAISE_APPLICATION_ERROR(-20001, 'Job Start Date must be later than today');
  END IF;
END;
/

CREATE OR REPLACE TRIGGER Experience_Date_Check
  BEFORE INSERT OR UPDATE 
  	ON Resume_Have_WorkExperience
  	FOR EACH ROW
BEGIN
  IF(:new.startDate > CURRENT_TIMESTAMP) THEN
    RAISE_APPLICATION_ERROR(-20001, 'Work Experience Start Date must be before today');
  END IF;

  IF(:new.endDate > CURRENT_TIMESTAMP) THEN
    RAISE_APPLICATION_ERROR(-20001, 'Work Experience End Date must be before today');
  END IF;
  
END;
/



