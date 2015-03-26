<?php
	session_start();
	require_once "connection.php";
	$mode=$_GET['mode'];
	$resumeID = $_GET['resumeID'];
	$userID = $_GET['userID'];
	$experienceID="";$startDate=""; $endDate=""; $jobDescription=""; $companyName="";$department="";$jobTitle="";
	
	if ($mode == 'Edit')
	{
		$experienceID = $_GET['experienceID'];
		$get_information = oci_parse($conn,"SELECT resumeID,userID,experienceID,TO_CHAR(startDate,'mm/dd/yyyy'),TO_CHAR(endDate,'mm/dd/yyyy'),jobDescription,companyName,department,jobTitle FROM Resume_Have_WorkExperience WHERE experienceID=:experienceID and resumeID=:resumeID and userID=:userID");
		oci_bind_by_name($get_information, ":resumeID", $resumeID);
		oci_bind_by_name($get_information, ":userID", $userID);
		oci_bind_by_name($get_information, ":experienceID", $experienceID );
		// Execute and Check Errors
		oci_execute($get_information , OCI_DEFAULT);
		$err = oci_error($get_information);
		if ($err) {
			oci_rollback($conn); 
			$err_code = $err['code']; 
			$error_msg = "SKILL LIST RETRIEVE ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
		}else
		{
			$row = oci_fetch_row($get_information);
			$_SESSION['experienceID'] = $experienceID;
			$startDate = $row[3];
			$endDate = $row[4];
			$jobDescription = $row[5];
			$companyName = $row[6];	
			$department = $row[7];	
			$jobTitle = $row[8];	
		}
	} 	
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if ($mode == 'Edit')
		{
			$startDate = $_POST['startDate'];
			$endDate = $_POST['endDate'];
			$jobDescription = $_POST['jobDescription'];
			$companyName = $_POST['companyName'];	
			$department = $_POST['department'];	
			$jobTitle = $_POST['jobTitle'];	
			$experienceID = $_SESSION['experienceID'];
			if (empty($startDate)||empty($companyName)||empty($experienceID)){
				echo "Required Field is missing (Experience ID, Start Date, Company Name)";
			}else{
				$UpdateExperienceSQL ="Update Resume_Have_WorkExperience SET startDate=TO_DATE(:startDate,'mm/dd/yyyy'),endDate=TO_DATE(:endDate,'mm/dd/yyyy'),jobDescription=:jobDescription,companyName=:companyName,department=:department,jobTitle=:jobTitle WHERE resumeID=:resumeID AND userID=:userID AND experienceID=:experienceID";
				$Update = oci_parse($conn,$UpdateExperienceSQL);
				oci_bind_by_name($Update, ":resumeID", $resumeID);
				oci_bind_by_name($Update, ":userID", $userID);
				oci_bind_by_name($Update, ":experienceID", $experienceID );
				oci_bind_by_name($Update, ":startDate", $startDate);
				oci_bind_by_name($Update, ":endDate", $endDate );
				oci_bind_by_name($Update, ":jobDescription", $jobDescription);
				oci_bind_by_name($Update, ":companyName", $companyName);
				oci_bind_by_name($Update, ":department", $department);
				oci_bind_by_name($Update, ":jobTitle", $jobTitle);
				oci_execute($Update, OCI_NO_AUTO_COMMIT);
				
				$err = oci_error($Update);
				if ($err) {
					oci_rollback($conn); 
					$err_code = $err['code']; 
					$error_msg = "EDIT NEW EXPERIENCE ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
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
		else 
		{
			$experienceID = $_POST['experienceID'];
			$startDate = $_POST['startDate'];
			$endDate = $_POST['endDate'];
			$jobDescription = $_POST['jobDescription'];
			$companyName = $_POST['companyName'];	
			$department = $_POST['department'];	
			$jobTitle = $_POST['jobTitle'];	
			global $resumeID,$userID;
			if (empty($startDate)||empty($companyName)||empty($experienceID)){
				echo "Required Field is missing (Experience ID, Start Date, Company Name)";
			}else{
				$InsertNewExperienceSQL = "INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES (:resumeID,:userID,:experienceID,TO_DATE(:startDate,'mm/dd/yyyy'),TO_DATE(:endDate,'mm/dd/yyyy'),:jobDescription,:companyName,:department,:jobTitle)";
				$insertNew = oci_parse($conn,$InsertNewExperienceSQL);
				oci_bind_by_name($insertNew, ":resumeID", $resumeID);
				oci_bind_by_name($insertNew, ":userID", $userID);
				oci_bind_by_name($insertNew, ":experienceID", $experienceID );
				oci_bind_by_name($insertNew, ":startDate", $startDate);
				oci_bind_by_name($insertNew, ":endDate", $endDate );
				oci_bind_by_name($insertNew, ":jobDescription", $jobDescription);
				oci_bind_by_name($insertNew, ":companyName", $companyName);
				oci_bind_by_name($insertNew, ":department", $department);
				oci_bind_by_name($insertNew, ":jobTitle", $jobTitle);
				oci_execute($insertNew, OCI_NO_AUTO_COMMIT);
				$err = oci_error($insertNew);
				if ($err) {
					oci_rollback($conn); 
					$err_code = $err['code']; 
					$error_msg = "CREATE NEW EXPERIENCE ERROR. Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
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
	<br><br>
	<div>
		<form method='post' action="<?php $_SERVER['PHP_SELF'];?>">
		<caption style="font-size:140%;">

		<b><?php echo $mode;?> Experience<br><br><b>
		</caption>
		Experience ID* : <input type="text" name="experienceID" value='<?php echo $experienceID;?>' >
		<br>
	 	Start Date(mm/dd/yyyy)* : <input type="text" name="startDate" value='<?php echo $startDate;?>' >
		<br>
		End Date(mm/dd/yyyy): <input type="text" name="endDate" value='<?php echo $endDate;?>' >
		<br>
		Job Description : <input type="text" name="jobDescription" value='<?php echo $jobDescription;?>'>
		<br>
		Company Name* : <input type="text" name="companyName" value='<?php echo $companyName;?>' >
		<br>
		Department : <input type="text" name="department" value='<?php echo $department;?>' >
		<br>
		Job Title : <input type="text" name="jobTitle" value='<?php echo $jobTitle;?>' >
		<br>
		<input type='submit' name='submitNew' value='<?php echo $mode;?> Experience' >
		<input type='button' name='cancel' value='Cancel' onclick=<?php if (isset($_SESSION['location'])) echo "location.href='" . $_SESSION['location']."'"; else echo "'history.back();'" ?> />
		</form>
	</div>
	<?php oci_close($conn); ?>
</html>