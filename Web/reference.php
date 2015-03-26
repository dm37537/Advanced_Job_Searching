<?php
session_start();
require_once "connection.php";
/*Initialize parameter*/
$mode=$_GET['mode'];
$userID = $_SESSION['userID'];
$referenceID="";
$jobTitle=""; $additionalInformation=""; $relationship=""; $duration=""; 
$rating=""; $companyName=""; $name=""; $email=""; $status=""; 

/*Get Variable*/
function getValues(){
	global $jobTitle, $additionalInformation, $referenceID, $relationship, $duration;
	global $rating, $companyName, $name, $email, $status, $userID;
 	$jobTitle=$_POST['jobTitle']; 
 	$additionalInformation=trim($_POST['additionalInformation']); 
 	$referenceID=trim($_POST['referenceID']); 
 	$relationship=$_POST['relationship']; 
 	$duration=$_POST['duration'];
 	$rating=$_POST['rating']; 
 	$companyName=$_POST['companyName']; 
 	$name=$_POST['name'];
 	$email=$_POST['email']; 
 	$status=trim($_POST['status']); 
 	$userID=$_POST['userID']; 
}

/*Bind Variable for SQL Update*/
function bindVariable($stid){
	global $jobTitle, $additionalInformation, $referenceID, $relationship, $duration;
	global $rating, $companyName, $name, $email, $status, $userID;
	oci_bind_by_name($stid, ":jobTitle", $jobTitle);
	oci_bind_by_name($stid, ":additionalInformation", $additionalInformation);
	oci_bind_by_name($stid, ":referenceID", $referenceID);
	oci_bind_by_name($stid, ":relationship", $relationship);
	oci_bind_by_name($stid, ":duration", $duration);
	oci_bind_by_name($stid, ":rating", $rating);
	oci_bind_by_name($stid, ":companyName", $companyName);
	oci_bind_by_name($stid, ":name", $name);
	oci_bind_by_name($stid, ":email", $email);
	oci_bind_by_name($stid, ":status", $status);
	oci_bind_by_name($stid, ":userID", $userID);
}

function updateInformation($sql, $conn){
	// Connect to database
	$stid = oci_parse($conn,$sql);
	bindVariable($stid);
	// Execute and Check Errors
	oci_execute($stid, OCI_NO_AUTO_COMMIT);	
	$err = oci_error($stid);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		if($err_code == 1) {
			$error_msg = "Your Reference ID is already used. Please try another Reference ID.<br>\n";
		} else {
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		}
		echo $error_msg;
		return 0;
	} else {
		oci_commit($conn); 
		return 1;
	}
}

function retrieveInfo($userID, $conn){
	$SelectSQL = "SELECT * FROM Reference_Recommend WHERE userID=:userID AND referenceID=:referenceID";
	retrieveSQL($SelectSQL, $conn, $userID);
}

function retrieveSQL($sql, $conn, $userID){
	global $jobTitle, $additionalInformation, $referenceID, $relationship, $duration;
	global $rating, $companyName, $name, $email, $status, $userID;
	// Connect to database
	$stid = oci_parse($conn,$sql);
	oci_bind_by_name($stid, ":userID", $userID);
	oci_bind_by_name($stid, ":referenceID", $referenceID);
	// Execute and Check Errors
	oci_execute($stid);	
	$err = oci_error($stid);
	if ($err) {
		oci_rollback($conn); 
		$err_code = $err['code']; 
		$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
		echo $error_msg;
	} else {
		// Retrieve Variable and fill in the global.
		$rows = oci_fetch_row($stid);
		$jobTitle=$rows[0];
		$additionalInformation=$rows[1];
		$relationship=$rows[3];
		$duration=$rows[4];
		$rating=$rows[5];
		$companyName=$rows[6];
		$name=$rows[7];
		$email=$rows[8];
		$status=$rows[9];
	}	
}
// MAIN START FUNCTION
// Check if it is from the submit or initial load
if ($_SERVER["REQUEST_METHOD"] == "POST"){	
	//Check userID and password.
	$userID = $_POST['userID'];
	$referenceID = $_POST['referenceID'];
	if (empty($userID)||empty($referenceID)){
		echo "User ID or Reference ID is missing";
	}
	else{
		// Get Variable Values
		getValues();
		if (empty($userID)||empty($referenceID)||empty($relationship)||empty($companyName)||empty($rating)){
			echo "Required Field is missing (User ID,Reference ID, Company Name, Relationship)";
		}else{
			//Check if Update or Create
			if ($mode=='Update') {
				// Update Information
				$UpdateSQL = "UPDATE Reference_Recommend SET jobTitle=:jobTitle, additionalInformation=:additionalInformation, name=:name, relationship=:relationship, duration=:duration, rating=:rating, companyName=:companyName, email=:email, status=:status WHERE userID=:userID AND referenceID=:referenceID";
				$success = updateInformation($UpdateSQL, $conn);	
				if ($success==1){
					// Successful Update return to the user information page
					oci_close($conn);
					header('Location: main.php');
					exit;
				}
			}else{
				// Create 
				$CreateSQL = "INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES (:jobTitle,:additionalInformation,:referenceID,:relationship,:duration,:rating,:companyName,:name,:email,:status,:userID)";
				$success = updateInformation($CreateSQL, $conn);
				if ($success==1){
					// Successful Update return to the user information page
					oci_close($conn);
					header('Location: main.php');
					exit;
				}
			}
		}
	}	
}elseif ($mode=='Update'){
	$referenceID=$_GET['referenceID'];
	if (empty($userID)){
		echo "User ID is missing";
	}elseif (empty($referenceID)){
		echo "Reference ID is missing";
	}else{
		// Retrieve Information
		retrieveInfo($userID, $conn);
	}
}
?>

<!DOCTYPE HTML>
<html> 
	<form method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
	   <h3><?php  echo $mode; ?> Reference Information</h3>
	   User ID* : <input type="text" name="userID" value='<?php echo $userID; ?>' readonly >
	   <br><br>
	   Reference ID*: <input type="text" name="referenceID" value='<?php echo $referenceID; ?>'<?php if ($mode=="Update") echo 'readonly'; ?> >
	   <br><br>
	   Company Name*    : <input type="text" name="companyName" value='<?php echo $companyName; ?>'>
	   <br><br>
	   Relationship*   : <input type="text" name="relationship" value='<?php echo $relationship; ?>' >
	   <br><br>
	   Job Title    : <input type="text" name="jobTitle" value='<?php echo $jobTitle; ?>'>
	   <br><br>
	   Name    : <input type="text" name="name" value='<?php echo $name; ?>'>
	   <br><br>
	   Email   : <input type="text" name="email" value='<?php echo $email; ?>' >
	   <br><br>
	   Reference Information : <TEXTAREA cols="40" rows="5" name="additionalInformation"><?php echo $additionalInformation; ?> </TEXTAREA>
	   <br><br>
	   Rating *    : <input type="number" name="rating" min="1" max="10" value='<?php echo $rating; ?>' >
	   <br><br>
	   Duration (Month)  : <input type="number" name="duration" min="0" value='<?php echo $duration; ?>' >
	   <br><br>
	   Status  : <input type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>> Active
				 <input type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>> Inactive
	   <br><br>
	   <input type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} else {echo 'Update';}?>' >
	   <input type="button" name='cancel' value="Cancel" onclick="location.href='/main.php'" />
	</form>
</html>