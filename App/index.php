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

		$sql = "select * from users where userID='".$userID."'";
		$result = $conn->query($sql);
		if(!$result ){
		   die('There was an error running the query [' . $conn->error . ']');
		}
		$row = $result->fetch_array();
		$conn->close();

		if(!empty($row)){	
			if($row[1] == $password){
				$_SESSION['userID']=$userID;
				header('Location: main.php');
				//echo "get in";
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
	<?php include 'head/head.php';?>
	<body>
		<br><br><br><br><br><br>
	  <div class="container">

	      <form class="form-signin" method="post" action="<?php $_SERVER['PHP_SELF'];?>">
	        <label for="inputEmail" class="sr-only"></label>
	        <input type="text" name="userID" id="inputEmail" class="form-control" placeholder="User ID" required autofocus>
	        <label for="inputPassword" class="sr-only"></label>
	        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
	        <input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="Log in"></input>
	      </form>

	    </div> <!-- /container -->
	    <div class="container">

	      <form class="form-signin">
	        <button class="btn btn-lg btn-primary btn-block" type="button" onclick="location.href='user.php?mode=New'">Create Account</button>
			<br><br><br><br>
			<button class="btn btn-lg btn-primary btn-block" type="button" onclick="location.href='jobSearch.php'">Search Job</button>
			<button class="btn btn-lg btn-primary btn-block" type="button" onclick="location.href='resumeSearch.php'">Search Resume</button>
	      </form>

	    </div> <!-- /container -->

	</body>
</html>


