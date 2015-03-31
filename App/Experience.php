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
		$sql = "SELECT resumeID,userID,experienceID,DATE_FORMAT(startDate, '%Y-%m-%d'),DATE_FORMAT(endDate, '%Y-%m-%d'),jobDescription,companyName,department,jobTitle FROM Resume_Have_WorkExperience WHERE experienceID= ?  and resumeID= ? and userID= ? ";
		try{
			// Connect to database
			$stmt = $conn->prepare($sql);

			$stmt->bind_param('sss', $experienceID, $resumeID, $userID);
			// Execute and Check Errors
			$stmt->execute();
			$err = $stmt->error;
			$result = $stmt->get_result();

			if ($err) {
				$conn->rollback();
				$err_code = $err['code']; 
				$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
				echo $error_msg;
			}else
			{
				$row = $result->fetch_array();
				$_SESSION['experienceID'] = $experienceID;
				$startDate = $row[3];
				$endDate = $row[4];
				$jobDescription = $row[5];
				$companyName = $row[6];	
				$department = $row[7];	
				$jobTitle = $row[8];	
			}
		
		}catch(mysqli_sql_exception $e) {
		    echo $e->__toString();
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
				$UpdateExperienceSQL ="Update Resume_Have_WorkExperience SET startDate = STR_TO_DATE( ? , '%Y-%m-%d'),endDate = STR_TO_DATE( ? , '%Y-%m-%d'),jobDescription= ? ,companyName= ? ,department= ? ,jobTitle= ? WHERE resumeID= ? AND userID= ?  AND experienceID= ? ";
				try{
					// Connect to database
					$stmt = $conn->prepare($UpdateExperienceSQL);

					$stmt->bind_param('sssssssss', $startDate, $endDate, $jobDescription, $companyName, $department, $jobTitle, $resumeID, $userID, $experienceID);
					// Execute and Check Errors
					$stmt->execute();
					$err = $stmt->error;

					if ($err) {
						$conn->rollback();
						$err_code = $err['code']; 
						$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
						echo $error_msg;
					}else
					{
						$conn->commit(); 
						if (isset($_SESSION['location'])) {
							header("Location: ".$_SESSION['location']);
						}else{
							header('Location: main.php');
						}
					}
				
				}catch(mysqli_sql_exception $e) {
				    echo $e->__toString();
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
				$InsertNewExperienceSQL = "INSERT INTO Resume_Have_WorkExperience (resumeID,userID,experienceID,startDate,endDate,jobDescription,companyName,department,jobTitle) VALUES (?, ?, ?,  STR_TO_DATE( ? , '%Y-%m-%d'), STR_TO_DATE( ? , '%Y-%m-%d'), ? , ? , ? , ? )";
				
				try{
					// Connect to database
					$stmt = $conn->prepare($InsertNewExperienceSQL);

					$stmt->bind_param('sssssssss', $resumeID, $userID, $experienceID, $startDate, $endDate, $jobDescription, $companyName, $department, $jobTitle);
					// Execute and Check Errors
					$stmt->execute();
					$err = $stmt->error;

					if ($err) {
						$conn->rollback();
						$err_code = $err['code']; 
						$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
						echo $error_msg;
					}else
					{
						$conn->commit(); 
						if (isset($_SESSION['location'])) {
							header("Location: ".$_SESSION['location']);
						}else{
							header('Location: main.php');
						}
					}
				
				}catch(mysqli_sql_exception $e) {
				    echo $e->__toString();
				}
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
	.table table-bordered 
	{
		width: 400px;
	}
	#center td
	{
		text-align: center;
	}

	</style>
	<?php include 'head/head.php';?>
	<body>
		<div class="container">		
		<br><br>
			
			<form class="form-inline" method='post' action="<?php $_SERVER['PHP_SELF'];?>">
			<table class='table table-bordered'>
				<tr id="center"><td colspan='2'><h2><?php echo $mode;?> Experience<h2></td></tr>
				<tr><td>Experience ID* : </td>
				<td><input class="form-control" type="text" name="experienceID" value='<?php echo $experienceID;?>' <?php if (($mode=="Edit")||($mode=="View")) echo 'readonly'; ?>></td></tr>
				<tr><td>
			 	Start Date(mm/dd/yyyy)* : </td><td><input class="form-control" type="text" name="startDate" value='<?php echo $startDate;?>' >
				</td></tr><tr><td>
				End Date(mm/dd/yyyy): </td><td><input class="form-control" type="text" name="endDate" value='<?php echo $endDate;?>' >
				</td></tr><tr><td>
				Job Description : </td><td><input class="form-control" type="text" name="jobDescription" value='<?php echo $jobDescription;?>'>
				</td></tr><tr><td>
				Company Name* : </td><td><input class="form-control" type="text" name="companyName" value='<?php echo $companyName;?>' >
				</td></tr><tr><td>
				Department : </td><td><input class="form-control" type="text" name="department" value='<?php echo $department;?>' >
				</td></tr><tr><td>
				Job Title : </td><td><input class="form-control" type="text" name="jobTitle" value='<?php echo $jobTitle;?>' >
				</td></tr><tr><td colspan='2'>
				<input class="btn btn-default" type='submit' name='submitNew' value='Save Experience' >
				<input class="btn btn-default" type='button' name='cancel' value='Cancel' onclick=<?php if (isset($_SESSION['location'])) echo "location.href='" . $_SESSION['location']."'"; else echo "location.href='./main.php'" ?> />
				</td></tr>
			</table>
			</form>

		</div>
		<?php $conn->close(); ?>
	</body>
</html>