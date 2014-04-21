#######################################################
#						                                          #
# Litotex Browsergame Engine (http://www.litotex.de)  #
# VERSION: 0.7.0				                              #  
# ENTWICKELT: FreeBG Team (http://www.freebg.de)      #
# COPYRIGHT 2008 FreeBG (http://www.freebg.de)	      #
# KONTAKT   extremmichi@yahoo.de						                                          #
#	Hinweis:				                                    #
# Diese Software ist urheberechtlich geschützt.	      #
#					       	                                    #
# Für jegliche Fehler oder Schäden, 		              #
# die durch diese Software auftreten könnten,         #
# übernimmt der Autor keine Haftung.		              #
#                                                     #
#  Alle Copyright - Hinweise innerhalb dieser Datei   #
#  dürfen NICHT entfernt und NICHT verändert werden.  #
#						                                          #
#  Released under the GNU General Public License      #
#                                                     #
#######################################################


ALLGEMEIN
Mit der Installation von Litotex stimmen Sie insbesondere folgenden Punkten zu: 
*) den Urheberrechtshinweis im Footer nicht zu entfernen, durch andere technische Möglichkeiten auszublenden oder unsichtbar zu machen.
*) den Urheberrechtshinweis in allen Templates, in der Form der von Litotex ausgelieferten Layoutstrukturierung anzuzeigen.

Wir empfehlen weiterhin die Installation in einen Unterordner des Webspaces.



INSTALLATION

1. Lade alle Dateien auf den Webspace und erstelle folgende Ordner
   templates_c
   templates_c/standard
   acp/templates_c
   Setzen Sie diesen Ordner mit CHMOD Rechten (nur Linux) auf 777 (0777)

2. Lege eine neue Datenbank an.
   Die Informationen wie Username etc. werden spÃ¤ter benoetigt.

3. Editiere die includes/config.php.sample in dem du deine Werte der Datenbank
   sowie der Serverpfade anpasst.
   Speichere die Datei danach als config.php im Verzeichnis includes	

4.  Importiere nun die mitgelieferte setup/db_clean.sql in deine Datenbank
    Hierzu kannst du phpmyadmin benutzen oder den sql-cleint deiner Wahl 

5.  Nun loesche den Ordner "setup" sowie den Ordner "_shipping" 

    Du solltest nun dein Game im Browser aufrufen kÃ¶nnen.

6.  erstelle nun einen User mittels der Registrierungsfunktion und wechsel noch mal in phpmyadmin.
    in der Tabelle cc1_users sollte der eben erstellte User zu finden sein . Editiere das Feld "Server_admin"
    indem du hier eine 1 eintrÃ¤gst. 
   
7.  Schuetze dein  ACP Verzeichnis mittels htaccess o.ae. um einen unbefugten Zutritt nicht zu ermoeglichen.

8.  Das Design von Litotex befindet sich im Ordner "themes\standard".
    Dieses kann ganz nach den persönlichen Ansprüchen geändert werden.
		 
9.  Für die automatische Punkteberechnung ist es norwendig einen Cronjob anzulegen.
    Der Cronjob muss die Datei http://ihrewebseite.tld/includes/update.php aufrufen.	 
    Wir empfehlen die Datei update.php umzubenennen.
	 
	 
INFORMATIONEN


1. Fragen und Probleme versuche ich zeitnah zu beantworten.
   Desweiteren kannst du gerne Verbesserungsideen vorschlagen oder bei Interesse am Projekt mitwirken.

Michi



