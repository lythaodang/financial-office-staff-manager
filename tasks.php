<?php
session_start();
	
if (!isset($_SESSION["user"])) {
    header("Location: ./index.php");
    exit();
}

if (isset($_GET['logout']) || (isset($_REQUEST['opt']) && $_REQUEST['opt']=='Logout')) {
	$_SESSION = array();
	session_destroy();
	header("Location: ./index.php");
	exit;
}

if (isset($_POST) && isset($_POST['filepath'])) {
	$filepath = $_POST['filepath'];
	if (file_exists($filepath)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		readfile($filepath);
		exit;
	}
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
			margin: 0px;
		}
		
		#navbar {
			margin-bottom: 10px;
		}
		
		#task-table-div { 
			margin-top: 10px;
			overflow: auto; 
		}
		
		.left-align {
			text-align: left;   
		}
		
		.right-align {
			text-align: right;   
		}
	
		.small-col {
			min-width: 30px;
		}
		
		.medium-col {
			min-width: 150px;
		}
		
		.big-col {
			min-width: 300px;
		}
		
		td,th, #thumbnail-div {
			overflow: auto; 
		}
		
		th {
			text-align: center;
		}
		
		.max-height {
			max-height: 125px;
		}
		
		input[type=checkbox] {
			-ms-transform: scale(1.5); 
			-moz-transform: scale(1.5); 
			-webkit-transform: scale(1.5); 
			-o-transform: scale(1.5); 
			transform: scale(1.5); 
			margin-right: 10px;
		}
		
		#alertsbox {
			max-height: 300px;
			overflow: auto; 
		}
		
		#createuploadbtns {
			margin: 0px;
			padding: 0px;
		}
	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="./js/timeout.js"></script>
	<script>
		var url = "./serverside.php";
		var currenttable;
		var asc = 1;
		var sortfield = "id";
		
		function clearForm() {
			$('#client').val("");
			$('#description').val("");
			$('#category').val("Blue Cross");
			$("#error-message").html("");
			$("#comment-message").html("");
			$("#comments").val(""); 
			$("#files-message").html("");
			$('#thumbnail-img').attr('src', '');
		}
		
		function updateTable() {
			$.post(url, {
				contentVar: "getTasks"
			}, function (data) {
				if (data == 'error') {
					alert("Tasks could not be retrieved. A system error has occurred. Please contact the system administrator.");
				} else {
					var tasks = JSON.parse(data);
					currenttable = tasks;
					parseTable(false, sortfield);
				}
			});
		}
		
		function parseTable(sort, prop) {
			$('#task-table tbody > tr').remove();
			$('#taskids > option').remove();
			
			if (sort) {
				if (prop == sortfield) {
					asc = asc * -1;
				} else {
					sortfield = prop;
				}
				
				if (currenttable.length >= 2) {
					currenttable.sort(function(a,b) {
						var afield = a[prop];
						var bfield = b[prop];
						if (prop == "id") {
							afield = Number(afield);
							bfield = Number(bfield);
						}
						if (prop == "createdtime") {
							afield = new Date(a[prop]);
							bfield = new Date(b[prop]);
						}
						if (prop != "id" && prop != "createdtime") {
							afield = afield.toLowerCase();
							bfield = bfield.toLowerCase();
						} 
						if (afield == bfield) {
							return 0;
						}
						
						if (afield < bfield) { return -1 * asc; }
						else { return 1 * asc; }
					});
				}
			}
			
			var temp = currenttable;
			for (var i = 0; i < temp.length; i++) {
				var id = temp[i][0];
				var postedtime = temp[i][1];
				var client = temp[i][2];
				var category = temp[i][3];
				var description = temp[i][4];
				var comments = temp[i][5];
				var assignedto = temp[i][6];
				var createdby = temp[i][7];
				var status = temp[i][8];
				var files = temp[i][9];
				var row = "";
				
				if (status == "Urgent") {
					row += "<tr class='danger'>";
				} else if (status == "New") {
					row += "<tr class='warning'>";
				} else if (status == "Complete") {
					row += "<tr class='success'>";
				} else if (status == "Incomplete") {
					row += "<tr class='info'>";
				} else {
					row += "<tr>";
				}
				
				// add row details
				formattedid = "<td>" + id + "</td>";
				postedtime = "<td>" + postedtime + "</td>";
				client = "<td>" + client + "</td>";
				category = "<td>" + category + "</td>";
				description = "<td><div class='max-height left-align'>" + description + "</div></td>";
				
				// format assigned to & createdby
				assignedto = "<td><div class='max-height'><strong>" + assignedto['name'] + "</strong><br>Email: " + assignedto['email'] + "<br>Phone: " + assignedto['phone'] + "<br>Agent Code: " + assignedto['agentcode'] + "<br>License: " + assignedto['license'] + "</div></td>";
				createdby = "<td><div class='max-height'><strong>" + createdby['name'] + "</strong><br>Email: " + createdby['email'] + "<br>Phone: " + createdby['phone'] + "<br>Agent Code: " + createdby['agentcode'] + "<br>License: " + createdby['license'] + "</div></td>";
				
				// format status
				var formattedstatus = "<td class='tiny-col'><select onchange='updateStatus(" + id + ",this.value)')>";
				
				if (status == "New") {
					formattedstatus += "<option value='New' selected>New</option><option value='Pending'>Pending</option><option value='Urgent'>Urgent</option><option value='Incomplete'>Incomplete</option><option value='Complete'>Complete</option></select></td>";
				} else if (status == "Pending") {
					formattedstatus += "<option value='New'>New</option><option value='Pending' selected>Pending</option><option value='Urgent'>Urgent</option><option value='Incomplete'>Incomplete</option><option value='Complete'>Complete</option></select></td>";
				} else if (status == "Complete") {
					formattedstatus += "<option value='New'>New</option><option value='Pending'>Pending</option><option value='Urgent'>Urgent</option><option value='Incomplete'>Incomplete</option><option value='Complete' selected>Complete</option></select></td>";
				} else if (status == "Incomplete") {
					formattedstatus += "<option value='New'>New</option><option value='Pending'>Pending</option><option value='Urgent' selected>Urgent</option><option value='Incomplete' selected>Incomplete</option><option value='Complete'>Complete</option></select></td>";
				} else {
					formattedstatus += "<option value='New' selected>New</option><option value='Pending'>Pending</option><option value='Urgent' selected>Urgent</option><option value='Incomplete'>Incomplete</option><option value='Complete'>Complete</option></select></td>";
				}
					
				// format comments
				var combine = "";
				var count = 0;
				if (comments.length > 0) {
					for (var j = 0; j < comments.length; j++) {
						var comment = comments[j];
						if (count%2 == 0) {
							combine += "<p class='left-align'><strong>" + comment['formatted'] + " (" + comment['role'].substring(0, 1) + ") " + comment['name'] + "</strong><br>" + comment['comments'] + "</p>";
						} else {
							combine += "<p class='right-align'><strong>" + comment['formatted'] + " (" + comment['role'].substring(0, 1) + ") " + comment['name'] + "</strong><br>" + comment['comments'] + "</p>";
						}
						count++;
					}
				} else {
					combine = "No comments made for this task.";
				}
				comments = "<td><div class='max-height'><div style='max-height:100px;overflow:auto;'>" + combine + "</div><button onclick='addComment(" + id + ")' data-toggle='modal' data-target='#comments-modal' href='#'><span style='font-size:smaller;'>Add Comment</span></button></div></td>";
				
				// format files
				combine = "";
				if (files.length > 0) {
					var col0 = "<div class='row'><div class='col-sm-6'>";
					var col1 = "<div class='col-sm-6'>";
			
					for (var j = 0; j < files.length; j++) {
						var file = files[j];
						var filepath = file[0];
						filepath = filepath.replace("\\", "");
						
						var filename = filepath.split("/");
						filename = filename[filename.length-1];
						
						var ext = filename.split(".");
						ext = ext[ext.length-1];
						
						var thumbnail;
						if (ext == "pdf") {
							thumbnail = "<div><form method='POST' action='#'><input type='image' src='https://www.skillcollector.com/wp-content/uploads/2015/06/pdf-icon-copy-min.png' alt='PDF' style='max-width:80px;height:auto;border:0' name='filepath' value='" + filepath + "'><p style='word-wrap: break-word;'>" + filename + "</p></form></div>";
						} else {
							thumbnail = "<a style='text-decoration:none; color:black;' data-toggle='modal' href='#thumbnail-modal' class='thumbnail' data-img-url='" + filepath +"' value='" + filename + "'><img style='max-width:80px;height:auto;border:0' src='" + filepath + "'><p style='word-wrap: break-word;'>" + filename + "</p></a>";
						}
						
						if (files.length > 1) {
							if (j%2 == 0) {
								col0 += thumbnail;
							} else {
								col1 += thumbnail;
							} 
						} else {
							combine = thumbnail;
						}
					}
					
					if (files.length > 1) {
						combine += col0 + "</div>" + col1 + "</div></div>";
					}
				} else {
					combine = "No files uploaded for this task.";
				}
				files += "<td><div class='max-height'>" + combine + "</div></td>";
				
				row += formattedid + postedtime + client + category + description + comments + formattedstatus + createdby + assignedto + files;
				
				<?php
				if ($_SESSION['role'] == 'Admin') {
				?>	
					row += "<td><button onclick='deleteTask(" + id + ")'>Delete</button></td>";
				<?php
				}
				?>
					
				row += "</tr>";
				$('#task-table tbody').append(row);
				$('#taskids').append("<option value='" + id + "'>" + id);
			}
			updateAlerts();
		}
		
		// COMMENT RELATED 
		function addComment(taskid) {
			$("#comment-message").html("Add Comment to task #" + taskid);
			$("#comment-message").val(taskid);
		}
		
		$(document).on('click', 'input#submit-comment', function() {
			var comments = $('#comments').val();
			var taskid = $("#comment-message").val();
			
			if (comments == "" || taskid == "") {
				$("#comments-message").html("<font color='red'><i>All fields must be filled</i></font>");
				return;
			}
			
			$.post(url, {
				contentVar: "createComment",
				comments: comments,
				taskid: taskid
			}, function (data) {
				if (data == 'success') {
					updateTable();
					clearForm();
					$("#comment-message").html("<font color='green'><i>Comment added to task # " + taskid + "</i></font>");
				} else if (data == 'Failed to create alert') {
					updateTable();
					clearForm();
					$("#comment-message").html("<font color='yellow'><i>Comment added to task # " + taskid + ". Failed to create alert. Please contact system administrator.</i></font>");
				}
				else {
					$("#comment-message").html("<font color='red'><i>Failed to add comment to task #" + taskid + ". Please contact system administrator./i></font>");
					console.log(data);
				}
			});
		});
	
		// TASK RELATED 
		<?php
			if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent') {
		?>	
				$(document).on('click', 'input#submit-task', function() {
					var client = $('#client').val();
					var description = $('#description').val();
					var category = $('#category').val();
					
					if (client == "" || description == "" || category == "") {
						$("#error-message").html("<font color='red'><i>All fields must be filled</i></font>");
						return;
					}
					
					$.post(url, {
						contentVar: "createTask",
						client: client,
						description: description,
						category: category
					}, function (data) {
						if (data == 'success') {
							updateTable();
							clearForm();
							$("#error-message").html("<font color='green'><i>Task created successfully.</i></font>");
						} else if ("none avail") {
							$("#error-message").html("<font color='red'><i>No staff is available for this task.</i></font>");						
						} else {
							$("#error-message").html("<font color='red'><i>A system error has occurred. Please contact the system administrator.</i></font>");
							console.log(data);
						}
					});
				});
				
				function deleteTask(taskid) {
					var confirm = window.confirm("Are you sure you want to delete task #" + taskid + "?");
					if (confirm == true) {
						$.post(url, {
							contentVar: "deleteTask",
							taskid: taskid
						}, function (data) {
							if (data == "success") {
								updateTable();
								alert("Task #" + taskid + " was deleted.");
							} else {
								alert("Failed to delete task #" + taskid + ". Please contact a system administrator.");
							}
						});
					} else {
						alert("Task #" + taskid + " was not deleted.");
					}
				}
		<?php
			}
		?>
		
		function updateStatus(taskid, status) {
			$.post(url, {
				contentVar: "updateStatus",
				taskid: taskid,
				status: status
			}, function (data) {
				if (data == 'success') {
					updateTable();
				} else if (data == 'no alert') {
					updateTable();
					alert("Status updated. Failed to create alert.");
				} else {
					alert("Failed to update status.");
				}
			});
		}
		
		// ALERTS RELATED
		function updateAlerts() {
			$('#alerts-dropdown > ul').remove();
			$.post(url, {
				contentVar: "getAlerts"
			}, function (data) {
				if (data == 'error') {
					alert("Failed to get alerts. Please contact a system administrator.");
				} else {
					var alerts = JSON.parse(data);
					if (alerts.length == 0) {
						$("#alerts").html("No Alerts");
					} else {
						$("#alerts").html("Alerts <font color='red'>(" + alerts.length + ") </font><b class='caret'></b>");
						var toappend = "<ul class='dropdown-menu'><button style='margin:auto;display:block;' onclick='deleteAlerts()'>Delete Selected</button>";
						for (var i in alerts) {
							var id = alerts[i][0];
							var note = alerts[i][1];
							var timeofchange = alerts[i][2];
							
							var alert = "<li><a href='#' onClick='deleteAlert(true," + id + ")'><input class='alertschecked' type='checkbox' value='" + id + "' name='alert'>" + timeofchange + "<br>" + note + "</li></a>";
							toappend += alert;	
						}
						toappend += "</ul>";
						$('#alerts-dropdown').append(toappend);
						$('.dropdown-menu input').click(function(event){
							event.stopPropagation();
						});
					}
				}
			});
		}
		
		function deleteAlerts() {
			var alerts = [];
			$('#alerts-dropdown :checked').each(function() {
				alerts.push($(this).val());
			});
			
			if (alerts.length != 0) {
				var confirm = window.confirm("Are you sure you want to delete alert(s)?");
				if (confirm) {
					if (alerts.length == 1) {
						deleteAlert(false,alerts[0]);
					}
					
					$.post(url, {
						contentVar: "deleteAlerts",
						alerts: JSON.stringify(alerts)
					}, function (data) {
						if (data == 'success') {
							updateAlerts();
						} else {
							console.log(data);
							alert("Failed to delete the alerts.");
						}
					});
				} else {
					alert("Alert was not deleted.");
				}
			} else {
				alert("No alerts selected.");
			}
		}
		
		function deleteAlert(ask, alertid) {
			if (ask) {
				var confirm = window.confirm("Are you sure you want to delete this alert?");
			}
			
			if (ask && confirm || !ask) {
				$.post(url, {
					contentVar: "deleteAlert",
					alertid: alertid
				}, function (data) {
					if (data == 'success') {
						updateAlerts();
					} else {
						alert("Failed to delete the alert.");
					}
				});
			} else {
				alert("Alert was not deleted.");
			}
		}
		
		// RESET PASSWORD
		function resetPassword() {
			var newPassword = prompt("Enter new password: ");
    
			if (newPassword != null && newPassword != "") {
				$.post(url, {
					contentVar: "resetPassword",
					newPassword: newPassword,
					username: "",
				}, function (data) {
					if (data == "success") {
						alert("Password successfully reset.\nPlease log in with your new password next time.");
					} else {
						alert("Could not reset password.");
						console.log(data);
					}
				});
			}
		}
		
		$(document).on('click', '.thumbnail', function() {
			$('#thumbnail-modal img').attr('src', $(this).attr('data-img-url'));
			$("#thumbnail-filepath").html($(this).attr('value'));
		});
	</script>
</head>

<body id="homepage" onload="set_interval()" onmousemove="reset_interval()" onclick="reset_interval()" onkeypress="reset_interval()" onscroll="reset_interval()">
	
	<!-- NAV BAR -->
	<nav class="navbar navbar-default" role="navigation" id="navbar">
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
					} else {
					?>
						<li><a href="#" onclick="resetPassword()"> Reset Password</a></li>
					<?php 
					} 
					?>
					<li class="dropdown" id="alerts-dropdown">
						<a  class='dropdown-toggle' data-toggle='dropdown' id="alerts" href="#"></a>
					</li>
					<li class="dropdown">
						<a  class='dropdown-toggle' data-toggle='dropdown' href="#">Welcome, <?php echo $_SESSION['user_name']; ?><b class='caret'></b></a></a>
						<ul class='dropdown-menu'>
							<li><a href="?logout"> Logout</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
    </nav>
	<!-- NAV BAR -->
	
	<div id="content" class="text-center">
		<?php
		if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent') {
		?>
			<!-- CREATE TASK MODAL -->
			<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog"  id="task-form">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
							<h4 class="modal-title">Create Task</h3>
						</div>
						<div class="modal-body">
							<p id="error-message" align="center"></p>
							<form role="form" name="task-form" method="post" action="#">
								<div class="form-group">
									<label>Client Name</label>
									<input type="text" id="client" name="client" class="form-control">
								</div>
								<div class="form-group">
									<label>Category Name</label>
									<select id="category" name="category" class="form-control">
										<option value="Blue Cross">Blue Cross
										<option value="Blue Shield">Blue Shield
										<option value="Covered CA">Covered CA
										<option value="Courtesy Call">Courtesy Call
										<option value="Everest">Everest
										<option value="Health Net">Health Net
										<option value="Kaiser">Kaiser
										<option value="Molina">Molina
										<option value="Nationwide">Nationwide
										<option value="Sharp">Sharp
										<option value="Transamerica">Transamerica
										<option value="Voya">Voya
										<option value="WFG">WFG
										<option value="Other">Other
									</select>	
								</div>
								<div class="form-group">
									<label>Description</label><br/>(Max of 500 characters)
									<textarea rows="15" cols="50" id="description" maxlength="500" name="description" class="form-control"></textarea>
								</div>
							</form>
							<input class="btn btn-success" type="button" value="Submit" id="submit-task">
						</div>
					</div>
				</div>
			</div>
			<!-- CREATE TASK MODAL -->
		<?php 
		}
		?>
		
		<div id="createuploadbtns">
			<?php
			if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent') {
			?>
			<!-- CREATE TASK BUTTON -->
				<a data-toggle='modal' data-target='#task-form' class="btn btn-primary btn-large">Create Task</a>
			<?php 
			}
			?>
			
			<!-- UPLOAD FILE BUTTON -->
			<a data-toggle='modal' data-target='#upload-form' class="btn btn-primary btn-large">Attach File</a>
		</div>
		
		<!-- UPLOAD FILE MODAL -->
		<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog"  id="upload-form">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
						<h4 class="modal-title">Attach File</h3>
					</div>
					<div class="modal-body">
						<p id="upload-message" align="center"></p>
						<form role="form" name="photo-form" method="post" action="fileupload.php" enctype='multipart/form-data'>
							<div class="form-group">
								<label>Task ID</label>
								<select id="taskids" name="taskids" class="form-control">
								</select>	
							</div>
							<div class="form-group">
								<input name='file_upload' id='file_upload' type='file' accept='application/pdf,image/*'/>
							</div>
							<input type='submit' id='submit-btn' value='Upload'/>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- UPLOAD FILE MODAL -->
		
		<!-- CREATE COMMENT MODAL -->
		<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog"  id="comments-modal">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
						<h4 class="modal-title">Add Comment</h3>
					</div>
					<div class="modal-body">
						<p id="comment-message" align="center" value=""></p>
						<form role="form" name="task-form" method="post" action="#">
							<div class="form-group">
								<label>Comments</label><br/>(Max of 500 characters)
								<textarea rows="15" cols="50" id="comments" maxlength="500" name="description" class="form-control"></textarea>
							</div>
						</form>
						<input class="btn btn-success" type="button" value="Submit" id="submit-comment">
					</div>
				</div>
			</div>
		</div>
		<!-- CREATE COMMENT MODAL -->
		
		<!-- FILES MODAL -->
		<div class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog" id="files-modal">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
						<h4 class="modal-title">Files</h3>
					</div>
					<div class="modal-body">
						<p id="files-message" align="center" value="">Click to enlarge</p>.
						<img id="thumbnail-img" style="max-width: 500px; height: auto; " src="" />
						<div style="min-width:250px;max-height:500px;overflow:auto" id="files">
							<!-- IMAGES WILL BE APPENDED HERE -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- FILES MODAL -->
		
		<!-- THUMBNAIL MODAL -->
		<div id='thumbnail-modal' class='modal fade' role='dialog'>
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" onClick="clearForm()">&times;</button>
						<h4 class="modal-title" id="thumbnail-filepath"></h3>
					</div>
					<div class='modal-body' id="thumbnail-div">
						<img id='modal-img' src='' alt='Enlarged image'>
					</div>
					<div class='modal-footer'>
						<button class='btn' data-dismiss='modal' aria-hidden='true'>Close</button>
					</div>
				</div>
			</div>
		</div>
		<!-- THUMBNAIL MODAL -->
		
		<!-- TASKS TABLE -->
		<div id="task-table-div">
			<table class="table table-bordered table-condensed table-responsive" id="task-table">
				<thead>
					<tr>
						<th class="small-col" onclick="parseTable(1, 'id')">Task ID<br><i class='glyphicon glyphicon-sort'></th>
						<th class="small-col" onclick="parseTable(1, 'createdtime')">Time Created<br><i class='glyphicon glyphicon-sort'></th>
						<th class="small-col" onclick="parseTable(1, 'client')">Client<br><i class='glyphicon glyphicon-sort'></th>
						<th class="small-col" onclick="parseTable(1, 'category')">Category<br><i class='glyphicon glyphicon-sort'></th>
						<th class="big-col">Task Description</th>
						<th class="big-col">Comments</th>
						<th class="small-col" onclick="parseTable(1, 'status')">Status<br><i class='glyphicon glyphicon-sort'></th>
						<th class="medium-col" onclick="parseTable(1, 'createdby')">Created By<br><i class='glyphicon glyphicon-sort'></th>
						<th class="medium-col" onclick="parseTable(1, 'assignedto')">Assigned To<br><i class='glyphicon glyphicon-sort'></th>
						<th class="big-col">Attachments</th>
						<?php
							if ($_SESSION['role'] == 'Admin') {
						?>
						<th class="small-col">Delete</th>
						<?php 
							}
						?>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div> 
		<!-- TASKS TABLE -->
	</div>
	
	<!-- Footer -->
	<div id="footer" class="container" align="center">
		<p class="text-muted">Copyright &copy; 2015 San Diego Office.</p>
	</div>
	<!-- Footer -->
	
	<script>
		function resizeTable() {
			$('#task-table-div').css('max-height',$(window).height() - $('#navbar').height() - $('#footer').height() - $('#createuploadbtns').height() - 25);
		}
		
		$( window ).resize(function() {
			resizeTable();
		});
		
		resizeTable();
		updateTable();
		updateAlerts();
	</script>
</body>