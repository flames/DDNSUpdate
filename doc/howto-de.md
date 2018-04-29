##DDNS Dokumentation 
###Eigener Dynamic DNS Service

**Für unser Bespiel nehmen wir folgende Bespieldaten:**

* Einen Server mit Debian Lenny und ISPCP Omega (letzteres ist nicht zwingend)

* Einen FQHN, z.B. _myhost.server.tld_

* bind9 und dnsutils installiert (was ja der Fall ist, wenn ISPCP eingerichtet ist).

* Natürlich eine Domain die dynamisch verwaltet werden soll, _ddns.tld_.

* Zwei IPs aus verschiedenen Netzen _123.123.**111**.10_ und _123.123.**222**.10_, die beide in _/etc/network/interfaces_ bereits konfiguriert sind.

*  Beide IP-Adressen müssen über euren Registrar als Nameserver registriert sein!

Wie genau das geht, ist abhängig von eurem Registrar, u.U. nehmt mit ihm Kontakt auf.

Um diese zu registrieren müsst ihr folgendes angeben:

    123.123.**111**.10 ns1.ddns.tld

    123.123.**222**.10 ns2.ddns.tld

**1.** in der DNS-Zonen-Konfiguration unseres Registrars/Domain-Robots setzen wir die NS-Records auf _ns1.ddns.tld_ und _ns2.ddns.tld_ (dies geht nur, wenn die Nameserver mit den dazugehörigen IPs registriert wurden, und diese auch bereits propagiert sind.)

**1.1** auf unserem Server erstellen wir einige Dateien, _nsupdate.key_ die den TSIG Schlüssel enthalten wird, der für sichere Aktualisierungen wichtig ist,

_**nsupdate.conf**_ wird die Zonen-Konfiguration für unsere dynamische Domain _**ddns.tld**_ enthalten. also...

_**#touch /etc/bind/nsupdate.key**_

_**#touch /etc/bind/nsupdate.conf**_

_**#chown bind:bind /etc/bind/nsupdate.***_

_**#chmod 644 /etc/bind/nsupdate.***_

**1.2** nun Inkludieren wir die beiden Dateien in die Hauptkonfigurationsdatei des Bind9, wir öffnen die Datei

_**#vi /etc/bind/named.conf**_

und fügen folgende Zeilen ganz oben hinzu:

    include "/etc/bind/nsupdate.key";

    include "/etc/bind/nsupdate.conf";

Achtung: um zu vermeiden, dass unsere Änderung von ISPCP überschrieben wird, müssen wir die gleiche Änderung auch in der ISPCP Arbeitsdatei machen

_**#vi /etc/ispcp/bind/working/named.conf**_

und wieder fügen wir die Zeilen ganz oben ein:

    include "/etc/bind/nsupdate.key";

    include "/etc/bind/nsupdate.conf";

**1.3** jetzt öffnen wir

_**#vi /etc/bind/nsupdate.conf**_

und fügen diesen Inhalt ein

    // dmn \[ddns.tld\] cfg entry BEGIN.

    zone "ddns.tld" {

                type     master;

                file       "/var/cache/bind/ddns.tld.db";

                notify  YES;

                allow-update {

                           key ddns.tld;

                };

    };

    // dmn \[ddns.tld\] cfg entry END.

beachtet das _allow-update_ Statement, _key ddns.tld_ ist der Name des Schlüssels. Diesen werden wir später für sichere Updates nutzen, er läuft uns noch einige Male über den Weg.

**1.4** nun erstellen wir die Cache-Datei die Bind für die dynamische Zone nutzen wird

_**#touch /var/cache/bind/ddns.tld.db**_

_**#chown bind:bind /var/cache/bind/ddns.tld.db**_

_**#chmod 644 /var/cache/bind/ddns.tld.db**_

**1.5** in der Cache-Datei legen wir manuell die Zone an

_**#vi /var/cache/bind/ddns.tld**_

    nun der Inhalt

    $ORIGIN .

    $TTL 60 ; 1 minute

    ddns.tld           IN SOA            ns1.ddns.tld. postmaster.ddns.tld. (

                                                   2007072555 ; serial

                                                   7200       ; refresh (2 hours)

                                                   900        ; retry (15 minutes)

                                                   1857600    ; expire (3 weeks 12 hours)

                                                   8400       ; minimum (2 hours 20 minutes)

    )

                                       NS       ns1.ddns.tld.

                                       NS       ns2.ddns.tld.

                                       A         123.123.1.10

                                       MX      10 mail.ddns.tld.

                                       TXT     "v=spf1 a mx ip4:123.123.111.10 ip4:123.123.111.10 ~all"

    $ORIGIN ddns.tld.

    www         A         123.123.1.10 ; www.ddns.tld

    *         CNAME           www      ; *.ddns.tld wildcard

    mail        A         123.123.1.10 ; mail.ddns.tld for MX record

    ns1        A         123.123.111.10 ; glue record, first ip

    ns2        A         123.123.222.10 ; glue record, second ip

wie ihr seht, haben wir in unserer Zone wieder die gleichen NS-Records, wie in den Registrar-Einstellungen, jedoch diesmal haben wir auch die passenden Subdomains _ns1_ und _ns2_ eingetragen und denen _IN A_ die IP-Adressen des Servers verpasst.

Diese Einträge nennt man _glue records_, dies erlaubt uns Nameserver als Subdomains einer Domain anzulegen, die sich wiederrum über Ihre eigenen Subdomains auflöst.

* Ohne die _glue_ _records_ würde folgendes passieren:

DNS-Anfrage an ddns.tld -> Antwort: frage ns1.ddns.tld und ns2.ddns.tld -> keine Antwort -> Anfrage an die Elterndomain delegieren -> diese antwortet wieder, dass ns1.ddns.tld und ns2.ddns.tld abgefragt werden sollen -> alles wieder von Vorne.

* Bei einer korrekten _glue_ _record_ Konfiguration sieht es dagegen so aus:

DNS-Anfrage na ddns.tld -> Antwort: frage ns1.ddns.tld und ns2.ddns.tld -> glue record zu unserem DNS Server, der ja dann die richtige Antwort kennt.

Hierbei möchte ich noch einmal unterstreichen, dass eine Registrierung der Subdomains ns1.ddns.tld und ns2.ddns.tld mit den beiden IP-Adressen unumgänglich ist, sonst kommen die DNS-Anfragen erst gar nicht bis zu unserem Server an!

**2.** nun benötigen wir den oben erwähnten sicheren Schlüssel, dafür gibt es einen Keygen

_**#cd /etc/bind**_

_**#dnssec-keygen -a HMAC-MD5 -b 512 -n HOST ddns.tld**_

der keygen erstellt uns zwei Dateien, _K<keyname>+157+<keyid>.key_ und _K<keyname>+157+<keyid>.private_. _<keyname>_ ist der name des Schlüssels, das hatten wir doch schon in _/etc/bind/nsupdate.conf_ vordefiniert, und nun mit dem Keygen-Parameter _-n HOST <keyname>_ die Schlüssel-Dateien erstellt. _<keyid>_ ist eine zufallsgenerierte Zahl. In unserem Beispiel kriegen wir also etwa solche Schlüssel-Dateien _Kddns.tld.+157+41090.key_ und _Kddns.tld.+157+41090.private_,

nun öffnen wir mit vi die Datei _Kddns.tld.+157+41090.private_ und kopieren den langen Schlüssel, der etwa so aussieht:

    Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==

**2.1** jetzt packen wir folgenden Inhalt in die Datei _/etc/bind/nsupdate.key_und fügen dabei den langen Schlüssel aus der _Kddns.tld.+157+41090.private_ bei _secret_ ein. Hier taucht wieder _key ddns.tld_ auf, den wir in der Zonen-Konfiguration bei _allow-update_ und beim generieren des Schlüssels verwendet haben.

    key ddns.tld {

                algorithm hmac-md5;

                secret "Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==";

    };

**2.2** Bind neustarten

_**#/etc/init.d/bind9 restart**_

DDNS funktioniert jetzt und wir können per _nsupdate_ Hosts und Records in der Zone _ddns.tld_ löschen und hinzufügen!

**3.** Da wir nun ein funktionierendes DDNS haben, wollen wir auch ein nettes Web-UI haben und auch ein HTTP-Update durchführen können. Hierfür habe ich ein PhP-Utility geschrieben (das allerdings noch ziemlich unreif ist, funktioniert jedoch bisher einwandfrei)

**3.1** Laden wir das DDNS Update Utility und entpacken es

_**#cd /var/www/ispcp/gui/tools/ddns**_

_**#wget http://citex.net/ddns.tar.gz**_

_**#tar xfvz ddns.tar.gz**_

**3.2** Um das Utility wie ein Panel aufrufen zu können, fügen wir in _**/etc/apache2/sites-enabled/00_master.conf**_ folgende Zeile hinzu. Einfach nach den anderen Aliasen im Haupt-VirtualHost

    Alias /ddns      /var/www/ispcp/gui/tools/ddns/

dann sieht es etwa so aus

    Alias /pma      /var/www/ispcp/gui/tools/pma/

    Alias /webmail  /var/www/ispcp/gui/tools/webmail/

    Alias /ftp      /var/www/ispcp/gui/tools/filemanager/

    Alias /mail      /var/www/ispcp/gui/tools/roundcube/
    
    Alias /ddns      /var/www/ispcp/gui/tools/ddns/

Selbiges wiederholen wir mit _/etc/ispcp/apache/00_master.conf_ um sicherzugehen, dass ISPCP unsere Änderung nicht überschreibt. Achtet jedoch darauf, dass hier der Pfad noch als Variable angegeben ist

    Alias /pma      {ROOT_DIR}/gui/tools/pma/

    Alias /webmail  {ROOT_DIR}/gui/tools/webmail/

    Alias /ftp      {ROOT_DIR}/gui/tools/filemanager/

    Alias /mail     {ROOT_DIR}/gui/tools/roundcube/

    Alias /ddns     {ROOT_DIR}/gui/tools/ddns/

**3.3** das Skript benötig die Schlüssel-Datei mit Endung _.key_, kopieren wir sie

_**#cp /etc/bind/Kddns.tld.+157+41090.key /etc/www/ispcp/gui/tools/ddns/keys/Kddns.tld.+157+41090.key**_

**3.4** dann teilen wir dem Script den Namen der Schlüssel-Datei mit

_**#vi /etc/www/ispcp/gui/tools/ddns/inc/config.php**_

und teilen der Variable _**$conf\['nskey'\]**_ den Dateinamen mit

    $conf\['nskey'\] = "keys/Kddns.tld.+157+41090.key";

Des weiteren passen wir die Zugangsdaten des Administrators an

    $conf\['adminuser'\] = "admin";

    $conf\['adminpass'\] = "pass123";

und die Domain, die standardmäßig aktualisiert werden soll (im Falle dass wir bei Schritt **1, 1.3, 1.4** und **1.5** mehr als eine dynamische Zone erstellt haben)

    $conf\['domain'\] = 'ddns.tld';

**3.5** um die Benutzer zu authentifizieren, erstellen wir eine MySQL Datenbank mit dem Namen _ddns_ und darin folgende Tabellen:

    CREATE TABLE \`accounts\` (

      \`A\_id\` int(11) NOT NULL auto\_increment,

      \`user\` varchar(20) collate utf8\_unicode\_ci NOT NULL,

      \`password\` varchar(20) collate utf8\_unicode\_ci NOT NULL,  

      \`dmnid\` int(11) NOT NULL,

      \`approved\` int(11) NOT NULL default '0',

      \`email\` varchar(100) collate utf8\_unicode\_ci NOT NULL,

      \`registered\` int(100) NOT NULL,
    
      \`lastupdate\` int(100) NOT NULL,
    
      \`ip\` varchar(50) collate utf8\_unicode\_ci NOT NULL,

      PRIMARY KEY  (\`A_id\`)

    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8\_unicode\_ci AUTO_INCREMENT=1 ;

    CREATE TABLE IF NOT EXISTS \`domains\` (

      \`D\_id\` int(11) NOT NULL auto\_increment,
    
      \`domain\` varchar(50) collate utf8\_unicode\_ci NOT NULL,_

      PRIMARY KEY  (\`D_id\`),
    
      UNIQUE KEY \`domain\` (\`domain\`)

    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8\_unicode\_ci AUTO_INCREMENT=3 ;

**3.6** zum Testen legen wir ein Paar Einträge an:

    INSERT INTO \`accounts\` (\`A_id\`, \`user\`, \`password\`, \`dmnid\`, \`approved\`, \`email\`, \`registered\`, \`lastupdate\`, \`ip\`) VALUES

    (1, 'myhomepc', 'pass123', 1, 1, 'home@mail.tld', 1285356317, 1285356317, ''),
    
    (2, 'myworkpc', 'pass123', 1, 1, 'work@mail.tld', 1285356317, 1285356317, '');

    INSERT INTO `domains` (`D_id`, `domain`) VALUES

    (1, 'ddns.tld'),

    (2, 'ddnsx.tld'),

    (3, 'ddnsy.tld');

**3.7** in der _**/etc/www/ispcp/gui/tools/ddns/inc/db.php**_ müssen wir noch die Zugangsdaten zur MySQL Datenbank angeben

    # Database connection.

    $db_host = "localhost";
    
    $db_user = "dbuser";

    $db_pass = "dbpass";

    $db_database = "ddns";

In meinem Fall habe ich für das DDNS Updatescript einen neuen MySQL User angelegt und diesem Rechte nur auf die Datenbank _ddns_ gegeben, damit nicht der _root_-User dafür Sorge tragen muss.

**3.8** Damit das Script _nsupdate_ ausführen kann, muss PhP Systembefehle ausführen können. Dies erlauben wir **nur** dem Master VirtualHost, auf dem ISPCP ausgeführt wird, damit unsere Kunden dieses Sicherheitsrisiko nicht ausnutzen könnten.

_**#vi /var/www/fcgi/master/php5/php.ini**_

    entfernt _system_ aus der _disable_functions_ Zeile

    disable_functions = show_source, system, shell_exec, passthru, exec, phpinfo, shell, symlink

sieht dann so aus

    disable_functions = show_source, shell_exec, passthru, exec, phpinfo, shell, symlink

wiederholen wir dies für die Datei _/etc/ispcp/fcgi/parts/master/php5/php.ini_ damit ISPCP unsere Änderungen nicht überschreibt.

**3.9** Nun ist es soweit, die Subdomains _myhomepc.ddns.tld_ und _myworkpc.ddns.tld_ können aktualisiert und an eure dynamische IP Zuhause oder sonst wo gebunden werden. Die Änderungen werden innerhalb weniger Minuten übernommen, meist sogar nur Sekunden!

Um weitere Subdomains für eure Hosts anzulegen, einfach wie Schritt **3.7** beschrieben in die Datenbank einfügen. Oder einfach über das DDNS Update Utility unter dem Menüpunkt Registrieren, dort ist alles selbsterklärend.

Um Manuell zu aktualisieren geht nach _http://myhost.server.tld/ddns_

Durch den Alias, ist es nun wie ein Panel aufrufbar, also genau wie euer Webmail oder phpMyAdmin aufzurufen.

Für die Benutzung in Routern, Skripten und sonstigen automatisierten Verfahren, folgende URL aufrufen (z.B. mit _wget_):

    http://myhost.server.tld/ddns/update.php?user=<username>&pass=<password>

bei mehreren dynamischen Zonen, den domain-Parameter mit der zu aktualisierenden Domain an die URL anhängen:

    http://myhost.server.tld/ddns/update.php?user=<username>&pass=<password>&domain=<domain>

Die IP des Hosts wird automatisch erkannt, sollte das nicht der Fall sein oder nicht gewünscht, den ip-Parameter mit der neuen IP-Adresse an die URL anhängen.

(z.B. ihr wollt einen Host Aktualisieren ruft aber das Update von einem anderen Host auf, oder wenn euer Router oder Proxy oder was auch immer die IP-Erkennung verhindert)

    http://myhost.server.tld/ddns/update.php?user=<username>&pass=<password>&ip=<ipaddress>

beides geht natürlich auch.

    http://myhost.server.tld/ddns/update.php?user=<username>&pass=<password>&domain=<domain>&ip=<ipaddress>

####Appendix

**Einige nützliche Befehle für Bind...**

Um manuelle Änderungen an der Hauptzone zu machen, muss diese eingefroren werden. Eingefrorene Zonen akzeptieren keine dynamischen Updates über _nsupdate_. _freeze_ speichert den aktuellen Zustand der Zone, es werden also alle dynamischen Änderungen erst in die Zonendatei geschrieben, danach die Zone auf statisch gesetzt.

    rndc freeze ddns.tld

Nach dem Änderungen an der Zonendatei gemacht wurden, muss sie neu geladen werden. _reload_ funktioniert nur mit statischen Zonen, bzw. dynamischen Zonen die eingefroren sind.

    rndc reload ddns.tld

sobald ihr wieder dynamische Updates zulassen wollt muss die Zone wieder "aufgetaut" werden. Dynamische Zonen akzeptieren keine manuellen Änderungen an der Zonen-Datei.

    rndc thaw ddns.tld

**Funktion**

Das Skript aktualisiert nicht nur den _**IN A**_ Record des Hosts, es erstellt außerdem einen _IN MX_ Record und einen _wildcard_-Eintrag.

Die Zone sieht dann so aus:

    host.ddns.tld.              60        IN A                 dyn-ip-address

    mail.host.ddns.tld.         60        IN A                 dyn-ip-address

    *.host.ddns.tld.            60        IN CNAME              host.ddns.tld.

    host.ddns.tld.              60        IN MX 10              mail.host.ddns.tld.

dyndns.org bietet das mittlerweile nur kostenpflichtig an (was auch der Grund war, wieso ich eine Alternative zu dyndns.org brauchte und das hier gebastelt habe).

**Info**

um weitere Domains/dynamische Zonen anzulegen, einfach Schritte **1, 1.3, 1.4** und **1.5** wiederholen, danach alle Zonen mit dem gleichen Schlüsselnamen versehen. Dann noch die zusätzlichen Domains in die Tabelle _domains_ hinzufügen.

**neue Version im Anhang (v. 0.3)**

#####ACHTUNG: Benutzer der Vorversion, die Datenbankstruktur hat sich verändert, bitte der _MYSQL.sql_ Datei im Archiv entnehmen.

Benutzt es auf eigene Gefahr, ich trage keinerlei Verantwortung für Schäden die durch die Benutzung des Skriptes oder dieser Anleitung passieren könnten. Viel Spaß.