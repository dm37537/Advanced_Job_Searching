
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
		$gpa=$_POST['gpa']; 
		if (isset($_POST['degree'])){
			$degree=trim($_POST['degree']); 
		}else{
			$degree=NULL;
		}
		$graduationDate=$_POST['graduationDate'];  
		$skillIDArray = $_POST['skill'];
		$skillLevelArray = $_POST['skilllevel'];
		$N = count($skillIDArray);
		// Check if any criteria is set 
		If ((empty($gpa))&&(empty($degree))&&(empty($graduationDate))&&($N <= 0)){
			echo 'No Search Criteria is set.';
		}else{
			// Set SQL Statement
			/* Do One by One and Intersect At the End*/
			$SQLGPA = 		"(SELECT R.userID, R.resumeID
							FROM Resume_Post R
							WHERE :gpa IS NULL OR R.gpa >= :gpa)";
							
			$SQLDEGREE = 	"(SELECT R.userID, R.resumeID
							FROM Resume_Post R
							WHERE (R.degree = 'Bachelor' OR R.degree = 'Doctorate' OR R.degree = 'Master') AND :degree = 'Bachelor'
							UNION
							SELECT R.userID, R.resumeID
							FROM Resume_Post R
							WHERE (R.degree = 'Doctorate' OR R.degree = 'Master') AND :degree = 'Master'
							UNION
							SELECT R.userID, R.resumeID
							FROM Resume_Post R
							WHERE (R.degree = 'Doctorate') AND :degree = 'Doctorate'
							UNION
							SELECT R.userID, R.resumeID
							FROM Resume_Post R
							WHERE :degree IS NULL)";
							
			$SQLGRADUATIONDATE = 	"(SELECT R.userID, R.resumeID
									FROM Resume_Post R
									WHERE :graduationDate IS NULL OR R.graduationDate <= TO_DATE(:graduationDate,'mm/dd/yyyy'))";
			
			$FINALSQL = $SQLGPA . "INTERSECT" . $SQLDEGREE . "INTERSECT". $SQLGRADUATIONDATE;
			
			
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
						$SingleSKillSQL = "(Select R.userID, R.resumeID
											FROM Resume_Post R
											WHERE EXISTS(
												Select RHS.skill_ID
												FROM Resume_Have_Skill RHS
												WHERE RHS.resumeID = R.resumeID AND RHS.userID = R.userID AND " . $skillID . "= RHS.skill_ID AND (" . $skillLevel . " <= RHS.knowledgeLevel OR " . $skillLevel . " IS NULL)))";
						if (empty($SQLSKILL)){
							$SQLSKILL = $SingleSKillSQL;
						}else{
							$SQLSKILL = $SQLSKILL . "INTERSECT" . $SingleSKillSQL;
						}
					}
				}
				if (!(empty($SQLSKILL))){
					//Wrap the result
					$SQLSKILL = "(" . $SQLSKILL . ")";
					//Intersect with Final Result
					$FINALSQL = $FINALSQL . "INTERSECT" . $SQLSKILL;
				}
			}		

			// Resume Record are selected.  Now Link to the User record to get User information as well as resume Information.
			$FINALSQL = "SELECT distinct U.name, U.email, R.gpa, R.degree, R.school, TO_CHAR(R.graduationDate,'mm/dd/yyyy')
						FROM Resume_Post R, Users U, (" . $FINALSQL . ") SearchResume WHERE SearchResume.resumeID = R.resumeID AND SearchResume.userID = R.userID AND R.userID = U.userID";
			// Connect to database
			$stid = oci_parse($conn,$FINALSQL);
			
			// Bind Variable
			oci_bind_by_name($stid, ":gpa", $gpa);
			oci_bind_by_name($stid, ":degree", $degree);
			oci_bind_by_name($stid, ":graduationDate", $graduationDate);
			// Execute and Check Errors
			oci_execute($stid);	
			$err = oci_error($stid);
			if ($err) {
				oci_rollback($conn); 
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
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
		<h3>Search Criteria</h3>
		<b>Minimum GPA</b> <input type="number" name="gpa" min="0" max="5" step="0.01">
		<br><br>
		<b>Minimum Degree</b><input type="radio" name="degree" value="Bachelor"> Bachelor
						 <input type="radio" name="degree" value="Master"> Master 
						 <input type="radio" name="degree" value="Doctorate">Doctorate
		<br><br>
		<b>Latest Graduation Date('mm/dd/yyyy')</b><input type="text" name="graduationDate">
		<br><br>
		<caption style="font-size:100%;">
		<b>Required Skill<br></b>
		</caption>
		<?php displaySkillOptionInfo(); ?>
		<input type='submit' name='submit' value='Search Resume' >
		<input type='button' name='cancel' value='Cancel' onclick="location.href='/index.php'" />
		</form>
	</div>
	<?php if (isset($displaySearch)&&($displaySearch == 1)) { ?>
		<br><br><br>
		<div>
			<h3>Search Result</h3>
			<table style="border:1px solid black;">
				<tr>
					<td><b>User Name</b></td>
					<td><b>User Email</b></td>
					<td><b>GPA</b></td>
					<td><b>Degree</b></td>
					<td><b>School</b></td>
					<td><b>Graduation Date</b></td>
				</tr>
			<?php if ($stid) { while($res = oci_fetch_row($stid)) { ?>
				<tr>
					<td><?php echo $res[0]; ?></td>
					<td><?php echo $res[1]; ?></td>
					<td><?php echo $res[2]; ?></td>
					<td><?php echo $res[3]; ?></td>
					<td><?php echo $res[4]; ?></td>
					<td><?php echo $res[5]; ?></td>
				</tr>
			<?php } }?>
			</table>
		</div>
	<?php $displaySearch = 0;} ?>
	<?php oci_close($conn); ?>
</html>