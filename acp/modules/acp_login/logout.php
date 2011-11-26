<?PHP
/*
 	************************************************************
 	Litotex Browsergame - Engine
 	http://www.Litotex.de
 	http://www.freebg.de

  Copyright (c) 2008 FreeBG Team
 	************************************************************
	Hinweis:
  Diese Software ist urheberrechtlich geschützt.

  Für jegliche Fehler oder Schäden, die durch diese Software
  auftreten könnten, übernimmt der Autor keine Haftung.
  
  Alle Copyright - Hinweise innerhalb dieser Datei 
  dürfen WEDER entfernt, NOCH verändert werden. 
  ************************************************************
  Released under the GNU General Public License 
  ************************************************************  

 */
 @session_start(); 
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');


 if(!isset($_SESSION['userid'])){
 	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
 	exit();
 	}


 /** set user inactive when logout **/
 $db->query("UPDATE cc".$n."_users SET lastactive=lastactive-'3600' WHERE userid='".$_SESSION['userid']."'");

 /** end a session time **/
 
 session_unregister('userid');



 header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');

 
?>
