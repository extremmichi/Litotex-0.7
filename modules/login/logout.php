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
$modul_name="logout";
require("./../../includes/global.php");


if(!isset($_SESSION['userid'])) {
	show_error('LOGIN_ERROR','core');
	exit();
}

if (is_modul_name_aktive('login')==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}
if($action=="eng") {
        show_error("english version not<br>available yet",'login',0);
        exit();
}


/** set user inactive when logout **/
$db->query("UPDATE cc".$n."_users SET lastactive=lastactive-'3600' WHERE userid='".$_SESSION['userid']."'");

/** end a session time **/

session_unregister('userid');
session_unregister('ttest');
session_unregister('ttestid');


header("LOCATION: ".LITO_ROOT_PATH_URL.'index.php');


?>
