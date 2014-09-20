
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
		$get_information = oci_parse($conn,"SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Resume_Have_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and resumeID='".$resumeID. "' AND userID='".$userID."'");
		}else{
		// Job Post Skill Pring
		$get_information = oci_parse($conn,"SELECT R.skill_ID, S.skillTitle, R.knowledgeLevel, S.skillDescription FROM Job_Require_Skill R, Skill S  WHERE R.skill_ID = S.skill_ID and jobID='".$jobID. "' AND userID='".$userID."'");
		}
		// Execute and Check Errors
		oci_execute($get_information , OCI_DEFAULT);
		$err = oci_error($get_information);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "SKILL LIST RETRIEVE ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}else{
			echo "<table>
						<tr>
							<td>Tittle</td><td>Level</td><td>Description</td>
							<td>

							</td>
						</tr>";
			while ($row = oci_fetch_row($get_information))
			{
				echo "<tr>
						<td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td><button name='delete' type='submit' value='".$row[0]."'>Delete</button></td>"."</tr>";
			}
			echo "</table>";
		}
	}
	
	function displaySkillOptionInfo(){
		global $conn, $mode, $resumeID, $userID, $jobID;
		$count=0;
		// Get Skill List and ID Form.  Modified to list only skill which are not added yet.
		if ($mode=='Job'){
			$skillListSQL = "(SELECT S.skillTitle, S.skill_ID FROM Skill S) MINUS (SELECT S.skillTitle, S.skill_ID FROM SKILL S, Job_Require_Skill R WHERE R.skill_ID=S.skill_ID AND R.jobID=:jobID AND R.userID =:userID)";
			$skillListSTID = oci_parse($conn,$skillListSQL);
			oci_bind_by_name($skillListSTID, ":jobID", $jobID);
			oci_bind_by_name($skillListSTID, ":userID", $userID);
		} else{
			$skillListSQL = "(SELECT S.skillTitle, S.skill_ID FROM Skill S) MINUS (SELECT S.skillTitle, R.skill_ID FROM SKILL S, Resume_Have_Skill R WHERE R.skill_ID=S.skill_ID AND R.resumeID=:resumeID AND R.userID =:userID)";
			$skillListSTID = oci_parse($conn,$skillListSQL);
			oci_bind_by_name($skillListSTID, ":resumeID", $resumeID);
			oci_bind_by_name($skillListSTID, ":userID", $userID);
		}
		// Execute and Check Errors
		oci_execute($skillListSTID, OCI_DEFAULT);
		$err = oci_error($skillListSTID);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "SKILL LIST RETRIEVE ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}else{
			while ($row = oci_fetch_row($skillListSTID)){
				echo "<input type='hidden' name='skill[]' value='".$row[1]."'>";
				echo "<input type='checkbox' name='skill[]' value='".$row[1]."'>". $row[0];
				echo "<select name='skilllevel[]'>
						<option value='0'>0</option>
						<option value='1'>1</option>
						<option value='2'>2</option>
						<option value='3'>3</option>
						<option value='4'>4</option>
						<option value='5'>5</option>
						</select>";
				echo "<br>";
			}
		}
	}
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
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
					if ($mode=='Job'){
						$InsertSQL = "INSERT INTO Job_Require_Skill (jobID,skill_ID,userID,knowledgeLevel) VALUES (:jobID, :skill_ID, :userID, :knowledgeLevel)";
						$insert = oci_parse($conn,$InsertSQL);
						oci_bind_by_name($insert, ":jobID", $jobID);
						oci_bind_by_name($insert, ":userID", $userID);
						oci_bind_by_name($insert, ":skill_ID", $skillID);
						oci_bind_by_name($insert, ":knowledgeLevel", $skillLevel);
					} else{
						$InsertSQL = "INSERT INTO Resume_Have_Skill (resumeID,skill_ID,userID,knowledgeLevel) VALUES (:resumeID, :skill_ID, :userID, :knowledgeLevel)";
						$insert = oci_parse($conn,$InsertSQL);
						oci_bind_by_name($insert, ":resumeID", $resumeID);
						oci_bind_by_name($insert, ":userID", $userID);
						oci_bind_by_name($insert, ":skill_ID", $skillID);
						oci_bind_by_name($insert, ":knowledgeLevel", $skillLevel);
					}	 

					//Execute and Check Errors
					oci_execute($insert, OCI_NO_AUTO_COMMIT);
					$err = oci_error($insert);
					if ($err) {
						oci_rollback($conn); 
						$err_code = $err['code']; 
						$error_msg = "ADD NEW SKILL ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
						echo $error_msg;
					}else{
						oci_commit($conn); 
						if (isset($_SESSION['location'])) {
							header("Location: ".$_SESSION['location']);
						}else{
							header('Location: main.php');
						}
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
				$InsertNewSkillSQL = "INSERT INTO Skill (skillTitle,skill_type,skill_ID,skillDescription) VALUES (:skillTitle,:skill_type,:skill_ID,:skillDescription)";
				$insertNew = oci_parse($conn,$InsertNewSkillSQL);
				oci_bind_by_name($insertNew, ":skillTitle", $skillTitle);
				oci_bind_by_name($insertNew, ":skill_type", $skill_type);
				oci_bind_by_name($insertNew, ":skill_ID", $skill_ID );
				oci_bind_by_name($insertNew, ":skillDescription", $skillDescription);
				oci_execute($insertNew, OCI_NO_AUTO_COMMIT);
				$err = oci_error($insertNew);
				if ($err) {
					oci_rollback($conn); 
					$err_code = $err['code']; 
					$error_msg = "CREATE NEW SKILL ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
					echo $error_msg;
				}else{
					oci_commit($conn); 
				}
			}
		} elseif (isset($_POST['delete'])) {
			// Delete Skill
			$skill_ID_delete = $_POST['delete'];
			if ($mode=='Job'){
				$deleteSQL = "DELETE FROM Job_Require_Skill WHERE jobID=:jobID AND skill_ID=:skill_ID AND userID=:userID";
				$delete = oci_parse($conn,$deleteSQL);
				oci_bind_by_name($delete, ":jobID", $jobID);
				oci_bind_by_name($delete, ":userID", $userID);
				oci_bind_by_name($delete, ":skill_ID", $skill_ID_delete);
			} else{
				$deleteSQL = "DELETE FROM Resume_Have_Skill WHERE resumeID=:resumeID AND skill_ID=:skill_ID AND userID=:userID";
				$delete = oci_parse($conn,$deleteSQL);
				oci_bind_by_name($delete, ":resumeID", $resumeID);
				oci_bind_by_name($delete, ":userID", $userID);
				oci_bind_by_name($delete, ":skill_ID", $skill_ID_delete);
			}	 
			//Execute and Check Errors
			oci_execute($delete, OCI_NO_AUTO_COMMIT);
			$err = oci_error($delete);
			if ($err) {
				oci_rollback($conn); 
				$err_code = $err['code']; 
				$error_msg = "DELETE SKILL ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}else{
				oci_commit($conn); 
			}
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
	<div>
		<form method='post' action="<?php $_SERVER['PHP_SELF'];?>">
		<caption style="font-size:140%;">
		<b>Current Skill Information</b>
		</caption>
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
		<input type='submit' name='submitUpdate' value='Add New Skill' >
		<input type='button' name='cancel' value='Cancel' onclick=<?php if (isset($_SESSION['location'])) echo "location.href='" . $_SESSION['location']."'"; else echo "location.href='/main.php'" ?> />
		</form>
	</div>
	<br><br>
	<div>
		<form method='post' action="<?php $_SERVER['PHP_SELF'];?>">
		<caption style="font-size:140%;">
		<b>Add New Skill Category<br><br><b>
		</caption>
		ID* : <input type="text" name="skill_ID" value=''>
		<br>
	 	Title*: <input type="text" name="skillTitle" value='' >
		<br>
		Type*: <input type="radio" name="skill_type" value='1'> Interpersonal Skill
			  <input type="radio" name="skill_type" value='2'> Programming Language
			  <input type="radio" name="skill_type" value='3'> Problem Solving
			  <input type="radio" name="skill_type" value='4'> Technical Certificate
			  <input type="radio" name="skill_type" value='5'> Medical Certificate
			  <input type="radio" name="skill_type" value='6'> Business Related
			  <input type="radio" name="skill_type" value='7'> Other
		<br>
		Skill Description : <input type="text" name="skillDescription" value='' >
		<br>
		<input type='submit' name='submitNew' value='Create New Skill' >
		</form>
	</div>
	<?php oci_close($conn); ?>
</html>