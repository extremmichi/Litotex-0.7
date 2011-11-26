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
$modul_name="screenshot";
require("./../../includes/global.php");



if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}


if($action=="main") {

	template_out('screenshot.html',$modul_name);
	exit();
}





?>
