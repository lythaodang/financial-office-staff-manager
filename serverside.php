<?php
	if (!isset($_SESSION)) {
		session_start();
	}
	
	class Database {
		// The database connection
		protected static $connection;

		/**
		 * Connect to the database
		 * @return bool false on failure / mysqli MySQLi object instance on success
		 */
		public function connect() {    
			// Try and connect to the database
			if(!isset(self::$connection)) {
				// Load configuration as an array. Use the actual location of your configuration file
				$config = parse_ini_file('./config.ini'); 
				self::$connection = new mysqli($config['servername'],$config['username'],$config['password'],$config['dbname']); 
			}

			// If connection was not successful, handle the error
			if(self::$connection === false) {
				// Handle error - notify administrator, log to a file, show an error screen, etc.
				return false;
			}
			return self::$connection;
		}

		/**
		 * Query the database
		 * @param $query The query string
		 * @return mixed The result of the mysqli::query() function
		 */
		public function query($query) {
			// Connect to the database
			$connection = $this -> connect();

			// Query the database
			$result = $connection -> query($query);

			return $result;
		}

		/**
		 * Fetch rows from the database (SELECT query)
		 * @param $query The query string
		 * @return bool False on failure / array Database rows on success
		 */
		public function select($query) {
			$rows = array();
			$result = $this -> query($query);
			if($result === false) {
				return false;
			}
			while ($row = $result -> fetch_assoc()) {
				$rows[] = $row;
			}
			return $rows;
		}

		/**
		 * Fetch the last error from the database
		 * @return string Database error message
		 */
		public function error() {
			$connection = $this -> connect();
			return $connection -> error;
		}

		/**
		 * Quote and escape value for use in a database query
		 * @param string $value The value to be quoted and escaped
		 * @return string The quoted and escaped string
		 */
		public function quote($value) {
			$connection = $this -> connect();
			return "'" . $connection -> real_escape_string($value) . "'";
		}
	}
	
	class PassHash {
		// blowfish
		private static $algo = '$2a';
		
		// cost parameter
		private static $cost = '$10';
	 
		// mainly for internal use
		public static function unique_salt() {
			return substr(sha1(mt_rand()),0,22);
		}
	 
		// this will be used to generate a hash
		public static function hash($password) {
			return crypt($password,
						self::$algo .
						self::$cost .
						'$' . self::unique_salt());
		}
	 
		// this will be used to compare a password against a hash
		public static function check_password($hash, $password) {
			$full_salt = substr($hash, 0, 29);
			$new_hash = crypt($password, $full_salt);
			return ($hash == $new_hash);
		}
	}
	
	/* ------------------------------------------------------------------
	                        TASK RELATED
	------------------------------------------------------------------ */
	function createTask() {
		$db = new Database();
		
		// get today's date/time
		$today = new DateTime("now", new DateTimeZone("America/Los_Angeles"));
		$cur_hour = $today->format("H");
		$curdayofweek = $today->format("l");
	
		// construct day variable to query from DB
		$col_end = substr($curdayofweek, 0, 3) . "E";
				
		// query next avail staff today
		$result = $db -> query("SELECT userid FROM schedule WHERE $col_end > ($cur_hour + 2) ORDER BY num_assigns LIMIT 1");
		$row = mysqli_fetch_array($result);
		
		for ($x = 0; $x < 7 && !count($row); $x++) {
			$today->modify('+1 day'); // get next day
			$tempdayofweek = $today->format("l"); // get next day of week
			
			// construct day variable to query from DB
			$col_start = substr($curdayofweek, 0, 3) . "S";
			
			// query next avail staff today
			$result = $db -> query("SELECT userid FROM schedule ORDER BY $col_start, num_assigns LIMIT 1");
			$row = mysqli_fetch_array($result);
		}			
		
		// insert task if staff found
		if (count($row)) {
			$client = $db -> quote($_POST['client']);
			$description = $db -> quote($_POST['description']);
			$category = $db -> quote($_POST['category']);
			$userid = $_SESSION['user'];
			$staff = $row['userid'];
			
			$result2 = $db -> query("INSERT INTO assignments (client, category, description, assignedto, createdby) VALUES ($client, $category, $description, '$staff', $userid)");
			
			if ($result2) {
				echo "success";
			} else {
				echo "Error in task creation: " . $db -> error();
			}
		} else {
			echo "none avail";
		}
	}
	
	function deleteTask() {
		$db = new Database();
		
		$taskid = $_POST['taskid'];
		$result = $db -> query("UPDATE assignments SET deleted = 1 WHERE id = $taskid");
		
		if ($result) {
			echo "success";
		} else {
			echo "Delete failed for task #$taskid: " . $db -> error();
		}
	}
	
	function getTasks() {
		$db = new Database();
		
		// query all if administrator, else query only theirs
		if ($_SESSION['role'] != "Admin") {
			$userid = $_SESSION['user'];
			$tasks = $db -> query("SELECT id,DATE_FORMAT(postedtime,'%W<br>%m/%d/%y<br>%h:%i %p') as postedtime,client,category,description,'' as comments,assignedto,createdby,status,'' as files,postedtime as createdtime FROM assignments where deleted = false AND (assignedto = $userid OR createdby = $userid) ORDER BY id DESC");
		} else {
			$tasks = $db -> query("SELECT id,DATE_FORMAT(postedtime,'%W<br>%m/%d/%y<br>%h:%i %p') as postedtime,client,category,description,'' as comments,assignedto,createdby,status,postedtime as createdtime FROM assignments where deleted = false ORDER BY id DESC");
		}
		
		if ($tasks) {
			$jsonData = array();
			
			// iterate task & get comments + files
			while ($task = mysqli_fetch_array($tasks)) {
				$taskid = $task['id'];
				
				// query createdby/assignedto
				$userdetails = $db -> query("SELECT name, email, phone, agentcode, license FROM member WHERE userid = '" . $task['assignedto'] . "' OR userid = '" . $task['createdby'] . "' ORDER BY role DESC"); 
				if ($userdetails) {
					$task[6] = mysqli_fetch_array($userdetails);
					$task[7] = mysqli_fetch_array($userdetails);
				} else {
					echo "error";
					return;
				}
				
				// query comments
				$comments = $db -> query("SELECT name, role, comments, DATE_FORMAT(postedtime,'%m/%d/%y %h:%i %p') AS formatted, postedtime FROM comments JOIN member ON comments.userid = member.userid WHERE comments.taskid = $taskid ORDER BY comments.postedtime DESC");
				if ($comments) {
					$combinecomments = array();
					while ($comment = mysqli_fetch_array($comments)) {
						$combinecomments[] = $comment;
					}
					$task[5] = $combinecomments;
				} else {
					echo "error";
					return;
				}
				
				// query files
				$files = $db -> query("SELECT imgpath FROM files WHERE taskid=$taskid ORDER BY postedtime DESC");
				if ($files) {
					$combinefiles = array();
					while ($file = mysqli_fetch_row($files)) {
						$combinefiles[] = $file;
					}
					$task[9] = $combinefiles;
				} else {
					echo "error";
					return;
				}
				
				$jsonData[] = $task; // add task to array for encoding
			}
			echo json_encode($jsonData); // encode data
		} else {
			echo "error";
		}
	}
	
	function createComment() {
		$db = new Database();
		
		$userid = $_SESSION['user'];
		$taskid = $_POST['taskid'];
		$comments = $db -> quote($_POST['comments']);
		
		$result = $db -> query("INSERT INTO comments (taskid, userid, comments) VALUES($taskid, $userid, $comments)");
		
		if ($result) {
			if (createAlert($taskid, "New comment for task #$taskid.")) {
				echo "success";
			} else {
				echo "Failed to create alert.";
			}
		} else {
			echo "Failed to create comment.";
		}
	}
	
	/* ------------------------------------------------------------------
	                        ALERTS RELATED
	------------------------------------------------------------------ */
	function createAlert($taskid, $message) {
		$db = new Database();
		$role = $_SESSION['role'];
		
		if ($role == "Admin" || $role == "Agent") {
			$result = $db -> query("SELECT assignedto FROM assignments WHERE id = $taskid");
		} else {
			$result = $db -> query("SELECT createdby FROM assignments WHERE id = $taskid");
		}
		
		if ($result) {
			$row = mysqli_fetch_row($result);
			$notefor = $row[0];
			$result2 = $db -> query("INSERT INTO alerts(taskid,notefor,note) VALUES($taskid,'$notefor','$message')");
			
			if ($result2) {
				return true;
			}
		}
		
		return false;
	}
	
	function getAlerts() {
		$db = new Database();
		$userid = $_SESSION['user'];
		$result = $db -> query("SELECT id,note,DATE_FORMAT(timeofchange,'%W %m/%d/%y %h:%i %p') FROM alerts WHERE notefor=$userid");
		
		if ($result) {
			$jsonData = array();
		
			while ($array = mysqli_fetch_row($result)) {
				$jsonData[] = $array;
			}
			echo json_encode($jsonData);
		} else {
			echo "error";
		}
	}
	
	function deleteAlert() {
		$db = new Database();
		
		$alertid = $_POST['alertid'];
		
		$result = $db -> query("DELETE FROM alerts WHERE id = $alertid");
		
		if ($result) {
			echo "success";
		} else {
			echo "error";
		}
	}
	
	function deleteAlerts() {
		$db = new Database();
		
		$alerts=json_decode($_POST['alerts']);
		$formatted = "(";
		$arrlength = count($alerts);
		
		for ($x = 0; $x < $arrlength; $x++) {
			$formatted .= $alerts[$x];
			if ($x != $arrlength - 1) {
				$formatted .= ",";
			}
		}
		$formatted .= ")";
		
		$result = $db -> query("DELETE FROM alerts WHERE id in $formatted");
		
		if ($result) {
			echo "success";
		} else {
			echo "error" . $db->error();
		}
	}
	
	/* ------------------------------------------------------------------
	                        PHOTOS RELATED
	------------------------------------------------------------------ */
	function insertFile($imagepath, $taskid) {
		$db = new Database();
		$userid = $_SESSION['user'];
		$imgpath = $db -> quote($imagepath);
		
		$result = $db -> query("INSERT INTO files (taskid, imgpath, userid) VALUES($taskid,$imgpath,$userid)");
		
		if ($result) {
			if (createAlert($taskid, "New file uploaded for task #$taskid.")) {
				echo "<script>alert('Successfully saved image.'); window.location.replace('./tasks.php');</script>";
			} else {
				echo "<script>alert('Successfully saved image. Failed to create alert.'); window.location.replace('./tasks.php');</script>";
			}
		} else {
			echo "<script>alert('Failed to save image to database. Please contact a system administrator'); window.location.replace('./tasks.php');</script>";
		}
	}
	
	/* ------------------------------------------------------------------
	                        USERS RELATED
	------------------------------------------------------------------ */
	function getUsers() {
		$db = new Database();
		
		$result = $db -> query("SELECT userid,name,role,phone,smdname,agentcode,license,email FROM member where deleted = false");
		
		if ($result) {
			$jsonData = array();
		
			while ($array = mysqli_fetch_row($result)) {
				$jsonData[] = $array;
			}
			echo json_encode($jsonData);
		} else {
			echo "error";
		}
	}
	
	function resetPassword() {
		$db = new Database();
		
		if ($_SESSION['role'] == "Admin") {
			$username = $db -> quote($_POST['username']);
		} else {
			$username = $_SESSION['user'];
		}
	
		$password = PassHash::hash($_POST['newPassword']);
		
		$result = $db -> query("UPDATE member SET password = '" . $password . "' WHERE userid = $username");
		
		if ($result) {
			echo "success";
		} else {
			echo "Can't reset password for $username: " . $db->error();
			echo "\nUPDATE member SET password = '" . $password . "' WHERE userid = $username";
		}
	}
	
	function deleteUser() {
		$db = new Database();
		
		$username = $db -> quote($_POST['username']);
		$result = $db -> query("UPDATE member SET deleted = 1 WHERE userid = $username");
		
		if ($result) {
			echo "success";
		} else {
			echo "Delete failed for $username: " . $db -> error();
		}
	}
	
	function createUser() {
		$db = new Database();
		
		$userid = $db -> quote($_POST['userid']);
		$password = PassHash::hash($_POST['password']);
		$name = $db -> quote($_POST['name']);
		$email = $db -> quote($_POST['email']);
		$smdname = $db -> quote($_POST['smdname']);
		$agentcode = $db -> quote($_POST['agentcode']);
		$license = $db -> quote($_POST['license']);
		$role = $db -> quote($_POST['role']);
		$phone = $db -> quote($_POST['phone']);
		
		$result = $db -> query("SELECT * FROM member WHERE userid=$userid");
		$data = mysqli_fetch_array($result);;
		
		if (count($data) == 0) {
			$result2 = $db -> query("INSERT INTO member(userid,password,name,email,smdname,agentcode,license,role,phone) VALUES($userid,'$password',$name,$email,$smdname,$agentcode,$license,$role,$phone)");
			if ($result) {
				echo "success";
			} 
			else {
				echo "Error in user registration: " . $db -> error();
			}
		} else {
			echo "already registered";
		}
	}

	function login() {
		$db = new Database();
		
		$username = $db -> quote($_POST['username']);
		$password = $_POST['password'];
		
		$result = $db -> query("SELECT id, name, password, role FROM member WHERE userid=$username AND deleted = false");
		
		if ($result) {
			$row = mysqli_fetch_array($result);
			if (count($row)) {
				if (PassHash::check_password($row['password'], $password)) {
					if ($row['role'] == "Staff") {
						checkSchedule($username);
						if (isset($_SESSION["scheduled"])) {
							if ($_SESSION["scheduled"] == false) {
								echo "You are not scheduled";
								return;
							}
						} else {
							echo "No schedule found";
							return;
						}
					} 
					
					$_SESSION['timeout'] = time();
					$_SESSION['user'] = $username;
					$_SESSION['user_name'] = $row['name'];
					$_SESSION['id'] = $row['id'];
					$_SESSION['role'] = $row['role'];
					
					echo "success";
				} else {
					echo "Password does not match";
				}
			} else {
				echo "Username does not exist";
			}
		} else {
			echo "Error logging in: " . $db->error();			
		}
		
	}
	
	function updateStatus() {
		$db = new Database();
		
		$status = $_POST['status'];
		$taskid = $_POST['taskid'];
		
		$result = $db -> query("UPDATE assignments SET status = '$status' WHERE id = $taskid");
		
		if ($result) {
			if (createAlert($taskid, "New status update for task #$taskid.")) {	
				echo "success";
			} else {
				echo "no alert";
			}
		} else {
			echo "Error updating status: " . $db->error();			
		}
	}
	
	/* ------------------------------------------------------------------
	                        SCHEDULES RELATED
	------------------------------------------------------------------ */
	function checkSchedule($username) {
		$db = new Database();
		
		$today = new DateTime("now", new DateTimeZone("America/Los_Angeles"));
		$cur_hour = $today->format("H");
		$curdayofweek = $today->format("l");
	
		$col_start = substr($curdayofweek, 0, 3) . "S";
		$col_end = substr($curdayofweek, 0, 3) . "E";
					
		$result = $db -> query("SELECT $col_start, $col_end FROM schedule WHERE userid=$username AND deleted = false");
		$row = mysqli_fetch_array($result);
		
		$emp_start = $row["$col_start"];
		$emp_end = $row["$col_end"];
				
		if ($cur_hour >= $emp_start && $cur_hour < $emp_end) {
			$_SESSION['scheduled'] = true;
		} else {
			$_SESSION['scheduled'] = false;
		}
	}
		
	function getSchedules() {
		$db = new Database();
		
		$result = $db -> query("SELECT * FROM schedule WHERE deleted = false");
		
		if ($result) {
			$jsonData = array();
		
			while ($array = mysqli_fetch_row($result)) {
				$jsonData[] = $array;
			}
			echo json_encode($jsonData);
		} else {
			echo "error";
		}
	}
	
	function updateSchedule() {
		$db = new Database();
		$userid = $db -> quote($_POST['userid']);
		
		if (($sunS = $_POST['sunS']) == -1) {$sunS = 'NULL';}
		if (($sunE = $_POST['sunE']) == -1) {$sunE = 'NULL';}
		if (($monS = $_POST['monS']) == -1) {$monS = 'NULL';}
		if (($monE = $_POST['monE']) == -1) {$monE = 'NULL';}
		if (($tueS = $_POST['tueS']) == -1) {$tueS = 'NULL';}
		if (($tueE = $_POST['tueE']) == -1) {$tueE = 'NULL';}
		if (($wedS = $_POST['wedS']) == -1) {$wedS = 'NULL';}
		if (($wedE = $_POST['wedE']) == -1) {$wedE = 'NULL';}
		if (($thuS = $_POST['thuS']) == -1) {$thuS = 'NULL';}
		if (($thuE = $_POST['thuE']) == -1) {$thuE = 'NULL';}
		if (($friS = $_POST['friS']) == -1) {$friS = 'NULL';}
		if (($friE = $_POST['friE']) == -1) {$friE = 'NULL';}
		if (($satS = $_POST['satS']) == -1) {$satS = 'NULL';}
		if (($satE = $_POST['satE']) == -1) {$satE = 'NULL';}
		
		$num_assigns = $_POST['num_assigns'];
		
		$result = $db -> query("UPDATE schedule SET sunS = $sunS, sunE = $sunE, monS = $monS, monE = $monE, tueS = $tueS, tueE = $tueE, wedS = $wedS, wedE = $wedE, thuS = $thuS, thuE = $thuE, friS = $friS, friE = $friE, satS = $satS, satE = $satE, num_assigns = $num_assigns WHERE userid = $userid");
		
		if ($result) {
			echo "success";
		} else {
			echo "Error in schedule update: " . $db -> error();
		}
	}
	
	
	
	/* ------------------------------------------------------------------
	                        FUNCTION TO EXECUTE
	------------------------------------------------------------------ */
	if (isset($_POST['contentVar'])) { 
		switch($_POST['contentVar']) {
			case 'login':
				login();
				break;
			case 'createTask':
				createTask();
				break;
			case 'getUsers':
				getUsers();
				break;
			case 'resetPassword':
				resetPassword();
				break;
			case 'deleteUser':
				deleteUser();
				break;
			case 'createUser':
				createUser();
				break;
			case 'getSchedules':
				getSchedules();
				break;
			case 'updateSchedule':
				updateSchedule();
				break;
			case 'getTasks':
				getTasks();
				break;
			case 'createComment':
				createComment();
				break;
			case 'updateStatus':
				updateStatus();
				break;
			case 'deleteTask':
				deleteTask();
				break;
			case 'getAlerts':
				getAlerts();
				break;
			case 'deleteAlert':
				deleteAlert();
				break;
			case 'deleteAlerts':
				deleteAlerts();
				break;
			default:
				echo "NaN";
				break;
		}
	}
?>