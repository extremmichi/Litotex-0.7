<?PHP
/*
 	************************************************************
 	Litotex BrowsergameEngine
 	http://www.Litotex.de
 	http://www.freebg.de

  Copyright (c) 2008 FreeBG Team
 	************************************************************
	Hinweis:
  Diese Software ist urheberechtlich geschützt.

  Für jegliche Fehler oder Schäden, die durch diese Software
  auftreten könnten, übernimmt der Autor keine Haftung.
  
  Alle Copyright - Hinweise Innerhalb dieser Datei 
  dürfen NICHT entfernt und NICHT verändert werden. 
  ************************************************************
  Released under the GNU General Public License 
  ************************************************************  
 */

@session_start(); 

	require('./includes/global.php');

  $modul_name="index"; 
 
 if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
  else $action="main";
  
  
if($action=="main") {
  	//$tpl ->display("login/login.html");
  	$tpl ->assign('if_disable_menu',1);
  	
		template_out('index.html',$modul_name);
  	exit();
  }

 
 
 
?>
