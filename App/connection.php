<?php
	//ini_set('display_errors', 'On'); 
	//$db = "localhost"; 
	//$conn = mysqli_connect("localhost", "freep491_meng", "md920823", "freep491_123");
	$conn = mysqli_connect("localhost", "root", "", "jobsearch");
	if($conn->connect_errno > 0){
    	die('Unable to connect to database [' . $conn->connect_error . ']');
	}
	//echo 'connect';
	//$driver = new mysqli_driver();
	//$driver->report_mode = MYSQLI_REPORT_ALL;
	//$conn->autocommit(TRUE);
	mysqli_autocommit($conn, FALSE);
	$debug = 0;
?>
