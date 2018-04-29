 DDNS Dokumentation

**To do (or ideas to improve)**

Das Script befindet sich in einem sehr frühen Entwicklungsstadium, Übersetzungen für einige Strings fehlen, Fehler können drin sein. Diverse Funktionen noch nicht oder noch nicht ganz fertig. Wer Interesse hat, kann gerne mithelfen.

*   1\. improvement: ddns.php - add http header for success and errors (so routers can log it correctly. currently we have only a text echo, which is always 200, so router thinks update was good, even if some error happened.)

*   https://github.com/kilburn/bdns
*   200 OK Die Anfrage wurde erfolgreich bearbeitet und das Ergebnis der Anfrage wird in der Antwort übertragen.
*   201 Created Die Anfrage wurde erfolgreich bearbeitet. Die angeforderte Ressource wurde vor dem Senden der Antwort erstellt. Das „Location“-Header-Feld enthält eventuell die Adresse der erstellten Ressource.
*   202 Accepted Die Anfrage wurde akzeptiert, wird aber zu einem späteren Zeitpunkt ausgeführt. Das Gelingen der Anfrage kann nicht garantiert werden.
*   409 Conflict Die Anfrage wurde unter falschen Annahmen gestellt. Im Falle einer PUT-Anfrage kann dies zum Beispiel auf eine zwischenzeitliche Veränderung der Ressource durch Dritte zurückgehen.
*   410 Gone Die angeforderte Ressource wird nicht länger bereitgestellt und wurde dauerhaft entfernt.

*   2\. security: bind keys need to be moved to a secure location out of htdocs! or may be better implementation at all?
*   3\. gui: translation strings for all elements
*   4\. docu: update and translate this documentation (standalone installation w/o any panel)
*   5\. security: passwords need to be encrypted and salted
*   6\. gui: "forgot password" feature
*   7\. update clients: windows, linux and osx (maybe some open source java client)
*   8\. security: ddns.php update with a user generated token-key instead of users main password
*   9\. gui admin: datatables with filtering and sorting (to easier find and clean inactive users / orphaned hosts)
*   10\. gui admin: datatables with checkbox column and mass editing feature (delete, allow, block)
*   11\. gui: better and responsive template
*   12\. docu: convert this docu to clean MD
*   13\. info: update info page with better and simplier instructions
*   14\. gui: do we need a real login system? guess not. maybe later, if service grows, so one user can register multiple hosts (separate user from host) and have a dashboard with overview of his hosts and status of his services on those hosts.
*   15\. gui: error block/allow/delete user. any button changes last id
*   16\. gui: admin.php create domains feature
*   17\. security: rndc key per user (generated at registration)
*   18\. clean: admin.php add clean_zone template when delete a host