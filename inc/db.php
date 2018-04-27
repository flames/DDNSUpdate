<?php

# Database connection.
$db_host = "localhost";
$db_user = "ddns";
$db_pass = "mysecretpass";
$db_database = "ddns";

$db = @new mysqli($db_host,$db_user,$db_pass,$db_database);
if (mysqli_connect_errno()) {
	$log .= $LANG['fatalerror'].$LANG['databaseconnect']."\n"; # Database connection error
	$error = 1;
    die ('Database connection error: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
}

?>