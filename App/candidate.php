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
		$userSelectSQL = "SELECT * FROM Users WHERE userID=?";
		$jobSeekerSelectSQL = "SELECT * FROM job_seeker WHERE userID=?";
		
		retrieveSQLUser($userSelectSQL, $conn, 1, $userID);
		retrieveSQLUser($jobSeekerSelectSQL, $conn, 2, $userID);
	}	
	function retrieveSQLUser($sql, $conn, $type, $userID){
		
		try{
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('s', $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;

			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $stmt->get_result();
				$rows = $result->fetch_array();
				if ($type == 1){
					global $user_password, $user_name, $user_age, $user_address, $user_email, $user_status;
					$user_password=$rows[1]; $user_name=$rows[2]; $user_age=$rows[3]; $user_address=$rows[4]; $user_email=$rows[5]; $user_status=$rows[6];
				}elseif ($type == 2){
					global $user_currentStatus, $user_preferJob, $user_currentJob;
					$user_currentStatus=$rows[0]; $user_preferJob=$rows[1]; $user_currentJob=$rows[2];
				}
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}

	}
	//REFERENCE 
	function retrieveInfoReference($userID, $conn){
		global $referenceSTID;
		$SelectSQL = "SELECT * FROM Reference_Recommend WHERE userID= ? ";
		try{
			// Connect to database
			$stmt = $conn->prepare($SelectSQL);

			$stmt->bind_param('s', $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			$referenceSTID = $stmt->get_result();

			if ($err) {
				$conn->rollback();
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}
		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}

	}
	//RESUME POSTING
	function retrieveInfoResume($userID, $conn){
		global $resumePostSTID;
		$SelectSQL = "SELECT gpa,degree,school,DATE_FORMAT(graduationDate, '%Y-%m-%d'),resumeID,additionalInfomation,userID,status FROM Resume_Post WHERE userID= ? ";
		
		try{
			// Connect to database
			$stmt = $conn->prepare($SelectSQL);

			$stmt->bind_param('s', $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			$resumePostSTID = $stmt->get_result();

			if ($err) {
				$conn->rollback();
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}
		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}

	}
	/* Display skill information */
	function displaySkill($resumeID){
		global $userID, $conn;

		$sql = "SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID= ? AND R.userID= ? ";

		try{
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('ss', $resumeID, $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			$skillSTID = $stmt->get_result();

			if ($err) {
				$conn->rollback();
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}else {
				echo "<table class='table table-striped' id='center'>
					<tr>
						<td>Tittle</td><td>Level</td><td>Description</td>
					</tr>";
				while ($row = $skillSTID->fetch_array())
				{
					echo "<tr>
							<td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td></tr>";
				}
				echo "</table>";
			}
		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}

	}
	function displayExp($resumeID){
		global $userID, $conn;
		
		$sql = "SELECT resumeID,userID,experienceID,DATE_FORMAT(startDate, '%Y-%m-%d'),DATE_FORMAT(endDate, '%Y-%m-%d'),jobDescription,companyName,department,jobTitle  FROM Resume_Have_WorkExperience WHERE resumeID= ? AND userID= ? ";

		try{
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('ss', $resumeID, $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			$expSTID = $stmt->get_result();

			if ($err) {
				$conn->rollback();
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}else {
				echo "<table class='table table-striped' id='center'>
						<tr>
							<td>Start Date</td><td>End Date</td><td>Job Description</td><td>Company Name</td><td>Department</td><td>Job Title</td>
						</tr>";
				while ($row = $expSTID->fetch_array())
				{
					?>
					<tr>
							<td><?php echo $row[3]; ?></td><td><?php echo $row[4]; ?></td><td><?php echo $row[5]; ?></td><td><?php echo $row[6]; ?></td><td><?php echo $row[7]; ?></td><td><?php echo $row[8]; ?></td>
							</tr>
					<?php 
				}
				echo "</table>";
			}
		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
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
	.table table-bordered td
	{
		text-align: left;
	}
	.table table-bordered 
	{
		width: 400px;
	}
	#center td
	{
		text-align: center;
	}

	</style>
	<?php include 'head/head.php';?>
	<body>

	<div class="container" >
		<div id="container">   
		<br><br> 
			<table class="table table-bordered">
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
		<div class="container" >
			<div id="container">    
				<table class="table table-bordered">
					<?php if ($resumePostSTID) {$ct=1; while($res = $resumePostSTID->fetch_array()) {?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Resume No.<?php echo $ct; $ct += 1; ?> </b>
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
		<div class="container" >
			<div id="container">    
				<table class="table table-bordered">
					<?php if ($referenceSTID) { $ct=1; while($res = $referenceSTID->fetch_array()) { ?>
					<tr>
						<td colspan="2" style="font-size:120%;" align="center">
							<b>Reference No.<?php echo $ct; $ct += 1; ?> </b>
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
	<?php $conn->close(); ?>
</body>
</html>