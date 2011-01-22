<p><b><span
style='font-size:14.0pt;'>Eigener Dynamic DNS Service</span></b></p>
<p><b>Für unser
  Bespiel nehmen wir folgende Bespieldaten</span></b>:</span></p>
<p><b>- </span></b>Einen
  Server mit Debian Lenny und ISPCP Omega (letzteres ist nicht zwingend)</span></p>
<p><b>- </span></b>Einen FQHN,
  z.B. <span class="SpellE"><i>myhost.server.tld</i></span></span></p>
<p><b>- </span></b>bind9 und
  dnsutils installiert (was ja der Fall ist, wenn ISPCP eingerichtet ist).</span></p>
<p><b>- </span></b>Natürlich eine
  Domain die dynamisch verwaltet werden soll, <i>ddns.tld</i>.</span></p>
<p><b>- </span></b>Zwei IPs
  aus verschiedenen Netzen <i>123.123.<b>111</b>.10</i> und <i>123.123.<b>222</b>.10</i>, die
  beide in <i>/etc/network/interfaces</i> bereits
  konfiguriert sind.</span></p>
<p><b>- </span></b>Beide
  IP-Adressen müssen über euren Registrar als Nameserver registriert sein!</span></p>
<p>Wie genau das geht, ist abhängig von eurem Registrar,
  u.U. nehmt mit ihm Kontakt auf.</span></p>
<p>Um diese zu registrieren müsst ihr folgendes angeben:</span></p>
<p><i>123.123.<b>111</b>.10 ns1.ddns.tld</span></i></p>
<p><i>123.123.<b>222</b>.10 ns2.ddns.tld</span></i></p>
<p><b><span
style='font-size:14.0pt;'>1.</span></b> in der DNS-Zonen-Konfiguration unseres
  Registrars/Domain-Robots setzen wir die NS-Records auf <i>ns1.ddns.tld</i> und <i>ns2.ddns.tld </i>(dies geht nur, wenn die Nameserver mit den dazugehörigen IPs registriert
  wurden, und diese auch bereits propagiert sind.)</span></p>
<p><b><span
style='font-size:14.0pt;'>1.1</span></b> auf unserem Server erstellen wir einige Dateien, <i>nsupdate.key</i> die den TSIG Schlüssel
  enthalten wird, der für sichere Aktualisierungen wichtig ist,</span></p>
<p><i>nsupdate.conf</span></i> wird die
  Zonen-Konfiguration für unsere dynamische Domain <i>ddns.tld</i> enthalten. also...</span></p>
<p><i><span
style='color:#E36C0A;'>touch /etc/bind/nsupdate.key</span></i></p>
<p><i><span
style='color:#E36C0A;'>touch /etc/bind/nsupdate.conf</span></i></p>
<p><i><span
style='color:#E36C0A;'>chown bind:bind /etc/bind/nsupdate.*</span></i></p>
<p><i><span
style='color:#E36C0A;'>chmod 644 /etc/bind/nsupdate.*</span></i></p>
<p><b><span
style='font-size:14.0pt;'>1.2</span></b> nun Inkludieren wir die beiden Dateien in
  die Hauptkonfigurationsdatei des Bind9, wir öffnen die Datei</p>
<p><i><span
style='color:#E36C0A;'>vi /etc/bind/named.conf</span></i></p>
<p>und fügen folgende
  Zeilen ganz oben hinzu:</p>
<p><i><span
style='color:#365F91;'>include &quot;/etc/bind/nsupdate.key&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>include &quot;/etc/bind/nsupdate.conf&quot;;</span></i></p>
<p>Achtung: um zu
  vermeiden, dass unsere Änderung von ISPCP überschrieben wird, müssen wir die
  gleiche Änderung auch in der ISPCP Arbeitsdatei machen</p>
<p><i><span
style='color:#E36C0A;'>vi /etc/ispcp/bind/working/named.conf</span></i></p>
<p>und wieder fügen
  wir die Zeilen ganz oben ein:</p>
<p><i><span
style='color:#365F91;'>include &quot;/etc/bind/nsupdate.key&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>include &quot;/etc/bind/nsupdate.conf&quot;;</span></i></p>
<p><b><span
style='font-size:14.0pt;'>1.3</span></b> jetzt öffnen wir</p>
<p><i><span
style='color:#E36C0A;'>vi /etc/bind/nsupdate.conf</span></i></p>
<p>und fügen diesen
  Inhalt ein</p>
<p><i><span
style='color:#365F91;'>// dmn [ddns.tld] cfg entry BEGIN.</span></i></p>
<p><i><span
style='color:#365F91;'>zone &quot;ddns.tld&quot; {</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; type&nbsp;&nbsp;&nbsp;&nbsp; master;</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; file&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &quot;/var/cache/bind/ddns.tld.db&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; notify&nbsp; YES;</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; allow-update
  {</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; key
  ddns.tld;</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; };</span></i></p>
<p><i><span
style='color:#365F91;'>};</span></i></p>
<p><i><span
style='color:#365F91;'>// dmn [ddns.tld] cfg entry END.</span></i></p>
<p>beachtet das <i>allow-update</i> Statement, <i>key ddns.tld</i> ist der Name des Schlüssels.
  Diesen werden wir später für sichere Updates nutzen, er läuft uns noch einige
  Male über den Weg.</p>
<p><b><span
style='font-size:14.0pt;'>1.4</span></b> nun erstellen wir die Cache-Datei die Bind
  für die dynamische Zone nutzen wird</p>
<p><i><span
style='color:#E36C0A;'>touch /var/cache/bind/ddns.tld.db</span></i></p>
<p><i><span
style='color:#E36C0A;'>chown bind:bind /var/cache/bind/ddns.tld.db</span></i></p>
<p><i><span
style='color:#E36C0A;'>chmod 644 /var/cache/bind/ddns.tld.db</span></i></p>
<p><b><span
style='font-size:14.0pt;'>1.5</span></b> in der Cache-Datei legen wir manuell die
  Zone an</p>
<p><i><span
style='color:#E36C0A;'>vi /var/cache/bind/ddns.tld</span></i></p>
<p>nun der Inhalt</p>
<p><i><span
style='color:#365F91;'>$ORIGIN .</span></i></p>
<p><i><span
style='color:#365F91;'>$TTL 60 ; 1 minute</span></i></p>
<p><i><span
style='color:#365F91;'>ddns.tld&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IN
  SOA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ns1.ddns.tld.
  postmaster.ddns.tld. (</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2007072555
  ; serial</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 7200&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ; refresh
  (2 hours)</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 900&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ;
  retry (15 minutes)</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1857600&nbsp;&nbsp;&nbsp; ; expire (3 weeks 12 hours)</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 8400&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ; minimum
  (2 hours 20 minutes)</span></i></p>
<p><i><span
style='color:#365F91;'>)</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NS&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ns1.ddns.tld.</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NS&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ns2.ddns.tld.</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 123.123.1.10</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; MX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 10 mail.ddns.tld.</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TXT&nbsp;&nbsp;&nbsp;&nbsp; &quot;v=spf1 a mx
  ip4:123.123.111.10 ip4:123.123.111.10 ~all&quot;</span></i></p>
<p><i><span
style='color:#365F91;'>$ORIGIN ddns.tld.</span></i></p>
<p><i><span
style='color:#365F91;'>www&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 123.123.1.10
  ; www.ddns.tld</span></i></p>
<p><i><span
style='color:#365F91;'>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; CNAME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; www&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ;
  *.ddns.tld wildcard</span></i></p>
<p><i><span
style='color:#365F91;'>mail&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 123.123.1.10
  ; mail.ddns.tld for MX record</span></i></p>
<p><i><span
style='color:#365F91;'>ns1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 123.123.111.10
  ; glue record, first ip</span></i></p>
<p><i><span
style='color:#365F91;'>ns2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 123.123.222.10
  ; glue record, second <span class="SpellE">ip</span></span></i></p>
<p>wie ihr seht, haben
  wir in unserer Zone wieder die gleichen NS-Records, wie in den
  Registrar-Einstellungen, jedoch diesmal haben wir auch die passenden Subdomains <i>ns1</i> und <i>ns2 </i>eingetragen und denen <i>IN
  A</i> die IP-Adressen des Servers verpasst.</p>
<p>Diese Einträge
  nennt man <i>glue records</i>, dies erlaubt
  uns Nameserver als Subdomains einer Domain anzulegen, die sich wiederrum über
  Ihre eigenen Subdomains auflöst.</p>
<p><b>-</b> Ohne die <span class="SpellE"><i>glue</i></span><i> <span class="SpellE">records</span></i> würde
  folgendes passieren:</p>
<p>DNS-Anfrage an
  ddns.tld -&gt; Antwort: frage ns1.ddns.tld und ns2.ddns.tld -&gt; keine Antwort
  -&gt; Anfrage an die Elterndomain delegieren -&gt; diese antwortet wieder, dass
  ns1.ddns.tld und ns2.ddns.tld abgefragt werden sollen -&gt; alles wieder von
  Vorne.</p>
<p><b>-</b> Bei einer korrekten <span class="SpellE"><i>glue</i></span><i> <span
class="SpellE">record</span></i> Konfiguration sieht es dagegen so aus:</p>
<p>DNS-Anfrage na
  ddns.tld -&gt; Antwort: frage ns1.ddns.tld und ns2.ddns.tld -&gt; <span
class="SpellE">glue</span> <span class="SpellE">record</span> zu unserem DNS
  Server, der ja dann die richtige Antwort kennt.</p>
<p>Hierbei möchte ich
  noch einmal unterstreichen, dass eine Registrierung der Subdomains ns1.ddns.tld
  und ns2.ddns.tld mit den beiden IP-Adressen unumgänglich ist, sonst kommen die
  DNS-Anfragen erst gar nicht bis zu unserem Server an!</p>
<p><b><span
style='font-size:14.0pt;'>2.</span></b> nun benötigen wir den oben erwähnten
  sicheren Schlüssel, dafür gibt es einen Keygen</p>
<p><i><span
style='color:#E36C0A;'>cd /etc/bind</span></i></p>
<p><i><span
style='color:#E36C0A;'>dnssec-keygen -a HMAC-MD5 -b 512 -n HOST ddns.tld</span></i></p>
<p>der keygen erstellt
  uns zwei Dateien, <i>K&lt;keyname&gt;+157+&lt;keyid&gt;.key</i> und <i>K&lt;keyname&gt;+157+&lt;keyid&gt;.private</i>. <i>&lt;keyname&gt;</i> ist der name des
  Schlüssels, das hatten wir doch schon in <i>/etc/bind/nsupdate.conf </i>vordefiniert, und nun mit dem Keygen-Parameter <i>-n HOST &lt;keyname&gt;</i> die Schlüssel-Dateien erstellt. <i>&lt;keyid&gt;</i> ist eine zufallsgenerierte
  Zahl. In unserem Beispiel kriegen wir also etwa solche Schlüssel-Dateien <i>Kddns.tld.+157+41090.key</i> und <i>Kddns.tld.+157+41090.private</i>,</p>
<p>nun öffnen wir mit
  vi die Datei <i>Kddns.tld.+157+41090.private</i> und kopieren den langen Schlüssel, der etwa so aussieht:</p>
<p><i><span
style='color:#365F91;'>Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==</span></i></p>
<p><b><span
style='font-size:14.0pt;'>2.1</span></b> jetzt packen wir folgenden Inhalt in die
  Datei <i>/<span class="SpellE">etc</span>/bind/<span
class="SpellE">nsupdate.key</span></i>und fügen dabei den langen Schlüssel aus
  der <i>Kddns.tld.+157+41090.private</i> bei <i>secret</i> ein. Hier taucht wieder <i>key ddns.tld</i> auf, den wir in der
  Zonen-Konfiguration bei <i>allow-update</i> und beim generieren des Schlüssels verwendet haben.</p>
<p><i><span
style='color:#365F91;'>key ddns.tld {</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; algorithm
  hmac-md5;</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; secret
  &quot;Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>};</span></i></p>
<p><b><span
style='font-size:14.0pt;'>2.2</span></b> Bind neustarten</p>
<p><i><span
style='color:#E36C0A;'>/etc/init.d/bind9 restart</span></i></p>
<p>DDNS funktioniert
  jetzt und wir können per <i>nsupdate</i> Hosts und Records in der Zone <i>ddns.tld</i> löschen und hinzufügen!</p>
<p><b><span
style='font-size:14.0pt;'>3.</span></b> Da wir nun ein funktionierendes DDNS haben,
  wollen wir auch ein nettes Web-UI haben und auch ein HTTP-Update durchführen
  können. Hierfür habe ich ein <span class="SpellE">PhP</span>-Utility geschrieben
  (das allerdings noch ziemlich unreif ist, funktioniert jedoch bisher
  einwandfrei)</p>
<p><b><span
style='font-size:14.0pt;'>3.1 </span></b>Laden wir das DDNS Update Utility und
  entpacken es</p>
<p><i><span
style='color:#E36C0A;'>cd /var/www/ispcp/gui/tools/ddns</span></i></p>
<p><i><span lang="ru"
style='color:#E36C0A;'>wget http://citex.net/ddns.tar.gz</span></i></p>
<p><i><span
style='color:#E36C0A;'>tar xfvz ddns.tar.gz</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.2</span></b> Um das Utility wie ein Panel aufrufen zu
  können, fügen wir in <i>/etc/apache2/sites-enabled/00_master.conf</i> folgende Zeile hinzu. Einfach nach den anderen Aliasen im Haupt-<span
class="SpellE">VirtualHost</span></p>
<p><i><span
style='color:#365F91;'>Alias /ddns&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /var/www/ispcp/gui/tools/ddns/</span></i></p>
<p>dann sieht es etwa
  so aus</p>
<p><i><span
style='color:#365F91;'>Alias /pma&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /var/www/ispcp/gui/tools/pma/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /webmail&nbsp; /var/www/ispcp/gui/tools/webmail/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /ftp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /var/www/ispcp/gui/tools/filemanager/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /mail&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /var/www/ispcp/gui/tools/roundcube/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /ddns&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /var/www/ispcp/gui/tools/ddns/</span></i></p>
<p>Selbiges
  wiederholen wir mit <i>/<span class="SpellE">etc</span>/<span
class="SpellE">ispcp</span>/<span class="SpellE">apache</span>/00_master.conf </i>um
  sicherzugehen, dass ISPCP unsere Änderung nicht überschreibt. Achtet jedoch
  darauf, dass hier der Pfad noch als Variable angegeben ist</p>
<p><i><span
style='color:#365F91;'>Alias /<span class="SpellE">pma</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {ROOT_DIR}/<span
class="SpellE">gui</span>/<span class="SpellE">tools</span>/<span class="SpellE">pma</span>/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /<span class="SpellE">webmail</span>&nbsp; {ROOT_DIR}/<span class="SpellE">gui</span>/<span
class="SpellE">tools</span>/<span class="SpellE">webmail</span>/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /ftp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {ROOT_DIR}/<span
class="SpellE">gui</span>/<span class="SpellE">tools</span>/<span class="SpellE">filemanager</span>/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /<span class="SpellE">mail</span>&nbsp;&nbsp;&nbsp;&nbsp; {ROOT_DIR}/<span
class="SpellE">gui</span>/<span class="SpellE">tools</span>/<span class="SpellE">roundcube</span>/</span></i></p>
<p><i><span
style='color:#365F91;'>Alias /<span class="SpellE">ddns</span>&nbsp;&nbsp;&nbsp;&nbsp; {ROOT_DIR}/<span
class="SpellE">gui</span>/<span class="SpellE">tools</span>/<span class="SpellE">ddns</span>/</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.3</span></b> das Skript benötig die Schlüssel-Datei mit Endung <i>.key</i>, kopieren wir sie</p>
<p><i><span
style='color:#E36C0A;'>cp /etc/bind/Kddns.tld.+157+41090.key
  /etc/www/ispcp/gui/tools/ddns/keys/Kddns.tld.+157+41090.key</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.4</span></b> dann teilen wir dem Script den Namen der
  Schlüssel-Datei mit</p>
<p><i><span
style='color:#E36C0A;'>vi /etc/www/ispcp/gui/tools/ddns/inc/config.php</span></i></p>
<p>und teilen der
  Variable <i>$conf['nskey'] </i>den
  Dateinamen mit</p>
<p><i><span
style='color:#365F91;'>$conf['nskey'] = &quot;keys/Kddns.tld.+157+41090.key&quot;;</span></i></p>
<p>Des weiteren passen
  wir die Zugangsdaten des Administrators an</p>
<p><i><span
style='color:#365F91;'>$conf['adminuser'] = &quot;admin&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>$conf['adminpass'] = &quot;pass123&quot;;</span></i></p>
<p>und die Domain, die
  standardmäßig aktualisiert werden soll (im Falle dass wir bei Schritt <b>1, 1.3, 1.4</b> und <b>1.5</b> mehr als eine dynamische Zone erstellt haben)</p>
<p><i><span
style='color:#365F91;'>$conf['domain'] = 'ddns.tld';</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.5</span></b> um die Benutzer zu authentifizieren,
  erstellen wir eine MySQL Datenbank mit dem Namen <i>ddns</i> und darin folgende Tabellen:</p>
<p><i><span
style='color:#365F91;'>CREATE TABLE `accounts` (</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `A_id` int(11)
  NOT NULL auto_increment,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `user`
  varchar(20) collate utf8_unicode_ci NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `password`
  varchar(20) collate utf8_unicode_ci NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `dmnid`
  int(11) NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `approved`
  int(11) NOT NULL default '0',</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `email`
  varchar(100) collate utf8_unicode_ci NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `registered`
  int(100) NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `lastupdate`
  int(100) NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `ip`
  varchar(50) collate utf8_unicode_ci NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; PRIMARY
  KEY&nbsp; (`A_id`)</span></i></p>
<p><i><span
style='color:#365F91;'>) ENGINE=MyISAM&nbsp; DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;</span></i></p>
<p><i><span
style='color:#365F91;'>CREATE TABLE IF NOT EXISTS `domains` (</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `D_id` int(11)
  NOT NULL auto_increment,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; `domain`
  varchar(50) collate utf8_unicode_ci NOT NULL,</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; PRIMARY
  KEY&nbsp; (`D_id`),</span></i></p>
<p><i><span
style='color:#365F91;'>&nbsp; UNIQUE KEY
  `domain` (`domain`)</span></i></p>
<p><i><span
style='color:#365F91;'>) ENGINE=MyISAM&nbsp; DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.6</span></b> zum Testen legen wir ein Paar Einträge an:</p>
<p><i><span
style='color:#365F91;'>INSERT INTO `accounts` (`A_id`, `user`, `password`,
  `dmnid`, `approved`, `email`, `registered`, `lastupdate`, `ip`) VALUES</span></i></p>
<p><i><span
style='color:#365F91;'>(1, '<span class="SpellE">myhomepc</span>', 'pass123', 1, 1,
  '<span class="SpellE">home@mail.tld</span>', 1285356317, 1285356317, ''),</span></i></p>
<p><i><span
style='color:#365F91;'>(2, '<span class="SpellE">myworkpc</span>', 'pass123', 1, 1,
  '<span class="SpellE">work@mail.tld</span>', 1285356317, 1285356317, '');</span></i></p>
<p><i><span
style='color:#365F91;'>INSERT INTO `<span class="SpellE">domains</span>` (`<span
class="SpellE">D_id</span>`, `<span class="SpellE">domain</span>`) VALUES</span></i></p>
<p><i><span
style='color:#365F91;'>(1, 'ddns.tld'),</span></i></p>
<p><i><span
style='color:#365F91;'>(2, '<span class="SpellE">ddnsx.tld</span>'),</span></i></p>
<p><i><span
style='color:#365F91;'>(3, '<span class="SpellE">ddnsy.tld</span>');</span></i></p>
<p><b><span
style='font-size:14.0pt;'>3.7</span></b> in der <i>/<span
class="SpellE">etc</span>/<span class="SpellE">www</span>/<span class="SpellE">ispcp</span>/<span
class="SpellE">gui</span>/<span class="SpellE">tools</span>/<span class="SpellE">ddns</span>/<span
class="SpellE">inc</span>/<span class="SpellE">db.php</span></i> müssen wir noch
  die Zugangsdaten zur MySQL Datenbank angeben</p>
<p><i><span
style='color:#365F91;'># Database <span class="SpellE">connection</span>.</span></i></p>
<p><i><span
style='color:#365F91;'>$<span class="SpellE">db_host</span> = &quot;<span
class="SpellE">localhost</span>&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>$<span class="SpellE">db_user</span> = &quot;<span
class="SpellE">dbuser</span>&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>$<span class="SpellE">db_pass</span> = &quot;<span
class="SpellE">dbpass</span>&quot;;</span></i></p>
<p><i><span
style='color:#365F91;'>$<span class="SpellE">db_database</span> = &quot;<span
class="SpellE">ddns</span>&quot;;</span></i></p>
<p>In meinem Fall habe
  ich für das DDNS Updatescript einen neuen MySQL User angelegt und diesem Rechte
  nur auf die Datenbank <i>ddns</i> gegeben,
  damit nicht der <i>root</i>-User dafür Sorge
  tragen muss.</p>
<p><b><span
style='font-size:14.0pt;'>3.8</span></b> Damit das Script <i>nsupdate</i> ausführen kann, muss PhP Systembefehle ausführen können.
  Dies erlauben wir <b>nur</b> dem Master
  VirtualHost, auf dem ISPCP ausgeführt wird, damit unsere Kunden dieses
  Sicherheitsrisiko nicht ausnutzen könnten.</p>
<p><i><span
style='color:#E36C0A;'>vi /var/www/fcgi/master/php5/php.ini</span></i></p>
<p>entfernt <i>system</i> aus der <i>disable_functions</i> Zeile</p>
<p><i><span
style='color:#365F91;'>disable_functions = show_source, system, shell_exec,
  passthru, exec, phpinfo, shell, symlink</span></i></p>
<p>sieht dann so aus</p>
<p><i><span
style='color:#365F91;'>disable_functions = show_source, shell_exec, passthru,
  exec, phpinfo, shell, symlink</span></i></p>
<p>wiederholen wir
  dies für die Datei<i> /<span class="SpellE">etc</span>/<span
class="SpellE">ispcp</span>/<span class="SpellE">fcgi</span>/<span class="SpellE">parts</span>/<span
class="SpellE">master</span>/php5/php.ini</i> damit ISPCP unsere Änderungen nicht
  überschreibt.</p>
<p><b><span
style='font-size:14.0pt;'>3.9</span></b> Nun ist es soweit, die Subdomains <i>myhomepc.ddns.tld</i> und <i>myworkpc.ddns.tld</i> können aktualisiert
  und an eure dynamische IP Zuhause oder sonst wo gebunden werden. Die Änderungen
  werden innerhalb weniger Minuten übernommen, meist sogar nur Sekunden!</p>
<p>Um weitere Subdomains
  für eure Hosts anzulegen, einfach wie Schritt <b>3.7</b> beschrieben in die Datenbank einfügen. Oder einfach über das
  DDNS Update Utility unter dem Menüpunkt Registrieren, dort ist alles
  selbsterklärend.</p>
<p>Um Manuell zu
  aktualisieren geht nach <i>http://myhost.server.tld/ddns</i></p>
<p>Durch den Alias,
  ist es nun wie ein Panel aufrufbar, also genau wie euer Webmail oder <span
class="SpellE">phpMyAdmin</span> aufzurufen.</p>
<p>Für die Benutzung
  in Routern, Skripten und sonstigen automatisierten Verfahren, folgende URL
  aufrufen (z.B. mit <span class="SpellE"><i>wget</i></span>):</p>
<p><i>http://myhost.server.tld/ddns/update.php?user=&lt;username&gt;&amp;pass=&lt;password&gt;</i></p>
<p>bei mehreren
  dynamischen Zonen, den domain-Parameter mit der zu aktualisierenden Domain an
  die URL anhängen:</p>
<p><i>http://myhost.server.tld/ddns/update.php?user=&lt;username&gt;&amp;pass=&lt;password&gt;&amp;domain=&lt;domain&gt;</i></p>
<p>Die IP des Hosts
  wird automatisch erkannt, sollte das nicht der Fall sein oder nicht gewünscht,
  den ip-Parameter mit der neuen IP-Adresse an die URL anhängen.</p>
<p>(z.B. ihr wollt
  einen Host Aktualisieren ruft aber das Update von einem anderen Host auf, oder
  wenn euer Router oder Proxy oder was auch immer die IP-Erkennung verhindert)</p>
<p><i>http://myhost.server.tld/ddns/update.php?user=&lt;username&gt;&amp;pass=&lt;password&gt;&amp;ip=&lt;ipaddress&gt;</i></p>
<p>beides geht natürlich
  auch.</p>
<p><i>http://myhost.server.tld/ddns/update.php?user=&lt;username&gt;&amp;pass=&lt;password&gt;&amp;domain=&lt;domain&gt;&amp;ip=&lt;ipaddress&gt;</i></p>
<p><b><span
style='font-size:14.0pt;'>Appendix</span></b></p>
<p><b>Einige nützliche Befehle für Bind...</b></p>
<p>Um manuelle Änderungen
  an der Hauptzone zu machen, muss diese eingefroren werden. Eingefrorene Zonen
  akzeptieren keine dynamischen Updates über <i>nsupdate</i>. <i>freeze</i> speichert den aktuellen
  Zustand der Zone, es werden also alle dynamischen Änderungen erst in die Zonendatei
  geschrieben, danach die Zone auf statisch gesetzt.</p>
<p><i><span
style='color:#E36C0A;'>rndc freeze ddns.tld</span></i></p>
<p>Nach dem Änderungen
  an der Zonendatei gemacht wurden, muss sie neu geladen werden. <i>reload</i> funktioniert nur mit statischen Zonen,
  bzw. dynamischen Zonen die eingefroren sind.</p>
<p><i><span
style='color:#E36C0A;'>rndc reload ddns.tld</span></i></p>
<p>sobald ihr wieder
  dynamische Updates zulassen wollt muss die Zone wieder &quot;aufgetaut&quot;
  werden. Dynamische Zonen akzeptieren keine manuellen Änderungen an der
  Zonen-Datei.</p>
<p><i><span
style='color:#E36C0A;'>rndc thaw ddns.tld</span></i></p>
<p><b>Funktion</b></p>
<p>Das Skript
  aktualisiert nicht nur den <i>IN A</i> Record des Hosts, es erstellt außerdem einen <i>IN MX</i> Record und einen <i>wildcard</i>-Eintrag.</p>
<p>Die Zone sieht dann
  so aus:</p>
<p><i><span
style='color:#365F91;'>host.ddns.tld.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 60&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IN A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; dyn-ip-address</span></i></p>
<p><i><span
style='color:#365F91;'>mail.host.ddns.tld.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 60&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IN A&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; dyn-ip-address</span></i></p>
<p><i><span
style='color:#365F91;'>*.host.ddns.tld.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 60&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IN
  CNAME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; host.ddns.tld.</span></i></p>
<p><i><span
style='color:#365F91;'>host.ddns.tld.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 60&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; IN MX
  10&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; mail.host.ddns.tld.</span></i></p>
<p>dyndns.org bietet
  das mittlerweile nur kostenpflichtig an (was auch der Grund war, wieso ich eine
  Alternative zu dyndns.org brauchte und das hier gebastelt habe).</p>
<p><b>Info</b></p>
<p>um weitere
  Domains/dynamische Zonen anzulegen, einfach Schritte <b>1, 1.3, 1.4</b> und <b>1.5</b> wiederholen, danach alle Zonen mit dem gleichen Schlüsselnamen versehen. Dann
  noch die zusätzlichen Domains in die Tabelle <i>domains</i> hinzufügen.</p>
<p><b>neue Version im Anhang (v. 0.3)</b></p>
<p>ACHTUNG: Benutzer
  der Vorversion, die Datenbankstruktur hat sich verändert, bitte der <i>MYSQL.sql</i> Datei im Archiv entnehmen.</p>
<p><b>To do</b></p>
<p>Das Script befindet
  sich in einem sehr frühen Entwicklungsstadium, Übersetzungen für einige Strings
  fehlen, Fehler können drin sein. Diverse Funktionen noch nicht oder noch nicht
  ganz fertig. Wer Interesse hat, kann gerne mithelfen.</p>
<p>Benutzt es auf
  eigene Gefahr, ich trage keinerlei Verantwortung für Schäden die durch die
  Benutzung des Skriptes oder dieser Anleitung passieren könnten. Viel Spaß.</p>
