<?php
	session_start();
	require_once "connection.php";
	/*Initialize parameter*/
	$mode=$_GET['mode'];
	$resumeUserID=$_GET['resumeUserID'];
	if (isset($_SESSION['userID'])) {
		$userID = $_SESSION['userID'];
	}else{
		$userID = "";
	}
	$gpa=""; $degree=""; $school=""; $graduationDate=""; 
	$additionalInfomation=""; $status=""; $resumeID="";

	/*Get Variable*/
	function getValues(){
		global $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status; 
		$gpa=$_POST['gpa']; 
		if (isset($_POST['degree'])){
			$degree=trim($_POST['degree']); 
		}else{
			$degree=NULL;
		}
		$school=$_POST['school']; 
		$graduationDate=$_POST['graduationDate']; 
		$additionalInfomation=$_POST['additionalInfomation']; 
		$status=$_POST['status']; 
		$resumeID=$_POST['resumeID']; 
	}

	/*Bind Variable for SQL Update*/
	function bindVariable($stid){
		// Bind to the variables
		global $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status, $resumeUserID;
		oci_bind_by_name($stid, ":userID", $resumeUserID);
		oci_bind_by_name($stid, ":gpa", $gpa);
		oci_bind_by_name($stid, ":degree", $degree);
		oci_bind_by_name($stid, ":school", $school);
		oci_bind_by_name($stid, ":graduationDate", $graduationDate);
		oci_bind_by_name($stid, ":resumeID", $resumeID);
		oci_bind_by_name($stid, ":additionalInfomation", $additionalInfomation);
		oci_bind_by_name($stid, ":status", $status);
	}

	/*Insert or Update User Information */
	function updateInformation($sql, $conn){
		// Connect to database
		$stid = oci_parse($conn,$sql);
		bindVariable($stid);
		// Execute and Check Errors
		oci_execute($stid, OCI_NO_AUTO_COMMIT);	
		$err = oci_error($stid);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			if($err_code == 1) {
				$error_msg = "Your Resume ID is already used. Please try another Resume ID.<br>\n";
			} else {
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error messages : " . $err['messages']. "<br>";
			}
			echo $error_msg;
			return 0;
		} else {
			oci_commit($conn); 
			return 1;
		}
	}

	/*Select User Information */
	function getInformation($sql, $conn){
		global $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status, $resumeUserID; 
		// Connect to database
		$stid = oci_parse($conn,$sql);
		oci_bind_by_name($stid, ":userID", $resumeUserID);
		oci_bind_by_name($stid, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($stid, OCI_DEFAULT);
		$err = oci_error($stid);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error messages : " . $err['messages']. "<br>";
			echo $error_msg;
		} else {
			$row = oci_fetch_row($stid);
			$gpa = $row[0];
			$degree = $row[1];
			$school = $row[2];
			$graduationDate = $row[3];
			$additionalInfomation = $row[5];
			$status = $row[7];
		}
	}

	/* Retrieve Information for Selected User*/
	function retrieveInfo(){
		global $resumeID, $resumeUserID, $conn;
		$userSelectSQL = "SELECT gpa,degree,school,TO_CHAR(graduationDate,'mm/dd/yyyy'),resumeID,additionalInfomation,userID,status FROM Resume_Post WHERE userID=:userID AND resumeID=:resumeID";
		getInformation($userSelectSQL, $conn);
	}

	/* Display skill information */
	function displaySkill(){
		global $resumeID, $resumeUserID, $conn;
		$skillSTID = oci_parse($conn,"SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID=:resumeID AND R.userID=:userID");
		oci_bind_by_name($skillSTID, ":userID", $resumeUserID);
		oci_bind_by_name($skillSTID, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($skillSTID, OCI_DEFAULT);
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
	
	function displayExp(){
		global $resumeID, $resumeUserID, $conn;
		$expSTID = oci_parse($conn,"SELECT resumeID,userID,experienceID,TO_CHAR(startDate,'mm/dd/yyyy'),TO_CHAR(endDate,'mm/dd/yyyy'),jobDescription,companyName,department,jobTitle  FROM Resume_Have_WorkExperience WHERE resumeID=:resumeID AND userID=:userID");
		oci_bind_by_name($expSTID, ":userID", $resumeUserID);
		oci_bind_by_name($expSTID, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($expSTID, OCI_DEFAULT);
		$err = oci_error($expSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "EXPERIENCE DISPLY ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error messages : " . $err['messages']. "<br>";
			echo $error_msg;
		} else {
			echo "<table>
					<tr>
						<td>Start Date</td><td>End Date</td><td>Job Description</td><td>Company Name</td><td>Department</td><td>Job Title</td><td>Edit</td>
					</tr>";
			while ($row = oci_fetch_row($expSTID))
			{
				?>
				<tr>
						<td><?php echo $row[3]; ?></td><td><?php echo $row[4]; ?></td><td><?php echo $row[5]; ?></td><td><?php echo $row[6]; ?></td><td><?php echo $row[7]; ?></td><td><?php echo $row[8]; ?></td>
						<td><button type='button' onclick="location.href='experience.php?mode=Edit&&experienceID=<?php echo urlencode($row[2]);?>&&resumeID=<?php echo urlencode($row[0]);?>&&userID=<?php echo urlencode($row[1]);?>'">Edit </button></td>
						</tr>
				<?php 
			}
			echo "</table>";
		}
	}	
	
	// MAIN START FUNCTION
	// Check if it is from the submit or initial load
	if ($_SERVER["REQUEST_METHOD"] == "POST"){	
		//Check resumeUserID and resumeID
		$resumeID = $_POST['resumeID'];
		$resumeUserID = $_POST['resumeUserID'];
		if (empty($resumeID)||empty($resumeUserID)){
			echo "Resume ID or Resume User ID is missing";
		}else{
			// Get Variable Values
			getValues();
			//Check if Update or Create
			if ($mode=='Update') {
				// Update Information
				$userUpdateSQL = "UPDATE Resume_Post SET  gpa=:gpa, degree=:degree, school=:school, graduationDate=TO_DATE(:graduationDate,'mm/dd/yyyy'), resumeID=:resumeID, additionalInfomation=:additionalInfomation, status=:status WHERE resumeID=:resumeID AND userID=:userID";
				$success=updateInformation($userUpdateSQL, $conn);
				if ($success==1){
					// Successful Update return to the user information page
					oci_close($conn);
					if (isset($_POST['skillEdit'])) {
						//Resume Updated correctly now move to the Skill Section
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . "?" . $_SERVER["QUERY_STRING"];
						header('Location: skill.php?mode=Resume&resumeID=' . urlencode($resumeID). '&userID=' .urlencode($resumeUserID));
					}elseif(isset($_POST['expEdit'])){
						//Resume Updated correctly now move to the Experience Section
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . "?" . $_SERVER["QUERY_STRING"];
						header('Location: experience.php?mode=Add&resumeID=' . urlencode($resumeID). '&userID=' .urlencode($resumeUserID));
						//header('Location: main.php');
					}else{
						//Resume Updated now move to the main page.
						header('Location: main.php');
					}
					exit;
				}
			}else{
				// Create 
				$userCreateSQL = "INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES (:gpa,:degree,:school,TO_DATE(:graduationDate,'mm/dd/yyyy'),:resumeID, :additionalInfomation, :userID, :status)";
				$success=updateInformation($userCreateSQL, $conn);
				if ($success==1){
					// Successful Update return to the user information page
					oci_close($conn);
					if (isset($_POST['skillEdit'])) {
						//Resume Updated correctly now move to the Skill Section
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . '?mode=Update&resumeID=' . urlencode($resumeID) . '&resumeUserID=' .urlencode($resumeUserID);
						header('Location: skill.php?mode=Resume&resumeID=' . urlencode($resumeID). '&userID=' .urlencode($resumeUserID));
					}elseif(isset($_POST['expEdit'])){
						//Resume Updated correctly now move to the Experience Section
						$_SESSION['location'] = $_SERVER['PHP_SELF'] . '?mode=Update&resumeID=' . urlencode($resumeID) . '&resumeUserID=' .urlencode($resumeUserID);
						header('Location: experience.php?mode=Add&resumeID=' . urlencode($resumeID). '&userID=' .urlencode($resumeUserID));
					}else{
						//Resume Updated now move to the main page.
						header('Location: main.php');
					}
					exit;
				}
			}
		}			
	}elseif (($mode=="Update")||($mode=="View")){
		// Retrieve Information
		$resumeID = $_GET['resumeID'];		
		$_SESSION['resumeID'] = $resumeID;
		
		if (empty($resumeID)||empty($resumeUserID)){
			echo "Resume ID or Resume User ID is missing";
		}else{
			// Retrieve Information
			retrieveInfo();
		}
	}
?>

<!DOCTYPE HTML>
<html> 
	<style>
	table,th,td,div
	{
		border:1px solid black;
	}
	</style>
	<form method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
		<h3><?php  echo $mode; ?> Resume Information</h3>
		User ID* : <input type="text" name="resumeUserID" value='<?php echo $resumeUserID; ?>' readonly >
		<br><br>
		Resume ID*: <input type="text" name="resumeID" value='<?php echo $resumeID; ?>' <?php if (($mode=="Update")||($mode=="View")) echo 'readonly'; ?> >
		<br><br>
		GPA : <input type="number" name="gpa" min="0" max="5" step="0.01" value='<?php echo $gpa; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
		<br><br>
		Degree : <input type="radio" name="degree" value="Bachelor" <?php if ($degree=='Bachelor') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Bachelor
						 <input type="radio" name="degree" value="Master" <?php if ($degree=='Master') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Master 
						 <input type="radio" name="degree" value="Doctorate" <?php if ($degree=='Doctorate') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>>Doctorate
		<br><br>
		School : <input type="text" name="school" value='<?php echo $school; ?>' <?php if ($mode=="View") echo 'readonly'; ?> >
		<br><br>
		Graduation Date('mm/dd/yyyy') : <input type="text" name="graduationDate" value='<?php echo $graduationDate; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
		<br><br>
		Additional Information :  <TEXTAREA cols="40" rows="5" name="additionalInfomation"  <?php if ($mode=="View") echo 'readonly'; ?> ><?php echo $additionalInfomation; ?>		</TEXTAREA>
		<br><br>
		Status  : <input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Active
				 <input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Inactive
		<br><br>
		<h3><?php echo $mode; ?> Skills
		<?php if ($mode!="View") {?>
		<input type="submit" id="skillEdit" name="skillEdit" value='Add Skill' />
		<?php } ?>
		</h3>
		<?php displaySkill(); ?>
		<br><br>
		<h3><?php echo $mode; ?> Experience
		<?php if ($mode!="View") {?>
		<input type="submit" id="expEdit" name="expEdit" value='Add Experience' />
		<?php } ?>
		</h3>
		<?php displayExp(); ?>
		<br><br>
		<?php if ($mode!="View") {?>
		<input type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} else {echo 'Update';}?>' >
		<?php } ?>
		<input type="button" name='cancel' value="Cancel" onclick="location.href='/main.php'" />
	</form>
	<?php oci_close($conn); ?>
</html>