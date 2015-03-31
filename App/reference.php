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

function updateInformation($sql, $conn){
	global $jobTitle, $additionalInformation, $referenceID, $relationship, $duration;
	global $rating, $companyName, $name, $email, $status, $userID;
	try{

		if ($sql == 'Update') {
			$SQL = "UPDATE Reference_Recommend SET jobTitle= ? , additionalInformation= ? , name= ? , relationship= ? , duration= ? , rating= ? , companyName= ? , email= ? , status= ?  WHERE userID= ? AND referenceID= ? ";			
			$stmt = $conn->prepare($SQL);
			$stmt->bind_param('sssssssssss', $jobTitle, $additionalInformation, $name, $relationship, $duration, $rating, $companyName, $email, $status, $userID, $referenceID);
			
		}else{
			$SQL = "INSERT INTO Reference_Recommend (jobTitle,additionalInformation,referenceID,relationship,duration,rating,companyName,name,email,status,userID) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
			$stmt = $conn->prepare($SQL);
			$stmt->bind_param('sssssssssss', $jobTitle, $additionalInformation,  $referenceID, $relationship, $duration, $rating, $companyName, $name, $email, $status, $userID);
				
		}

		// Execute and Check Errors
		$stmt->execute();
		$err = $stmt->error;

		if ($err) {
			$conn->rollback();
			$err_code = $err['code']; 
			$error_msg = "Some unknown database error occurred. Please inform database administrator with these error messages.<br>\n" . "Error code : " . $err['code'] . "<br>" . "Error message : " . $err['message']. "<br>";
			echo $error_msg;
			return 0;
		}else
		{
			$conn->commit(); 
			return 1;
		}
	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
	}
}

function retrieveInfo($userID, $conn){
	$SelectSQL = "SELECT * FROM Reference_Recommend WHERE userID= ? AND referenceID= ? ";
	retrieveSQL($SelectSQL, $conn, $userID);
}

function retrieveSQL($sql, $conn, $userID){
	global $jobTitle, $additionalInformation, $referenceID, $relationship, $duration;
	global $rating, $companyName, $name, $email, $status, $userID;
	try{
		$SQL = "SELECT * FROM Reference_Recommend WHERE userID= ? AND referenceID= ? ";
		$stmt = $conn->prepare($SQL);
		$stmt->bind_param('ss',$userID, $referenceID );
		// Execute and Check Errors
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
	
	}catch(mysqli_sql_exception $e) {
	    echo $e->__toString();
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
				$success = updateInformation('Update', $conn);	
				if ($success==1){
					// Successful Update return to the user information page
					$conn->close();
					header('Location: main.php');
					exit;
				}
			}else{
				// Create 
				$success = updateInformation('create', $conn);
				if ($success==1){
					// Successful Update return to the user information page
					$conn->close();
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
	   <table class='table table-bordered'>
		<tr id="center"><td colspan='2'><h2>Reference Information<h2></td></tr>
	   <tr><td><label>User ID* : </label></td><td><input class="form-control" type="text" name="userID" value='<?php echo $userID; ?>' readonly >
	   </td></tr><tr><td>
	   <label>Reference ID*: </label></td><td><input class="form-control" type="text" name="referenceID" value='<?php echo $referenceID; ?>'<?php if ($mode=="Update") echo 'readonly'; ?> >
	   </td></tr><tr><td>
	   <label>Company Name*    :</label> </td><td><input class="form-control" type="text" name="companyName" value='<?php echo $companyName; ?>'>
	   </td></tr><tr><td>
	   <label>Relationship*   : </label></td><td><input class="form-control" type="text" name="relationship" value='<?php echo $relationship; ?>' >
	   </td></tr><tr><td>
	   <label>Job Title    : </label></td><td><input class="form-control" type="text" name="jobTitle" value='<?php echo $jobTitle; ?>'>
	   </td></tr><tr><td>
	   <label>Name    : </label></td><td><input class="form-control" type="text" name="name" value='<?php echo $name; ?>'>
	  </td></tr><tr><td>
	   <label>Email   : </label></td><td><input class="form-control" type="text" name="email" value='<?php echo $email; ?>' >
	   </td></tr><tr><td>
	   <label>Reference Information : </td><td><TEXTAREA class="form-control" cols="40" rows="5" name="additionalInformation"><?php echo $additionalInformation; ?> </TEXTAREA>
	   </td></tr><tr><td>
	   <label>Rating *    : </label></td><td><input class="form-control" type="number" name="rating" min="1" max="10" value='<?php echo $rating; ?>' >
	   </td></tr><tr><td>
	   <label>Duration (Month)  : </label></td><td><input class="form-control" type="number" name="duration" min="0" value='<?php echo $duration; ?>' >
	   </td></tr><tr><td>
	   <label>Status  : </label></td><td><div class='radio'>
	   			<input class="form-control" type="radio" name="status" value="1" <?php if ($status) {echo 'checked';} ?>> Active
				 <input class="form-control" type="radio" name="status" value="0" <?php if (!$status) {echo 'checked';} ?>> Inactive
	   </div></td></tr><tr><td colspan='2'>	   
	   <input class="btn btn-default" type="submit" name='submit' value='<?php if ($mode=='New'){echo 'Submit';} else {echo 'Update';}?>' >
	   <input class="btn btn-default" type="button" name='cancel' value="Cancel" onclick="location.href='./main.php'" />
		</td></tr>
		</table>
	</form>
</div>
</div>
</body>
</html>