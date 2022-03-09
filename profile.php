<?php
include 'config.php';
/*
if (!$conn){
	echo "Failed to connect to database: " . mysqli_connect_error ();
}
if(isset ($_SESSION['memberID'])){
	$memberID =  $_SESSION['memberID'];
///}elseif(isset ($_GET['memberID'])){
///	$memberID =  $_GET['memberID'];
}else{
	header ("Location: register.php");
	exit();
}
*/
$pageTitle = "Profile";

$firstname = NULL;
$lastname = NULL;
$email = NULL;
$username = NULL;
$password = NULL;
$password_verify = NULL;
$firstname_error = NULL;
$lastname_error  = NULL;
$email_error = NULL;
$password_error = NULL;
$password_match_error  = NULL;
$invalid_image = NULL;
$fileinfo = NULL;
$image_name = NULL;

$valid = TRUE;
$update = FALSE;
$pagecontent = NULL;


if (isset($_GET['message'])){
	$message = "<p class='text-danger'> " . $_GET['message'] . "</p>";
}else{
	$message = NULL;
}

if (isset($_GET['action'])){
	$message = "<p class='text-danger'> Record " . $_GET['action'] . "</p>";
}else{
	$message = NULL;
}

if (isset($_GET['update'])){
	$update= TRUE;
}

if (isset($_POST['update'])) {
	$firstname = mysqli_real_escape_string($conn,ucwords(trim($_POST['firstname'])));
	if (empty($firstname)){ 
		$firstname_error = '<span class="text-danger"> - Field Required!</span>';
		$valid = FALSE;
	}
	
	$lastname = mysqli_real_escape_string($conn,ucwords(trim($_POST['lastname'])));	
	if (empty($lastname)){ 
		$lastname_error = '<span class="text-danger"> - Field Required!</span>';
		$valid = FALSE;
	}
	
	$email = (trim($_POST['email']));
	if (empty($email)){ 
		$email_error = '<span class="text-danger"> - Field Required!</span>';
		$valid = FALSE;
	}
	if (!preg_match('/[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}/', $email)) {
		$email_error = '<span class="text-danger"> - Invalid email address! email@web.com </span>';
		$valid = FALSE;
	} 
	
	if ($valid){
		$query = "UPDATE `membership` SET `firstname` = '$firstname', `lastname` = '$lastname', `email` = '$email' WHERE `memberID`= $memberID;";
		$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
		if (!$result) {
			die(mysqli_error($conn));
		}
	}
	
	$password = trim($_POST['password']);
	if (!empty ($password)){
		$password_verify = trim($_POST['password_verify']);
		if (strcmp ($password, $password_verify)){
			$password_match_error = '<span class="text-danger"> - Password and Password Verify should match!</span>';
			$valid = FALSE;
		}else{
			///encrypt
			$password = password_hash($password, PASSWORD_DEFAULT);
			$query = "UPDATE `membership` SET  `password` = '$password' WHERE `memberID`= $memberID;";
			$result = mysqli_query($conn, $query);
			if (!$result) {
				die(mysqli_error($conn));
			}else{
				$row_count = mysqli_affected_rows ($conn);
				if ($row_count ==1){
					echo "<p> Record updated </p>";
				}else{
					echo "<p> Password updated failed! </p>";
				}
			}
		}
	}

	if (!empty ($_FILES['profile_image']['name'] )) {
		unlink ("upload/" . $_POST ['image']);
		$filetype = pathinfo($_FILES['profile_image']['name'],PATHINFO_EXTENSION);
		if ((($filetype == "gif") or ($filetype == "jpg") or ($filetype == "png")) and 
		$_FILES['profile_image']['size'] < 300000) {
			if ($_FILES['profile_image']['error'] > 0) {
				$valid = FALSE;
				$file_error = $_FILES["profile_image"]["error"];
				$invalid_image = "<p class ='error'>Return Code:   $file_error <br>";
				switch ($file_error){
				case 1: $invalid_image .= "the file exceed the MAX_FILE_SIZE setting in the page </p>";
					break;
				case 2: $invalid_image .= "the file exceed the MAX_FILE_SIZE setting in the page </p>";
					break;
				default: 
					$invalid_image .= "Something is wrong </p>";
					break;
				}
			} else { 
				$image_name = $_FILES["profile_image"]["name"];
				$file = "upload/$image_name";
				$fileinfo = "<p> Upload: $image_name <br>"; 
				$fileinfo .= "Type: " . $_FILES['profile_image']['type'] . "<br>";
				$fileinfo .=  "Size: " . ($_FILES['profile_image']['size'] / 1024) . " Kb <br>";
				$fileinfo .=  "Temp file: " . $_FILES["profile_image"]["tmp_name"] .  "</p>";
				if (file_exists("$file")) {
					$invalid_image = "<span class='error' > $image_name already exists. </span> ";
					$valid = FALSE;
				} else {
					if (move_uploaded_file($_FILES['profile_image']['tmp_name'], "$file" )){
						$fileinfo .= "<p> Your file has been uploaded, as: 	$file</p>";
						///change
						$query = "UPDATE `membership` SET `image`= '$image_name' WHERE `memberID`=$memberID;";
						$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
						if (!$result) {
							die(mysqli_error($conn));
						} else {
							$row_count = mysqli_affected_rows($conn);
							if ($row_count == 1) {
								echo "<p>Record updated</p>";
							} else {
								echo  "<p>Upload image update failed</p>";
							}
						}	
					} else {
						$invalid_image .= '<span class ="error"> Your image could not be record.</span> ';
					}
				}
			}
		}else {
			$invalid_image  = '<span class = "error"> This is not image. </span> ';
			$valid = FALSE;
		}
	}
}

	$query = "SELECT * FROM `membership` WHERE `memberID`= $memberID;";
	$result = mysqli_query($conn, $query);
	if (!$result) {
		die(mysqli_error($conn));
	}
	if ($row = mysqli_fetch_assoc ($result)){
		$firstname = $row ['firstname'];
		$lastname = $row ['lastname'];
		$username = $row ['username'];
		$email = $row ['email'];
		$image_name = $row ['image'];
	}else{
		$message = "Sorry, we could not find you record.";
	}
	
if (!$update){
$pagecontent .= <<<HERE
	<section class="container">
		$message
		<h1> Welcome, $firstname  $lastname </h1>
		<figure><img src = "upload/$image_name" alt= "profile_image" class="profile_image" style="float:left;padding:15px;width:220px;height:220px;">
			<figcaption> Member:  $firstname  $lastname </figcaption>
		</figure>
		<p><a href="profile.php?update&memberID=$memberID"> Update Profile </a></p>
		<p>Email:  $email </p>
		<p>You are logged in. Your information has been saved </p>
		<p>This is your username for future login</p>
		<p> Username: <strong> $username</strong></p>
	<section>\n
HERE;

}else{	
$pagecontent .=<<<HERE
	<section class="container">
	$message
	<p>Please, update your information. </p>
	<form method="post" enctype="multipart/form-data" action="profile.php">
		<div class="form-group">
			<label for="firstname">First Name: </label>
			<input type="text" placeholder="First Name" name="firstname" id="firstname" value="$firstname" class="form-control">
			$firstname_error
		</div>
		<div class="form-group">
			<label for="lastname">Last Name: </label>
			<input type="text" placeholder="Last Name" name="lastname" id="lastname" value="$lastname" class="form-control">
			$lastname_error
		</div>
		<div class="form-group">
			<label for="email">Email: </label>
			<input type="text" placeholder="example@example.com" name="email" id="email" value="$email" class="form-control">
			$email_error 
		</div>
		<div class="form-group">
			<label for="password">Password: </label>
			<input type="password" placeholder="" name="password" id="password" value="" class="form-control">
			$password_error
		</div>
		<div class="form-group">
			<label for="password_verify">Password Verify: </label>
			<input type="password" placeholder="" name="password_verify" id="password_verify" value="" class="form-control">
			$password_match_error
		</div>
		<figure><img src="upload/$image_name" alt= "Profile image" class="profile_image" style="padding:15px;width:220px;height:220px;"/>
		<figcaption>Member:  $firstname  $lastname  </figcaption>
		</figure>
			<p style "clear: both;">Please upload an image for your profile. </p>
			<div class="form-group"> 
			<input type="hidden"  name="MAX_FILE_SIZE"  value="300000" >
			<label for="profile_image">File to Upload: </label> <span class="text-danger" style="font-size:20px;background-color:powderblue;"><br>$invalid_image </span>
			<input type="file" name="profile_image" id="profile_image" class="form-control">
			</div>
			<div class="form-group">
				<input type="hidden" class="btn btn-primary btn-lg" name="image" value="$image_name">
				<input type="hidden" class="btn btn-primary btn-lg" name="memberID" value="$memberID">
				<input type="submit" class="btn btn-warning btn-lg" name="update" value="Update Profile">
			</div>
	</form>
	<form method="post" action="delete-verify.php">
		<div class="form-group">
			<input type="hidden" class="btn btn-primary btn-lg" name="memberID" value="$memberID">
			<input type="submit" class="btn btn-danger btn-lg" name="Delete" value="Delete Profile">
		</div>
	</form>
		
	</section>\n
HERE;
}

include_once 'template.php';
##echo "<pre>";
##print_r ($_POST);
##echo "<pre>";

?>