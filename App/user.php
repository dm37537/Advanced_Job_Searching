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


/*Insert or Update User Information */
function updateInformation($action, $type){
	global $conn;
	global $userID, $password, $name, $age, $address, $email, $status;
	global $currentStatus, $preferJob, $currentJob;
	global $companyName, $description, $department, $companySize;

	try{
		if ($action == 'update') {
			if ($type == 1) {
				$userUpdateSQL = "UPDATE users SET password= ? , name=?, age=?, address=?, email=?, status=? WHERE userID=?";
				$stmt = $conn->prepare($userUpdateSQL);
				$stmt->bind_param('sssssss', $password, $name, $age, $address, $email, $status, $userID);
			}else if ($type == 2){
				$jobSeekerUpdateSQL = "UPDATE job_seeker SET currentStatus= ?, preferJob=?, currentJob=? WHERE userID=?";
				$stmt = $conn->prepare($jobSeekerUpdateSQL);
				$stmt->bind_param('ssss', $currentStatus, $preferJob, $currentJob, $userID);
			}else{
				$employerUpdateSQL = "UPDATE employer SET companyName=?, description=?, department=?, companySize=? WHERE userID=?";
				$stmt = $conn->prepare($employerUpdateSQL);
				$stmt->bind_param('sssss', $companyName,$description,$department,$companySize,$userID);
			}
		}else{
			if ($type == 1) {
				$userCreateSQL = "INSERT INTO users (userID,password,name,age,address,email,status) VALUES (?,?,?,?,?,?,?)";
				$stmt = $conn->prepare($userCreateSQL);
				$stmt->bind_param('sssssss', $userID, $password, $name, $age, $address, $email, $status);
			}else if ($type == 2){
				$jobSeekerCreateSQL = "INSERT INTO job_seeker (currentStatus,preferJob,currentJob,userID) VALUES (?,?,?,?)";
				$stmt = $conn->prepare($jobSeekerCreateSQL);
				$stmt->bind_param('ssss', $currentStatus, $preferJob, $currentJob, $userID);
			}else{
				$employerCreateSQL = "INSERT INTO employer (companyName,description,department,companySize,userID) VALUES (?,?,?,?,?)";
				$stmt = $conn->prepare($employerCreateSQL);
				$stmt->bind_param('sssss', $companyName,$description,$department,$companySize,$userID);
			}
		}
		$stmt->execute();
		$err = $stmt->error;
		if ($err) {
			$conn->rollback();
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
			return 0;
		}else {
			return 1;
		}	
	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
	}
}

/* Retrieve Information for Selected User*/
function retrieveInfo($userID, $conn){
	retrieveSQL($conn, 1, $userID);
	retrieveSQL($conn, 2, $userID);
	retrieveSQL($conn, 3, $userID);
}

function retrieveSQL($conn, $type, $userID){
	
	try{
		if ($type == 1) {
			$userSelectSQL = "SELECT * FROM users WHERE userID= ? ";
			$stmt = $conn->prepare($userSelectSQL);
			$stmt->bind_param('s', $userID);
		}else if ($type == 2){
			$jobSeekerSelectSQL = "SELECT * FROM job_seeker WHERE userID= ?";
			$stmt = $conn->prepare($jobSeekerSelectSQL);
			$stmt->bind_param('s', $userID);
		}else{
			$employerSelectSQL = "SELECT * FROM employer WHERE userID= ? ";
			$stmt = $conn->prepare($employerSelectSQL);
			$stmt->bind_param('s', $userID);
		}
		$stmt->execute();
		$err = $stmt->error;
		$result = $stmt->get_result();
		if ($err) {
			$conn->rollback();
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
			return 0;
		}else {
			// Retrieve Variable and fill in the global.
			$rows = $result->fetch_array();
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
	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
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
			$success1 = updateInformation('update', 1);
			$success2 = updateInformation('update', 2);
			$success3 = updateInformation('update', 3);	
			if (($success1==1) && ($success2==1) && ($success3==1)){
				// Successful Update return to main page
				$conn->commit(); 
				$conn->close();
				header('Location: main.php');
				exit;
			}else{
				$conn->rollback(); 
			}			
		}else{
			// Create 
			$success1 = updateInformation('create', 1);
			$success2 = updateInformation('create', 2);
			$success3 = updateInformation('create', 3);
			if (($success1==1) && ($success2==1) && ($success3==1)){
				// Successful Update return to main page
				$conn->commit(); 
				$_SESSION['userID']=$userID; 
				$conn->close();
				header('Location: main.php');
				exit;
			}else{
				echo $userID . "entered and ROLL BACK";
				$conn->rollback(); 
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
				<tr><td colspan='2'><h2><?php  echo $mode; ?> User Information</h2></td></tr>
		   <tr><td><label>User ID* : </label> </td><td><input class="form-control" type="text" name="userID" value='<?php echo $userID; ?>' <?php if ($mode=="Update") echo 'readonly'; ?>>
		   </td></tr>
		   <tr><td><label>Password* </label> </td><td><input class="form-control" type="text" name="password" value='<?php echo $password; ?>' >
		   </td></tr>
		   <tr><td><label>Name*    : </label> </td><td><input class="form-control" type="text" name="name" value='<?php echo $name; ?>'>
		  </td></tr>
		   <tr><td><label>Age     : </label> </td><td><input class="form-control" type="number" name="age" min="0" max="200" value='<?php echo $age; ?>' >
		   </td></tr>
		   <tr><td><label>Address : </label> </td><td><TEXTAREA class="form-control" cols="40" rows="5" name="address"><?php echo $address; ?> </TEXTAREA>
		   </td></tr>
		   <tr><td><label>Email*   : </label> </td><td><input class="form-control" type="text" name="email" value='<?php echo $email; ?>' >
		   </td></tr>
		   <tr><td><label>Status  : </label> </td><td>
		   			<div class='radio'> <input class="form-control" type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>> Active
					 <input class="form-control" type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>> Inactive
		   </div></td></tr>
		   <tr><td colspan='2'><h2><?php echo $mode; ?> Job Seeker Information</h2></td></tr>
		   <tr><td><label>Current Status : </label> </td><td><input class="form-control" type="text" name="currentStatus" value='<?php echo $currentStatus; ?>' >
		   </td></tr>
		   <tr><td><label>Prefer Job:</label> </td><td> <input class="form-control" type="text" name="preferJob" value='<?php echo $preferJob; ?>' >
		   </td></tr>
		   <tr><td><label>Current Job: </label> </td><td><input class="form-control" type="text" name="currentJob" value='<?php echo $currentJob; ?>'>
		   </td></tr>
		   <tr><td colspan='2'><h2><?php echo $mode; ?> Employer Information</h2></td></tr>
		   <tr><td><label>Company Name : </label> </td><td><input class="form-control" type="text" name="companyName" value='<?php echo $companyName; ?>'>
		   </td></tr>
		   <tr><td><label>Description: </label> </td><td><TEXTAREA class="form-control" cols="40" rows="5" name="description"><?php echo $description; ?> </TEXTAREA>
		   </td></tr>
		   <tr><td><label>Department: </label> </td><td><input class="form-control" type="text" name="department" value='<?php echo $department; ?>'>
		   </td></tr>
		   <tr><td><label>Company Size: </label> </td><td><input class="form-control" type="text" name="companySize" value='<?php echo $companySize; ?>'>
		   </td></tr>
		    <tr><td colspan='2'>
		   <input class="btn btn-default" type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} else {echo 'Update';}?>' >
		   <input class="btn btn-default" type="button" name='cancel' value="Cancel" onclick="location.href='./main.php'" /></td></tr>
		</form>
		</div>
		</div>
	</body>
</html>