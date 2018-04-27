<?php
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

# Write nsupdate script to a temporary file.
function file_write($filename, $data)
{
	$fh = fopen($filename, "w");
	if (!$fh)
	{
		$log .= $LANG['fatalerror']."fopen() failed.";
		$error = 1;
	}
	$rc = fwrite($fh, $data);
	if ($rc === FALSE)
	{
		$log .= $LANG['fatalerror']."fwrite() failed.";
		$error = 1;
	}
	if (!fclose($fh))
	{
		$log .= $LANG['fatalerror']."fclose() failed.";
		$error = 1;
	}
	return $rc;
}

# Get available Domains for DDNS and create form element.
$sql = "SELECT D_id, domain FROM domains";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
	$domainselect .= '<option value="'.$row['D_id'].'">'.$row['domain'].'</option>'."\n";
}
include("html/formupdate.html");

# get $conf['dmnid'] id from $conf['domain']
$sql = "SELECT D_id, domain FROM domains WHERE domain = '$conf[domain]'";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
	$conf['dmnid'] = $row['D_id'];
}

# If form submitted.
if (isset($_POST['update']))
{
	# Retrieve username and password.
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$dmnid = $_POST['dmnid'];
	$ip = $_POST['ip'];
	if (empty($user))
	{
		$log .= $LANG['error'].$LANG['specifyuser']."\n"; # Username not specified
		$error = 1;
	}
	if (empty($pass))
	{
		$log .= $LANG['error'].$LANG['specifypass']."\n"; # Password not specified
		$error = 1;
	}
	# Retrieve domain and IP address.
	if (empty($dmnid))
	{
		$dmnid = $conf['dmnid'];
		$log .= $LANG['warning'].$LANG['defaultdomain']['1'].$user.$conf['domain'].$LANG['defaultdomain']['2']."\n"; # Domain not specified, using default
	}
	if (empty($ip))
	{
		$ip = $_SERVER["REMOTE_ADDR"];
		$log .= $LANG['warning'].$LANG['autoip']['1'].$ip.$LANG['autoip']['2']."\n"; # Address not specified, trying to auto recognize
	}
	if ( !preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip) ) {
		$ip = $_SERVER["REMOTE_ADDR"];
	    $log .= $LANG['warning'].$LANG['ipnotvalid']['1'].$ip.$LANG['ipnotvalid']['2']."\n"; # Address not specified, trying to auto recognize
	}
	# Lookup this user and validate the password.
	$user = $db->real_escape_string($user);
	$pass = $db->real_escape_string($pass);
	$dmnid = $db->real_escape_string($dmnid);
	$sql = "SELECT D.D_id, D.domain, A.A_id, A.user, A.password, A.dmnid, A.approved, A.email, A.lastupdate, A.ip FROM domains AS D JOIN accounts AS A ON D.D_id = '$dmnid' AND A.dmnid = '$dmnid' AND A.user = '$user'";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		if ($row["password"] != $pass) {
			$log .= $LANG['error'].$LANG['noaccount']."\n"; # Could not authenticate this account
			$error = 1;
		}
		else if ($row["approved"] != 1) {
			$log .= $LANG['error'].$LANG['accountnotactiveyet']."\n"; # Account not activated by admin yet
			$error = 1;
		}
		else {
			$host = $user.".".$row['domain'];
			$newip = gethostbyname($host);
			if ($ip != $newip)
			{
				# Generate a command script for nsupdate.
				$tempfile = tempnam($conf['tempdir'], "nsupdate");
				if (!$tempfile)
				{
					$log .= $LANG['fatalerror'].$LANG['notempfile']."\n"; # Failed generating temporary file name
					$error = 1;
				}
				eval("\$filecontent = \"$UPDATE_TEMPLATE\";");
				file_write($tempfile, $filecontent);
				# Run the nsupdate command.
				$rc = system("nsupdate -k {$conf[nskey]} $tempfile 2>&1", $ex);
				unlink($tempfile);
				#$log .= "tempfile ".$tempfile."\n"; # Tempfile path for debugging
				if ($rc === FALSE || $ex != 0)
				{
					$log .= $LANG['fatalerror'].$LANG['nsupdateerror']."\n"; # nsupdate command failed
					#$log .= $LANG['fatalerror'].$rc."\n"; # Return of system() $rc $ex for debugging purposes
					$error = 1;
				}
				# save update time and new ip to database
				$lastupdate = time();
				$ip = $db->real_escape_string($ip);
				$sql = "UPDATE accounts SET lastupdate = '$lastupdate', ip = '$ip' WHERE A_id = '$row[A_id]'";
				$update = $db->query($sql);
			}
			else
			{
				$log .= $LANG['warning'].$LANG['abuse']['1'].$ip.$LANG['abuse']['2']."\n"; # Address is already assigned to host, dont abuse
			}
		}
		if ($error != 1) {
			eval("echo(\"" . addslashes($RESPOND_TEMPLATE) . "\");");
			$log .= $LANG['success'].$LANG['updateok']['1'].$ip.$LANG['updateok']['2']."\n"; # Your host is now assigned to address;
		}
		if (!empty($log)) {
			eval("echo(\"" . addslashes($LOG_TEMPLATE) . "\");");
		}
		if ($error != 1) {
			eval("echo(\"" . addslashes($ZONE_TEMPLATE) . "\");");
		}
	}
}

?>