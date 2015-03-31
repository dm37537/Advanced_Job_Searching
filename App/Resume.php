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

	/*Insert or Update User Information */
	function updateInformation($sql, $conn){
		global $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status, $resumeUserID; 
		try{
			// Connect to database
			
			if ($sql == "create") {
				$userCreateSQL = "INSERT INTO Resume_Post (gpa,degree,school,graduationDate,resumeID,additionalInfomation,userID,status) VALUES (?,?,?,STR_TO_DATE( ? , '%Y-%m-%d'),?,?,?,?)";
				$stmt = $conn->prepare($userCreateSQL);
				$stmt->bind_param('ssssssss',  $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $resumeUserID, $status);
			}else{
				$userUpdateSQL = "UPDATE Resume_Post SET  gpa= ? , degree= ? , school= ?, graduationDate = STR_TO_DATE( ? , '%Y-%m-%d'), resumeID= ?, additionalInfomation= ?, status= ? WHERE resumeID= ? AND userID= ? ";
				$stmt = $conn->prepare($userUpdateSQL);
				$stmt->bind_param('sssssssss',  $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status, $resumeID, $resumeUserID);
			}	
			
			//$sql = "UPDATE Resume_Post SET  gpa= 3.96 , degree= 'bachelor' , school= 'UT', graduationDate = STR_TO_DATE( '2015-12-12' , '%Y-%m-%d'), resumeID= 2, additionalInfomation= 'No much', status= 1 WHERE resumeID= 2 AND userID= 'meng.da'";
			//$conn->query($sql);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;

			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
				return 0;
			}else{
				// Commit transaction
				mysqli_commit($conn);
				return 1;
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}
	
	}

	/*Select User Information */
	function getInformation($sql, $conn){
		global $gpa, $degree, $school, $graduationDate, $resumeID, $additionalInfomation, $status, $resumeUserID; 
		try{
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('ss', $resumeUserID, $resumeID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;

			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $stmt->get_result();
				$row = $result->fetch_array();
				$gpa = $row[0];
				$degree = $row[1];
				$school = $row[2];
				$graduationDate = $row[3];
				$additionalInfomation = $row[5];
				$status = $row[7];
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}
	}

	/* Retrieve Information for Selected User*/
	function retrieveInfo(){
		global $resumeID, $resumeUserID, $conn;
		$userSelectSQL = "SELECT gpa,degree,school,DATE_FORMAT(graduationDate, '%Y-%m-%d'),resumeID,additionalInfomation,userID,status FROM Resume_Post WHERE userID= ? AND resumeID= ?";
		getInformation($userSelectSQL, $conn);
	}

	/* Display skill information */
	function displaySkill(){
		global $resumeID, $resumeUserID, $conn;

		try{
			$sql = "SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID= ? AND R.userID= ? ";
			// Connect to database
			$stmt = $conn->prepare($sql);
			$stmt->bind_param('ss', $resumeID, $resumeUserID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;

			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $stmt->get_result();
				echo "<tr>
					<td>Tittle</td><td>Level</td><td>Description</td>
				</tr>";
				while ($row = $result->fetch_array())
				{
					echo "<tr>
							<td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td></tr>";
				}
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}
	}
	
	function displayExp(){
		global $resumeID, $resumeUserID, $conn;

		try{
			$sql = "SELECT resumeID,userID,experienceID,DATE_FORMAT(startDate, '%Y-%m-%d'),DATE_FORMAT(endDate, '%Y-%m-%d'),jobDescription,companyName,department,jobTitle  FROM Resume_Have_WorkExperience WHERE resumeID= ? AND userID= ? ";
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('ss', $resumeID, $resumeUserID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;

			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $stmt->get_result();
				echo "<tr>
						<td>Start Date</td><td>End Date</td><td>Job Description</td><td>Company Name</td><td>Department</td><td>Job Title</td><td>Edit</td>
					</tr>";
				while ($row = $result->fetch_array())
				{
					?>
						<tr>
							<td><?php echo $row[3]; ?></td><td><?php echo $row[4]; ?></td><td><?php echo $row[5]; ?></td><td><?php echo $row[6]; ?></td><td><?php echo $row[7]; ?></td><td><?php echo $row[8]; ?></td>
							<td><button class="btn btn-default" type='button' onclick="location.href='experience.php?mode=Edit&experienceID=<?php echo urlencode($row[2]);?>&resumeID=<?php echo urlencode($row[0]);?>&userID=<?php echo urlencode($row[1]);?>'">Edit </button></td>
						</tr>
					<?php 
				}
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
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
				
				$success=updateInformation('update', $conn);
				if ($success==1){
					// Successful Update return to the user information page
					//$conn->close();
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
						$_SERVER['PHP_SELF'];
						header('Location: ./main.php');
					}
					//exit;
				}
			} else{
				// Create 
				$userCreateSQL = "create";
				$success=updateInformation($userCreateSQL, $conn);
				if ($success==1){
					// Successful Update return to the user information page
					//$conn->close();
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
						$_SERVER['PHP_SELF'];
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
	.table table-bordered td
	{
		text-align: left;
	}
	#center td
	{
		text-align: center;
	}
	h2, h3 
	{
		text-align: center;
	}

	</style>
	<?php include 'head/head.php';?>
	<body>
		<div class="container">
		<div class="container">
		<br><br>
		<form class="form-inline" method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
			<table class="table table-bordered" >
				<tr><td colspan='2'><h2><?php  echo $mode; ?> Resume Information</h2></td></tr>
				<tr><td><label>User ID*:</label> </td><td><input class="form-control" type="text" name="resumeUserID" value='<?php echo $resumeUserID; ?>' readonly ></td></tr>

				<tr><td><label>Resume ID*:  </label> </td><td><input class="form-control" type="text" name="resumeID" value='<?php echo $resumeID; ?>' <?php if (($mode=="Update")||($mode=="View")) echo 'readonly'; ?> >
				</td></tr>
				<tr><td><label>GPA :  </label> </td><td><input class="form-control" type="number" name="gpa" min="0" max="5" step="0.01" value='<?php echo $gpa; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
				</td></tr>
				<tr><td><label>Degree :  </label> </td><td>
						<div class="radio">
								<input  type="radio" name="degree" value="Bachelor" <?php if ($degree=='Bachelor') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Bachelor
								 <input type="radio" name="degree" value="Master" <?php if ($degree=='Master') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>> Master 
								 <input type="radio" name="degree" value="Doctorate" <?php if ($degree=='Doctorate') {echo 'checked';} ?> <?php if ($mode=="View") echo 'disabled'; ?>>Doctorate
						</div>
				</td></tr>

				<tr><td><label>School :</label> </td><td><input class="form-control" type="text" name="school" value='<?php echo $school; ?>' <?php if ($mode=="View") echo 'readonly'; ?> >
				</td></tr>
				<tr><td><label>Graduation Date('mm/dd/yyyy') : </label></td><td><input class="form-control" type="text" name="graduationDate" value='<?php echo $graduationDate; ?>' <?php if ($mode=="View") echo 'readonly'; ?>>
				</td></tr>
				<tr><td><label>Additional Information :  </label></td><td><TEXTAREA class="form-control" cols="40" rows="5" name="additionalInfomation"  <?php if ($mode=="View") echo 'readonly'; ?> ><?php echo $additionalInfomation; ?>		</TEXTAREA>
				</td></tr>
				<tr><td><label>Status  : </label> </td><td> 
						<div class="radio">
								<input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Active	 
						 		<input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>  <?php if ($mode=="View") echo 'disabled'; ?>> Inactive
						</div>
				</td></tr>
				<tr ><td colspan='2'><h3><?php echo $mode; ?> Skills
				<?php if ($mode!="View") {?>
				<input class="btn btn-default" type="submit" id="skillEdit" name="skillEdit" value='Add Skill' />
				<?php } ?>
				</h3></td></tr>
				<tr><td colspan='2'><table class="table table-striped" id="center"><?php displaySkill(); ?></table></td></tr>

				<tr><td colspan='2'><h3><?php echo $mode; ?> Experience
				<?php if ($mode!="View") {?>
				<input class="btn btn-default" type="submit" id="expEdit" name="expEdit" value='Add Experience' />
				<?php } ?>
				</h3></td></tr>

				<tr><td colspan='2'><table class="table table-striped" id="center"><?php displayExp(); ?></table></td></tr>
				<tr id="center"><td colspan='2'>
				<?php if ($mode!="View") { ?>
				<input class="btn btn-default" type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} else {echo 'Update';} ?>' >
				<?php } else { ?>
				<button class="btn btn-default" type='button' onclick="location.href='candidate.php?resumeUserID=<?php echo $resumeUserID;?>'">Contact</button>
				<?php } ?>
				<input class="btn btn-default" type="button" name='cancel' value="Cancel" onclick="location.href='./main.php'" />
				
				</td></tr>
			</table>
		</form>
		</div>
		</div>
	<?php $conn->close();?>

	</body>
</html>