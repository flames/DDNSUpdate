<?php
error_reporting(E_ERROR | E_WARNING);
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

# TODO:
# email address instead of username to authenticate.
# explode username from host/domain "user.domain.tld" to make it fritzbox freandly.
# need to feagure out what returns fritzbox accepts. that is why i commented all returns out. if you need them for error-hunting comment them in.

require_once("inc/config.php");
require_once("inc/db.php");
require_once("inc/templates.php");

# Write nsupdate script to a temporary file.
function file_write($filename, $data)
{
	$fh = fopen($filename, "w");
	if (!$fh)
	{
		echo "fopen() failed.";
	}
	$rc = fwrite($fh, $data);
	if ($rc === FALSE)
	{
		echo "fwrite() failed.";
	}
	if (!fclose($fh))
	{
		echo "fclose() failed.";
	}
	return $rc;
}

# get $conf['dmnid'] id from $conf['domain']
$sql = "SELECT D_id, domain FROM domains WHERE domain = '$conf[domain]'";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
	$conf['dmnid'] = $row['D_id'];
}

# Retrieve username and password.
$user = $_REQUEST['user'];
$pass = $_REQUEST['pass'];
$domain = $_REQUEST['domain'];
$domain = str_replace($user.".", "", $domain); # fritzbox needs "user.domain.tld" as domain to not produce error "update successfull, but dns resolve failed". we need "domain.tld" only.
$ip = $_REQUEST['ip'];
if (empty($user))
{
	echo "Error: Username not specified.\n";
	exit(0);
}
if (empty($pass))
{
	echo "Error: Password not specified.\n";
	exit(0);
}
# Retrieve domain and IP address.
if (empty($domain))
{
	$domain = $conf['domain'];
	#echo "Warning: Domain not specified, using default: ".$user.".".$domain.", yours?\n";
}
if (empty($ip))
{
	$ip = $_SERVER["REMOTE_ADDR"];
	#echo "Warning: IP address not specified, trying to auto recognize... ".$ip.", right?\n";
}
if ( !preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip) ) {
	$ip = $_SERVER["REMOTE_ADDR"];
    #echo "Your specified IP address is not valid, trying to auto recognize a valid one... ".$ip.", right?\n";
}

# lookup for dmnid
$domain = $db->real_escape_string($domain);
$sql = "SELECT D_id, domain FROM domains WHERE domain = '$domain'";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
	$dmnid = $row['D_id'];
}

# Lookup this user/host and validate the password.
$user = $db->real_escape_string($user);
$pass = $db->real_escape_string($pass);
$sql = "SELECT D.D_id, D.domain, A.A_id, A.user, A.password, A.dmnid, A.approved, A.email, A.lastupdate, A.ip FROM domains AS D JOIN accounts AS A ON D.D_id = '$dmnid' AND A.dmnid = '$dmnid' AND A.user = '$user'";
$result = $db->query($sql);
if ($result->num_rows == 0) {
	echo "Error: Could not authenticate this account. Wrong username.\n";
}
while ($row = $result->fetch_assoc()) {
	if ($row["password"]!=$pass) {
		echo "Error: Could not authenticate this account. Wrong password.\n";
		exit(0);
	}
	else if ($row["approved"] != 1) {
		echo "Error: Account is not activated by admin yet, please be patient.\n";
		exit(0);
	}
	else {
		$host = $user.".".$domain;
		$newip = gethostbyname($host);
		if ($ip != $newip) # comment this whole if-else in, if you want to do update-abuse-checking
		{
			# Generate a command script for nsupdate.
			$tempfile = tempnam($conf['tempdir'], "nsupdate");
			if (!$tempfile)
			{
				echo "Fatal Error: failed generating temporary file name, try again later.\n";
				exit(0);
			}
			eval("\$filecontent = \"$UPDATE_TEMPLATE\";");
			file_write($tempfile, $filecontent);
			# Run the nsupdate command.
			$rc = system("nsupdate -k {$conf[nskey]} $tempfile 2>&1", $ex);
			unlink($tempfile);
			#echo "tempfile ".$tempfile."\n"; # Tempfile path for debugging
			if ($rc === FALSE || $ex != 0)
			{
				echo "Fatal Error: nsupdate command failed, try again later.\n";
				exit(0);
			}
			# save update time and new ip to database
			$lastupdate = time();
			$ip = $db->real_escape_string($ip);
			$sql = "UPDATE accounts SET lastupdate = '$lastupdate', ip = '$ip' WHERE A_id = '$row[A_id]'";
			$update = $db->query($sql);
			#echo "Success: Your host ".$host." has been successfully assigned to IP address ".$ip.". Please note, there is a delay of a few minutes before the update takes effect.\n";
			echo "Success\n";
		}
		else
		{
			echo "Warning: Your Host is already assigned to this IP address ".$ip.", update canceled. Please do not\nabuse the server with unnecessary or too frequent updates.\n";
		}
	}
}

?>