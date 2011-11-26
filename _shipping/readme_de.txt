#######################################################
#						                                          #
# Litotex Browsergame Engine (http://www.litotex.de)  #
# VERSION: 0.7.0				                              #  
# ENTWICKELT: FreeBG Team (http://www.freebg.de)      #
# COPYRIGHT 2008 FreeBG (http://www.freebg.de)	      #
#						                                          #
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

Für die Installation und für den Betrieb ist es notwendig das FTP über PHP funktioniert.
Wir empfehlen weiterhin die Installation in einen Unterordner des Webspaces.



VORBEREITUNG


1. Laden Sie alle Dateien auf den Webspace und erstellen Sie folgende Ordner
   setup_tmp
   Setzen Sie diesen Ordner mit CHMOD Rechten (nur Linux) auf 777 (0777)


2. Legen Sie eine neue Datenbank an.
	 Die Informationen wie Username etc. werden während des Setups benötigt.

3. Rufen Sie nun die URL Ihrer Webseite wie folgt auf: 
	 http://ihrewebseite.tld/setup.php und folgen Sie den Anweisungen.



NACH DER INSTALLATION


1. Nach der Beendigung der Installation löschen Sie die Datei "setup.php" 

2. Schützen Sie ihr ACP Verzeichnis mittels htaccess o.ä. um einen unbefugten Zutritt nicht zu ermöglichen.

3. Das Design von Litotex befindet sich im Ordner "themes\standard".
	 Dieses kann ganz nach den persönlichen Ansprüchen geändert werden.
		 
4. Für die automatische Punkteberechnung ist es norwendig einen Cronjob anzulegen.
	 Der Cronjob muss die Datei http://ihrewebseite.tld/includes/update.php aufrufen.	 
	 Wir empfehlen die Datei update.php umzubenennen.
	 
	 
INFORMATIONEN

1. Im offiziellen Supportforum, erreichbar unter http://litotex.de, erhalten Sie natürlich auf Wunsch
Hilfe. Desweiteren können Sie uns gerne Verbesserungsideen vorschlagen oder bei Interesse am Projekt mitwirken.


Ihr FreeBG-Team,
http://www.freebg.de
http://www.litotex.de

