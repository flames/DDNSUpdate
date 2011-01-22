<?php

$LANG['specifyuser'] = "Specify your username.";
$LANG['tooshortuser'] = "Username is too short.";
$LANG['specifypass'] = "Specify your password.";
$LANG['tooshortpass'] = "Short password specified you have, young padawan.";
$LANG['title'] = "Manual update utility";
$LANG['user'] = "Username";
$LANG['pass'] = "Password";
$LANG['domain'] = "Domain";
$LANG['ip'] = "IP-Address";
$LANG['showinfo'] = "show info";
$LANG['hideinfo'] = "hide info";
$LANG['email'] = "eMail";

$LANG['error'] = "<img src='images/sign_error.png'> Error: ";
$LANG['fatalerror'] = "<img src='images/sign_error.png'> Fatal Error: ";
$LANG['warning'] = "<img src='images/sign_warning.png'> Warning: ";
$LANG['success'] = "<img src='images/sign_tick.png'> Success: ";

$LANG['databaseconnect'] = "Database connection error.";
$LANG['databaseerror'] = "Database not responding, try again later.";
$LANG['databaseresult'] = "Databse returned a bad result, try again later.";
$LANG['noaccount'] = "Could not authenticate this account";
$LANG['notempfile'] = "Failed generating temporary file name, try again later.";
$LANG['nsupdateerror'] = "nsupdate command failed, try again later.";

$LANG['abuse']['1'] = "Your Host is already assigned to this IP address <span class='important'>";
$LANG['abuse']['2'] = "</span>, update canceled. Please do not\nabuse the server with unnecessary or too frequent updates.";
$LANG['defaultdomain']['1'] = "Domain not specified, using default: <span class='important'>";
$LANG['defaultdomain']['2'] = "</span>, yours?";
$LANG['autoip']['1'] = "IP address not specified, trying to auto recognize... <span class='important'>";
$LANG['autoip']['2'] = "</span>, right?";
$LANG['updateok']['1'] = "Your host is now assigned to <span class='important'>";
$LANG['updateok']['2'] = "</span>.";

$LANG['updatelog'] = "Update log";
$LANG['yourzone'] = "Your zone";

$RESPOND_TEMPLATE = '<h2>Update erfolgreich</h2>
<p>Your host <span class="important">{$p_host}</span> has been successfully assigned to IP address <span class="important">{$p_ip}</span>.</p>
<p>Please note, there is a delay of a few minutes before the update takes effect.</p>
<p><a href="index.php">back</a></p>';

?>
