<?php
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

# Get available Domains for DDNS, create form element and save new user.
$sql = "SELECT D_id, domain FROM domains";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
	$domainselect .= '<option value="'.$row['D_id'].'">'.$row['domain'].'</option>'."\n";
}
include("html/formregister.html");

# create new user
$user = trim(htmlspecialchars($_POST['user'], ENT_QUOTES, "UTF-8"));
$email = trim(htmlspecialchars($_POST['email'], ENT_QUOTES, "UTF-8"));
$pass = trim(htmlspecialchars($_POST['pass'], ENT_QUOTES, "UTF-8"));
$passretype = trim(htmlspecialchars($_POST['passretype'], ENT_QUOTES, "UTF-8"));
$dmnid = trim(htmlspecialchars($_POST['dmnid'], ENT_QUOTES, "UTF-8"));
if (isset($_POST['register']))
{
	if (empty($user)) {
		echo '<b>Bam! Specify username.</b><br>';
	}
	else if (strlen($user) < 4) {
		echo '<b>Bam! This username is too short.</b><br>';
	}
	else if (empty($email)) {
		echo '<b>Bam! Specify email.</b><br>';
	}
	else if (!eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $email)) {
		echo '<b>Bam! eMail is not valid.</b><br>';
	}
	else if (empty($pass)) {
		echo '<b>Bam! Specify password.</b><br>';
	}
	else if (strlen($pass) < 6) {
		echo '<b>Bam! Specify password.</b><br>';
	}
	else if ($pass != $passretype) {
		echo '<b>Bam! Password and retype do not match.</b><br>';
	}
	else {
		$user = $db->real_escape_string($user);
		$email = $db->real_escape_string($email);
		$pass = $db->real_escape_string($pass);
		$dmnid = $db->real_escape_string($dmnid);
		$sql = "SELECT user, dmnid FROM accounts WHERE user = '$user' AND dmnid = '$dmnid'";
		$result = $db->query($sql);
		if ( $result->num_rows ) {
			echo '<b>Bam! This username is already in use on this domain.</b><br>';
		}
		else {
			echo '<b>Bam! congrats, register this username.</b><br>';
			$timestamp = time();
			$sql = "INSERT INTO accounts(A_id,user,password,dmnid,approved,email,registered,lastupdate,ip) VALUES ('','$user','$pass','$dmnid','0','$email','$timestamp','$timestamp','')";
			$insert = $db->query($sql);
		}
	}
}

?>
