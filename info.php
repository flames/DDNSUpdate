<div class="spacer top"></div>
<p>For automated updates you can use this URL directly. Exchange the patameter values for username and password 
<span class="important">http://<?php echo $_SERVER['HTTP_HOST']; ?>/ddns.php?user=&lt;username&gt;&amp;pass=&lt;password&gt;</span></p>

Actually we offer only one domain. As soon as you will be able to choose more top-level domains for your host and you decide to use another domain than "ddns.tld", just add parameter <span class="important">&amp;domain=&lt;domain&gt;</span> to the URL</p>

<p>Normally our server auto recognizes your hosts IP address. If you would like to override the autorecognition, simply add the parameter <span class="important">&amp;ip=&lt;ipaddress&gt;</span> to the URL. This might be usefull in some situations:<br>
<ul style="font-size:smaller">
	<li>Address recognition fails if you are behind a HTTP Proxy or VPN. Some firewalls/NAT-router may break auto recognition also.</li>
	<li>You want to use the DDNS service with another host than the one you are sending the update request from.</li>
	<li>You might want to rely on Your own IP-recognition on ddns client side.</li>
</ul>
</p>

<p>On Linux and Mac simply create a shell script and call it with cron or ipup, but please <b>do not abuse</b> the server with too frequently updates.<br>
    <pre>
	    <code>
#!/bin/sh
cd /tmp
rm update.php* 2>/dev/null
wget https://<?php echo $_SERVER['HTTP_HOST']; ?>/ddns.php?user=&lt;username&gt;&amp;pass=&lt;password&gt; 2>/dev/null
        </code>
    </pre>
</p>
<div class="spacer bottom"></div>