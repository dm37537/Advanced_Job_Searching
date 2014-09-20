<?php
	session_start();
	require_once "connection.php";
	if (isset($_GET['resumeUserID'])) {
		$userID = $_GET['resumeUserID'];
	}else{
		$userID = "";
	}
	//
	//
	//Global Variable
	//USER  -- Add user_ in front of the variable to remove confusion
	$user_password=""; $user_name=""; $user_age=""; $user_address=""; $user_email=""; $user_status="";
	$user_currentStatus=""; $user_preferJob=""; $user_currentJob="";
	$user_companyName=""; $user_description=""; $user_department=""; $user_companySize="";
	//REFERENCE 
	$referenceSTID = "";
	//RESUME POSTING 
	$resumePostSTID ="";
	//
	//Loading Module for each section
	//USER
	function retrieveInfoUser($userID, $conn){
		$userSelectSQL = "SELECT * FROM Users WHERE userID=:userID";
		$jobSeekerSelectSQL = "SELECT * FROM job_seeker WHERE userID=:userID";
		
		retrieveSQLUser($userSelectSQL, $conn, 1, $userID);
		retrieveSQLUser($jobSeekerSelectSQL, $conn, 2, $userID);
	}	
	function retrieveSQLUser($sql, $conn, $type, $userID){
		// Connect to database
		$stid = oci_parse($conn,$sql);
		oci_bind_by_name($stid, ":userID", $userID);
		// Execute and Check Errors
		oci_execute($stid, OCI_NO_AUTO_COMMIT);	
		$err = oci_error($stid);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		} else {
			// Retrieve Variable and fill in the global.
			$rows = oci_fetch_row($stid);
			if ($type == 1){
				global $user_password, $user_name, $user_age, $user_address, $user_email, $user_status;
				$user_password=$rows[1]; $user_name=$rows[2]; $user_age=$rows[3]; $user_address=$rows[4]; $user_email=$rows[5]; $user_status=$rows[6];
			}elseif ($type == 2){
				global $user_currentStatus, $user_preferJob, $user_currentJob;
				$user_currentStatus=$rows[0]; $user_preferJob=$rows[1]; $user_currentJob=$rows[2];
			}
		}	
	}
	//REFERENCE 
	function retrieveInfoReference($userID, $conn){
		global $referenceSTID;
		$SelectSQL = "SELECT * FROM Reference_Recommend WHERE userID=:userID";
		// Connect to database
		$referenceSTID = oci_parse($conn, $SelectSQL);
		oci_bind_by_name($referenceSTID, ":userID", $userID);
		// Execute and Check Errors
		oci_execute($referenceSTID, OCI_NO_AUTO_COMMIT);	
		$err = oci_error($referenceSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}
	}
	//RESUME POSTING
	function retrieveInfoResume($userID, $conn){
		global $resumePostSTID;
		$SelectSQL = "SELECT gpa,degree,school,TO_CHAR(graduationDate,'mm/dd/yyyy'),resumeID,additionalInfomation,userID,status FROM Resume_Post WHERE userID=:userID";
		// Connect to database
		$resumePostSTID = oci_parse($conn, $SelectSQL);
		oci_bind_by_name($resumePostSTID, ":userID", $userID);
		// Execute and Check Errors
		oci_execute($resumePostSTID, OCI_NO_AUTO_COMMIT);	
		$err = oci_error($resumePostSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}
	}
	/* Display skill information */
	function displaySkill($resumeID){
		global $userID, $conn;
		$skillSTID = oci_parse($conn,"SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID=:resumeID AND R.userID=:userID");
		oci_bind_by_name($skillSTID, ":userID", $userID);
		oci_bind_by_name($skillSTID, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($skillSTID, OCI_NO_AUTO_COMMIT);
		$err = oci_error($skillSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "SKILL DIPLAY ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error messages : " . $err['messages']. "<br>";
			echo $error_msg;
		} else {
			echo "<table>
				<tr>
					<td>Tittle</td><td>Level</td><td>Description</td>
				</tr>";
			while ($row = oci_fetch_row($skillSTID))
			{
				echo "<tr>
						<td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td></tr>";
			}
			echo "</table>";
		}
	}
	function displayExp($resumeID){
		global $userID, $conn;
		$expSTID = oci_parse($conn,"SELECT resumeID,userID,experienceID,TO_CHAR(startDate,'mm/dd/yyyy'),TO_CHAR(endDate,'mm/dd/yyyy'),jobDescription,companyName,department,jobTitle  FROM Resume_Have_WorkExperience WHERE resumeID=:resumeID AND userID=:userID");
		oci_bind_by_name($expSTID, ":userID", $userID);
		oci_bind_by_name($expSTID, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($expSTID, OCI_NO_AUTO_COMMIT);
		$err = oci_error($expSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "EXPERIENCE DISPLY ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error messages : " . $err['messages']. "<br>";
			echo $error_msg;
		} else {
			echo "<table>
					<tr>
						<td>Start Date</td><td>End Date</td><td>Job Description</td><td>Company Name</td><td>Department</td><td>Job Title</td>
					</tr>";
			while ($row = oci_fetch_row($expSTID))
			{
				?>
				<tr>
						<td><?php echo $row[3]; ?></td><td><?php echo $row[4]; ?></td><td><?php echo $row[5]; ?></td><td><?php echo $row[6]; ?></td><td><?php echo $row[7]; ?></td><td><?php echo $row[8]; ?></td>
						</tr>
				<?php 
			}
			echo "</table>";
		}
	}	
	//
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
		retrieveInfoResume($userID, $conn);
	}
?>

<!DOCTYPE HTML>
<html> 
	<style>
	table,th,td
	{
		border:1px solid black;
	}
	</style>
	<!--User Information-->
	<div class="panel2" >
		<div id="charInfo">    
			<table width="500px" style="border:1px solid black;">
				<caption style="font-size:140%;">
					<b>User Information</b>
                </caption>
				<tr>
					<td colspan="2" style="font-size:120%;" align="center"><b>General User Information</b></td>
				</tr>
				<tr>
					<td><b>Name</b></td>
					<td><?php echo $user_name; ?></td>
				</tr>
				<tr>
					<td><b>Email</b></td>
					<td><?php echo $user_email; ?></td>
				</tr>
				<tr>
					<td><b>Status</b></td>
					<td><?php if ($user_status==1) echo 'Active'; else echo 'Inactive'; ?></td>
				</tr>
				<tr>
					<td colspan="2" style="font-size:120%;" align="center"><b>Job Seeker Account Information</b></td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td><b>Current Status</b></td>
					<td><?php echo $user_currentStatus; ?></td>
				</tr>
				<tr>
					<td><b>Prefer Job</b></td>
					<td><?php echo $user_preferJob; ?></td>
				</tr>
				<tr>
					<td><b>Current Job</b></td>
					<td><?php echo $user_currentJob; ?></td>
				</tr>
			</table>
		</div>
	</div>
	<!--End User Information-->
	<br><br>
	<!--Resume Information-->
	<?php if ($jobSeeker == 1) { ?>
		<br><br>
		<div class="panel2" >
			<div id="charInfo">    
				<table width="800px" style="border:1px solid black;">
					<caption style="font-size:140%;">
						<b>Resume Information</b>
					</caption>
					<?php if ($resumePostSTID) {while($res = oci_fetch_row($resumePostSTID)) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Resume ID: <?php echo $res[4]; ?></b>
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
					<tr>
						<td><b>Graduation Date</b></td>
						<td><?php echo $res[3]; ?></td>
					</tr>
					<tr>
						<td><b>Status</b></td>
						<td><?php if ($res[7]==1) echo 'Active'; else echo 'Inactive'; ?></td>
					</tr>
					<tr>
						<td><b>Skill</b></td>
						<td><?php displaySkill($res[4]); ?></td>
					</tr>
					<tr>
						<td><b>Work Experience</b></td>
						<td><?php displayExp($res[4]); ?></td>
					</tr>
					<?php } } ?>
				</table>
			</div>
		</div>
	<?php }?>
	<!--End Resume Posting Information-->
	<br><br>
	<!--Reference Information-->
	<?php if ($jobSeeker == 1) { ?>
		<div class="panel2" >
			<div id="charInfo">    
				<table width="500px" style="border:1px solid black;">
					<caption style="font-size:140%;">
						<b>Reference Information</b>
					</caption>
					<?php if ($referenceSTID) { while($res = oci_fetch_row($referenceSTID)) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Reference ID: <?php echo $res[2]; ?></b>
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
					<?php } }?>
				</table>
			</div>
		</div>
	<?php } ?>
	<!--End Reference Information-->
	<input type="button" name='cancel' value="Return to main" onclick="location.href='/main.php'" />
	<?php oci_close($conn); ?>
</html>