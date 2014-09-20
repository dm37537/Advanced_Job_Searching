DROP TABLE Reference_Recommend;
DROP TABLE Job_Require_Skill;
DROP TABLE Resume_Have_Skill;
DROP TABLE JobSeeker_Apply_Job;
DROP TABLE Resume_Have_WorkExperience;
DROP TABLE Resume_Post;
DROP TABLE Job_Post;
DROP TABLE Skill;
DROP TABLE Job_Seeker;
DROP TABLE Employer;
DROP TABLE Users; 
PURGE recyclebin;
select * from tab;
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



INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('ilsun.lee','il2255','Ilsun Lee','29','3251 Romano Street, Malden, MA 02148','il2255@yahee.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('meng.da','md3356','Meng Da','22','1762 Harley Vincent Drive, Brecksville, OH 44141','md3356@googla.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('jacob.smith','js5683','Jacob Smith','25','2414 Oakmound Road, Chicago, IL 60605','jacob.s@outlok.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('mason.johnson','masonJ2039','Mason Johnson','24','3907 Pritchard Court, Oronoco, MN 55960','johnson.m@googla.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('ethan.thomas','Thomas3957','Ethan Thomas','23','1617 Charles Street, Ypsilanti, MI 48198','et3567@yahee.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('sophia.lee','thisisSophia','Sophia Lee','28','1304 Ryder Avenue, Bellevue, WA 98007','lee.s123@hetmail.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('emma.lopez','Lopez8093','Emma Lopez','30','2406 White Lane, Macon, GA 31206','elopez@hetmail.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('isabella.hill','LeoniaPark80','Isabella Hill','31','1081 Timber Oak Drive, Lubbock, TX 79401','Isa.Hill@colombie.edu','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('olivia.nelson','Eminem8090','Olivia Nelson','24','2078 Hardesty Street, Albany, NY 12207','on2345@bern.edu','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('aiden.perez','NYRules7629','Aiden Parez','22','1778 Paradise Lane Chino, CA 91710','parez.aiden.56@yahee.com','0');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('yahoo_web_hr','FsGKQe7s','Yahoo_web_HR','','3339 Lowndes Hill Park Road, Bakersfield, CA 93307','hr_web@yahoo.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('yahoo_marketing','MNwVu3fy','Yahoo_marketing_HR','','3339 Lowndes Hill Park Road, Bakersfield, CA 93307','hr_market@yahoo.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('google_IT','sDef2bdT','google_IT','','4178 Robinson Lane, Delaware, OH 43015','hr_it@google.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('google_marketing','D6F9yUNP','google_marketing','','4178 Robinson Lane, Delaware, OH 43015','hr_marketing@google.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('chase','DYaPMrVm','chase','','4928 Ingram Street, Kettering, OH 45429','recruit@chase.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('bloomberg','ct23my9h','bloomberg','','387 Dale Avenue, Seattle, WA 98109','humanresource@bloomberg.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('apple','SDkBpp2M','apple','','635 Hide A Way Road, Orlando, FL 32810','hr@appler.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('facebook','AHUMSM6h','facebook','','2078 Still Street, Norwalk, OH 44857','HR@facebook.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('ibm','rFNvsKPG','IBM','','1977 Clement Street, Atlanta, GA 30337','HR@ibm.com','1');
INSERT INTO Users (userID,password,name,age,address,email,status) VALUES ('ny_hospital','brhyTX9q','nyhospital','','4712 Simpson Avenue, New York NY 10024','ny_hospital_hr@nyhospital.org','1');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','ilsun.lee');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','meng.da');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','jacob.smith');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','mason.johnson');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','ethan.thomas');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','sophia.lee');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','emma.lopez');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','isabella.hill');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','olivia.nelson');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('','','','','aiden.perez');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Yahoo','Yahoo! wants to spread some cheer to Internet users around the world. Its network of websites offers news, entertainment, and shopping, as well as search results. Yahoo! generates most of its revenue through providing search and display advertising.','Web Application','Over 10000 ','yahoo_web_hr');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Yahoo','Yahoo! wants to spread some cheer to Internet users around the world. Its network of websites offers news, entertainment, and shopping, as well as search results. Yahoo! generates most of its revenue through providing search and display advertising.','Marketing','Over 10000 ','yahoo_marketing');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Google','Google is a global technology company. The Company''s business is primarily focused around key areas, such as search, advertising and platforms. The Company generates revenue primarily by delivering online advertising. ','Information Technology','Over 40000 ','google_IT');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Google','Google is a global technology company. The Company''s business is primarily focused around key areas, such as search, advertising and platforms. The Company generates revenue primarily by delivering online advertising. ','Marketing','Over 40000 ','google_marketing');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Chase','Chase is a financial holding company. The Company is a global financial services firm and a banking institution in the United States, with global operations. ','Finance','Over 250000 ','chase');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Bloomberg','Bloomberg L.P. is a financial software services, news and data company created by Michael Bloomberg in 1982. Bloomberg provides news, data, analytics and communication for the global business and financial world.','Finance','15000','bloomberg');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Apple','Apple Inc., formerly Apple Computer, Inc., is an American multinational corporation headquartered in Cupertino, California, that designs, develops, and sells consumer electronics, computer software and personal computers. ','Consumer Electronics, Information Techonology, Research and Development, Marketing and etc.','80000','apple');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('Facebook','Facebook is the world''s largest social network, with over 1.15 billion monthly active users.','Information Technology','Over 6000','facebook');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('IBM','IBM is a multinational computer technology and consulting corporation. IBM manufactures and sells computer hardware and software, and offers infrastructure services and consulting services.','Information Technology Consult, Research and Development, Administrative and etc.','Over 430000','ibm');
INSERT INTO employer (companyName,description,department,companySize,userID) VALUES ('New York Hospital','New York Hospital is the oldest hospital in New York which provides state-of-the-art inpatient, ambulatory and preventative care in all areas of medicine.','Healthcare Professional, Information Technology, Administrative and etc.','2000','ny_hospital');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Actively looking for a job','Software Engineer','Software Developer','ilsun.lee');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Actively looking for an internship','Software Developer','Student','meng.da');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Looking for an internship','Network Administrator','Student','jacob.smith');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Looking for a full time position','Database Administrator','Student','mason.johnson');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Looking for an additional part time position','Registered Nurse','Registered Nurse','ethan.thomas');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Not currently looking for a job','General Manager','Manager','sophia.lee');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Not currently looking for a job','Consultant','Consultant','emma.lopez');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Looking for an intership position','Quantitative Analyst','Student','isabella.hill');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Looking for a new job','Systems Engineer','Software Engineer','olivia.nelson');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('Seeking a new position','Business Analyst','Consultant','aiden.perez');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','yahoo_web_hr');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','yahoo_marketing');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','google_IT');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','google_marketing');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','chase');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','bloomberg');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','apple');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','facebook');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','ibm');
INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES ('','','','ny_hospital');

INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.65','Bachelor','Carnegie Mellon University',TO_DATE('05/03/06', 'mm/dd/yy'),'1','Currently pursuing the master''s degree in columbia university and worked in the electiric medical record firm.','ilsun.lee','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.54','Bachelor','The University of Taxas at Austin ',TO_DATE('05/23/15', 'mm/dd/yy'),'2','Currently pursuing the computer science degree and attending Columbia University as exchange student. ','meng.da','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.12','Bachelor','Columbia University',TO_DATE('05/10/15', 'mm/dd/yy'),'3','Worked in Akamai as intern for software engineer with focus on the network management. ','jacob.smith','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.56','Doctorate','Wlliam Patterson University',TO_DATE('05/13/14', 'mm/dd/yy'),'4','Recevied the ph.D from the william paterson university with focus on the database security.','mason.johnson','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.21','Master','New York University',TO_DATE('12/03/12', 'mm/dd/yy'),'5','Registered nurse and currently working in the ER department in the mount sinai','ethan.thomas','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('2.99','Bachelor','Fordam University',TO_DATE('05/03/10', 'mm/dd/yy'),'6','Have experience in social network marketing through Facebook, Twitter and Pinterest.','sophia.lee','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.56','Bachelor','Rutgers University',TO_DATE('05/08/09', 'mm/dd/yy'),'7','Specialized in the brand management.','emma.lopez','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.91','Bachelor','Havard University',TO_DATE('05/23/14', 'mm/dd/yy'),'8','Minor in the math and economics. Interned in medallion fund during 2012 summer.','isabella.hill','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.28','Bachelor','Princeton University',TO_DATE('05/29/13', 'mm/dd/yy'),'9','Worked as web application developer and built the start-up website called checkinorout.com. Currently learning the mobile application for the andriod and iOS.','olivia.nelson','1');
INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES ('3.08','Bachelor','Yale University',TO_DATE('05/03/07', 'mm/dd/yy'),'10','Looking for a new job as business analyst. Worked as consultant in various firms.','aiden.perez','0');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Software developer','3.3','Bachelor','Software development','San francisco',TO_DATE('09/01/14','mm/dd/yy'),'technology',TO_DATE('07/01/14','mm/dd/yy'),'1','google_IT');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Marketing Manager','3','Master','manage marketing department','New York',TO_DATE('06/01/14','mm/dd/yy'),'communication',TO_DATE('05/24/14','mm/dd/yy'),'1','google_marketing');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Web developer','3','Bachelor','web application development','Chicago',TO_DATE('08/01/14','mm/dd/yy'),'technology',TO_DATE('07/20/14','mm/dd/yy'),'1','yahoo_web_hr');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Public relations','2.8','Bachelor','communicate with other company','Houston',TO_DATE('09/01/14','mm/dd/yy'),'communication',TO_DATE('07/01/14','mm/dd/yy'),'1','yahoo_marketing');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Personal Banker','3','Bachelor','provide personal service to customer','Austin',TO_DATE('10/02/14','mm/dd/yy'),'communication',TO_DATE('08/20/14','mm/dd/yy'),'1','chase');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Financial Analyst','3.5','Bachelor','analize finicial report','Newark',TO_DATE('11/01/14','mm/dd/yy'),'analyze',TO_DATE('10/01/14','mm/dd/yy'),'1','bloomberg');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Mac OS developer','3.5','Master','os development','Los Angles',TO_DATE('09/01/14','mm/dd/yy'),'technology',TO_DATE('08/01/14','mm/dd/yy'),'1','apple');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','JAVA developer','3.3','Bachelor','Software development with java','San Jose',TO_DATE('06/01/14','mm/dd/yy'),'technology',TO_DATE('05/01/14','mm/dd/yy'),'1','facebook');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Security Consultant','3.6','Master','consultant of computer security issues','Austin',TO_DATE('05/01/14','mm/dd/yy'),'technology',TO_DATE('04/20/14','mm/dd/yy'),'1','ibm');
INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES ('1','Registered Nurse','3.4','Bachelor','Nurse for Emergency Department','New York',TO_DATE('10/01/14','mm/dd/yy'),'healthcare',TO_DATE('09/01/14','mm/dd/yy'),'1','ny_hospital');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Product Manager','I was a manager and mentor for Ilsun Lee. He is very energetic and accurate. He gets his job done on time and keeps his promises. ','1','Manager','3','8','Epic Systems','Emily  Brown','eb2345@epic.com','1','ilsun.lee');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Software Developer','Ilsun was my mentor when I first started in the company. He is very easy-going and friendly. He has a deep knowledge in the network and visual basic programming. ','2','Coworker','1','9','Epic Systems','Irene  Thomas','it592@epic.com','1','ilsun.lee');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Professor','I was the senior thesis advisor for him during his senior year in college. He worked on the wireless networking congestion project and successfully finished his project.','3','Professor','2','10','Carnegie Mellon University','Cheryl Green','cg1593@cmu.edu','1','ilsun.lee');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Nurse','I was coworker of Ethan. It was a pleasure to work with him. He is always willing to help in hard times. ','4','Coworker','2','7','Mount Sinai Hospital','Harold Hernandez','harold.h@sinai.org','1','ethan.thomas');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Marketing Director','She was the intern in our firm during the summer 2010. She worked on brand management of small company Rainbow Cookie and the client was very satisfied with her work.','5','Manager','1','8','Denso','Helen Harris','h.helen@denso.com','1','sophia.lee');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Brand Manager ','Sophia was my coworker in Nike. We worked on the social network marketing on Facebook, Twitter and Pinterest. She has a good knowledge how the social network works and interact.','6','Coworker','2','8','Nike','Rachel Young','racheal.young.12@nike.com','1','sophia.lee');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Professor','I was a thesis adviser for Mason for 4 years during his ph.D. He has an deep knowledge in the database and successfully depended and earned his ph.D in 2013. ','7','Thesis adviser','4','9','William Paterson University','Philip Wood','pw29573@wpu.edu','1','mason.johnson');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Software Developer','Smith is one of the brightest network administrator that I had worked with. He identifies the problem in constructive manner and fixes in very fast timeline. ','8','Coworker','2','8','Akamai ','Timothy Howard','timothy.h@akamai.com','1','jacob.smith');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('Physician','He was my primary nurse for the patient care. He always prepares the patient and necessary document in timely manner. I highly recommend him to any other hospital','9','Coworker','2','8','Mount Sinai Hospital','Ashley Miller','a.miller@sinai.org','1','ethan.thomas');
INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES ('QA Software Engineer','I was the main QA engineer for Isabella. She takes the criticism in positive way and fixes the error in elegent manner. ','10','Coworker','1','7','Chase','Carol Sanchez','sanchez.carol@chase.com','1','isabella.hill');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Communication skill','1','communicaion_1','ability to have good communication with people');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Writing skill','1','writing_1','ability to write articles');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('java,javascript,php','2','java_2','ability to code in java,javascript,php');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Team work','1','team_1','ability to perform team work');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Leader skill','1','leader_1','ability to lead a team');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Solving problem skill','3','solving_3','ability to solve problems effectively');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('c/c++','2','c_2','ability to code in c,c++');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('analytical skill','3','analytical_3','ability to analyse a large number of data');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('microsoft office','3','office_3','ability to use microsoft office');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('planning skill','1','planning_1','ability to make a plan');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('CCNA Routing and Switching','3','ccna_3','Cisco certified Network associate routing and switching');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Sun Certified Network Administrator','3','sunNetwork_3','Solaris Operating system network administrato');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('python','2','python_2','ability to write python code');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('IMA Certified Internet Market','4','IMA_4','internet marketing association certification');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Certified Public Accountant','4','CPA_4','certified public accountant certification');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('Microsoft Certified Database Administrator','3','MCDBA_4','ability to administrate microsoft database such as SQL Server, Access');
INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES ('ANCC Certification','5','ANCC_5','American nurse credentialing center certification');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','java_2','ilsun.lee','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','c_2','ilsun.lee','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','python_2','ilsun.lee','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','communicaion_1','ilsun.lee','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','solving_3','ilsun.lee','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('1','sunNetwork_3','ilsun.lee','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('2','java_2','meng.da','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('2','c_2','meng.da','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('2','python_2','meng.da','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('2','communicaion_1','meng.da','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('2','solving_3','meng.da','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('3','java_2','jacob.smith','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('3','c_2','jacob.smith','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('3','python_2','jacob.smith','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('3','sunNetwork_3','jacob.smith','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('3','ccna_3','jacob.smith','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('4','MCDBA_4','mason.johnson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('4','java_2','mason.johnson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('4','c_2','mason.johnson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('4','analytical_3','mason.johnson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('4','python_2','mason.johnson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('5','ANCC_5','ethan.thomas','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('5','communicaion_1','ethan.thomas','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('5','writing_1','ethan.thomas','2');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('5','team_1','ethan.thomas','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('5','leader_1','ethan.thomas','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('6','communicaion_1','sophia.lee','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('6','writing_1','sophia.lee','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('6','analytical_3','sophia.lee','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('6','planning_1','sophia.lee','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('6','IMA_4','sophia.lee','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('7','communicaion_1','emma.lopez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('7','writing_1','emma.lopez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('7','analytical_3','emma.lopez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('7','planning_1','emma.lopez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('7','IMA_4','emma.lopez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('8','communicaion_1','isabella.hill','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('8','writing_1','isabella.hill','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('8','analytical_3','isabella.hill','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('8','planning_1','isabella.hill','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('8','CPA_4','isabella.hill','4');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('9','java_2','olivia.nelson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('9','c_2','olivia.nelson','3');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('9','office_3','olivia.nelson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('9','python_2','olivia.nelson','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('10','communicaion_1','aiden.perez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('10','writing_1','aiden.perez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('10','solving_3','aiden.perez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('10','analytical_3','aiden.perez','5');
INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES ('10','planning_1','aiden.perez','5');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','java_2','google_IT','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','python_2','google_IT','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','communicaion_1','google_marketing','4');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','java_2','yahoo_web_hr','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','python_2','yahoo_web_hr','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','communicaion_1','yahoo_marketing','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','planning_1','yahoo_marketing','3');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','analytical_3','chase','2');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','CPA_4','bloomberg','4');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','c_2','apple','5');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','team_1','apple','5');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','java_2','facebook','4');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','python_2','facebook','4');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','sunNetwork_3','ibm','1');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','python_2','ibm','5');
INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES ('1','ANCC_5','ny_hospital','1');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('1','ilsun.lee','1',TO_DATE('09/01/06','mm/dd/yy'),TO_DATE('09/16/08','mm/dd/yy'),'Developed software for Medical Record System','Epic Systems','Research and Development','Software Developer');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('1','ilsun.lee','2',TO_DATE('10/01/08','mm/dd/yy'),TO_DATE('03/01/12','mm/dd/yy'),'Researched Wireless Network congestion problem','Carnegie Mellon University','Computer Science','Research Assitant');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('5','ethan.thomas','1',TO_DATE('08/01/13','mm/dd/yy'),'','Worked as nurse in the ER Department','Mount Sinai Hospital','Emergency Room','Registered Nurse');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('6','sophia.lee','1',TO_DATE('05/01/10','mm/dd/yy'),TO_DATE('08/30/10','mm/dd/yy'),'Worked on marketing management plan for Rainbow Cookie','Denso','Marketing','Intern');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('6','sophia.lee','2',TO_DATE('09/01/12','mm/dd/yy'),'','Worked on brand management on social network','Nike','Marketing','Marketing Manager');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('4','mason.johnson','1',TO_DATE('06/01/13','mm/dd/yy'),TO_DATE('08/30/13','mm/dd/yy'),'Worked on database security research in Oracle','Oracle','Research and Development','Intern');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('3','jacob.smith','1',TO_DATE('06/01/11','mm/dd/yy'),TO_DATE('08/30/11','mm/dd/yy'),'Worked as network administrator','Akamai','IT','Intern');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('8','isabella.hill','1',TO_DATE('06/01/09','mm/dd/yy'),TO_DATE('08/01/09','mm/dd/yy'),'Worked on financial software in Java','Chase','IT','Software Developer');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('7','emma.lopez','1',TO_DATE('06/01/12','mm/dd/yy'),TO_DATE('06/01/13','mm/dd/yy'),'Worked on as financial consultant','Facebook','Business','Intern');
INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES ('9','olivia.nelson','1',TO_DATE('09/01/10','mm/dd/yy'),'','Worked as kernel developer ','Greenhill','Operating Systems','System Developer');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('meng.da','1','google_IT','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('meng.da','1','yahoo_web_hr','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('mason.johnson','1','facebook','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('ethan.thomas','1','ny_hospital','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('isabella.hill','1','chase','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('isabella.hill','1','bloomberg','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('olivia.nelson','1','apple','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('olivia.nelson','1','ibm','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('aiden.perez','1','chase','1');
INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES ('aiden.perez','1','bloomberg','1');
