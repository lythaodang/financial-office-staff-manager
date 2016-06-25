<?php
if (isset($_POST) && isset($_POST['filepath'])) {
	$filepath = $_POST['filepath'];
	if (file_exists($_POST['filepath'])) {
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