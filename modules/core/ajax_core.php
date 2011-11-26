<?PHP /* ************************************************************ Litotex
Browsergame - Engine http://www.Litotex.de http://www.freebg.de

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
$modul_name="ajax_core";
require("./../../includes/global.php");

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";


if(!isset($_SESSION['userid'])) {
	echo("error");
	exit();
}

if($action=="main") {

	echo("main");
}
if($action=="gettime"){
	echo(date("d.m.Y H:i:s",time()));
	exit();
}

if($action=="get_b_count"){

	if (intval($userdata['rassenid']) <= 0){
		exit();
	}


	$result=$db->query("SELECT count(groupid) as anz  FROM cc".$n."_groups where group_status =1 and to_userid ='".$userdata['userid']."'  ");
	$row=$db->fetch_array($result);
	if (intval($row['anz'])>0 ){

		$module=get_modulname(9);
		$battle_modul_org="./../".$module[0]."/".$module[1];
		$ret_msg="<a href=\"$battle_modul_org\"><img src=\"".LITO_IMG_PATH_URL.$module[0]."/battle.png\" border=\"0\"> Du wirst von ". $row['anz']." Gruppe(n) angegriffen !!!</a>";

	}else{
	$ret_msg="";

}
echo($ret_msg);

}
if($action=="get_msg_count"){

	if (intval($userdata['rassenid'])<=0){
		exit();
	}


	$new_msg_count=get_new_msg_count();

	if (intval($new_msg_count)> 0 ){
		$module=get_modulname(6);
		$msg_modul_org="./../".$module[0]."/".$module[1];
		$ret_msg="<a href=\"$msg_modul_org\"><img src=\"".LITO_IMG_PATH_URL.$module[0]."/newpost.png\" border=\"0\"> {$new_msg_count} neue Nachrichten</a>";
	}else{
	$ret_msg="";

}
echo($ret_msg);
}

?>