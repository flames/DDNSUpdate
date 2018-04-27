/******
 *
 *	DDNS Update Utility
 *	Developped by Arthur Mayer, a.mayer@citex.net
 *	Released under LGPL, Apache and BSD licenses (use the one you want)
 *
******/

// ajax user check
var ajax = null;
// initiate
function usercheck_init(){
	if (window.XMLHttpRequest) {
		ajax = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {
		try {
			ajax = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (ex) {
				try {
					ajax = new ActiveXObject("Microsoft.XMLHTTP");
				}
			catch (ex) {
			}
		}
	}
}
// status
function usercheck_status() {
	if (ajax.readyState == 4) {
		var respondelement = document.getElementById("user");
		respondelement .innerHTML = ajax.responseText;
	}
}
// call
function usercheck_call() {
	var user = document.ddnsform.user.value;  // Name vom Formularfeld, in diesen Fall "user"
	var dmnid = document.ddnsform.dmnid.value;  // Name vom Formularfeld, in diesen Fall "dmnid"
	var respondelement  = document.getElementById("user");
	respondelement .innerHTML = '<img src="images/loading.gif" alt="Lade..." />'; // Lade Grafik
	ajax.open("GET", "inc/ajaxcheck.php?checkuser=" + user + "&checkdomain=" + dmnid); // Daten holen
	ajax.onreadystatechange = usercheck_status;
	ajax.send(null);
}
// load
var respondelement = usercheck_init();

// form check
// TODO: rewrite this ugly hardcoded shit xD
function checkForm()
{
	document.ddnsform.user.style.borderColor=null;
	document.getElementById("user").innerHTML = null;
	document.ddnsform.pass.style.borderColor=null;
	document.getElementById("pass").innerHTML = null;
	if (document.ddnsform.passretype) {
		document.ddnsform.passretype.style.borderColor=null;
		document.getElementById("passretype").innerHTML = null;
	}

	if(document.ddnsform.user.value=="")
	{
	alert ("<?=$LANG['specifyuser'] ?>");
	document.ddnsform.user.focus();
	document.ddnsform.user.style.borderColor='#FF3300';
	document.getElementById("user").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
	return false;
	}

	if( document.ddnsform.user.value.length < 4  )
	{
	alert ("<?=$LANG['tooshortuser'] ?>");
	document.ddnsform.user.focus();
	document.ddnsform.user.style.borderColor='#FF3300';
	document.getElementById("user").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
	return false;
	}

	if(document.ddnsform.pass.value=="")
	{
	alert ("<?=$LANG['specifypass'] ?>");
	document.ddnsform.pass.focus();
	document.ddnsform.pass.style.borderColor='#FF3300';
	document.getElementById("pass").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
	return false;
	}

	if( document.ddnsform.pass.value.length < 6  )
	{
	alert ("<?=$LANG['tooshortpass'] ?>");
	document.ddnsform.pass.focus();
	document.ddnsform.pass.style.borderColor='#FF3300';
	document.getElementById("pass").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
	return false;
	}

	if (document.ddnsform.passretype) {
		if(document.ddnsform.passretype.value=="")
		{
		alert ("<?=$LANG['retypepass'] ?>");
		document.ddnsform.passretype.focus();
		document.ddnsform.passretype.style.borderColor='#FF3300';
		document.getElementById("passretype").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		return false;
		}
		if( document.ddnsform.passretype.value != document.ddnsform.pass.value  )
		{
		alert ("<?=$LANG['passnotmatch'] ?>");
		document.ddnsform.pass.focus();
		document.ddnsform.pass.style.borderColor='#FF3300';
		document.ddnsform.passretype.style.borderColor='#FF3300';
		document.getElementById("pass").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		document.getElementById("passretype").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		return false;
		}
	}
	/*if (document.ddnsform.email) {
		if(document.ddnsform.email.value=="")
		{
		alert ("<?=$LANG['emaileempty'] ?>");
		document.ddnsform.email.focus();
		document.ddnsform.email.style.borderColor='#FF3300';
		document.getElementById("email").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		return false;
		}
		if( document.ddnsform.email.value != document.ddnsform.pass.value  )
		{
		alert ("<?=$LANG['emailinvalid'] ?>");
		document.ddnsform.email.focus();
		document.ddnsform.pass.style.borderColor='#FF3300';
		document.ddnsform.passretype.style.borderColor='#FF3300';
		document.getElementById("pass").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		document.getElementById("passretype").innerHTML = '<img src="images/sign_error.png" alt="Error" />';
		return false;
		}
	}*/
}