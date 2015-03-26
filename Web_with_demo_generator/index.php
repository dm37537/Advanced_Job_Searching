<?php
session_start();
require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{	
	$userID = $_POST['userID'];
	$password = $_POST['password'];
	if (empty($userID)||empty($password)){
		echo "User ID or Password is missing";
	}
	else{
		$stid = oci_parse($conn, "select * from Users where userID='".$userID."'"); 
		oci_execute($stid, OCI_DEFAULT);
		$row = oci_fetch_row($stid);
		oci_close($conn);
		if(!empty($row)){	
			if($row[1] == $password){
				$_SESSION['userID']=$userID;
				header('Location: main.php');
				exit;
			}else{
				echo "Password is wrong";
			}
		}else{
			echo "User does not exist";
		}
	}
}
?>
<!DOCTYPE HTML>
<html> 
	<h3>Log in</h3>
	<form method="post" action="<?php $_SERVER['PHP_SELF'];?>"> 
   	User ID*: <input type="text" name="userID">
   	<br><br>
   	Password*: <input type="text" name="password" >
   	<br><br>
   	<input type="submit" name="submit" value="Submit">
	</form>
	<h3>Create an account</h3>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"> 
	<button type="button" onclick="location.href='user.php?mode=New'">Create</button>
	<br><br><br><br>
	<button type="button" onclick="location.href='jobSearch.php'">Search Job</button>
	<button type="button" onclick="location.href='resumeSearch.php'">Search Resume</button>
	</form>
</html>


