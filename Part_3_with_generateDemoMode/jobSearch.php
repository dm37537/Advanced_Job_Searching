
<?php
	session_start();
	require_once "connection.php";	
	if (isset($_SESSION['userID'])) $userID = $_SESSION['userID'];
	
	function displaySkillOptionInfo(){
		global $conn;
		// Get Skill List and ID Form
		$skillListSQL = "SELECT S.skillTitle, S.skill_ID FROM SKILL S";
		$skillListSTID = oci_parse($conn,$skillListSQL);
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
		// Get Variable
		$startDate = $_POST['startDate'];
		$gpa = $_POST['gpa'];
		$requiredDegree = $_POST['requiredDegree'];
		$skillIDArray = $_POST['skill'];
		$skillLevelArray = $_POST['skilllevel'];
		$N = count($skillIDArray);
		// Check if any criteria is set 
		If ((empty($startDate))&&(empty($gpa))&&(empty($requiredDegree))&&($N <= 0)){
			echo 'No Search Criteria is set.';
		}else{
			// Set SQL Statement
			/* Do One by One and Intersect At the End*/
			$SearchByStartDateSQL =	"(SELECT distinct J.jobTitle, J.jobDescription, J.status, E.companyName
									FROM  Job_Post J, employer E,
									(SELECT J.userID, J.jobID
									FROM Job_Post J
									WHERE J.startDate >= TO_DATE(:startDate , 'mm/dd/yyyy') OR J.startDate IS NULL OR :startDate IS NULL
									) Qualifiedjob
									WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID)" ;

			$SearchByGPASQL =	"(SELECT distinct J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E,
								(SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE J.requiredGPA <= :gpa OR J.requiredGPA IS NULL OR :gpa IS NULL
								) Qualifiedjob
								WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID)" ;
									
			$SearchByDegreeSQL = "(SELECT distinct J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E,
								(SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE (J.requiredDegree = 'Bachelor') AND :requiredDegree = 'Bachelor'
								UNION
								SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master') AND :requiredDegree = 'Master'
								UNION
								SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Doctorate' OR J.requiredDegree = 'Master') AND :requiredDegree = 'Doctorate'
								UNION
								SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE J.requiredDegree IS NULL
								UNION
								SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE :requiredDegree IS NULL
								)Qualifiedjob
								WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID)";		

			
			$FINALSQL = $SearchByStartDateSQL . "INTERSECT" . $SearchByGPASQL . "INTERSECT". $SearchByDegreeSQL;
			
			// SKILL Needs Special Treatment since it involves another record. 
			If ($N > 0){
				// Initialize
				$prevID = $skillIDArray[0];
				$levelIndex = 0;
				$SQLSKILL = "";
				for($i=1; $i < $N; $i++)
				{
					$currID = $skillIDArray[$i];
					$addSkill = 0;
					if ($currID == $prevID){
						$skillID = "'" . $currID . "'";
						if ($skillLevelArray[$levelIndex]==0){
							$skillLevel = 'NULL';
						}else{
							$skillLevel = "'" . $skillLevelArray[$levelIndex] . "'" ;
						}
						$addSkill = 1;
					}else{
						$levelIndex = $levelIndex + 1;
					}
					$prevID = $currID;

					if ($addSkill==1){
						$SingleSKillSQL = "(JRS.skill_ID = " . $skillID . " AND (JRS.knowledgeLevel <= " . $skillLevel . " OR JRS.knowledgeLevel IS NULL))";
						if (empty($SQLSKILL)){
							$SQLSKILL = $SingleSKillSQL;
						}else{
							$SQLSKILL = $SQLSKILL . " OR " . $SingleSKillSQL;
						}
					}
				}
				if (!(empty($SQLSKILL))){
					//Wrap the result thus return job ID and job User ID
					$SQLSKILL = "SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE NOT EXISTS(
									(SELECT JRS.skill_ID 
									 FROM Job_Require_Skill JRS
									 WHERE JRS.jobID = J.jobID AND JRS.userID = J.userID)
									 MINUS(
									 SELECT JRS.skill_ID
									 FROM Job_Require_Skill JRS
									 WHERE JRS.jobID = J.jobID  AND JRS.userID = J.userID AND (" . $SQLSKILL . ")))";
					//Wrap the result thus return job information as company information
					$SQLSKILL = "(SELECT distinct J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E, (" . $SQLSKILL . ") Qualifiedjob WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID) ";
					//Intersect with Final Result
					$FINALSQL = $FINALSQL . "INTERSECT" . $SQLSKILL;
				}
			}
			
			// Connect to database
			$stid = oci_parse($conn,$FINALSQL);
			
			// Bind Variable
			oci_bind_by_name($stid, ":gpa", $gpa);
			oci_bind_by_name($stid, ":startDate", $startDate);
			oci_bind_by_name($stid, ":requiredDegree", $requiredDegree);
			// Execute and Check Errors
			oci_execute($stid);	
			$err = oci_error($stid);
			if ($err) {
				oci_rollback($conn); 
				$err_code = $err['code']; 
				$error_msg = "SEARCH Error. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			} else {
				// Display the result, set the variable and the bottom html will display it.
				$displaySearch = '1';
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

			<b>Search Criteria</b><br><br>
			</caption>
			<b>My GPA </b><input type="number" name="gpa" min="0" max="5" step="0.01">
			<b>My Degree : </b><select name='requiredDegree'>
							<option value=''></option>
							<option value='Bachelor'>Bachelor</option>
							<option value='Master'>Master</option>
							<option value='Doctorate'>Doctorate</option>
							</select>
			<b>Start Date('mm/dd/yyyy') : </b><input type="text" name="startDate" value='' >
			<br><br>
			
			<caption style="font-size:100%;">
			<b>My Skills</b><br><br>
			</caption>
			<?php displaySkillOptionInfo(); ?>
			<input type='submit' name='search' value='Search Job' >
			<input type='button' name='cancel' value='Cancel' onclick="location.href='/index.php'" />
			</form>
		</div>
		<?php if (isset($displaySearch)&&($displaySearch == 1)) { ?>
		<br><br><br>
		<div>
			<h3>Search Result</h3>
			<table style="border:1px solid black;">
				<tr>
					<td><b>Job Title</b></td>
					<td><b>Job description</b></td>
					<td><b>Job Status</b></td>
					<td><b>Company</b></td>
				</tr>
			<?php if ($stid) { while($res = oci_fetch_row($stid)) { ?>
				<tr>
					<td><?php echo $res[0]; ?></td>
					<td><?php echo $res[1]; ?></td>
					<td><?php if ($res[2] == 1) echo "Open"; else echo "Closed"; ?></td>
					<td><?php echo $res[3]; ?></td>
				</tr>
			<?php } }?>
			</table>
		</div>
		<?php $displaySearch = 0;} ?>
	<?php oci_close($conn); ?>
</html>