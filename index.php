<?php
error_reporting(E_ERROR | E_WARNING);
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

# TODO: improve translation, not all strings are included from language files, better to say most of them are hardcoded
session_start();

require_once("inc/config.php");
require_once("inc/db.php");
require_once("inc/templates.php");

$hostname= $_SERVER['HTTP_HOST'];
if (!$hostname) $hostname = $conf['hostname'];
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); # get browsers language
if(in_array($lang,$conf['lang'])) { # if translation for browsers language is there, use it
	include("l10n/".$lang.".lang.php");
}
else # else use english
{
	include("l10n/en.lang.php");
}

# Admin login / logout management, adminform generation
if (isset($_POST['adminlogin']) AND $_POST['adminuser'] == $conf['adminuser'] AND $_POST['adminpass'] == $conf['adminpass']) {
	$_SESSION['adminloggedin'] = 'muy bien';
}
if (isset($_POST['adminlogout'])) {
	$_SESSION['adminloggedin'] = '';
}
if ($_SESSION['adminloggedin'] == 'muy bien') {
	$adminform = '<form method="post" action="index.php" name="adminform">
	<div class="input-group input-group-sm adminlogin">
	    <input type="submit" value="logout" name="adminlogout" class="btn btn-primary" />
	</div>
	</form>';
	$adminbutton = '<a href="index.php?site=admin" class="nav-link">Administration</a>';
}
else {
	$adminform = '<form method="post" action="index.php?site=admin" name="adminform">
	<div class="input-group input-group-sm adminlogin">
	    <input type="text" name="adminuser" class="form-control" />
	    <input type="password" name="adminpass" class="form-control" />
	    <input type="submit" value="login" name="adminlogin" class="btn btn-primary input-group-addon right" />
	</div>
	</form>';
	$adminbutton = '';
}

# site beginns
echo '<?xml version="1.0" encoding="UTF-8"?>';

include("html/header.html");

$site = $_GET['site'];
if (!isset($site))
{
	$site = $conf['site'];
}
if (file_exists($site.".html"))
{
	include($site.".html");
}
else if (file_exists($site.".php"))
{
	include($site.".php");
}
else
{
	echo '<div>Error 404<br/>Site "'.$site.'" does not exist or is not available yet.</div>';
}

include("html/footer.html");

?>