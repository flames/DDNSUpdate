 DDNS Dokumentation          

Свой динамический ДНС сервис / Dynamic DNS Service  
чтоб все работало нужно выполнить следующие требования:  
\- Один Debian-Server с ISPCP, FQHN myhost.server.tld, bind9 и dnsutils (если вы устанавливали сервер по документации испцп, то все отлично).  
\- Два IP адреса из разных сеток 123.123.111.10 и 123.123.222.10, которые уже настроены в /etc/network/interfaces.  
\- Оба IP адреса должны быть зарегистрированы как Nameserver. Регистрация ДНС серверов происходит по разному, это зависит от вашего регистратора доменов. Для этого требуются следующие данные:  
123.123.111.10 ns1.ddns.tld  
123.123.222.10 ns2.ddns.tld  
\- Ну и конечно нам нужен домен, который мы будем обновлять динамически ddns.tld.  
1. в панели регистратора поставим IN NS записи на ns1.ddns.tld и ns2.ddns.tld (это будет работать только после регистрации этих имен с нашими IP aдресами)  
1.1 на нашем сервере создадим пару файлов, nsupdate.key будет содержать TSIG ключ, который позволяет зашифровано и безопасно обновлять домен.  
Во втором файле, nsupdate.conf, будет днс зона для динамического домена ddns.tld. и так...  
touch /etc/bind/nsupdate.key  
touch /etc/bind/nsupdate.conf  
chown bind:bind /etc/bind/nsupdate.*  
chmod 644 /etc/bind/nsupdate.*  
1.2 надо инклудировать эти новые файлы в главную конфигурацию Bind9. Открываем нашим любимым эдитором (я люблю vi, пользуйте что вам по душе)  
vi /etc/bind/named.conf  
и в самом начале добавляем две строки:  
include "/etc/bind/nsupdate.key";  
include "/etc/bind/nsupdate.conf";  
Внимание, что-бы панель не попортила наши изменения, надо прописать их и сюда  
vi /etc/ispcp/bind/working/named.conf  
и добавить опять-же те две строки, в самом верху:  
include "/etc/bind/nsupdate.key";  
include "/etc/bind/nsupdate.conf";  
1.3 Открываем конфигурационный файл  
vi /etc/bind/nsupdate.conf  
и добавляем новую днс зону  

PHP Code:  

`// dmn [ddns.tld] cfg entry BEGIN.[/i]  
  zone "ddns.tld" {  
              type     master;  
              file       "/var/cache/bind/ddns.tld.db";  
              notify  YES;  
              allow-update {  
                         key ddns.tld;  
  };  
  };  
  // dmn [ddns.tld] cfg entry END.   
`

Обратите внимание на allow-update, key ddns.tld это название ключа для безопасных обновлений. Это название пригодиться позже еще.  
1.4 создаем Cache-файл для Bind в котором он будет сохранять обновления зоны  
touch /var/cache/bind/ddns.tld.db  
chown bind:bind /var/cache/bind/ddns.tld.db  
chmod 644 /var/cache/bind/ddns.tld.db  
                        
1.5 в Cache-файл  
vi /var/cache/bind/ddns.tld.db  
добавим изначальную конфигурацию  

PHP Code:  

`$ORIGIN .  
  $TTL 60 ; 1 minute  
  ddns.tld           IN SOA            ns1.ddns.tld. postmaster.ddns.tld. (  
                                                 2007072555 ; serial  
                                                 7200       ; refresh (2 hours)  
                                                 900        ; retry (15 minutes)  
                                                 1857600    ; expire (3 weeks 12 hours)  
                                                 8400       ; minimum (2 hours 20 minutes)  
  )  
                                     NS       ns1.ddns.tld.  
                                     NS       ns2.ddns.tld.  
                                     A         123.123.1.10  
                                     MX      10 mail.ddns.tld.  
                                     TXT     "v=spf1 a mx ip4:123.123.111.10 ip4:123.123.111.10 ~all"  
  $ORIGIN ddns.tld.  
  www                          A         123.123.1.10 ; www.ddns.tld  
  *                                 CNAME           www       ; *.ddns.tld wildcard  
  mail                A         123.123.1.10 ; mail.ddns.tld for MX record  
  ns1                             A         123.123.111.10 ; glue record, first ip  
  ns2                             A         123.123.222.10 ; glue record, second ip   
`

как видите в нашей зоне опять IN NS-записи которые приводят в замкнутый круг, но... в этот раз мы прописали под-домены ns1 и ns2 с записями IN A к нашим IP-адресам. Это так называемые glue record, они нам позволяют использовать днс сервера на его-же под-доменах, без проблемы что запросы будут бегать по кругу.  
2. Вот тут нам потребуется выше названый ключ для шифрования обновлений, чтоб его создать воспользуемся генератором из пакета dnsutils  
cd /etc/bind  
dnssec-keygen -a HMAC-MD5 -b 512 -n HOST ddns.tld  
генератор создаст нам два файла, K<keyname>+157+<keyid>.key и K<keyname>+157+<keyid>.private. <keyname> является названием ключа, которое, если я не ошибаюсь, мы прописали в /etc/bind/nsupdate.conf. Это не совпадение, параметр генератора -n HOST <keyname> наколдовал. <keyid> просто число сгенерированое случайным методом, чтоб избежать повторность. В нашем примере мы получим два файла примерно с таким названием Kddns.tld.+157+41090.key и Kddns.tld.+157+41090.private,  
откроем эдитором Kddns.tld.+157+41090.private и скопируем длинный ключ, который примерно так выглядит:  
Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==  
2.1 Засунем его в /etc/bind/nsupdate.key под секцией secret, добавим все это вот так:  

PHP Code:  

`key ddns.tld {  
              algorithm hmac-md5;  
              secret "Y1xlce0Ub0ePfxslRVbfYUh/berC9R32XmFaen3VScpHw9fX79ZNo9ESGUhH5qtVXoTyyvdouP7t1TIgm62Whg==";  
  };   
`

И опять то-же самое название key ddns.tld, что в конфигурации зоны под allow-update и при создании ключа.  
2.2 Перезагрузим Bind  
/etc/init.d/bind9 restart  
почти все, динамические обновления зоны уже работают! С помощью nsupdate мы можем добавлять и удалять записи в зоне ddns.tld!  
3. Так как у нас есть рабочая динамическая зона, мы хотим и удобный для управления Web-UI и возможность обновлять через HTTP.  
3.1 Скачиваем DDNS Update Utility и распаковываем  
cd /var/www/ispcp/gui/tools/  
wget [http://citex.net/ddns.tar.gz](http://citex.net/ddns.tar.gz)  
tar xfvz ddns.tar.gz  
mv ddns-0.3 ddns  
3.2 В /etc/apache2/sites-enabled/00_master.conf и /etc/ispcp/apache/00_master.conf  
добаим алиас, в главный VirtualHost  
Alias /ddns      /var/www/ispcp/gui/tools/ddns/  
Результат примерно такой:  

PHP Code:  

`Alias /pma      /var/www/ispcp/gui/tools/pma/  
  Alias /webmail  /var/www/ispcp/gui/tools/webmail/  
  Alias /ftp      /var/www/ispcp/gui/tools/filemanager/  
  Alias /mail      /var/www/ispcp/gui/tools/roundcube/  
  Alias /ddns      /var/www/ispcp/gui/tools/ddns/   
`

apache веб-сервер мы перезагрузим чють позже, т.к. еще будем делать изменения которые требуют его перезагрузку.  
3.3 Скрипту требуется один из файлов, что мы генерировали раньше, тот что с суффиксом .key, скопируем его  
cp /etc/bind/Kddns.tld.+157+41090.key /var/www/ispcp/gui/tools/ddns/keys/Kddns.tld.+157+41090.key  
3.4 Укажем его в конфигурации скрипта  
vi /var/www/ispcp/gui/tools/ddns/inc/config.php  
подскажем изменяемой $conf\['nskey'\] название файла  
$conf\['nskey'\] = "keys/Kddns.tld.+157+41090.key";  
кроме того укажем имя и пароль администратора  
$conf\['adminuser'\] = "admin";  
$conf\['adminpass'\] = "pass123";  
и домен который мы хотим обновлять по умолчанию (в случае если мы создали в шагах 1, 1.3, 1.4 и 1.5 больше чем одну динамическую зону)  
$conf\['domain'\] = 'ddns.tld';  
3.5 для аутентификации пользователей требуется база данных MySQL, создадим ее и назовем ddns, в нее добавим следующие таблички:  

PHP Code:  

``CREATE TABLE `accounts` (  
  `A_id` int(11) NOT NULL auto_increment,  
  `user` varchar(20) collate utf8_unicode_ci NOT NULL,  
  `password` varchar(20) collate utf8_unicode_ci NOT NULL,  
  `dmnid` int(11) NOT NULL,  
  `approved` int(11) NOT NULL default '0',  
  `email` varchar(100) collate utf8_unicode_ci NOT NULL,  
  `registered` int(100) NOT NULL,  
  `lastupdate` int(100) NOT NULL,  
  `ip` varchar(50) collate utf8_unicode_ci NOT NULL,  
    PRIMARY KEY  (`A_id`)  
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;   
``

PHP Code:  

``CREATE TABLE IF NOT EXISTS `domains` (  
  `D_id` int(11) NOT NULL auto_increment,  
  `domain` varchar(50) collate utf8_unicode_ci NOT NULL,  
    PRIMARY KEY  (`D_id`),  
    UNIQUE KEY `domain` (`domain`)  
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;   
``

3.6 чтоб протестировать, закинем пару учетных записей:  

PHP Code:  

``INSERT INTO `accounts` (`A_id`, `user`, `password`, `dmnid`, `approved`, `email`, `registered`, `lastupdate`, `ip`) VALUES  
  (1, 'myhomepc', 'pass123', 1, 1, 'home@mail.tld', 1285356317, 1285356317, ''),  
  (2, 'myworkpc', 'pass123', 1, 1, 'work@mail.tld', 1285356317, 1285356317, '');   
``

поля registered и lastupdate являются unix timestamp, добавляя записи вручную укажем что угодно, скрип опять-же заполняет эти поля настоящим временем регистрации и обновления.  

PHP Code:  

``INSERT INTO `domains` (`D_id`, `domain`) VALUES  
  (1, 'ddns.tld'),  
  (2, 'ddnsx.tld'),  
  (3, 'ddnsy.tld');   
``

тут мы можем указать все домены, которым мы создали в шагах 1, 1.3, 1.4 и 1.5 динамические зоны.  
3.7 в /var/www/ispcp/gui/tools/ddns/inc/db.php нужно теперь указать доступ к базе данных MySQL  
\# Database connection.  
$db_host = "localhost";  
$db_user = "dbuser";  
$db_pass = "dbpass";  
$db_database = "ddns";  
лично я создал нового MySQL юзера для скрипта и дал ему права только на базу данных ddns, чтоб не рисковать root-юзером.  
3.8 Что-бы скрипт мог выполнять команду nsupdate, требуется разрешить PhP пользовать функцыю system(). Это мы позволим только главному VirtualHost-у,  
на котором работает панель, что-бы нашы клиенты не могли воспользоваться этой дыркой в безопасности сервера.  
vi /var/www/fcgi/master/php5/php.ini  
удалим system из строки disable_functions:  
disable\_functions = show\_source, system, shell_exec, passthru, exec, phpinfo, shell, symlink  
результат такой:  
disable\_functions = show\_source, shell_exec, passthru, exec, phpinfo, shell, symlink  
теперь перезагрузим апач /etc/init.d/apache2 restart т.к. больше изменений в его конфигурации, и в конфигурации к нему подключеных модуелей больше не будет.  
3.9 Наконец, хосты myhomepc.ddns.tld и myworkpc.ddns.tld мы можем цеплять на наш домашний или любой другой IP адрес. Обновления вступают в силу в течении нескольких минут, восновном даже секунд!  
Что-бы добавить больше хостов, повторите шаг 3.6, тоесть добавьте запись в базу данных. Или в DDNS Update Utility кликните на Register, там все само объяснится.  
Для обновления вручную идем по адресу http://myhost.server.tld/ddns и кликнем на Update  
Для обновленя в рутерах, скриптах и других автоматизациях, используем HTTP URL:  
http://myhost.server.tld/ddns/ddns.php?user=<username>&pass=<password>  
Если вы создали несколько динамических зон, то пользователь должен указать домен, на котором он заегистрировал свой хост, для чего требуется подвесить параметр domain к URL:  
http://myhost.server.tld/ddns/ddns.php?user=<username>&pass=<password>&domain=<domain>  
IP адрес хоста восновном распознается автоматически, если это по какой-то причине не срабатывает или не требуется, то можно указать его вручную и подцепить к URL:  
http://myhost.server.tld/ddns/ddns.php?user=<username>&pass=<password>&ip=<ipaddress>  
вот так можно указать оба параметра:  
http://myhost.server.tld/ddns/ddns.php?user=<username>&pass=<password>&domain=<domain>&ip=<ipaddress>  
Дополнительная информация  
Несколько нужных команд для Bind  
Что-бы делат ручные изменения в динаической зоне ее надо "заморозить". Внимание, замороженя зона не принимает обновления от nsupdate. freeze сохранит все динамические изменения в кэше зоны и поставит ее в статус static zone:  
rndc freeze ddns.tld  
после ручных изменений требуется перезагрузить зону, что-бы Bind их принял. reload работает только со static zone и замороеными динамическими:  
rndc reload ddns.tld  
после чего надо зону опять "оттаить". thaw поменяет статус oбратно в dynamic zone. В этом статусе зона не принемает ручных изменений, но принимает опять nsupdate:  
rndc thaw ddns.tld  
Функциональность зоны  
Скипт обновляет не только IN A запись хоста, еще он создает IN MX запись и wildcard на все под-домены хоста:  

PHP Code:  

`host.ddns.tld.                          60        IN A                            dyn-ip-address  
  mail.host.ddns.tld.                  60        IN A                            dyn-ip-address  
  *.host.ddns.tld.                       60        IN CNAME                 host.ddns.tld.  
  host.ddns.tld.                          60        IN MX 10                   mail.host.ddns.tld.   
`

серезная альтернатива к dyndns.org, темболее у них только два динамических хоста безплатно ![Wink](images/smilies/wink.gif "Wink")  
Дополнительные домены  
Что-бы добавить дополнительные домены/динамические зоны, повторите шаги 1, 1.3, 1.4 и 1.5, всем зонам укажите тот-же самый TSIG ключ. Добавьте записи в базу данных, как описано в параграфе 3.6.  
Новая версия скрипта (v. 0.3)  
Внимание пользователи старой версии: структура базы данных изменилась, сравните с файлом MYSQL.sql в архиве.  
To do  
Скрипт еще очень молодой и находится в стадии alpha, требуются переводчики, некоторые стринги прошите железно в скрипт и не берутся из языковых файлов, могут быть ошибки и недоделки. Кто хочет помочь, пишите.  
За ущерб связаный с моей документацией и скриптом я не несу ни какой ответственности. Удачи.