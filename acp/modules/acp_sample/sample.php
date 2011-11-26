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
 
 if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
  else $action="main";

$modul_name="acp_sample";
 


  if($action=="main") {
	 
	  $sql="SELECT * from  cc".$n."_users";
	$result_users=$db->query($sql);
  
  while($row_g=$db->fetch_array($result_users)) {
  $daten[] = $row_g; 
  }
  
  
  $tpl->assign('daten', $daten); 
   $tpl->assign('test1', "http://www.blabla.de"); 
	$test="www";
	
	 template_out('sample.html',$modul_name);
  	exit();
  }





?>
