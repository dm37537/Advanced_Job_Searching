/* 
This is an example of query which returns the list of jobs where the end user has the resume which satisfies the job-posting.
I have used the first resume posted by user 'ilsun.lee'. The resume GPA, degree and skills were compared with posted job and 
returns the job which are satisfied by the given resume. 
*/

SELECT J.userID, J.jobTitle, J.jobID
FROM Job_Post J
WHERE J.requiredGPA <= (SELECT R.gpa
						FROM Resume_Post R
						WHERE R.userID = 'ilsun.lee' AND R.resumeID = '1')
INTERSECT
(
	SELECT J.userID, J.jobTitle, J.jobID
	FROM Job_Post J
	WHERE (J.requiredDegree = 'Bachelor') AND EXISTS (
		SELECT R.degree 
		FROM Resume_Post R
		WHERE R.userID = 'ilsun.lee' AND R.resumeID = '1' AND R.degree = 'Bachelor')
	UNION
	SELECT J.userID, J.jobTitle, J.jobID
	FROM Job_Post J
	WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master') AND EXISTS (
		SELECT R.degree 
		FROM Resume_Post R
		WHERE R.userID = 'ilsun.lee' AND R.resumeID = '1' AND R.degree = 'Master')
	UNION
	SELECT J.userID, J.jobTitle, J.jobID
	FROM Job_Post J
	WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master' OR J.requiredDegree = 'Doctorate') AND EXISTS (
		SELECT R.degree 
		FROM Resume_Post R
		WHERE R.userID = 'ilsun.lee' AND R.resumeID = '1' AND R.degree = 'Doctorate')
)
INTERSECT
Select J.userID, J.jobTitle, J.jobID
FROM Job_Post J
WHERE NOT EXISTS(
	(SELECT JRS.skill_ID 
	 FROM Job_Require_Skill JRS
	 WHERE JRS.jobID = J.jobID AND JRS.userID = J.userID)
	 MINUS(
	 Select RHS.skill_ID
	 FROM Resume_Have_Skill RHS, Job_Require_Skill JRS
	 WHERE RHS.userID = 'ilsun.lee' AND RHS.resumeID = '1' AND JRS.jobID = J.jobID  AND JRS.userID = J.userID 
	 	AND JRS.skill_ID = RHS.skill_ID AND JRS.knowledgeLevel <= RHS.knowledgeLevel));