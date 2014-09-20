<?php
session_start();
require_once "connection.php";
/*Initialize parameter*/
$mode=$_GET['mode'];
$employerID=$_GET['employerID'];
if (isset($_SESSION['userID'])) {
	$userID = $_SESSION['userID'];
}else{
	$userID = "";
}
$jobID=""; $jobTitle=""; $requiredGPA=""; $requiredDegree=""; $jobDescription=""; 
$location=""; $startDate=""; $jobType=""; $deadline=""; $status=""; 
$skillsSTID="";
function retrieveSkill(){
	global $skillsSTID,$employerID, $conn, $jobID;
	$SelectSQL = "SELECT S.skillTitle, J.knowledgeLevel, J.skill_ID FROM Job_Require_Skill J, Skill S WHERE J.userID=:employerID AND J.jobID=:jobID AND J.skill_ID=S.skill_ID";
	// Connect to database
	$skillsSTID = oci_parse($conn, $SelectSQL);
	oci_bind_by_name($skillsSTID, ":jobID", $jobID);
	oci_bind_by_name($skillsSTID, ":employerID", $employerID);
	// Execute and Check Errors
	oci_execute($skillsSTID);	
	$err = oci_error($skillsSTID);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		echo $error_msg;
	}
}

/*Get Variable*/
function getValues(){
	global $jobID, $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
	global $location, $startDate, $jobType, $deadline, $status;
 	$jobID=trim($_POST['jobID']); 
 	$jobTitle=trim($_POST['jobTitle']); 
 	$requiredGPA=trim($_POST['requiredGPA']); 
	if (isset($_POST['requiredDegree'])){
		$requiredDegree=trim($_POST['requiredDegree']); 
	}else{
		$requiredDegree="";
	}
 	$jobDescription=trim($_POST['jobDescription']);
 	$location=trim($_POST['jobLocation']); 
 	$startDate=trim($_POST['startDate']); 
 	$jobType=trim($_POST['jobType']);
 	$deadline=trim($_POST['deadline']); 
	if (isset($_POST['status'])){
		$status=trim($_POST['status']); 
	}else{
		$status="";
	}
}

/*Bind Variable for SQL Update*/
function bindVariable($stid){
	global $jobID, $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
	global $location, $startDate, $jobType, $deadline, $status, $employerID;
	oci_bind_by_name($stid, ":jobID", $jobID);
	oci_bind_by_name($stid, ":jobTitle", $jobTitle);
	oci_bind_by_name($stid, ":requiredGPA", $requiredGPA);
	oci_bind_by_name($stid, ":requiredDegree", $requiredDegree);
	oci_bind_by_name($stid, ":jobDescription", $jobDescription);
	oci_bind_by_name($stid, ":location", $location);
	oci_bind_by_name($stid, ":startDate", $startDate);
	oci_bind_by_name($stid, ":jobType", $jobType);
	oci_bind_by_name($stid, ":deadline", $deadline);
	oci_bind_by_name($stid, ":status", $status);
	oci_bind_by_name($stid, ":userID", $employerID);
}

function updateInformation($sql, $conn){
	// Connect to database
	$stid = oci_parse($conn,$sql);
	bindVariable($stid);
	// Execute and Check Errors
	oci_execute($stid,OCI_NO_AUTO_COMMIT);	
	$err = oci_error($stid);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		if($err_code == 1) {
			$error_msg = "Your Job ID is already used. Please try another Job ID.<br>\n";
		} else {
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		}
		echo $error_msg;
		return 0;
	} else {
		oci_commit($conn); 
		return 1;
	}
}

function retrieveSQL($sql){
	global $jobID, $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
	global $location, $startDate, $jobType, $deadline, $status, $conn, $employerID;
	// Connect to database
	$stid = oci_parse($conn,$sql);
	oci_bind_by_name($stid, ":jobID", $jobID);
	oci_bind_by_name($stid, ":employerID", $employerID);
	// Execute and Check Errors
	oci_execute($stid);	
	$err = oci_error($stid);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		echo $error_msg;
	} else {
		// Retrieve Variable and fill in the global.
		$rows = oci_fetch_row($stid);
		$jobID=$rows[0];
		$jobTitle=$rows[1];
		$requiredGPA=$rows[2];
		$requiredDegree=$rows[3];
		$jobDescription=$rows[4];
		$location=$rows[5];
		$startDate=$rows[6];
		$jobType=$rows[7];
		$deadline=$rows[8];
		$status=$rows[9];
		$employerID=$rows[10];
	}	
}

// MAIN START FUNCTION
// Check if it is from the submit or initial load
if ($_SERVER["REQUEST_METHOD"] == "POST"){	
	//Check employerID and jobID
	$employerID = $_POST['employerID'];
	$jobID = $_POST['jobID'];
	if (empty($employerID)||empty($jobID)){
		echo "User ID or Job ID is missing";
	}
	else{
		// Get Variable Values
		getValues();
		if (empty($employerID)||empty($jobID)||empty($jobTitle)||empty($jobDescription)){
			echo "Required field is missing (User ID, Job ID, Job TItle, Job Description";
		}else{
			//Check if Update or Create
			if ($mode=='Update') {
				// Update Information
				global $jobID, $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
				global $location, $startDate, $jobType, $deadline, $status, $conn, $employerID;
				$UpdateSQL = "UPDATE Job_Post SET jobTitle=:jobTitle, requiredGPA=:requiredGPA, requiredDegree=:requiredDegree, jobDescription=:jobDescription, location=:location, startDate=TO_DATE(:startDate,'mm/dd/yyyy'), jobType=:jobType, deadline=TO_DATE(:deadline,'mm/dd/yyyy'), status=:status WHERE userID=:userID AND jobID=:jobID";
				$success = updateInformation($UpdateSQL, $conn);	
				if ($success==1){
					oci_close($conn);
					if (isset($_POST['skillEdit'])) {
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . "?" . $_SERVER["QUERY_STRING"];
						header('Location: skill.php?mode=Job&jobID=' . urlencode($jobID). '&userID=' .urlencode($employerID));
					}else{
						header('Location: main.php');
					}
					exit;
				}
			}elseif ($mode=='New' || $mode=='Demo'){
				// Create 
				$CreateSQL = "INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES (:jobID,:jobTitle,:requiredGPA,:requiredDegree,:jobDescription,:location,TO_DATE(:startDate,'mm/dd/yy'),:jobType,TO_DATE(:deadline,'mm/dd/yy'),:status,:userID)";
				$success = updateInformation($CreateSQL, $conn);
				if ($success==1){
					// Successful Update return to the user information page
					oci_close($conn);
					if (isset($_POST['skillEdit'])) {
						//After successful creation return to the updating the job posting rather than create.
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . '?mode=Update&jobPostID=' . urlencode($jobID). '&employerID=' .urlencode($employerID);
						header('Location: skill.php?mode=Job&jobID=' . urlencode($jobID). '&userID=' .urlencode($employerID));
					}else{
						header('Location: main.php');
					}
					exit;
				}
			}elseif ($mode=='View'){
				// Applying for Job
				$stid = oci_parse($conn,"INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES (:userID,:jobID,:job_post_userID,1)");
				oci_bind_by_name($stid, ":userID", $userID);
				oci_bind_by_name($stid, ":jobID", $jobID);
				oci_bind_by_name($stid, ":job_post_userID", $employerID);
				// Execute and Check Errors
				oci_execute($stid,OCI_NO_AUTO_COMMIT);	
				$err = oci_error($stid);
				if ($err) {
					oci_rollback($conn); 
					$err_code = $err['code']; 
					if($err_code == 1) {
						$error_msg = "You have already applied for this Job.<br>\n";
					} else {
						$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
					}
					echo $error_msg;
				} else {
					oci_commit($conn); 
					header('Location: main.php');
				}
			}
		}
	}		
}elseif (($mode=="Update")||($mode=="View")){
	$jobID=$_GET['jobPostID'];
	$employerID=$_GET['employerID'];
	if (empty($jobID)||empty($employerID)){
		echo "Job ID or Employer User ID is missing";
	}else{
		// Retrieve Information
		$SelectSQL = "SELECT jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,TO_CHAR(startDate, 'mm/dd/yyyy'),jobType,TO_CHAR(deadline,'mm/dd/yyyy'),status,userID FROM Job_Post WHERE jobID=:jobID AND userID=:employerID";
		retrieveSQL($SelectSQL);
		retrieveSkill();
	}
}
if ($mode=='Demo')
{
	$jobID = 1;
	$jobTitle = "Oncology Nurse";
	$jobDescription = "Rational Stationary Nurse in Oncology Unit, 4 Shift a week. ";
	$startDate = "06/01/2014";
	$location = "Holy Name, NJ";
	$jobType = "Medical Profession";
	$deadline = "05/15/2014";
	$status = 1;
	
	
}
?>

<!DOCTYPE HTML>
<html> 
	<form method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
	   <h3><?php  echo $mode; ?> Job Post Information</h3>
	   User ID* : <input type="text" name="employerID" value='<?php echo $employerID; ?>' readonly >
	   <br><br>
	   Job ID*: <input type="text" name="jobID" value='<?php echo $jobID; ?>' <?php if (($mode=="Update")||($mode=="View")) echo 'readonly'; ?> >
	   <br><br>
	   Job Title*    : <input type="text" name="jobTitle" value='<?php echo $jobTitle; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>
	   Required GPA : <input type="number"  name="requiredGPA" min="0" max="5" step="0.01" value='<?php echo $requiredGPA; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>
	   Required Degree : <input type="radio" name="requiredDegree" value="Bachelor" <?php if ($requiredDegree=='Bachelor') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Bachelor
						 <input type="radio" name="requiredDegree" value="Master" <?php if ($requiredDegree=='Master') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Master 
						 <input type="radio" name="requiredDegree" value="Doctorate" <?php if ($requiredDegree=='Doctorate') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>>Doctorate
	   <br><br>
	   Job Description* : <TEXTAREA cols="40" rows="5" name="jobDescription"  <?php if ($mode=="View") echo 'readonly'; ?> ><?php echo $jobDescription; ?></TEXTAREA>
	   <br><br>
	   Start Date('mm/dd/yyyy')   : <input type="text" name="startDate" value='<?php echo $startDate; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>
	   Location         : <input type="text" name="jobLocation" value='<?php echo $location; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>
	   Job Type   : <input type="text" name="jobType" value='<?php echo $jobType; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>
	   Dead Line('mm/dd/yyyy')   : <input type="text" name="deadline" value='<?php echo $deadline; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   <br><br>	
	   Status  : <input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Active
				 <input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Inactive
	    <br><br>
		<!--Skill Information-->
		<style>
		table,th,td
		{
			border:1px solid black;
		}
		</style>
		<div class="panel2" >
			<div id="charInfo">    
				<table width="500px" style="border:1px solid black;">
					<caption style="font-size:100%;">
						<b>Skill Information<b>
						<?php if ($mode!="View"){ ?>
						<input type="submit" id="skillEdit" name="skillEdit" value='Edit' />
						<?php } ?>
					</caption>
					<tr>
						<td><b>Skill </b></td>
						<td><b>Knowledge Level</b></td>
					</tr>
					<?php if ($skillsSTID){ while($res = oci_fetch_row($skillsSTID)) { ?>
					<tr>
						<td><?php echo $res[0]; ?></td>
						<td><?php echo $res[1]; ?></td>
					</tr>
					<?php }} ?>
					</table>
				</div>
			</div>
			<!--End Skill Information-->
	   

	   <input type="submit" name='submit' value='<?php if ($mode=='New'|| $mode=='Demo'){echo 'Submit';} elseif ($mode=='Update') {echo 'Update';} else {echo 'Apply';}?>' />
	   <input type="button" name='cancel' value="Cancel" onclick="location.href='/main.php'" />
		 <input type="button" name='GenerateEmployerDemo' value="GenerateEmployerDemo" onclick="location.href='jobPost.php?mode=Demo&employerID=Holy_name_hospital'" />	
	</form>
</html>