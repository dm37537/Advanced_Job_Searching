<?php
	session_start();
	require_once "connection.php";
	$userID=$_SESSION['userID'];
	//print($userID);
	//
	//
	//Global Variable
	//USER  -- Add user_ in front of the variable to remove confusion
	$user_password=""; $user_name=""; $user_age=""; $user_address=""; $user_email=""; $user_status="";
	$user_currentStatus=""; $user_preferJob=""; $user_currentJob="";
	$user_companyName=""; $user_description=""; $user_department=""; $user_companySize="";
	//REFERENCE 
	$referenceSTID = "";
	//JOB POSTING 
	$jobPostSTID = "";
	//RESUME POSTING 
	$resumePostSTID ="";
	//APPLIED JOB POSTING 
	$appliedJobInfoSTID="";
	//
	//
	//Loading Module for each section
	//USER
	function retrieveInfoUser($userID, $conn){
		$userSelectSQL = "SELECT * FROM users WHERE userID = ? ";
		$jobSeekerSelectSQL = "SELECT * FROM job_seeker WHERE userID = ?";
		$employerSelectSQL = "SELECT * FROM employer WHERE userID = ?";
		
		
		retrieveSQLUser($userSelectSQL, $conn, 1, $userID);
		retrieveSQLUser($jobSeekerSelectSQL, $conn, 2, $userID);
		retrieveSQLUser($employerSelectSQL, $conn, 3, $userID);
	}	

	function retrieveSQLUser($sql, $conn, $type, $userID){
		// Connect to database
		//$stid = oci_parse($conn,$sql);
		//echo $sql;

		$stmt = $conn->stmt_init();
		$stmt->prepare($sql);

		if (!$stmt) {
			print('false');
		}
		$stmt->bind_param('s', $userID);
		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		} else {
			// Retrieve Variable and fill in the global.
			$result = $stmt->get_result();
			$rows = $result->fetch_array();
			//print($rows[1]);
			//print($rows[2]);
			if ($type == 1){
				global $user_password, $user_name, $user_age, $user_address, $user_email, $user_status;
				$user_password=$rows[1]; $user_name=$rows[2]; $user_age=$rows[3]; $user_address=$rows[4]; $user_email=$rows[5]; $user_status=$rows[6];
			}elseif ($type == 2){
				global $user_currentStatus, $user_preferJob, $user_currentJob;
				$user_currentStatus=$rows[0]; $user_preferJob=$rows[1]; $user_currentJob=$rows[2];
			}else{
				global $user_companyName, $user_description, $user_department, $user_companySize;
				$user_companyName=$rows[0]; $user_description=$rows[1]; $user_department=$rows[2]; $user_companySize=$rows[3];
			}
		}	
	}
	//REFERENCE 
	function retrieveInfoReference($userID, $conn){
		global $referenceSTID;
		$SelectSQL = "SELECT * FROM Reference_Recommend WHERE userID = '".$userID."'";
		// Connect to database
		$referenceSTID = $conn->query($SelectSQL);
		if(!$referenceSTID ){
		   die('There was an error running the query [' . $conn->error . ']');
		}
		//$conn->close();
	}
	//JOB POSTING
	function retrieveInfoJobPost($userID, $conn){
		global $jobPostSTID;
		$SelectSQL = "SELECT jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,DATE_FORMAT(startDate, '%Y-%m-%d'),jobType,DATE_FORMAT(deadline, '%Y-%m-%d'),status,userID FROM Job_Post WHERE userID = '".$userID."'";
		// Connect to database
		//print($SelectSQL);
		
		$jobPostSTID = $conn->query($SelectSQL);
		if(!$jobPostSTID ){
		   die('There was an error running the query [' . $conn->error . ']');
		}
		//$conn->close();
		
	}

	//JOB POSTING -> FITTING RESUME
	function retrieveInfoJobPostResume($jobID) {
		global $userID, $conn;
		$SelectSQL = 	"SELECT distinct AllowedResume.userID, AllowedResume.resumeID, U.name, U.email 
						FROM users U, 
							(
								SELECT R.userID, R.resumeID
								FROM Resume_Post R
								WHERE (R.degree = 'Bachelor' OR R.degree = 'Doctorate' OR R.degree = 'Master') AND EXISTS 
												 (SELECT J.requiredDegree 
												  FROM Job_Post J
												  WHERE J.userID = ? AND J.jobID = ? AND J.requiredDegree = 'Bachelor')
								UNION
								SELECT R.userID, R.resumeID
								FROM Resume_Post R
								WHERE (R.degree = 'Doctorate' OR R.degree = 'Master') AND EXISTS 
												 (SELECT J.requiredDegree 
												  FROM Job_Post J
												  WHERE J.userID = ? AND J.jobID = ? AND J.requiredDegree = 'Master')
								UNION
								SELECT R.userID, R.resumeID
								FROM Resume_Post R
								WHERE (R.degree = 'Doctorate') AND EXISTS 
												 (SELECT J.requiredDegree 
												  FROM Job_Post J
												  WHERE J.userID = ? AND J.jobID = ? AND J.requiredDegree = 'Doctorate')
								UNION
								SELECT R.userID, R.resumeID
								FROM Resume_Post R
								WHERE (SELECT J.requiredDegree 
									   FROM Job_Post J
									   WHERE J.userID = ? AND J.jobID = ? ) IS NULL
							)AllowedResume, Resume_Post R INNER JOIN
							(
							SELECT R.userID, R.resumeID
							FROM Resume_Post R INNER JOIN 
								(
								Select R.userID, R.resumeID
								FROM Resume_Post R
								WHERE NOT EXISTS(
									 SELECT JRS.skill_ID 
									 FROM Job_Post J, Job_Require_Skill JRS
									 WHERE J.userID = ? AND J.jobID = ? AND J.jobID = JRS.jobID AND J.userID = JRS.userID
									 AND JRS.skill_ID NOT IN(
									 Select RHS.skill_ID
									 FROM Resume_Have_Skill RHS, Job_Require_Skill JRS
									 WHERE RHS.resumeID = R.resumeID AND RHS.userID = R.userID AND JRS.userID = ? AND JRS.jobID = ?
										AND JRS.skill_ID = RHS.skill_ID AND (JRS.knowledgeLevel <= RHS.knowledgeLevel OR JRS.knowledgeLevel IS NULL)
										   					)
									
												)
								) A USING (userID, resumeID)
								WHERE R.gpa >= (SELECT J.requiredGPA 
												FROM Job_Post J
												WHERE J.userID = ? AND J.jobID = ?) OR
												(SELECT J.requiredGPA 
												FROM Job_Post J
												WHERE J.userID = ? AND J.jobID = ?) IS NULL
							)B USING (userID, resumeID)
						WHERE AllowedResume.userID = U.userID AND AllowedResume.resumeID = R.resumeID AND R.status = '1' 
						AND AllowedResume.userID NOT IN (SELECT userID FROM JobSeeker_Apply_Job WHERE job_post_userID = ? AND jobID = ?)";
		try{
		// Connect to database
		$jobPostResumeSTID = $conn->prepare($SelectSQL);
		//$jobPostResumeSTID->bindParam(':userID', $userID);
		//$jobPostResumeSTID->bindParam(':jobID', $jobID);
		$jobPostResumeSTID->bind_param('ssssssssssssssssss', $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID, $userID, $jobID);
		// Execute and Check Errors
		$jobPostResumeSTID->execute();
		$err = $jobPostResumeSTID->error;

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		}
		}catch(mysqli_sql_exception $e) {

		    echo $e->__toString();
		}
		$result = $jobPostResumeSTID->get_result();
		return $result;
	}
	//JOB POSTING -> APPLIED USER
	function retrieveInfoAppliedInfo($jobID) {
		global $userID, $conn;
		$SelectSQL = 	"SELECT U.userID, U.name, U.email 
						 FROM JobSeeker_Apply_Job J, users U
						 WHERE J.jobID = ? AND J.job_post_userID = ? AND J.userID=U.userID
						";
		// Connect to database
		$stid = $conn->prepare($SelectSQL);
		$stid->bind_param('ss', $jobID, $userID);
		// Execute and Check Errors
		$stid->execute();
		$err = $stid->error;
		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		}
		$result = $stid->get_result();
		return $result;
	}
	//RESUME POSTING
	function retrieveInfoResume($userID, $conn){
		global $resumePostSTID;
		$SelectSQL = "SELECT gpa,degree,school,DATE_FORMAT(graduationDate, '%Y-%m-%d'),resumeID,additionalInfomation,userID,status FROM Resume_Post WHERE userID = '".$userID."'";
		// Connect to database
		//print($SelectSQL);
		$resumePostSTID = $conn->query($SelectSQL);
		if(!$resumePostSTID ){
		   die('There was an error running the query [' . $conn->error . ']');
		}
	}
	//RESUME POSTING -> FITTING JOB
	function retrieveInfoCandidateJob($resumeID) {
		global $userID, $conn;
		$SelectSQL =   "SELECT distinct J.userID, J.jobTitle, J.jobID, J.status, U.name, E.companyName
						FROM users U, employer E, 
						(
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = ? AND R.resumeID = ? AND R.degree = 'Bachelor')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = ? AND R.resumeID = ? AND R.degree = 'Master')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master' OR J.requiredDegree = 'Doctorate') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = ? AND R.resumeID = ? AND R.degree = 'Doctorate')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE J.requiredDegree IS NULL
						) AllowedJob, Job_Post J INNER JOIN
						(	SELECT J.userID, J.jobID
							FROM Job_Post J INNER JOIN 
							(
								SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE NOT EXISTS(
									 SELECT JRS.skill_ID 
									 FROM Job_Require_Skill JRS
									 WHERE JRS.jobID = J.jobID AND JRS.userID = J.userID 
									 AND JRS.skill_ID NOT IN
									 (
									 SELECT RHS.skill_ID
									 FROM Resume_Have_Skill RHS, Job_Require_Skill JRS
									 WHERE RHS.userID = ? AND RHS.resumeID = ? AND JRS.jobID = J.jobID  AND JRS.userID = J.userID 
											AND JRS.skill_ID = RHS.skill_ID AND (JRS.knowledgeLevel <= RHS.knowledgeLevel OR JRS.knowledgeLevel IS NULL)
									 )
												)
								
							) A USING (userID, jobID)
							WHERE J.requiredGPA <= (SELECT R.gpa
													FROM Resume_Post R
													WHERE R.userID = ? AND R.resumeID = ?) OR J.requiredGPA IS NULL
						) B USING (userID, jobID) 
						WHERE J.status = '1' AND AllowedJob.userID = J.userID AND AllowedJob.jobID = J.jobID AND J.userID = U.userID AND U.userID = E.userID
						AND AllowedJob.userID  NOT IN (SELECT J.job_post_userID
							FROM JobSeeker_Apply_Job J
							WHERE J.userID = ? AND J.jobID = AllowedJob.jobID)";
					/*AND (J.userID, J.jobID) NOT IN
							(SELECT J.job_post_userID, J.jobID
							FROM JobSeeker_Apply_Job J
							WHERE J.userID = ?)*/	
					/*AND AllowedJob.jobID NOT IN (SELECT J.jobID
							FROM JobSeeker_Apply_Job J
							WHERE J.userID = ?)*/
		try{
		// Connect to database
		$stmt = $conn->prepare($SelectSQL);

		$stmt->bind_param('sssssssssss', $userID, $resumeID, $userID, $resumeID, $userID, $resumeID, $userID, $resumeID, $userID, $resumeID, $userID);

		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		}
		}catch(mysqli_sql_exception $e) {

		    echo $e->__toString();
		}
		$result = $stmt->get_result();
		return $result;
	}
	//
	//
	if ($_SERVER["REQUEST_METHOD"] == "POST"){	
		//Logout
		$_SESSION['userID'] = '';
		header('Location: index.php');
	}
	//Main php
	if (empty($userID)){
		echo "User ID is missing";
	}else{
		// Retrieve Information Add one by one with global ID as well.
		// Do not duplicate global ID which can be used in html.
		retrieveInfoUser($userID, $conn);
		if (empty($user_currentStatus) && empty($user_preferJob) && empty($user_currentJob)) {
			$jobSeeker = 0;
		}else{
			$jobSeeker = 1;
		}
		
		if (empty($user_companyName) && empty($user_description) && empty($user_department) && empty($user_companySize)) {
			$employer = 0;
		}else{
			$employer = 1;
		}
		retrieveInfoReference($userID, $conn);
		retrieveInfoJobPost($userID, $conn);
		retrieveInfoResume($userID, $conn);
	}
?>

<!DOCTYPE HTML>
<html> 
	<?php include 'head/head.php';?>
  	
  	<body>
	<!--User Information-->
	<div  class="container" >
			
		<div  class="container">    
			<br><br>
			<table class="table table-bordered">
			
				<tr>
					<td colspan="2" style="font-size:120%;" align="center"><b>General User Information </b><input type="button" class="btn btn-default" name="userEdit" value='Edit' onclick="location.href='user.php?mode=Update'"/></td>
				</tr>
				<tr>
					<td><b>User ID</b></td>
					<td><?php echo $userID; ?></td>
				</tr>
				<tr>
					<td><b>Name</b></td>
					<td><?php echo $user_name; ?></td>
				</tr>
				<?php if ($debug==1) { ?>
				<tr>
					<td><b>Age</b></td>
					<td><?php echo $user_age; }?></td>
				</tr>
				<tr>
					<td><b>Address</b></td>
					<td><?php echo $user_address; ?></td>
				</tr>
				<tr>
					<td><b>Email</b></td>
					<td><?php echo $user_email; ?></td>
				</tr>
				<tr>
					<td><b>Status</b></td>
					<td><?php if ($user_status==1) echo 'Active'; else echo 'Inactive'; ?></td>
				</tr>
				<?php  if ($employer != 1) {?>
				<tr>
					<td colspan="2" style="font-size:120%;" align="center"><b>Job Seeker Account Information</b></td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td><b>Current Status</b></td>
					<td><?php echo $user_currentStatus; }?></td>
				</tr>
				<?php if ($debug==1) { ?>
				<tr>
					<td><b>Prefer Job</b></td>
					<td><?php echo $user_preferJob; ?></td>
				</tr>
				<tr>
					<td><b>Current Job</b></td>
					<td><?php echo $user_currentJob; ?></td>
				</tr>
				<?php } if ($employer == 1) {?>
				<tr>
					<td colspan="2" style="font-size:120%;" align="center"><b>Employer Account Information</b></td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td><b>Company Name</b></td>
					<td><?php if($user_companyName != "") {echo $user_companyName;} else {echo "Currently unemployed";} }?></td>
				</tr>
				<?php if ($debug==1) { ?>
				<tr>
					<td><b>Description</b></td>
					<td><?php echo $user_description; ?></td>
				</tr>
				<tr>
					<td><b>Department</b></td>
					<td><?php echo $user_department; ?></td>
				</tr>
				<tr>
					<td><b>Company Size</b></td>
					<td><?php echo $user_companySize; ?></td>
				</tr>
				<?php } ?>
			</table>
		</div>
	</div>
	<!--End User Information-->
	<!--Job Posting Information-->
	<?php if ($employer == 1) { ?>
		<br><br>
		<div class="container" >
			<div class="container">    
				<table class="table table-bordered" style="border:1px solid black;">
					<caption style="font-size:140%;">
						<input type="button" class="btn btn-default" id="jobPostAdd" name="jobPostAdd" value='New Job Posting' onclick=<?php echo "\"location.href='jobPost.php?mode=New&employerID=" . urlencode($userID) . "'\""; ?> />
					</caption>
					<?php if ($jobPostSTID) {while($res = $jobPostSTID->fetch_array()) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Job Post No. <?php echo $res[0]; ?></b>
							<input type="button" class="btn btn-default" id="jobPostEdit" name="jobPostEdit" value='Edit' onclick=<?php echo "\"location.href='jobPost.php?mode=Update&jobPostID=" . urlencode($res[0]) . "&employerID=" . urlencode($res[10]) . "'\""; ?> />
						</td>
					</tr>
					<tr>
						<td><b>Job Title</b></td>
						<td><?php echo $res[1]; ?></td>
					</tr>
					<tr>
						<td><b>Job Description</b></td>
						<td><?php echo $res[4]; ?></td>
					</tr>
					<?php if ($debug==1) { ?>
					<tr>
						<td><b>Required GPA</b></td>
						<td><?php echo $res[2]; ?></td>
					</tr>
					<tr>
						<td><b>Required Degree</b></td>
						<td><?php echo $res[3]; ?></td>
					</tr>
					<tr>
						<td><b>Location</b></td>
						<td><?php echo $res[5]; ?></td>
					</tr>
					<tr>
						<td><b>Start Date</b></td>
						<td><?php echo $res[6]; ?></td>
					</tr>
					<tr>
						<td><b>Job Type</b></td>
						<td><?php echo $res[7]; ?></td>
					</tr>
					<tr>
						<td><b>Deadline</b></td>
						<td><?php echo $res[8]; ?></td>
					</tr>
					<tr>
						<td><b>Status</b></td>
						<td><?php if ($res[9]==1) echo 'Active'; else echo 'Inactive'; ?></td>
					</tr>
					<?php } ?>
					<?php $jobPostResumeSTID=retrieveInfoJobPostResume($res[0]); if ($jobPostResumeSTID) {while($res1 = $jobPostResumeSTID->fetch_array()) { ?>
					<tr>
						<td><b>Possible Candidate</b>
						<input class="btn btn-default" type="button" id="candidateResume" name="candidateResume" value='View Resume' onclick=<?php echo "\"location.href='resume.php?mode=View&resumeID=" . urlencode($res1[1]) . "&resumeUserID=" . urlencode($res1[0]) . "'\""; ?> />
						</td>
						<td><?php echo $res1[2]."<br>(".$res1[3].")</br>"; ?> 
						</td>
					</tr>
					<?php } }?>
					<?php $appliedJobInfoSTID=retrieveInfoAppliedInfo($res[0]); if ($appliedJobInfoSTID) {while($res1 = $appliedJobInfoSTID->fetch_array()) { ?>
					<tr>
						<td><b>Applied Candidate</b>
						<input class="btn btn-default" type="button" id="candidateResume" name="candidateResume" value='View User Info' onclick=<?php echo "\"location.href='candidate.php?mode=View&resumeUserID=" . urlencode($res1[0]) . "'\""; ?> />
						</td>
						<td><?php echo $res1[1]."<br>(".$res1[2].")</br>"; ?> 
						</td>
					</tr>
					<?php } }?>
					<?php } } ?>
				</table>
			</div>
		</div>
	<?php } ?>
	<!--End Job Posting Information-->
	<!--Resume Information-->
	<?php if ($jobSeeker == 1) { ?>
		<br><br>
		<div class="container" >
			<div class="container">    
				<table class="table table-bordered" style="border:1px solid black;">
					<caption style="font-size:140%;">
						<input type="button" class="btn btn-default" id="resumeAdd" name="resumeAdd" value='New Resume' onclick=<?php echo "\"location.href='resume.php?mode=New&resumeUserID=" . urlencode($userID) . "'\""; ?> />			
					</caption>
					<?php if ($resumePostSTID) {while($res = $resumePostSTID->fetch_array()) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Resume <?php //echo $res[4]; ?> Information </b>
							<input type="button" class="btn btn-default" id="jobPostEdit" name="jobPostEdit" value='Edit' onclick=<?php echo "\"location.href='Resume.php?mode=Update&resumeID=" . urlencode($res[4]) . "&resumeUserID=" . urlencode($res[6]) . "'\""; ?> />
						</td>
					</tr>
					<tr>
						<td><b>Degree/GPA</b></td>
						<td><?php echo $res[1]; ?></td>
					</tr>
					<tr>
						<td><b>School</b></td>
						<td><?php echo $res[2]; ?></td>
					</tr>
					<tr>
						<td><b>Additional Information</b></td>
						<td><?php echo $res[5]; ?></td>
					</tr>
					<?php if ($debug==1) { ?>
					<tr>
						<td><b>Graduation Date</b></td>
						<td><?php echo $res[3]; ?></td>
					</tr>
					<tr>
						<td><b>Status</b></td>
						<td><?php if ($res[7]==1) echo 'Active'; else echo 'Inactive'; ?></td>
					</tr>
					<?php } ?>
					<?php $jobPostResumeSTID=retrieveInfoCandidateJob($res[4]); if ($jobPostResumeSTID) {while($res1 = $jobPostResumeSTID->fetch_array()) { ?>
					<tr>
						<td><b>Possible Job</b>
						<input type="button" class="btn btn-default" id="candidateJob" name="candidateJob" value='View Job' onclick=<?php echo "\"location.href='jobPost.php?mode=View&jobPostID=" . urlencode($res1[2]) . "&employerID=" . urlencode($res1[0]) . "'\""; ?> />
						</td>
						<td><?php echo $res1[1]."<br>(".$res1[5].")</br>"; ?> 
						</td>
					</tr>
					<?php } }?>
					<?php } } ?>
				</table>
			</div>
		</div>
	<?php }?>
	<!--End Resume Posting Information-->
	<!--Reference Information-->
	<?php if ($jobSeeker == 1) { ?>
		<br></br>
		<div class="container" >
			<div class="container">    
				<table class="table table-bordered" >
					<caption style="font-size:140%;">
						<input type="button" class="btn btn-default" id="referenceAdd" name="referenceAdd" value='New Reference' onclick="location.href='reference.php?mode=New'"/>
					</caption>
					<?php if ($referenceSTID) { while($res = $referenceSTID->fetch_array()) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Reference <?//php echo $res[2]; ?> Information</b>
							<input type="button" class="btn btn-default" id="referenceEdit" name="referenceEdit" value='Edit' onclick=<?php echo "\"location.href='reference.php?mode=Update&referenceID=" . urlencode($res[2])."'\""; ?> />
						</td>
					</tr>
					<tr>
						<td><b>Name</b></td>
						<td><?php echo $res[7]; ?></td>
					</tr>
					<tr>
						<td><b>Company Name</b></td>
						<td><?php echo $res[6]; ?></td>
					</tr>
					<?php if ($debug==1) { ?>
					<tr>
						<td><b>Job Title</b></td>
						<td><?php echo $res[0]; ?></td>
					</tr>
					<tr>
						<td><b>Email</b></td>
						<td><?php echo $res[8]; ?></td>
					</tr>
					<tr>
						<td><b>Information</b></td>
						<td><?php echo $res[1]; ?></td>
					</tr>
					<tr>
						<td><b>Rating</b></td>
						<td><?php echo $res[5]; ?></td>
					</tr>
					<tr>
						<td><b>Relationship</b></td>
						<td><?php echo $res[3]; ?></td>
					</tr>
					<tr>
						<td><b>Duration(Years)</b></td>
						<td><?php echo $res[4]; ?></td>
					</tr>
					<tr>
						<td><b>Status</b></td>
						<td><?php if ($res[9]==1) echo 'Active'; else echo 'Inactive'; ?></td>
					</tr>
					<?php } ?>
					<?php } }?>
				</table>
			</div>
		</div>
	<?php } ?>
	<!--End Reference Information-->
	</body>
	<?php //$conn->close(); ?>
</html>