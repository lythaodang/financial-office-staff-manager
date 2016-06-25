var url = "./serverside.php";

$(function () {
	$('#login-btn').click(function (e) {
		e.preventDefault();
		
		var username = $("#username").val();
		var password = $("#password").val();
		username = username.toLowerCase();
		
		if (username == "" || password == "") {
			$("#error-message").html("<font color='red'><i>All fields must be filled</i></font>");
			return false;
		}
	
		$.post(url, {
		  contentVar: "login",
		  username: username,
		  password: password
		}, function (data) {
			if (data == "success") {
				window.location.href = "./tasks.php";
			} else if (data == "Password does not match" || data == "Username does not exist"
				|| data == "No schedule found" || data == "You are not scheduled") {
				$("#error-message").html('<font color="red"><i>' + data + '</i></font>');
			} else {
				$("#error-message").html('<font color="red"><i>System Error: Please contact system admin.</i></font>');
				console.log(data);
			}
		});
		
		return false;
	}); 
});