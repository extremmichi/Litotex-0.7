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
require("./../../includes/global.php");

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="grouping";

if(!isset($_SESSION['userid'])) {
	show_error('LOGIN_ERROR','core');
	exit();
}

if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

if($action=="main") {

	$new_found_inhalt=array();
	$new_found=array();
	$new_found_inhalt_2=array();
	$new_found_2=array();

	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid='$userdata[activeid]' and group_status='0'");
	while($row=$db->fetch_array($result)) {
		$res1=$row['res1'];
		$res2=$row['res2'];
		$res3=$row['res3'];
		$res4=$row['res4'];
		$result_inhalt=$db->query("SELECT * FROM cc".$n."_groups_inhalt WHERE group_id='$row[groupid]'");
		$inhalt="";
		while($row_inhalt=$db->fetch_array($result_inhalt)) {

			$result_name=$db->query("SELECT * FROM cc".$n."_soldiers WHERE tabless='$row_inhalt[type]' AND race = '$userdata[rassenid]'");
			$row_name=$db->fetch_array($result_name);
			$inhalt .= trim($row_name['name'])." -> ".$row_inhalt['anzahl']."<br>";
		}
		$inhalt .="---------------<br>";
		$inhalt .="$op_set_n_res1: $res1<br>";
		$inhalt .="$op_set_n_res2: $res2<br>";
		$inhalt .="$op_set_n_res3: $res3<br>";
		$inhalt .="$op_set_n_res4: $res4<br>";

		$tt=make_tooltip_text($inhalt);
		$delete_url = "grouping.php?action=remove&groupid=".$row['groupid'];

		if ($res1 > 0 || $res2 > 0 || $res3 > 0 || $res4 > 0){
			$empty_url = "grouping.php?action=make_empty&id=".$row['groupid'];
		}else{
		$empty_url ="";
	}
	$new_found_inhalt=array($row['name'],$row['speed'],$tt,$delete_url,$empty_url );
	array_push($new_found,$new_found_inhalt);
}

$result_new=$db->query("SELECT * FROM cc".$n."_soldiers WHERE race='$userdata[rassenid]' AND sol_type = 0");
while($row_new=$db->fetch_array($result_new)) {
	$new_found_inhalt_2=array($row_new['name'],"\"".$row_new['tabless']."\"",$userdata[$row_new['tabless']]);
	array_push($new_found_2,$new_found_inhalt_2);
}
$tpl->assign('daten', $new_found);
$tpl->assign('daten2', $new_found_2);
template_out('grouping.html',$modul_name);
exit();
}


if($action=="formate") {

	$z = 0;
	$name = trim($_POST['name']);
	if ($name==""){
		$name=$userdata['name']."_".rand(1,299);
	}

	$low_speed=-1;
	$result_new=$db->query("SELECT * FROM cc".$n."_soldiers WHERE race='$userdata[rassenid]' AND sol_type = 0");
	while($row_new=$db->fetch_array($result_new)) {
		$tabless = $row_new['tabless'];
		$cur_speed=$row_new['traveltime'];

		if ($low_speed == -1){
			$low_speed=$cur_speed;
		}

		if(!empty($_POST[$tabless]) AND $_POST[$tabless] != 0) {

			if (intval($_POST[$tabless]) > $userdata[$tabless]){
				show_error('GROUPING_ERROR_1',$modul_name);
				exit();
			}

			//Thx to jacob
			if (intval($_POST[$tabless]) < 1){
				show_error('GROUPING_ERROR_1',$modul_name);
				exit();
			}

			if($z == 0) { //Beim ersten Durchlauf neue GRuppe erstellen


				$db->query("INSERT INTO cc".$n."_groups (name,islandid) VALUES ('$name','$userdata[activeid]')");
				$groupid=$db->insert_id();
			}

			if ($cur_speed < $low_speed){
				$low_speed=$cur_speed;
			}

			$db->query("INSERT INTO cc".$n."_groups_inhalt (group_id,anzahl,type) VALUES ('$groupid','$_POST[$tabless]','$tabless')");

			$db->query("UPDATE cc".$n."_countries SET $tabless = $tabless - $_POST[$tabless] WHERE islandid='$userdata[islandid]'");
			$z++;
		}
		$db->unbuffered_query("UPDATE cc".$n."_groups SET speed='$low_speed' WHERE groupid='$groupid'");
	}

	header("LOCATION: grouping.php");
	exit();
}

if($action=="remove") {
	$groupid=intval($_GET['groupid']);
	if(!$groupid) {
		show_error('GROUPING_ERROR_2',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE groupid='$groupid' AND islandid='$userdata[activeid]'");
	$row=$db->fetch_array($result);
	if($row['islandid']!=$userdata['activeid']) {
		show_error('GROUPING_ERROR_3',$modul_name);
		exit();
	}

	if($row['group_status'] != 0) {
		show_error('GROUPING_ERROR_2',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_groups_inhalt WHERE group_id='$row[groupid]'");

	while($row=$db->fetch_array($result)) {

		$db->query("UPDATE cc".$n."_countries SET $row[type]=$row[type]+'$row[anzahl]' WHERE islandid='$userdata[activeid]'");
	}

	$db->query("DELETE FROM cc".$n."_groups WHERE groupid='$groupid'");
	$db->query("DELETE FROM cc".$n."_groups_inhalt WHERE group_id='$groupid'");
	header("LOCATION: grouping.php");
	exit();
}

if($action=="make_empty") {

	$groupid=intval($_GET['id']);
	if(!$groupid) {
		show_error('GROUPING_ERROR_2',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE groupid='$groupid' and group_status='0' AND islandid='$userdata[activeid]'");
	$row=$db->fetch_array($result);
	if ($row['groupid']==""){
		show_error('GROUPING_ERROR_2',$modul_name);
		exit();
	}
	$g_res1=round($row['res1'],0);
	$g_res2=round($row['res2'],0);
	$g_res3=round($row['res3'],0);
	$g_res4=round($row['res4'],0);

	$db->query("UPDATE cc".$n."_countries SET res1=res1+'$g_res1',res2=res2+'$g_res2', res3=res3+'$g_res3', res4=res4+'$g_res4'   WHERE islandid='$userdata[activeid]' and 	userid ='$userdata[userid]'");
	$db->query("UPDATE cc".$n."_groups SET res1='0',res2='0', res3='0', res4='0'   WHERE islandid='$userdata[activeid]' and 	group_status=0");
	header("LOCATION: grouping.php");
}



?>
