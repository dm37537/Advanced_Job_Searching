<?php
session_start();
require_once "connection.php";
/*Initialize parameter*/
$mode=$_GET['mode'];
if (isset($_SESSION['userID'])) {
	$userID = $_SESSION['userID'];
}else{
	$userID = "";
}
$password=""; $name=""; $age=""; $address=""; $email=""; $status="";
$currentStatus=""; $preferJob=""; $currentJob="";
$companyName=""; $description=""; $department=""; $companySize="";

/*Get Variable*/
function getValues(){
	global $userID, $password, $name, $age, $address, $email, $status;
	global $currentStatus, $preferJob, $currentJob;
	global $companyName, $description, $department, $companySize;
 	$name=$_POST['name']; 
 	$age=$_POST['age']; 
 	$address=trim($_POST['address']); 
 	$email=$_POST['email']; 
 	$status=$_POST['status'];
 	$currentStatus=$_POST['currentStatus']; 
 	$preferJob=$_POST['preferJob']; 
 	$currentJob=$_POST['currentJob'];
 	$companyName=$_POST['companyName']; 
 	$description=trim($_POST['description']); 
 	$department=$_POST['department']; 
 	$companySize=$_POST['companySize'];
}

/*Bind Variable for SQL Update*/
function bindVariable($stid, $type){
	// Bind to the variables
	global $userID;
	oci_bind_by_name($stid, ":userID", $userID);
	if ($type == 1){
		global $password, $name, $age, $address, $email, $status;
		oci_bind_by_name($stid, ":password", $password);
		oci_bind_by_name($stid, ":name", $name);
		oci_bind_by_name($stid, ":age", $age);
		oci_bind_by_name($stid, ":address", $address);
		oci_bind_by_name($stid, ":email", $email);
		oci_bind_by_name($stid, ":status", $status);
	}elseif ($type == 2){
		global $currentStatus, $preferJob, $currentJob;
		oci_bind_by_name($stid, ":currentStatus", $currentStatus);
		oci_bind_by_name($stid, ":preferJob", $preferJob);
		oci_bind_by_name($stid, ":currentJob", $currentJob);

	}else{
		global $companyName, $description, $department, $companySize;
		oci_bind_by_name($stid, ":companyName", $companyName);
		oci_bind_by_name($stid, ":description", $description);
		oci_bind_by_name($stid, ":department", $department);
		oci_bind_by_name($stid, ":companySize", $companySize);
	}
}

/*Insert or Update User Information */
function updateInformation($sql, $type){
	global $conn;
	// Connect to database
	$stid = oci_parse($conn,$sql);
	bindVariable($stid, $type);
	// Execute and Check Errors
	oci_execute($stid, OCI_NO_AUTO_COMMIT);	
	$err = oci_error($stid);
	if ($err) {
		$err_code = $err['code']; 
		if($err_code == 1) {
			$error_msg = "Your User ID is already used. Please try another User ID.<br>\n";
		} else {
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		}
		echo $error_msg;
		return 0;
	} else {
		return 1;
	}
}

/* Retrieve Information for Selected User*/
function retrieveInfo($userID, $conn){
	$userSelectSQL = "SELECT * FROM Users WHERE userID=:userID";
	$jobSeekerSelectSQL = "SELECT * FROM job_seeker WHERE userID=:userID";
	$employerSelectSQL = "SELECT * FROM employer WHERE userID=:userID";
	
	retrieveSQL($userSelectSQL, $conn, 1, $userID);
	retrieveSQL($jobSeekerSelectSQL, $conn, 2, $userID);
	retrieveSQL($employerSelectSQL, $conn, 3, $userID);
}

function retrieveSQL($sql, $conn, $type, $userID){
	// Connect to database
	$stid = oci_parse($conn,$sql);
	oci_bind_by_name($stid, ":userID", $userID);
	// Execute and Check Errors
	oci_execute($stid, OCI_NO_AUTO_COMMIT);	
	$err = oci_error($stid);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		echo $error_msg;
	} else {
		// Retrieve Variable and fill in the global.
		$rows = oci_fetch_row($stid);
		if ($type == 1){
			global $password, $name, $age, $address, $email, $status;
			$password=$rows[1];
			$name=$rows[2];
			$age=$rows[3];
			$address=$rows[4];
			$email=$rows[5];
			$status=$rows[6];
		}elseif ($type == 2){
			global $currentStatus, $preferJob, $currentJob;
			$currentStatus=$rows[0];
			$preferJob=$rows[1];
			$currentJob=$rows[2];
		}else{
			global $companyName, $description, $department, $companySize;
			$companyName=$rows[0];
			$description=$rows[1];
			$department=$rows[2];
			$companySize=$rows[3];
		}
	}	
}

// MAIN START FUNCTION
// Check if it is from the submit or initial load
if ($_SERVER["REQUEST_METHOD"] == "POST"){	
	//Check userID and password.
	$userID = $_POST['userID'];
	$password = $_POST['password'];
	if (empty($userID)||empty($password)){
		echo "Required Field is missing (User ID, Password, Name, Email)";
	}
	else{
		// Get Variable Values
		getValues();
		if (empty($userID)||empty($password)||empty($name)||empty($email)){
			echo "Required Field is missing (User ID, Password, Name, Email)";
			exit;
		}	
		//Check if Update or Create
		if ($mode=='Update') {
			// Update Information
			$userUpdateSQL = "UPDATE Users SET password=:password, name=:name, age=:age, address=:address, email=:email, status=:status WHERE userID=:userID";
			$jobSeekerUpdateSQL = "UPDATE job_seeker SET currentStatus=:currentStatus, preferJob=:preferJob, currentJob=:currentJob WHERE userID=:userID";
			$employerUpdateSQL = "UPDATE employer SET companyName=:companyName, description=:description, department=:department, companySize=:companySize WHERE userID=:userID";
			$success1 = updateInformation($userUpdateSQL, 1);
			$success2 = updateInformation($jobSeekerUpdateSQL, 2);
			$success3 = updateInformation($employerUpdateSQL, 3);	
			if (($success1==1) && ($success2==1) && ($success3==1)){
				// Successful Update return to main page
				oci_commit($conn); 
				oci_close($conn);
				header('Location: main.php');
				exit;
			}else{
				oci_rollback($conn); 
			}			
		}else{
			// Create 
			$userCreateSQL = "INSERT INTO Users (userID,password,name,age,address,email,status) VALUES (:userID,:password,:name,:age,:address,:email,:status)";
			$jobSeekerCreateSQL = "INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES (:currentStatus,:preferJob,:currentJob,:userID)";
			$employerCreateSQL = "INSERT INTO employer (companyName,description,department,companySize,userID) VALUES (:companyName,:description,:department,:companySize,:userID)";
			$success1 = updateInformation($userCreateSQL, 1);
			$success2 = updateInformation($jobSeekerCreateSQL, 2);
			$success3 = updateInformation($employerCreateSQL, 3);
			if (($success1==1) && ($success2==1) && ($success3==1)){
				// Successful Update return to main page
				oci_commit($conn); 
				$_SESSION['userID']=$userID; 
				oci_close($conn);
				header('Location: main.php');
				exit;
			}else{
				echo $userID . "entered and ROLL BACK";
				oci_rollback($conn); 
			}
		}
	}	
}elseif ($mode=='Update'){
	if (empty($userID)){
		echo "User ID is missing";
	}else{
		// Retrieve Information
		retrieveInfo($userID, $conn);
	}
}

if ($mode=='JobSeekerDemo')
{
	$userID = 'j_logan';
	$password = 'j_logan';
	
	$name='James Logan';
	$age='35';
	$address='Riverbend Eyecare 
3505 Pemberton Square Boulevard 
Vicksburg, MS(Mississippi) 39180'; 
	$email='logan@yahooooo.com';
	
	$status = 1;
	
	$currentStatus='Looking for a nursing job';
	
	$preferJob='Nurse';

	$currentJob='None';
	
	
}

elseif ($mode=='EmployerDemo')
{
	$userID = 'Holy_name_hospital';
	$password = 'Hnhospital';
	
	$name='Holy Name Hospital';
	$address='Halifax Regional Hospital? 
2204 Wilborn Avenue 
Holy Name, NJ 24592'; 
	$email='HR@holyname.com';
	
	$status = 1;
	
	$companyName='Holy Name Hospital';
	
	$description = 'Holy Name Medical Center in Teaneck, NJ has provided the communities and families of northern New Jersey with compassionate medical and nursing care. 
Oncology';

	$companySize = "Over 500";
	
	
}

?>

<!DOCTYPE HTML>
<html> 

<body>
	<form method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
	   <h3><?php  echo $mode; ?> User Information</h3>
	   User ID* : <input  type="text" name="userID" value='<?php echo $userID; ?>' <?php if ($mode=="Update") echo 'readonly'; ?>>
	   <br></br>
	   Password* <input type="text" name="password" value='<?php echo $password; ?>' >
	   <br></br>
	   Name*    : <input type="text" name="name" value='<?php echo $name; ?>'>
	   <br></br>
	   Age     : <input type="number" name="age" min="0" max="200" value='<?php echo $age; ?>' >
	   <br></br>
	   Address : <TEXTAREA id="mytext" cols="40" rows="5" name="address"><?php echo $address; ?> </TEXTAREA>
	   <br></br>
	   Email*   : <input type="text" name="email" value='<?php echo $email; ?>' >
	   <br></br>
	   Status  : <input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>> Active
				 <input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>> Inactive
	   <br></br>
	   <h3><?php echo $mode; ?> Job Seeker Information</h3>
	   Current Status : <input type="text" name="currentStatus" value='<?php echo $currentStatus; ?>' >
	   <br></br>
	   Prefer Job: <input type="text" name="preferJob" value='<?php echo $preferJob; ?>' >
	   <br></br>
	   Current Job: <input type="text" name="currentJob" value='<?php echo $currentJob; ?>'>
	   <br></br>
	   <h3><?php echo $mode; ?> Employer Information</h3>
	   Company Name : <input type="text" name="companyName" value='<?php echo $companyName; ?>'>
	   <br></br>
	   Description: <TEXTAREA cols="40" rows="5" name="description"><?php echo $description; ?> </TEXTAREA>
	   <br></br>
	   Department: <input type="text" name="department" value='<?php echo $department; ?>'>
	   <br></br>
	   Company Size: <input type="text" name="companySize" value='<?php echo $companySize; ?>'>
	   <br></br>
	   <input type="submit" name='submit' value='<?php if ($mode=='New' || $mode == 'JobSeekerDemo' || $mode == 'EmployerDemo'){echo 'Submit';} else {echo 'Update';}?>' >
	   <input type="button" name='cancel' value="Cancel" <?php if ($mode=='New' || $mode == 'JobSeekerDemo' || $mode == 'EmployerDemo'){?>onclick="javascript:location.href='index.php'"<?php }else {?>onclick="javascript:location.href='main.php'"<?php }?> />
	   <input type="button" name='GenerateJobSeekerDemo' value="GenerateJobSeekerDemo" onclick="location.href='user.php?mode=JobSeekerDemo'" />
	    <input type="button" name='GenerateEmployerDemo' value="GenerateEmployerDemo" onclick="location.href='user.php?mode=EmployerDemo'" />
	</form>
	
	</body>
</html>