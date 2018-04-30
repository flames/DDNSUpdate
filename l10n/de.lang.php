<?php

$LANG['specifyuser'] = "Benutzername nicht angegeben.";
$LANG['tooshortuser'] = "Benutzername zu kurz.";
$LANG['specifypass'] = "Passwort nicht angegeben.";
$LANG['tooshortpass'] = "Ein kurzes Passwort du hast, junger Padawan.";
$LANG['retypepass'] = "Passwort wiederholen.";
$LANG['passnotmatch'] = "Passwörter stimmen nicht überein.";
$LANG['titleupdate'] = "Manuelles Update";
$LANG['titleregister'] = "Registrieren";
$LANG['user'] = "Benutzername";
$LANG['pass'] = "Passwort";
$LANG['passretype'] = "Passwort wiederholen";
$LANG['domain'] = "Domain";
$LANG['ip'] = "IP-Adresse";
$LANG['showinfo'] = "Info einblenden";
$LANG['hideinfo'] = "Info ausblenden";
$LANG['linkupdate'] = "<a href=\"index.php\">Update</a>";
$LANG['linkregister'] = "<a href=\"create.php\">Register</a>";
$LANG['email'] = "E-Mail";

$LANG['error'] = "<img src='images/sign_error.png'> Fehler: ";
$LANG['fatalerror'] = "<img src='images/sign_error.png'> System Fehler: ";
$LANG['warning'] = "<img src='images/sign_warning.png'> Warnung: ";
$LANG['success'] = "<img src='images/sign_tick.png'> Erfolgreich: ";

$LANG['databaseconnect'] = "Datenbankverbindung fehlerhaft.";
$LANG['databaseerror'] = "Datenbank antwortet nicht, bitte sp&auml;ter versuchen.";
$LANG['databaseresult'] = "Datenbank lieferte ung&uuml;tiges Ergebnis, bitte sp&auml;ter versuchen.";
$LANG['noaccount'] = "Dieses Konto konnte nicht verifiziert werden.";
$LANG['accountnotactiveyet'] = "Account noch nicht vom Administrator freigegeben. Bitte noch ein wenig Geduld.";
$LANG['notempfile'] = "Temporäre Datei konnte nicht erstellt werden.";
$LANG['nsupdateerror'] = "nsupdate Script gescheitert, bitte sp&auml;ter versuchen.";

$LANG['abuse']['1'] = "Der Host ist bereits der IP-Adresse <span class='important'>";
$LANG['abuse']['2'] = "</span> zugeteilt, Update wird abgebrochen. Bitte f&uuml;hren\nSie die Updates nicht zu oft aus, das schadet dem Service.";
$LANG['defaultdomain']['1'] = "Keine Domain angegeben, benutze Stadrardvorgabe: <span class='important'>";
$LANG['defaultdomain']['2'] = "</span>, stimmt das?";
$LANG['autoip']['1'] = "IP-Adresse nicht angegeben, versuche automatisch zu erkennen...<span class='important'>";
$LANG['autoip']['2'] = "</span>, richtig?";
$LANG['ipnotvalid']['1'] = "IP-Adresse ist ungültig, versuche automatisch eine gültige zu erkennen...<span class='important'>";
$LANG['ipnotvalid']['2'] = "</span>, richtig?";
$LANG['updateok']['1'] = "Ihr Host wurde nun der IP-Adresse <span class='important'>";
$LANG['updateok']['2'] = "</span> zugeteilt.";

$LANG['updatelog'] = "Update-Log";
$LANG['yourzone'] = "Ihre Zone";

$RESPOND_TEMPLATE = '<h2>Update erfolgreich</h2>
<p>Ihr Host <span class="important">{$p_host}</span> wurde erfolgreich der IP-Adresse <span class="important">{$p_ip}</span> zugeteilt.</p>
<p>Bitte beachten Sie, es k&ouml;nnen einige Minuten vergehen, bis die &Auml;nderung wirksam wird.</p>
<p><a href="index.php">zur&uuml;ck</a></p>';

?>