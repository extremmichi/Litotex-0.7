<?PHP
/*
 	************************************************************
 	Litotex Browsergame - Engine
 	http://www.Litotex.de
 	http://www.freebg.de

  Copyright (c) 2008 FreeBG Team
 	************************************************************
	Hinweis:
  Diese Software ist urheberrechtlich gesch�tzt.

  F�r jegliche Fehler oder Sch�den, die durch diese Software
  auftreten k�nnten, �bernimmt der Autor keine Haftung.
  
  Alle Copyright - Hinweise innerhalb dieser Datei 
  d�rfen WEDER entfernt, NOCH ver�ndert werden. 
  ************************************************************
  Released under the GNU General Public License 
  ************************************************************  

 */



	@session_start(); 

	if (!isset ($_SESSION['litotex_start_acp'])){
		header('LOCATION: ./../../index.php');
		
		}
	
	
	require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

  $modul_name="acp_login";
  require(LITO_LANG_PATH.$modul_name."/lang_".$lang_suffix.".php");

  
  if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
  else $action="main";


function login_error($error_msg){
		global  $tpl;
		$modul_name="acp_login";
		$tpl ->assign('if_disable_menu',1);
		$tpl ->assign('LITO_ERROR_MSG', $error_msg);
    $tpl ->assign('if_login_error',1);
  	template_out('login.html',$modul_name);
	}




  if($action=="main") {
  	//$tpl ->display("login/login.html");
  	$tpl ->assign('if_disable_menu',1);
  	
		template_out('login.html',$modul_name);
  	exit();
  }






  if($action=="submit") {
  	
   $username=strtolower($_POST['username']);
   $password=c_trim($_POST['password']);


   if(!$username || !$password) {
  
    login_error($ln_login_e_1);
    exit();
    
   }

   $result=$db->query("SELECT * FROM cc".$n."_users WHERE username='$username'");
   $row=$db->fetch_array($result);

   if(strtolower($row['username'])!=$username) {
    trace_msg ("login ERROR '$username' wrong username",2);
    login_error($ln_login_e_2);
    exit();
   }

   if($row['password']!=md5($password)) {
    trace_msg ("login ERROR '$username' wrong password",2);
    login_error($ln_login_e_2);
    
    exit();
   }
   if(!$row['serveradmin']){
  $grp_q = $db->query("SELECT `perm_lvl`, `id` FROM `cc" .$n."_user_groups` WHERE `id` = '".$row['group']."'");
	$grp = $db->fetch_array($grp_q);
	if(!$grp){
		login_error('Schwerer Fehler! Die Usergruppe konnte nicht gefunden werden! Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
		exit;
	}
  if($grp['perm_lvl'] <= 0){
		login_error('Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
		exit();
	}

  
   }
		
    $userid=intval($row['userid']);
		$_SESSION['userid']=$userid;
		trace_msg ("login OK '$username' ",2);
   $db->unbuffered_query("UPDATE cc".$n."_users SET lastlogin='".time()."', ip='".getenv("REMOTE_ADDR")."' WHERE username='$username'");

    header("LOCATION: ".LITO_MODUL_PATH_URL.'acp_core/admin.php');

  exit();
  }

?>
