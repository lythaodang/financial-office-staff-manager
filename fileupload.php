<?php
include_once "./serverside.php";
$destination_folder	= './fileuploads/'; //upload directory ends with / (slash)

if (isset($_POST) && isset($_FILES['file_upload'])) {
	if (!is_uploaded_file($_FILES['file_upload']['tmp_name']) || !isset($_POST['taskids'])) {
		echo "<script>alert('No file or task id chosen'); window.location.replace('./tasks.php');</script>";
		exit;
	} 
	
	// GET FILE INFO
	$file_name = $_FILES['file_upload']['name'];
	$file_size = $_FILES['file_upload']['size'];
	$file_tmp = $_FILES['file_upload']['tmp_name'];
	$file_type = $_FILES['file_upload']['type'];
	$file_info = pathinfo($file_name);
	$file_ext = strtolower($file_info["extension"]);

	// CHECK EXTENSIONS
	$extensions= array("jpeg","jpg","png","pdf","gif");
	if(in_array($file_ext,$extensions) === false) {
		echo "<script>alert('File extension not allowed. Must be jpeg, jpg, png, gif, or pdf.'); window.location.replace('./tasks.php');</script>";
	}
	
	if (!file_exists($destination_folder)) {
		mkdir($destination_folder, 0777);
	}
	$row = $_POST['taskids']; // get task id
	// create the task id's file if doesn't exist
	if (!file_exists($destination_folder . $row)) {
		mkdir($destination_folder . $row . '', 0777);
	}	
	
	$file_name_only = strtolower($file_info["filename"]); //file name only, no extension
	$file_path = $destination_folder .	$row . '/' . $file_name;
	
	$count = 1;
	while (file_exists($file_path)) {
		$file_path = $destination_folder .	$row . '/' . $file_name_only. '_' .  $count . '.' . $file_ext;
		$count++;
	}
	
	// REDUCE IMAGE SIZE IF IMAGE
	if ($file_ext != "pdf") {
		$image_size_info = getimagesize($file_tmp); //get image size
	
		if ($image_size_info) {
			$image_width 		= $image_size_info[0]; //image width
			$image_height 		= $image_size_info[1]; //image height
			$image_type 		= $image_size_info['mime']; //image type
			
			if ($image_width <= 0 || $image_height <= 0) { 
				echo "<script>alert('Invalid image file.'); window.location.replace('./tasks.php');</script>";
			} 
		} else {
			echo "<script>alert('Invalid image file.'); window.location.replace('./tasks.php');</script>";
		}
	
		// switch statement below checks allowed image type 
		// as well as creates new image from given file 
		switch($image_type) {
			case 'image/png':
				$image_res =  imagecreatefrompng($file_tmp); break;
			case 'image/gif':
				$image_res =  imagecreatefromgif($file_tmp); break;			
			case 'image/jpeg': case 'image/pjpeg':
				$image_res = imagecreatefromjpeg($file_tmp); break;
			default:
				$image_res = false;
		}

		if ($image_res) {
			//call normal_resize_image() function to proportionally resize image
			if(normal_resize_image($image_res, $file_path, $image_type, $image_width, $image_height)) {
				insertFile($file_path, $row);
			} else {
				echo "<script>alert('Failed to save image.'); window.location.replace('./tasks.php');</script>";
			}
			
			imagedestroy($image_res); //freeup memory
		}
	} else {
		move_uploaded_file($file_tmp, $file_path);
		insertFile($file_path, $row);
	}
} 

// PROPORTIONALLY RESIZE IMAGE 
function normal_resize_image($source, $destination, $image_type, $image_width, $image_height) {
	$max_image_size = 1000; //Maximum image size (height and width)
	
	//do not resize if image is smaller than max size
	if($image_width <= $max_image_size && $image_height <= $max_image_size){
		if(save_image($source, $destination, $image_type)){
			return true;
		}
	}
	
	//Construct a proportional size of new image
	$image_scale = min($max_image_size/$image_width, $max_image_size/$image_height);
	$new_width = ceil($image_scale * $image_width);
	$new_height = ceil($image_scale * $image_height);
	
	$new_canvas	= imagecreatetruecolor($new_width, $new_height); //Create a new true color image
	
	//Copy and resize part of an image with resampling
	if(imagecopyresampled($new_canvas, $source, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height)){
		if(save_image($new_canvas, $destination, $image_type)) { //save resized image
			return true;
		}
	}

	return false;
}

// SAVE FILE TO DESTINATION
function save_image($source, $destination, $image_type){
	$jpeg_quality = 100; //jpeg quality
	
	switch(strtolower($image_type)) { //determine mime type
		case 'image/png': 
			imagepng($source, $destination); return true; //save png file
			break;
		case 'image/gif': 
			imagegif($source, $destination); return true; //save gif file
			break;          
		case 'image/jpeg': case 'image/pjpeg': 
			imagejpeg($source, $destination, $jpeg_quality); return true; //save jpeg file
			break;
		default: return false;
	}
}
?>