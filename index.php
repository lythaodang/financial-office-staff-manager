<?php
session_start();

if (isset($_SESSION["user"])) {
	header("location:tasks.php");
	exit();
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>San Diego Office</title>
	<link rel="icon" type="image/ico" href="http://icons.iconseeker.com/ico/clearblack/black-home.ico">
	 
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="./js/login.js"></script>
	
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>

<body>
	<!-- Navigation -->
	<nav class="navbar navbar-default">
  		<div class="container-fluid">
    		<div class="navbar-header">
      			<a class="navbar-brand"><span class="glyphicon glyphicon-home"></span> San Diego Office</a>
			</div>
		</div>
	</nav>
	
	<!-- Login Panel -->
	<div class="container"> 
        <div id="loginbox" style="margin-top:40px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
            <div class="panel panel-info" >
                <div class="panel-heading">
                    <div class="panel-title">Sign In</div>
				</div>     
                    
                <div style="padding:25px" class="panel-body" >
					<p style="padding-top:10px" id="error-message" align="center"></p>
                    <form id="loginform" role="form"> 
					
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="username" type="text" class="form-control" name="username" placeholder="username">                                        
                        </div>
                
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="password" type="password" class="form-control" name="password" placeholder="password">
                        </div>                  

                        <div class="form-group">
							<!-- Login Button -->
                            <div align="center">
								<input type="submit" id="login-btn" class="form-control btn btn-login btn-success" value="Login"/>
                            </div>
                        </div>   
                    </form>     
                </div>                     
            </div>  
        </div>
	</div>
	
	<!-- Footer -->
	<footer class="footer">
      <div class="container" align="center">
        <p class="text-muted">Copyright &copy; 2015 San Diego Office.</p>
      </div>
    </footer>
</body>
</html>