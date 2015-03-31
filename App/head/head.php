<!DOCTYPE HTML>
<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>First Job</title>
	    <link rel="shortcut icon" href="images/job-icon.png" /> 
	    <!-- Bootstrap -->
	    <link href="css/bootstrap.min.css" rel="stylesheet">
	    <link href="css/signin.css" rel="stylesheet">

	    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	    <!--[if lt IE 9]>
	      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	    <![endif]-->
 </head>
 <style type="text/css">
  	td{
  		font-family: calibri;
  		font-size: 12pt;
  	}

 </style>
	<div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="./main.php">Job Matcher</a>
        </div>
        <?php if (isset($_SESSION['userID'])) { ?>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li class="inactive"><a href="./main.php">Main page</a></li>
          </ul>
            <p class="navbar-text pull-right">
              <b> <?php echo 'Welcome '.$_SESSION['userID']; ?></b>
              &nbsp;<a href="logout.php" class="navbar-link">Logout</a>
             
            </p>
        </div>
         <?php }?>
      </div>
  </div>