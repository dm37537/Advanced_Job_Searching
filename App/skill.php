
<?php
	session_start();
	require_once "connection.php";
	$mode=$_GET['mode'];	
	if (isset($_GET['jobID'])) $jobID = $_GET['jobID'];
	if (isset($_GET['userID'])) $userID = $_GET['userID'];
	if (isset($_GET['resumeID'])) $resumeID = $_GET['resumeID'];
	$skillTitle=""; $skill_type=""; $skill_ID=""; $skillDescription="";
	
	function displaySkillInfo($type){
		global $conn,$resumeID,$jobID,$userID;
		if ($type==1){
		// Resume Skill Print
		$get_information = "SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID='".$resumeID. "' AND userID='".$userID."'";
		}else{
		// Job Post Skill Print
		$get_information = "SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Job_Require_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and jobID='".$jobID. "' AND userID='".$userID."'";
		}


		try{
			// Connect to database
			$stmt = $conn->prepare($get_information);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $stmt->get_result();
				echo "<table class='table table-bordered'><tr id='center'><td colspan='4'><h3>Current Skill Information</h3></td></tr>
							<tr>
								<td>Tittle</td><td>Level</td><td>Description</td>
								<td>

								</td>
							</tr>";
				while ($row = $result->fetch_array())
				{
					echo "<tr>
							<td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td><button name='delete' type='submit' value='".$row[0]."'>Delete</button></td>"."</tr>";
				}
				echo "</table>";
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}

	}
	
	function displaySkillOptionInfo(){
		global $conn, $mode, $resumeID, $userID, $jobID;
		$count=0;
		// Get Skill List and ID Form.  Modified to list only skill which are not added yet.
		try{
			if ($mode=='Job'){
				$skillListSQL = "SELECT S.skillTitle, S.skill_ID FROM Skill S WHERE (S.skillTitle, S.skill_ID) NOT IN (SELECT S.skillTitle, S.skill_ID FROM SKILL S, Job_Require_Skill R WHERE R.skill_ID=S.skill_ID AND R.jobID= ? AND R.userID = ? )";
				$skillListSTID = $conn->prepare($skillListSQL);
				$skillListSTID->bind_param('ss', $jobID, $userID);
			} else{
				$skillListSQL = "SELECT S.skillTitle, S.skill_ID FROM Skill S WHERE (S.skillTitle, S.skill_ID) NOT IN (SELECT S.skillTitle, R.skill_ID FROM SKILL S, Resume_Have_Skill R WHERE R.skill_ID=S.skill_ID AND R.resumeID= ? AND R.userID = ? )";
				$skillListSTID = $conn->prepare($skillListSQL);
				$skillListSTID->bind_param('ss', $resumeID, $userID);
			}
			$skillListSTID->execute();
			$err = $skillListSTID->error;
			if ($err) {
				$conn->rollback();
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
				echo $error_msg;
			}else{
				$result = $skillListSTID->get_result();
				echo "<table class='table table-bordered'>";
				while ($row = $result->fetch_array()){
					
					echo "<tr><td><input type='hidden' name='skill[]' value='".$row[1]."'>";
					echo "<input type='checkbox' name='skill[]' value='".$row[1]."'></td><td>". $row[0]."</td>";
					echo "<td><select name='skilllevel[]'>
							<option value='0'>0</option>
							<option value='1'>1</option>
							<option value='2'>2</option>
							<option value='3'>3</option>
							<option value='4'>4</option>
							<option value='5'>5</option>
							</select></td></tr>";
					
				}
				echo "</table>";
			}

		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
		}
	}
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		global $knowledgeLevel;
		if (isset($_POST['submitUpdate'])) {
			// Add New Skill to Resume or Job
			// Get Checked Input
			$skillIDArray = $_POST['skill'];
			$skillLevelArray = $_POST['skilllevel'];
			// No Need to Check Level we allow blank thus if it is zero make it as null
			$N = count($skillIDArray);
			if ($N > 0){
				$prevID = $skillIDArray[0];
				$levelIndex = 0;
			}
			for($i=1; $i < $N; $i++)
	    	{
	    		$currID = $skillIDArray[$i];
	    		$addSkill = 0;
	    		if ($currID == $prevID){
	    			$skillID = $currID;
					if ($skillLevelArray[$levelIndex]==0){
						$skillLevel = NULL;
					}else{
						$skillLevel = $skillLevelArray[$levelIndex];
					}
					$addSkill = 1;
				}else{
					$levelIndex = $levelIndex + 1;
				}
				$prevID = $currID;

				if ($addSkill==1){
					try{
						if ($mode=='Job'){
							$InsertSQL = "INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES (?, ?, ?, ?)";
							$insert = $conn->prepare($InsertSQL);
							$insert->bind_param('ssss', $jobID, $skillID, $userID, $skillLevel);

						} else{
							$InsertSQL = "INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES (?, ?, ?, ?)";
							$insert = $conn->prepare($InsertSQL);
							$insert->bind_param('ssss', $resumeID, $skillID, $userID, $skillLevel);
						}	
						$insert->execute();
						$err = $insert->error;

						if ($err) {
							$conn->rollback();
							$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
							echo $error_msg;
						}else{
							$conn->commit();
							if (isset($_SESSION['location'])) {
								//header("Location: ".$_SESSION['location']);
							}else{
								header('Location: main.php');
							}
						}

					
					}catch(mysqli_sql_exception $e) {
					    echo $e->__toString();
					} 

				}
	    	}
		} elseif (isset($_POST['submitNew'])) {
			// Add New Skill Category
			$skillTitle = $_POST['skillTitle'];
			if (isset($_POST['skill_type'])){
				$skill_type=trim($_POST['skill_type']); 
			}else{
				$skill_type=NULL;
			}
			$skill_ID = $_POST['skill_ID'];
			$skillDescription = $_POST['skillDescription'];		
			if (empty($skillTitle)||empty($skill_type)||empty($skill_ID)){
				echo "Required Field is missing (Skill Title, Skill Type, Skill ID)";
			}else{
				try{
					$InsertNewSkillSQL = "INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES (?,?,?,?)";
					$insertNew = $conn->prepare($InsertNewSkillSQL);
					$insertNew->bind_param('ssss', $skillTitle, $skill_type, $skill_ID, $skillDescription);
					$insertNew->execute();
					$err = $insertNew->error;

					if ($err) {
						$conn->rollback();
						$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
						echo $error_msg;
					}else{
						$conn->commit();
					}

				}catch(mysqli_sql_exception $e) {
				    echo $e->__toString();
				} 
			}
		} elseif (isset($_POST['delete'])) {
			try{
				// Delete Skill
				$skill_ID_delete = $_POST['delete'];
				if ($mode=='Job'){
					$deleteSQL = "DELETE FROM Job_Require_Skill WHERE jobID=? AND skill_ID=? AND userID=? ";
					$delete = $conn->prepare($deleteSQL);
					$delete->bind_param('sss', $jobID, $skill_ID_delete, $userID );
				} else{
					$deleteSQL = "DELETE FROM Resume_Have_Skill WHERE resumeID=? AND skill_ID=? AND userID=? ";
					$delete = $conn->prepare($deleteSQL);
					$delete->bind_param('sss', $resumeID, $skill_ID_delete, $userID);
				}	
				$delete->execute();
				$err = $delete->error;

				if ($err) {
					$conn->rollback();
					$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\nError message : " . $err. "<br>";
					echo $error_msg;
				}else{
					$conn->commit();
				} 
			}catch(mysqli_sql_exception $e) {
			    echo $e->__toString();
			} 
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
	h2 
	{
		text-align: center;
	}

	</style>
	<?php include 'head/head.php';?>
	<body>
		<div class="container">
		<br><br>
		<div>
			<form method='post' action="<?php $_SERVER['PHP_SELF'];?>">
			<?php if ($mode=='Job') displaySkillInfo(2); else displaySkillInfo(1); ?>
			</form>
		</div>
		<br><br>
		<div>
			<form method='post' action="<?php $_SERVER['PHP_SELF'];?>">
			<caption style="font-size:140%;">
			<b>Add New Skill<br><br></b>
			</caption>
			<?php displaySkillOptionInfo(); ?>
			<input class="btn btn-default" type='submit' name='submitUpdate' value='Add New Skill' >
			<input class="btn btn-default" type='button' name='cancel' value='Cancel' onclick=<?php if (isset($_SESSION['location'])) echo "location.href='" . $_SESSION['location']."'"; else echo "location.href='./main.php'" ?> />
			</form>
		</div>
		<br><br>
		<div>
			<form class="form-inline" method='post' action="<?php $_SERVER['PHP_SELF'];?>">
			<table class='table table-bordered'>
			<tr><td colspan='2'><h3>Add New Skill Categorize</h3></td></tr>
			<tr><td>
			ID* : </td><td><input class="form-control" type="text" name="skill_ID" value=''>
			</td></tr>
			<tr><td>
		 	Title*: </td><td><input class="form-control" type="text" name="skillTitle" value='' >
			</td></tr>
			<tr><td>
			Type*: </td><td><div class="radio"><input type="radio" name="skill_type" value='1'> Interpersonal Skill
				  <input type="radio" name="skill_type" value='2'> Programming Language
				  <input type="radio" name="skill_type" value='3'> Problem Solving
				  <br><input type="radio" name="skill_type" value='4'> Technical Certificate
				  <input type="radio" name="skill_type" value='5'> Medical Certificate
				  <input type="radio" name="skill_type" value='6'> Business Related
				  <input type="radio" name="skill_type" value='7'> Other
			</div></td></tr><tr><td>
			Description : </td><td><input class="form-control" type="text" name="skillDescription" value='' >
			</td></tr>
			<tr><td colspan='2'><input class="btn btn-default" type='submit' name='submitNew' value='Create New Skill' ></td></tr></table>
			</form>
		</div>
		</div>
		<?php $conn->close(); ?>
	</body>
</html>