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
$modul_name="login";
require("./../../includes/global.php");




if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}


if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";




if($action=="main") {
	$tpl ->assign('if_disable_menu',1);
	template_out('login.html',$modul_name);
	exit();
}


if($action=="submit") {

	$username=strtolower($_POST['username']);
	$password=c_trim($_POST['password']);

	if(!$username || !$password) {
		show_error("LOGIN_ERROR_1",'login');
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_users WHERE username='$username'");
	$row=$db->fetch_array($result);

	if(strtolower($row['username'])!=$username) {
		trace_msg ("login ERROR '$username' wrong username",2);
		show_error("LOGIN_ERROR_2",'login');
		exit();
	}

	if($row['password']!=md5($password)) {
		trace_msg ("login ERROR '$username' wrong password",2);
		show_error("LOGIN_ERROR_2",'login');
		exit();
	}
	$userid=intval($row['userid']);

	$_SESSION['userid']=$userid;
	trace_msg ("login OK '$username' ",2);
	$db->unbuffered_query("UPDATE cc".$n."_users SET lastlogin='".time()."', ip='".getenv("REMOTE_ADDR")."' WHERE username='$username'");
	header("LOCATION: ".LITO_MODUL_PATH_URL.'members/members.php');
	exit();
}

?>
