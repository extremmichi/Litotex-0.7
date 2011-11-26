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

$modul_name="news";

require("./../../includes/global.php");



if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}


if($action=="main") {
	$new_found_inhalt=array();
	$new_found=array();
	$result_news=$db->query("SELECT * FROM cc".$n."_news where activated='1' order by news_id desc");

	while($row_g=$db->fetch_array($result_news)) {
		$tt_text=$row_g['text'];

		$new_found_inhalt=array($row_g['news_id'],$row_g['date'],$tt_text,$row_g['heading'],$row_g['activated']);
		array_push($new_found,$new_found_inhalt);
	}
	$tpl->assign('daten', $new_found);
	template_out('news.html',$modul_name);
	exit();

}





?>
