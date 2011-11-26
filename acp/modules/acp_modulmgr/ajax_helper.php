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

$filename="ajax_helper.php";

require("./../../includes/global.php");


if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

if($action=="make_new") {
	$the_id=intval($_GET['new_id']);


	$erlaubt = intval(ini_get('allow_url_fopen'));
	if ($erlaubt == 0 ){
		echo("error 0x2")	;
		exit();
	}


	$result=$db->query("SELECT modul_name,current_version FROM cc".$n."_modul_admin where modul_admin_id ='".$the_id."'");
	$row=$db->fetch_array($result);
	$module_name =trim($row['modul_name']);
	$curent_version =trim($row['current_version']);


	$in = "http://update.freebg.de/updinfo.php?action=v&m=".$module_name."&n=".LITO_ROOT_PATH_URL ;
	$version=file_get_contents($in );
	$version_check = compare_versions_sinus($curent_version,$version );
	$img="";
	if ($version_check ==1){
		$db->unbuffered_query("UPDATE cc".$n."_modul_admin SET new_upd_available ='1'  WHERE modul_name ='".$module_name."'");
		$upd_img="<img src=\"./../../images/standard/acp_modulmgr/info.png\">";
		$upd_img="<a href=\"datei.html\" onclick=\"fenster('http://update.freebg.de/updinfo.php?action=info&uname=$module_name');return false;\"><img src=\"./../../images/standard/acp_modulmgr/info.png\" alt=\"update Information\" title=\"Update Information\" width=\"20\" height=\"20\" border=\"0\">";
		$img="<a href=\"?action=remote_update&mod=".$module_name."\"><img src=\"./../../images/standard/acp_modulmgr/upd_ok.png\" border=\"0\"></a>".$upd_img;

	}elseif($version_check ==0){
		$img="<img src=\"./../../images/standard/acp_modulmgr/stop.png\">";
	}


	$out="$version.$img";

	echo($out);
	exit();

	for($i=0;$i < 20;$i++) {
		$out="bearbeite update ". $i."<br>";
		$all.=$out;

		echo($out);
		for($ii=0;$ii < 3;$ii++) {
			echo("...");
		}


	}
	echo('clean');
	echo($all);


}

?>