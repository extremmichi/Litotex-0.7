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
$modul_name="impressum";
require("./../../includes/global.php");



if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

if($action=="main") {


	$tpl->assign('GAME_IMPRESSUM', $op_impressum);
	$tpl->assign('GAME_IMPRESSUM_ADMIN_NAME', $op_set_game_author);
	$tpl->assign('GAME_IMPRESSUM_GAME_NAME', $op_set_gamename);
	$tpl->assign('GAME_IMPRESSUM_GAME_URL', $op_set_game_url);
	$tpl->assign('GAME_IMPRESSUM_GAME_ADMINMAIL', $op_admin_email);
	$tpl->assign('GAME_IMPRESSUM_GAME_SUPMAIL', $op_support_email);


	template_out('impressum.html',$modul_name);
	exit();

}

?>
