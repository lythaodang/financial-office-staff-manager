//user login sessions
var timer = 0;
var timeout = 900000; // 15 mins in milliseconds
function set_interval() {
	 // the interval 'timer' is set as soon as the page loads
	 timer = setInterval("auto_logout()", timeout); // in milliseconds
}

function reset_interval() {
	//resets the timer. The timer is reset on each of the below events:
	// 1. mousemove   2. mouseclick   3. key press 4. scroliing
	//first step: clear the existing timer
	if (timer != 0) {
		clearInterval(timer);
		timer = 0;
		
		// reset timer
		timer = setInterval("auto_logout()", timeout);
	}
}

function auto_logout() {
	// this function will redirect the user to the logout script
	window.location = "tasks.php?opt=Logout";
}