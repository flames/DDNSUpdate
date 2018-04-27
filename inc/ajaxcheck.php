<?php
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

require_once("../inc/db.php");
#
# Check if user already exists with this domain
#
$checkuser = trim(htmlspecialchars($_GET['checkuser'], ENT_QUOTES, "UTF-8"));
$checkdomain = trim(htmlspecialchars($_GET['checkdomain'], ENT_QUOTES, "UTF-8"));
if (!empty($checkuser) AND !empty($checkdomain)) {
	if (strlen($checkuser) < 4) {
		echo '<img src="images/sign_error.png" id="user"> <b>This username is too short.</b>';
	}
	else {
		$checkuser = $db->real_escape_string($checkuser);
		$checkdomain = $db->real_escape_string($checkdomain);
		$sql = "SELECT user, dmnid FROM accounts WHERE user = '$checkuser' AND dmnid = '$checkdomain'";
		$result = $db->query($sql);
		if ( $result->num_rows ) {
			echo '<img src="images/sign_error.png" id="user"> <b>This username is already in use on this domain.</b>';
		}
		else {
			echo '<img src="images/sign_tick.png" id="user"> <b>Feel free to register this username.</b>';
		}
		$result->close();
	}
}
?>