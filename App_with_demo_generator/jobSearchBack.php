<?php
	session_start();
	require_once "connection.php";
	//$userID=$_SESSION['userID'];
	$gpa = "";
	
	
		//JOB POSTING -> FITTING RESUME
	function retrieveInfoJobPostResume($jobID) {
		$SelectSQL =   "SELECT J.userID, J.jobTitle, J.jobID, J.status, U.name, E.companyName
						FROM Users U, employer E, Job_Post J,
						(SELECT J.userID, J.jobID
						FROM Job_Post J
						WHERE J.requiredGPA <= (SELECT R.gpa
												FROM Resume_Post R
												WHERE R.userID = :userID AND R.resumeID = :resumeID) OR J.requiredGPA IS NULL
						INTERSECT
						(
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = :userID AND R.resumeID = :resumeID AND R.degree = 'Bachelor')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = :userID AND R.resumeID = :resumeID AND R.degree = 'Master')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Master' OR J.requiredDegree = 'Doctorate') AND EXISTS (
								SELECT R.degree 
								FROM Resume_Post R
								WHERE R.userID = :userID AND R.resumeID = :resumeID AND R.degree = 'Doctorate')
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE J.requiredDegree IS NULL
						)
						INTERSECT
						SELECT J.userID, J.jobID
						FROM Job_Post J
						WHERE NOT EXISTS(
							(SELECT JRS.skill_ID 
							 FROM Job_Require_Skill JRS
							 WHERE JRS.jobID = J.jobID AND JRS.userID = J.userID)
							 MINUS(
							 SELECT RHS.skill_ID
							 FROM Resume_Have_Skill RHS, Job_Require_Skill JRS
							 WHERE RHS.userID = :userID AND RHS.resumeID = :resumeID AND JRS.jobID = J.jobID  AND JRS.userID = J.userID 
									AND JRS.skill_ID = RHS.skill_ID AND (JRS.knowledgeLevel <= RHS.knowledgeLevel OR JRS.knowledgeLevel IS NULL)))
						MINUS
						SELECT J.job_post_userID, J.jobID
						FROM JobSeeker_Apply_Job J
						WHERE J.userID = :userID
						) AllowedJob
						WHERE J.status = '1' AND AllowedJob.userID = J.userID AND AllowedJob.jobID = J.jobID AND J.userID = U.userID AND U.userID = E.userID";
		// Connect to database
		$stid = oci_parse($conn, $SelectSQL);
		oci_bind_by_name($stid, ":userID", $userID);
		oci_bind_by_name($stid, ":resumeID", $resumeID);
		// Execute and Check Errors
		oci_execute($stid);	
		$err = oci_error($stid);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "CANDIDATE JOB ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}
		return $stid;
	}
	
	
	
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$startDate = $_POST['startDate'];
		$gpa = $_POST['gpa'];
		$requiredDegree = $_POST['requiredDegree'];
		
		if (empty($startDate ) && empty($gpa ) && empty($requiredDegree )  )
		{

				echo "No SQL excute!";
		}
		else
		{
			
			$SearchSQL = ""; $Search="";
			//echo "get in";
			
			//Search for start date
				echo "Start Date<br>";
				
				echo $startDate."<br>";
				$SearchByStartDateSQL ="(SELECT J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E,
								(SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE J.startDate >= TO_DATE(:startDate , 'mm/dd/yyyy') OR J.startDate IS NULL OR :startDate IS NULL
								) Qualifiedjob
								WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID)" ;
				
				
			
			//Search for gpa
				echo "GPA<br>";
				
				$SearchByGPASQL ="(SELECT J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E,
								(SELECT J.userID, J.jobID
								FROM Job_Post J
								WHERE J.requiredGPA <= :gpa OR J.requiredGPA IS NULL OR :gpa IS NULL
								) Qualifiedjob
								WHERE Qualifiedjob.jobID = J.jobID AND Qualifiedjob.userID = J.userID AND J.userID = E.userID)" ;
				

			
			//Search for required degree
		
				
					$SearchByDegreeSQL = "(SELECT J.jobTitle, J.jobDescription, J.status, E.companyName
								FROM  Job_Post J, employer E,
							(SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Bachelor' OR J.requiredDegree = 'Doctorate' OR J.requiredDegree = 'Master') AND :requiredDegree = 'Bachelor'
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Doctorate' OR J.requiredDegree = 'Master') AND :requiredDegree = 'Master'
							UNION
							SELECT J.userID, J.jobID
							FROM Job_Post J
							WHERE (J.requiredDegree = 'Doctorate') AND :requiredDegree = 'Doctorate'
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

			
			
			$SearchSQL = $SearchByGPASQL . "INTERSECT" . $SearchByDegreeSQL . "INTERSECT". $SearchByStartDateSQL;
			$Search= oci_parse($conn,$SearchSQL);
			oci_bind_by_name($Search, ":gpa", $gpa);
			oci_bind_by_name($Search, ":startDate", $startDate);
			oci_bind_by_name($Search, ":requiredDegree", $requiredDegree);
			//excute SQL
			if ($Search != NULL)
			{
			
				oci_execute($Search, OCI_DEFAULT);
				
				
				echo "<table>";
				
				echo "<td>Job Title</td><td>Job description</td><td>Job Status</td><td>Company</td>";
				while ($row = oci_fetch_row($Search))
				{
					?>
					
					<tr>
					<td><?php echo $row[0]; ?></td><td><?php echo $row[1]; ?></td><td><?php if ($row[2] == 1) echo "Open";
																							else echo "Closed"; ?></td><td><?php echo $row[3]; ?></td>
					</tr>
					<?php 
				}
				
				echo "</table>";
				$err = oci_error($Search);
				if ($err) {
					oci_rollback($conn); 
					$err_code = $err['code']; 
					$error_msg = "CANDIDATE JOB ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
					echo $error_msg;
				}
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

		<b>Search by GPA<br><br><b>
		</caption>
		GPA : <input type="text" name="gpa" value='' >
		Required Degree : <select name='requiredDegree'>
						<option value=''></option>
						<option value='Bachelor'>Bachelor</option>
						<option value='Master'>Master</option>
						<option value='Doctorate'>Doctorate</option>
						</select>
		Start Date : <input type="text" name="startDate" value='' >
		<br>
		<input type='submit' name='Submit' value='Submit' >
		<input type='button' name='cancel' value='Cancel' onclick= />
		</form>
	</div>
		<?php oci_close($conn); ?>
</html>