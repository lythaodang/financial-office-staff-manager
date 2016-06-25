<?php
session_start();
	
if (!isset($_SESSION["user"]) || (isset($_SESSION["role"]) && $_SESSION["role"] != "Admin")) {
    header("Location: ./index.php");
    exit();
}

if (isset($_GET['logout']) || (isset($_REQUEST['opt']) && $_REQUEST['opt']=='Logout')) {
	$_SESSION = array();
	session_destroy();
	header("Location: ./index.php");
	exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>San Diego Office</title>
	<link rel="icon" type="image/ico" href="http://icons.iconseeker.com/ico/clearblack/black-home.ico">
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  		<style>
		#content {
			margin: 25px;
		}
		
		#user-table-div, #schedule-table-div { 
			margin-top: 25px;
			max-height: 500px;
			overflow: auto; 
		}
		
		th,td {
			min-width: 150px;
			text-align: center;  
			max-height: 50px;
		}
	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="./js/timeout.js"></script>
	<script>
		var url = "./serverside.php";
		
		<?php
			if ($_SESSION['role'] == 'Admin') {
		?>	
		
		// CLEAR FORM
		function clearForm() {
			$("#error-message").html("");
			$('form input[type="text"]').each(function(){
				$(this).val("");  
			});
			$('form input[type="password"]').each(function(){
				$(this).val("");  
			});
		}
		
		// CREATE USER
		$(document).on('click', 'input#create-user', function() {
			var userid = $('#userid').val();
			userid = userid.toLowerCase();
			var password = $('#password').val();
			var name = $('#name').val();
			var email = $('#email').val();
			var smdname = $('#smdname').val();
			var agentcode = $('#agentcode').val();
			var license = $('#license').val();
			var role = $('#role').val();
			var phone = $('#phone').val();
			
			if (userid == "" || password == "" || name == "" || email == "" || phone == "" 
			|| smdname == "" || agentcode == "" || license == "" || role == "") {
				$("#error-message").html("<font color='red'><i>All fields must be filled</i></font>");
				return;
			}
			
			$.post(url, {
				contentVar: "createUser",
				userid: userid,
				password: password,
				name: name,
				email: email,
				smdname: smdname,
				agentcode: agentcode,
				license: license,
				role: role,
				phone: phone
			}, function (data) {
				if (data == 'success') {
					clearForm();
					updateUserTable();
					updateScheduleTable();
					$("#error-message").html("<font color='green'><i>User created successfully.</i></font>");
				} else if (data == "already registered") {
					$("#error-message").html("<font color='red'><i>Userid already used.</i></font>");
				} else {
					$("#error-message").html("<font color='red'><i>A system error has occurred. Please contact the system administrator.</i></font>");
					console.log(data);
				}
			});
		});
		
		// DELETE USER
		function deleteUser(btnClicked) {
			var confirm = window.confirm("Are you sure you want to delete user: " + btnClicked.value + "?");
			if (confirm == true) {
				$.post(url, {
					contentVar: "deleteUser",
					username: btnClicked.value
				}, function (data) {
					if (data == "success") {
						updateUserTable();
						updateScheduleTable();
						alert("User '" + btnClicked.value + "' was deleted.");
					} else {
						alert("Failed to delete user: " + btnClicked.value + ". Please contact a system administrator.");
					}
				});
			} else {
				alert("User '" + btnClicked.value + "' was not deleted.");
			}
		}
		
		// RESET PASSWORD
		function resetPassword(btnClicked) {
			var newPassword = prompt("Enter new password: ");
    
			if (newPassword != null && newPassword != "") {
				$.post(url, {
					contentVar: "resetPassword",
					newPassword: newPassword,
					username: btnClicked.value
				}, function (data) {
					if (data == "success") {
						updateUserTable();
						alert("Password successfully reset.");
					} else {
						alert("Could not reset password for " + btnClicked.value);
						console.log(data);
					}
				});
			}
		}
		
		// UPDATE USER TABLE
		function updateUserTable() {
			$.post(url, {
				contentVar: "getUsers"
			}, function (data) {
				if (data == 'error') {
					alert("A system error occurred. Could not retrieve users. Please contact system administrator.");
				} else {
					var users = JSON.parse(data);
					$('#user-table tbody > tr').remove();
					for(var i in users) {
						var loginid = users[i][0];
						var name = users[i][1];
						var role = users[i][2];
						var phone = users[i][3];
						var smd = users[i][4];
						var agentCode = users[i][5];
						var license = users[i][6];
						var email = users[i][7];
						var row = '<tr><td>' + loginid + '</td>' +
							'<td>' + name + '</td>' +
							'<td>' + role + '</td>' +
							'<td>' + phone + '</td>' +
							'<td>' + smd + '</td>' +
							'<td>' + agentCode + '</td>' + 
							'<td>' + license + '</td>' +
							'<td>' + email + '</td>' +
							'<td>' + "<button onclick='resetPassword(this)' href='#' value='" + loginid + "'>Reset</button><button onclick='deleteUser(this)' value='" + loginid + "'>Delete</button></td></tr>";
						$('#user-table tbody').append(row);
					}
				}
			});
		}
		
		// UPDATE SCHEDULE TABLE
		function updateScheduleTable() {
			$.post(url, {
				contentVar: "getSchedules"
			}, function (data) {
				if (data == 'error') {
					alert("A system error occurred. Could not retrieve schedules. Please contact system administrator.");
				} else {
					var users = JSON.parse(data);
					$('#schedule-table tbody > tr').remove();
					for(var i in users) {
						var loginid = users[i][0];
						var sunS = users[i][1];
						var sunE = users[i][2];
						var monS = users[i][3];
						var monE = users[i][4];
						var tueS = users[i][5];
						var tueE = users[i][6];
						var wedS = users[i][7];
						var wedE = users[i][8];
						var thuS = users[i][9];
						var thuE = users[i][10];
						var friS = users[i][11];
						var friE = users[i][12];
						var satS = users[i][13];
						var satE = users[i][14];
						var num_assigns = users[i][15];
						var sun, mon, tue, wed, thu, fri, sat;
						
						if (sunS != null) {
							sun = sunS + "-" + sunE;
						} else { 
							sun = "-"; 
							sunS = -1;
							sunE = -1;
						};
						if (monS != null) {
							mon = monS + "-" + monE;
						} else { 
							mon = "-"; 
							monS = -1;
							monE = -1;
						};
						if (tueS != null) {
							tue = tueS + "-" + tueE;
						} else { 
							tue = "-"; 
							tueS = -1;
							tueE = -1;
						};
						if (wedS != null) {
							wed = wedS + "-" + wedE;
						} else { 
							wed = "-"; 
							wedS = -1;
							wedE = -1;
						};
						if (thuS != null) {
							thu = thuS + "-" + thuE;
						} else { 
							thu = "-"; 
							thuS = -1;
							thuE = -1;
						};
						if (friS != null) {
							fri = friS + "-" + friE;
						} else { 
							fri = "-"; 
							friS = -1;
							friE = -1;
						};
						if (satS != null) {
							sat = satS + "-" + satE;
						} else { 
							sat = "-";
							satS = -1;
							satE = -1;
						};
						
						var row = '<tr><td>' + loginid + '</td>' +
							'<td>' + sun + '</td>' +
							'<td>' + mon + '</td>' +
							'<td>' + tue + '</td>' +
							'<td>' + wed + '</td>' +
							'<td>' + thu + '</td>' + 
							'<td>' + fri + '</td>' +
							'<td>' + sat + '</td>' +
							'<td>' + num_assigns + '</td>' +
							'<td>' + "<button onclick='editSchedule(" + '"' + loginid + '"' + "," + 
							sunS + "," + sunE + "," + monS + "," + monE + "," + 
							tueS + "," + tueE + "," + wedS + "," + wedE + "," + 
							thuS + "," + thuE + "," + friS + "," + friE + "," + 
							satS + "," + satE + "," + num_assigns +
							")' data-toggle='modal' data-target='#schedule-form' href='#'>Edit</button></tr>";
						$('#schedule-table tbody').append(row);
					}
				}
			});
		}
		
		// UPDATE SCHEDULE
		$(document).on('click', 'input#change-schedule', function() {
			var userid = $('#suserid').val();
			var sunS = Number($('#SundayFrom').val());
			var sunE = Number($('#SundayTo').val());
			var monS = Number($('#MondayFrom').val());
			var monE = Number($('#MondayTo').val());
			var tueS = Number($('#TuesdayFrom').val());
			var tueE = Number($('#TuesdayTo').val());
			var wedS = Number($('#WednesdayFrom').val());
			var wedE = Number($('#WednesdayTo').val());
			var thuS = Number($('#ThursdayFrom').val());
			var thuE = Number($('#ThursdayTo').val());
			var friS = Number($('#FridayFrom').val());
			var friE = Number($('#FridayTo').val());
			var satS = Number($('#SaturdayFrom').val());
			var satE = Number($('#SaturdayTo').val());
			var num_assigns = $("#num_assigns").val();
			
			if ((sunS == -1 && sunE != -1) || (sunS != -1 && sunE == -1) || 
				(monS == -1 && monE != -1) || (monS != -1 && monE == -1) || 
				(tueS == -1 && tueE != -1) || (tueS != -1 && tueE == -1) || 
				(wedS == -1 && wedE != -1) || (wedS != -1 && wedE == -1) || 
				(thuS == -1 && thuE != -1) || (thuS != -1 && thuE == -1) || 
				(friS == -1 && friE != -1) || (friS != -1 && friE == -1) || 
				(satS == -1 && satE != -1) || (satS != -1 && satE == -1)) { 
				$("#suserid").html("<font color='red'><i>Invalid input. Start and End must both have values or both be N/A.</i></font>");
				return;
			}
			if ((sunS != -1 && sunS >= sunE) || (monS != -1 && monS >= monE) || (tueS != -1 && tueS >= tueE) 
				|| (wedS != -1 && wedS >= wedE) || (thuS != -1 && thuS >= thuE) || (friS != -1 && friS >= friE) 
				|| (satS != -1 && satS >= satE)) {
				$("#suserid").html("<font color='red'><i>Invalid input. Start must be before End.</i></font>");
				return;
			}
			if (num_assigns < 0) {
				$("#suserid").html("<font color='red'><i>Number of assignments must be 0 or more.</i></font>");
				return;
			}
					
			$.post(url, {
				contentVar: "updateSchedule",
				userid: userid,
				sunS: sunS,
				sunE: sunE,
				monS: monS,
				monE: monE,
				tueS: tueS,
				tueE: tueE,
				wedS: wedS,
				wedE: wedE,
				thuS: thuS,
				thuE: thuE,
				friS: friS,
				friE: friE,
				satS: satS,
				satE: satE,
				num_assigns: num_assigns
			}, function (data) {
				if (data == "success") {
					$("#suserid").html("<font color='green'><i>Schedule was updated.</i></font>");
					updateScheduleTable();
				} else {
					$("#suserid").html("<font color='red'><i>System error when updating schedule. Please notify system administrator.</i></font>");
					console.log(data);
				}
			});
		});
		
		// EDIT SCHEDULE MODAL
		function editSchedule(userid,sunS,sunE,monS,monE,tueS,tueE,wedS,wedE,thuS,thuE,friS,friE,satS,satE,num_assigns) {
			$(".modal-body #suserid").val(userid); 
			$("#SundayFrom").val(sunS);
			$("#SundayTo").val(sunE);
			$("#MondayFrom").val(monS);
			$("#MondayTo").val(monE);
			$("#TuesdayFrom").val(tueS);
			$("#TuesdayTo").val(tueE);
			$("#WednesdayFrom").val(wedS);
			$("#WednesdayTo").val(wedE);
			$("#ThursdayFrom").val(thuS);
			$("#ThursdayTo").val(thuE);
			$("#FridayFrom").val(friS);
			$("#FridayTo").val(friE);
			$("#SaturdayFrom").val(satS);
			$("#SaturdayTo").val(satE);
			$("#num_assigns").val(num_assigns);
			
			$("#suserid").html("Changing schedule for user: " + userid);
		}		
		
		function setUpModal() {
			$('.day').each(function() {
				var day = $(this).attr('id');
				$(this).append('<div style="text-align:left;float:left;min-width:80px;" id="label">' + day + ' </div>');
				$(this).append('<select id="' + day + 'From" class="hour from"></select>');
				$(this).append(' to <select id="' + day + 'To" class="hour to"></select>');
			});

			$('.hour').each(function() {
				$(this).append('<option selected="selected" value="-1">N/A</option>');
				for (var h = 0; h < 25; h++) {
					$(this).append('<option value="' + h + '">' + h + '</option>');
				}
			});
		}		
		<?php
			}
		?>
	</script>
</head>

<body id="homepage" onload="set_interval()" onmousemove="reset_interval()" onclick="reset_interval()" onkeypress="reset_interval()" onscroll="reset_interval()">
	
	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#"><span class="glyphicon glyphicon-home"></span> San Diego Office</a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="tasks.php"> Tasks</a></li>
					<?php
					if ($_SESSION['role'] == 'Admin') {
					?>
						<li><a href="users.php"> Users</a></li>
					<?php 
					}
					?>
					<li class="dropdown">
						<a  class='dropdown-toggle' data-toggle='dropdown' href="#">Welcome, <?php echo $_SESSION['user_name']; ?><b class='caret'></b></a></a>
						<ul class='dropdown-menu'>
							<li><a href="?logout"> Logout</a></li>
						</ul>
					</li>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
    </nav>
	
	<div id="content" class="text-center">
		<?php
		if ($_SESSION['role'] == 'Admin') {
		?>
			<h2><strong>Users</strong></h2>
			<!-- CREATE USER BUTTON -->
			<a data-toggle='modal' data-target='#user-form' class="btn btn-primary btn-large">Create User</a>
			
			<!-- USERS TABLE -->
			<div id="user-table-div">
				<table class="table table-bordered table-condensed table-responsive" id="user-table">
					<thead>
						<tr>
							<th>Login ID <i class='glyphicon glyphicon-sort'></th>
							<th>Name <i class='glyphicon glyphicon-sort'></th>
							<th>Role <i class='glyphicon glyphicon-sort'></th>
							<th>Phone</th>
							<th>SMD Name</th>
							<th>Agent Code</th>
							<th>License</th>
							<th>Email</th>
							<th>Reset/Delete</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			
			<h2><strong>Schedules</strong></h2>
			<!-- SCHEDULES TABLE -->
			<div id="schedule-table-div">
				<table class="table table-bordered table-condensed table-responsive" id="schedule-table">
					<thead>
						<tr>
							<th>Login ID <i class='glyphicon glyphicon-sort'></th>
							<th>Sunday</th>
							<th>Monday</th>
							<th>Tuesday</th>
							<th>Wednesday</th>
							<th>Thursday</th>
							<th>Friday</th>
							<th>Saturday</th>
							<th># Tasks Assigned</th>
							<th>Edit</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			
			<!-- CREATE USER MODAL -->
			<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog"  id="user-form">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
							<h4 class="modal-title">Create User</h3>
						</div>
						<div class="modal-body">
							<p style="padding-top:10px" id="error-message" align="center"></p>
							<form role="form" name="user-form" method="post" action="#">
								<div class="form-group row">
									<label for="userid" class="col-sm-3 form-control-label">User ID</label>
									<div class="col-sm-9">
										<input maxlength="32" type="text" name="userid" id="userid" placeholder="User ID" class="form-control">		
									</div>
								</div>
								<div class="form-group row">
									<label for="password" class="col-sm-3 form-control-label">Password</label>
									<div class="col-sm-9">
										<input maxlength="255" type="password" name="password" id="password" placeholder="Password" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="name" class="col-sm-3 form-control-label">Name</label>
									<div class="col-sm-9">
										<input maxlength="32" type="text" name="name" id="name" placeholder="Name" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="phone" class="col-sm-3 form-control-label">Phone</label>
									<div class="col-sm-9">
										<input maxlength="12" type="text" name="phone" id="phone" placeholder="Phone" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="email" class="col-sm-3 form-control-label">Email</label>
									<div class="col-sm-9">
										<input maxlength="50" type="text" name="email" id="email" placeholder="Email" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="smdname" class="col-sm-3 form-control-label">SMD Name</label>
									<div class="col-sm-9">
										<input maxlength="50" type="text" name="smdname" id="smdname" placeholder="SMD Name" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="agentcode" class="col-sm-3 form-control-label">Agent Code</label>
									<div class="col-sm-9">
										<input maxlength="20" type="text" name="agentcode" id="agentcode" placeholder="Agent Code" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label for="license" class="col-sm-3 form-control-label">License</label>
									<div class="col-sm-9">
										<input maxlength="40" type="text" name="license" id="license" placeholder="License" class="form-control">	
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-3 form-control-label">Role</label>
									<div class="col-sm-9">
										<select name="role" id="role" class="form-control">
											<option value="Agent">Agent
											<option value="Staff">Staff
										</select>	
									</div>
								</div>
							</form>
							<input class="btn btn-success" type="button" value="Submit" id="create-user">
						</div>
					</div>
				</div>
			</div>
			<!-- CREATE USER MODAL -->
			
			<!-- EDIT SCHEDULE MODAL -->
			<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog" id="schedule-form">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
							<h4 class="modal-title">Edit Schedule</h3>
						</div>
						<div class="modal-body">
							<p style="padding-top:10px" id="suserid" align="center" value=""></p>
							<form class="schedule-modal" role="form" name="schedule-modal" method="post" action="#">
								<div class="input-group">
									<div id="hourForm:">
										<div id="Sunday" class="day form-control"></div>
										<div id="Monday" class="day form-control"></div>
										<div id="Tuesday" class="day form-control"></div>
										<div id="Wednesday" class="day form-control"></div>
										<div id="Thursday" class="day form-control"></div>
										<div id="Friday" class="day form-control"></div>
										<div id="Saturday" class="day form-control"></div>
									</div>
									<div class="form-control" style="min-height:40px">
										<div style="text-align:left;float:left;min-width:80px;">
											# Tasks
										</div>
										<input style="max-width:100px" type="number" id="num_assigns" min="0">
									</div>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<input class="btn btn-success" type="button" value="Submit" id="change-schedule">
						</div>
					</div>
				</div>
			</div>
			<!-- EDIT SCHEDULE MODAL -->
			
		<?php 
		}
		?>
	</div>
	
	<!-- Footer -->
	<footer class="footer">
		<div class="container" align="center">
			<p class="text-muted">Copyright &copy; 2015 San Diego Office.</p>
		</div>
	</footer>
	<!-- Footer -->
	
	<script>
		setUpModal();
		updateUserTable();
		updateScheduleTable();
	</script>
</body>