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
	try{
		$sql = "SELECT S.skillTitle, J.knowledgeLevel, J.skill_ID FROM Job_Require_Skill J, Skill S WHERE J.userID=? AND J.jobID=? AND J.skill_ID=S.skill_ID";
		// Connect to database
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('ss', $employerID, $jobID);
		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;
		$skillsSTID = $stmt->get_result();

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		}
	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
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


function updateInformation($sql, $conn){
	
	global $jobID, $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
	global $location, $startDate, $jobType, $deadline, $status, $conn, $employerID;
	try{
		// Connect to database		
		if ($sql == "create") {
			$CreateSQL = "INSERT INTO Job_Post (jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,startDate,jobType,deadline,status,userID) VALUES (?,?,?,?,?,STR_TO_DATE( ? , '%Y-%m-%d'),?,STR_TO_DATE( ? , '%Y-%m-%d'),?,?)";
			$stmt = $conn->prepare($CreateSQL);
			$stmt->bind_param('ssssssssss',   $jobTitle, $requiredDegree, $requiredDegree, $jobDescription, $location, $startDate, $jobType, $deadline, $status, $employerID);
		}else{
			$UpdateSQL = "UPDATE Job_Post SET jobTitle= ?, requiredGPA=?, requiredDegree=?, jobDescription=?, location=?, startDate = STR_TO_DATE( ? , '%Y-%m-%d'), jobType=?, deadline = STR_TO_DATE( ? , '%Y-%m-%d'), status=? WHERE userID=? AND jobID=?";
			$stmt = $conn->prepare($UpdateSQL);
			$stmt->bind_param('sssssssssss',  $jobTitle, $requiredGPA, $requiredDegree, $jobDescription, $location, $startDate, $jobType, $deadline, $status, $employerID, $jobID);
		}	

		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
			return 0;
		}else{
			//print('commit');
			// Commit transaction
			mysqli_commit($conn);
			return 1;
		}

	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
	}
}

function retrieveSQL($jobID, $employerID){
	global $jobTitle, $requiredGPA, $requiredDegree, $jobDescription;
	global $location, $startDate, $jobType, $deadline, $status, $conn;

	try{
		// Connect to database		
		$SelectSQL = "SELECT jobID,jobTitle,requiredGPA,requiredDegree,jobDescription,location,DATE_FORMAT(startDate, '%Y-%m-%d'),jobType,DATE_FORMAT(deadline, '%Y-%m-%d'),status,userID FROM Job_Post WHERE jobID= ? AND userID= ?";
		$stmt = $conn->prepare($SelectSQL);
		$stmt->bind_param('ss',   $jobID, $employerID);

		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;

		if ($err) {
			$conn->rollback();
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
			echo $error_msg;
		}else{
			$result = $stmt->get_result();
			while ($rows = $result->fetch_array())
			{
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

	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
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
				$success = updateInformation('update', $conn);	
				if ($success==1){
					if (isset($_POST['skillEdit'])) {
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . "?" . $_SERVER["QUERY_STRING"];
						header('Location: skill.php?mode=Job&jobID=' . urlencode($jobID). '&userID=' .urlencode($employerID));
					}else{
						header('Location: ./main.php');
					}
				}
			}elseif ($mode=='New'){
				// Create 
				$success = updateInformation('create', $conn);
				if ($success==1){
					// Successful Update return to the user information page
					if (isset($_POST['skillEdit'])) {
						//After successful creation return to the updating the job posting rather than create.
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . '?mode=Update&jobPostID=' . urlencode($jobID). '&employerID=' .urlencode($employerID);
						header('Location: skill.php?mode=Job&jobID=' . urlencode($jobID). '&userID=' .urlencode($employerID));
					}else{
						header('Location: ./main.php');
					}
				}
			}elseif ($mode=='View'){

				try{
					// Connect to database		
					$UpdateSQL = "INSERT INTO JobSeeker_Apply_Job (userID,jobID,job_post_userID,status) VALUES (?,?,?,1)";
					$stmt = $conn->prepare($UpdateSQL);
					$stmt->bind_param('sss', $userID, $jobID, $employerID);

					// Execute and Check Errors
					$stmt->execute();
					$err = $stmt->error;

					if ($err) {
						$conn->rollback();
						$err_code = $err['code']; 
						if($err_code == 1) {
							$error_msg = "You have already applied for this Job.<br>\n";
						}else{
							$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err['code']. "<br>". "Error message : " . $err['message']. "<br>";
						}
						echo $error_msg;
					}else{
						mysqli_commit($conn);
						header('Location: main.php');
					}
		
				}catch(mysqli_sql_exception $e) {
				    echo $e->__toString();
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
		retrieveSQL($jobID, $employerID);
		retrieveSkill();
	}
}

?>

<!DOCTYPE HTML>
<html> 
	<style>
	.table table-bordered td
	{
		text-align: left;
	}
	#center td
	{
		text-align: center;
	}
	h3 
	{
		text-align: center;
	}

	</style>
	<?php include 'head/head.php';?>
	<body>
		<div class="container">
		<div class="container">
		<br><br>
		<h3><?php  echo $mode; ?> Job Post Information</h3>
		<form class="form-inline" method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
	   <table class="table table-bordered" >
	   <tr><td><label>User ID* : </label> </td><td><input class="form-control" type="text" name="employerID" value='<?php echo $employerID; ?>' readonly >
	   </tr></td><tr><td>
	   <label>Job ID*: </label> </td><td><input class="form-control" type="text" name="jobID" value='<?php echo $jobID; ?>' <?php if (($mode=="Update")||($mode=="View")) echo 'readonly'; ?> >
	   </tr></td><tr><td>
	   <label>Job Title*    : </label> </td><td><input class="form-control" type="text" name="jobTitle" value='<?php echo $jobTitle; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Required GPA : </label> </td><td><input class="form-control" type="number"  name="requiredGPA" min="0" max="5" step="0.01" value='<?php echo $requiredGPA; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Required Degree : </label> </td><td>
	   				<div class="radio">
	   					<input type="radio" name="requiredDegree" value="Bachelor" <?php if ($requiredDegree=='Bachelor') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Bachelor
						 <input type="radio" name="requiredDegree" value="Master" <?php if ($requiredDegree=='Master') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Master 
						 <input type="radio" name="requiredDegree" value="Doctorate" <?php if ($requiredDegree=='Doctorate') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>>Doctorate
	   				</div>
	   </tr></td><tr><td>
	   <label>Job Description* :</label> </td><td><TEXTAREA class="form-control" cols="40" rows="5" name="jobDescription"  <?php if ($mode=="View") echo 'readonly'; ?> ><?php echo $jobDescription; ?></TEXTAREA>
	   </tr></td><tr><td>
	   <label>Start Date('mm/dd/yyyy')   :</label> </td><td><input class="form-control" type="text" name="startDate" value='<?php echo $startDate; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Location         :</label> </td><td><input class="form-control" type="text" name="jobLocation" value='<?php echo $location; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Job Type   :</label> </td><td><input class="form-control" type="text" name="jobType" value='<?php echo $jobType; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Dead Line('mm/dd/yyyy')   :</abel> </td><td><input class="form-control" type="text" name="deadline" value='<?php echo $deadline; ?>'  <?php if ($mode=="View") echo 'readonly'; ?>>
	   </tr></td><tr><td>
	   <label>Status  :</label> </td><td><input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Active
				 <input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Inactive
	    </tr></td><tr><td colspan='2'>
		<!--Skill Information-->  
		<table class="table table-striped" id="center">
			<h3><?php echo $mode; ?> Skills
				<?php if ($mode!="View") {?>
				<input class="btn btn-default" type="submit" id="skillEdit" name="skillEdit" value='Add Skill' />
				<?php } ?>
				</h3>
			<tr>
				<td><b>Skill </b></td>
				<td><b>Knowledge Level</b></td>
			</tr>
			<?php if ($skillsSTID){ while($res = $skillsSTID->fetch_array()) { ?>
			<tr>
				<td><?php echo $res[0]; ?></td>
				<td><?php echo $res[1]; ?></td>
			</tr>
			<?php }} ?>
		</table>

			<!--End Skill Information-->
	   </tr></td><tr id="center"><td colspan='2'>

	   <input class="btn btn-default" type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} elseif ($mode=='Update') {echo 'Update';} else {echo 'Apply';}?>' />
	   <input class="btn btn-default"  type="button" name='cancel' value="Cancel" onclick="location.href='./main.php'" />
	</tr></td></table>
	</form>
</div>
</div>
</body>
</html>