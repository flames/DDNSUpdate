<?php
/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

$UPDATE_TEMPLATE = 'server {$conf[nameserver]}
zone {$row[domain]}
update delete {$host}. MX 10 mail.{$host}.
update delete *.{$host}.
update delete mail.{$host}.
update delete {$host}.
update add {$host}. {$conf[ttl]} A {$ip}
update add mail.{$host}. {$conf[ttl]} A {$ip}
update add *.{$host}. {$conf[ttl]} CNAME {$host}.
update add {$host}. {$conf[ttl]} MX 10 mail.{$host}.
send
';

$CLEAN_TEMPLATE = 'server {$conf[nameserver]}
zone {$row[domain]}
update delete {$host}. MX 10 mail.{$host}.
update delete *.{$host}.
update delete mail.{$host}.
update delete {$host}.
send
';

$LOG_TEMPLATE = '<br><p>{$LANG[updatelog]}: <pre class="script">{$log}</pre></p>';

$ZONE_TEMPLATE = '<br><p>{$LANG[yourzone]}: <pre class="script">
{$host}.			{$conf[ttl]}	IN A			{$ip}
mail.{$host}.		{$conf[ttl]}	IN A			{$ip}
*.{$host}.		{$conf[ttl]}	IN CNAME		{$host}.
{$host}.			{$conf[ttl]}	IN MX 10		mail.{$host}.
</pre></p>';

?>
