<?php
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

# php datetime to unix timestamp
function  unixTimestamp($timestamp){
	$timestamp = date_parse($timestamp);
	$timestamp = mktime(
		$timestamp['hour'],
		$timestamp['minute'],
		$timestamp['second'],
		$timestamp['month'],
		$timestamp['day'],
		$timestamp['year'],
		-1 # 1 if the time is during daylight savings time (DST), 0 if it is not, -1 if it is unknown
	);
	return $timestamp;
}

# "time ago" for last update
# use to unix timestamps: $registered = timeAgo($row['registered']);
# use to php datetime: $registered = timeAgo(unixTimestamp($row['registered']));
function  timeAgo($timestamp, $granularity=2){
	$difference = time() - $timestamp;
	if ($difference < 0) { return 'less than a second'; }
	$periods = array(
		'year'	=> 12 * 30 * 24 * 60 * 60,
		'month'	=> 30 * 24 * 60 * 60,
		'week'	=>  7 * 24 * 60 * 60,
		'day'	=> 24 * 60 * 60,
		'hr'	=> 60 * 60,
		'min'	=> 60,
		'sec'	=>  1
	);
	$output = '';
	foreach($periods as $key => $value){
		if($difference >= $value){
			$time = round($difference / $value);
			$difference %= $value;
			$output .= ($output ? ' ' : '').$time.' ';
			$output .= (($time > 1 && $key == 'day') ? $key.'s' : $key);
			$granularity--;
		}
		if($granularity == 0) break;
	}
	return $output;
}

# admin panel, list users, edit, delete, activate, deactivate.
# since i made this script for my own usage, there are no confirmation dialogs like "do you really want to delete this user"
# and no error treating, so be carefull. you are welcome to improve this script ;)
if ($_SESSION['adminloggedin'] == 'muy bien') {
	$id = $_POST['id'];
	$userspass = $_POST['userspass'];
	if(isset($_POST['set'])){
		$sql = "UPDATE accounts SET password = '$userspass' WHERE A_id = '$id'";
		$update = $db->query($sql);
		# NOT TODO: we could also give options to change username and domain of user, but
		# this does not make any sense, we can simply delete user and create new one.
		# if you want to realize those options, do not forget to "nsupdate delete" the old zone entry,
		# the new zone entry will be created automatically at next update.
		# always keep the zone clean!
	}
	if(isset($_POST['delete'])) {
		$sql = "DELETE FROM accounts WHERE A_id ='$id'";
		$delete = $db->query($sql);
		# TODO: add here "nsupdate delete" to keep the zone clean. example nsupdate template:
		# nsupdate template already there, inc/templates.php, $CLEAN_TEMPLATE
	}
	if(isset($_POST['allow'])){
		$sql = "UPDATE accounts SET approved = '1' WHERE A_id = '$id'";
		$update = $db->query($sql);
	}
	if(isset($_POST['block'])){
		$sql = "UPDATE accounts SET approved = '0' WHERE A_id = '$id'";
		$update = $db->query($sql);
	}

	$sql = "SELECT D.D_id, D.domain, A.A_id, A.user, A.password, A.dmnid, A.approved, A.email, A.registered, A.lastupdate, A.ip FROM domains AS D INNER JOIN accounts AS A ON D.D_id = A.dmnid";
	$result = $db->query($sql);
	$list .= '<h2>Administration</h2>';
	$list .= '<table class="adminlist">
			  <thead>
			  <tr bgcolor="#c0c0c0">';
	$list .= '<th align="right">id</th>
			  <th align="left">Host</th>
			  <th></th>
			  <th></th>
			  <th align="left">pass</th>
			  <th>eMail</th>
			  <th>Registered</th>
			  <th></th>
			  <th align="left">Current IP</th>
			  <th align="left">Update IP</th>
			  <th align="left">Last update</th>';
	$list .= '</tr>
			  </thead>';
	while ($row = $result->fetch_assoc()) {
		$list .= '<form method="post" action="index.php?site=admin" class="inlineform" name="admin'.$row['A_id'].'">
				  <tbody>
				  <tr>';
		$list .= '<td bgcolor="#c0c0c0" align="right"><b>'.$row['A_id'].'</b></td>';
		$list .= '<td>'.$row['user'].'.'.$row['domain'].'</td>';
		$list .= '<td><input type="hidden" name="id" value="'.$row['A_id'].'" />';
		$list .= '<input type="Submit" value="delete" name="delete"></td>';
		$list .= '<td>';
		if ($row['approved'] == 0) { $list .= '<input type="Submit" value="allow" name="allow">'; }
		else { $list .= '<input type="Submit" value="block" name="block">'; }
		$list .= '</td>';
		# on the next line you may want to change input type to password, or even not to query the password from database in the statement above.
		$list .= '<td><input type="text" name="userspass" value="'.$row['password'].'" size="10" /><input type="Submit" value="set" name="set"></td>';
		$list .= '<td>'.$row['email'].'</td>';
		$registered = timeAgo($row['registered']); # call function to convert timestamp to "time ago" string
		$list .= '<td>'.$registered.'</td>'; # "time ago" string
		$list .= '<td><a href="http://'.$row['user'].'.'.$row['domain'].'" target="_blank"><img src="images/globe_go.png"></a></td>';
		$currip = gethostbyname($row['user'].'.'.$row['domain']);
		$list .= '<td>'.$currip.'</td>';
		$list .= '<td>'.$row['ip'].'</td>';
		#$list .= '<td>'.$row['lastupdate'].'</td>'; # normal timestamp Y-m-d H:i:s ## omfg, i forgot why i created this line and also why commented it out. maybe for debugging? guess we can delete it. xD
		$lastupdate = timeAgo($row['lastupdate']); # call function to convert timestamp to "time ago" string
		$list .= '<td>'.$lastupdate.'</td>'; # "time ago" string
		$list .= '</tr>
				  </tbody>
				  </form>';
		$list .= "\n";
	}
	$list .= '</table>
			  <br>';
	echo $list;
}
else {
	echo '<h2>Administration</h2><p>Access denied!</p>';
}

?>
