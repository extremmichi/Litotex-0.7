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

/*group_status
0 =home
1 =attack
2= back
*/

@session_start();
$modul_name="battle";
require("./../../includes/global.php");


if(!isset($_SESSION['userid'])) {
	show_error('LOGIN_ERROR','core');
	exit();
}

if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

timebanner_init(200,"bar_organge.gif");

if($action=="main") {

	//##########################
	//Anzeige gesendete Truppen#
	//##########################
	$z = 0;
	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid = '$userdata[activeid]' AND group_status = 1");
	$action_count=0;
	while($row=$db->fetch_array($result)) {
		$res1=$row['res1'];
		$res2=$row['res2'];
		$res3=$row['res3'];
		$res4=$row['res4'];

		$z++;
		$groupid = $row['groupid'];
		$attack_time = $row['traveltime'] + $op_battle_countdown_time;

		if($attack_time < time()) {
			header("LOCATION: battle.php?action=abort&gid=$groupid");
			exit();
		}

		$requesttime = $row['traveltime'] - time();
		$message_1="";
		$result_island=$db->query("SELECT * FROM cc".$n."_countries WHERE x = '$row[endx]' AND y = $row[endy]");
		$row_island=$db->fetch_array($result_island);
		$island_name = $row_island['name']." (".$row_island['x'].":".$row_island['y'].")";
		$to_islandid = $row_island['islandid'];
		$group_name=group($groupid);
		$groupid = $row['groupid'];
		$time = time();

		$result_inhalt=$db->query("SELECT * FROM cc".$n."_groups_inhalt WHERE group_id='$row[groupid]'");
		$inhalt="";
		while($row_inhalt=$db->fetch_array($result_inhalt)) {

			$result_name=$db->query("SELECT * FROM cc".$n."_soldiers WHERE tabless='$row_inhalt[type]' AND race = '$userdata[rassenid]'");
			$row_name=$db->fetch_array($result_name);
			$inhalt .= trim($row_name['name'])." -> ".$row_inhalt['anzahl']."<br>";
		}

		if($requesttime > 0) {
			$z++;
			$message=make_timebanner($row['starttime'],$row['traveltime'],$z,"battle.php");
		} else {
			$z++;
			$attack_request_time = $attack_time - time();
			$countdown =make_timebanner($attack_request_time ,$attack_time ,$z,"battle.php");

			$message_1="<form name=\"angriff\" action=\"battle.php?action=angriff\" method=\"post\">
			<input type=\"hidden\" name=\"groupid\" value=\"$groupid\">
			<input type=\"hidden\" name=\"to_islandid\" value=\"$to_islandid\">
			<input type=\"submit\" class=\"button\" name=\"submit\" value=\"Angriff\">
			</form> ";

			$message=$countdown;
		}
		$inhalt .="---------------<br>";
		$inhalt .="$op_set_n_res1: $res1<br>";
		$inhalt .="$op_set_n_res2: $res2<br>";
		$inhalt .="$op_set_n_res3: $res3<br>";
		$inhalt .="$op_set_n_res4: $res4<br>";
		$battle_action[$action_count]['tt']=make_tooltip_text($inhalt );
		$battle_action[$action_count]['group_name']=$group_name;
		$battle_action[$action_count]['island_name']=$island_name;
		$battle_action[$action_count]['message']=$message;
		$battle_action[$action_count]['groupid']=$groupid;
		$battle_action[$action_count]['message_1']= $message_1;

		$action_count++;

	}
	$tpl->assign('daten_battle_action', $battle_action);
	//################
	//Anzeige Rückzug#
	//################
	//$z=0;
	$battle_back_count=0;
	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid = '$userdata[activeid]' AND group_status = 2");
	while($row=$db->fetch_array($result)) {
		$res1=$row['res1'];
		$res2=$row['res2'];
		$res3=$row['res3'];
		$res4=$row['res4'];
		$z++;
		$group_name = $row['name'];
		$requesttime = $row['traveltime'] - time();
		if($requesttime > 0) {

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


			$backurl="battle.php";
			$message=make_timebanner($row['starttime'],$row['traveltime'],$z,"battle.php","progressBar_blue");

			$target_name=get_countrie_name_from_id($row['islandid'],1);
			$battle_back[$battle_back_count]['tt']=make_tooltip_text($inhalt );
			$battle_back[$battle_back_count]['island_name']=$target_name;
			$battle_back[$battle_back_count]['group_name']=$group_name;
			$battle_back[$battle_back_count]['message']=$message;

			$battle_back_count++;

		} else {
			$db->query("UPDATE cc".$n."_groups SET group_status = 0, traveltime = 0, endx = 0, endy = 0 WHERE groupid='$row[groupid]'");
			header("LOCATION: battle.php");
			exit();
		}
	}
	$tpl->assign('daten_battle_back', $battle_back);


	$time = time();
	$count=0;
	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE endy = '$userdata[y]' AND endx = $userdata[x] AND group_status = 1");
	$battle_def_count=0;
	while($row=$db->fetch_array($result)) {
		$z++;
		$group_name = $row['name'];
		$time = time();

		if($row['traveltime'] > $time) {

			$requesttime = $row['traveltime'] - $time;
			if($requesttime > 0) {

				$request_counter = make_timebanner($row['starttime'],$row['traveltime'],$z,"battle.php","progressBar_red");
			} else {
				$request_counter = "Vor Ort";
			}
		} else{
		$request_counter = "Vor Ort";
	}

	$requesttime_battle = $row['traveltime'];
	$requesttime_battle_end = ($row['traveltime'] + $op_battle_countdown_time) ;
	$requesttime_battle_total_end = $requesttime_battle_end  - $time;


	if ($requesttime_battle_total_end  > $op_battle_countdown_time){
		$request_battle_counter ="auf den weg zum Ziel";
	}else{

	if($requesttime_battle_total_end > 0) {
		$z++;
		$request_battle_counter = make_timebanner($row['traveltime'] ,$requesttime_battle_end,$z,"battle.php","progressBar_red");
	} else {
		$request_battle_counter = "Beendet";
	}
}
$result_angreifer=$db->query("SELECT * FROM cc".$n."_countries WHERE islandid='$row[islandid]'");
$row_angreifer=$db->fetch_array($result_angreifer);

$angreifer_land = $row_angreifer['name']." (".$row_angreifer['x'].":".$row_angreifer['y'].")";
$angreifer_user_id = $row_angreifer['userid'];

$result_angreifer=$db->query("SELECT * FROM cc".$n."_users WHERE userid='$angreifer_user_id'");
$row_angreifer=$db->fetch_array($result_angreifer);

$angreifer_name = $row_angreifer['username'];

$count++;

$battle_def[$battle_def_count]['angreifer_name']=$angreifer_name;
$battle_def[$battle_def_count]['angreifer_land']=$angreifer_land;
$battle_def[$battle_def_count]['group_name']=$group_name;
$battle_def[$battle_def_count]['request_counter']=$request_counter;
$battle_def[$battle_def_count]['request_battle_counter']=$request_battle_counter;

$battle_def_count++;

}
$tpl->assign('daten_battle_def', $battle_def);

$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid = '$userdata[activeid]' AND group_status = 0");
while($row=$db->fetch_array($result)) {
	$groups .= "<option value=\"".$row['groupid']."\">".$row['name']."</option>";
}

$tpl->assign('groups', $groups);
template_out('battle.html',$modul_name);
exit();
}
if($action=="send") {

	$endx=intval($_POST['endx']);
	$endy=intval($_POST['endy']);
	$groupid=intval($_POST['groupid']);

	if(!$endx || $endx < 0 || !$endy || $endy < 0) {
		show_error('LM_system_e',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_countries WHERE x='$endx' AND y='$endy'");
	$row=$db->fetch_array($result);
	$target_user_id=$row['userid'];
	$result2=$db->query("SELECT * FROM cc".$n."_users WHERE userid='".$row['userid']."'");
	$row2=$db->fetch_array($result2);

	$time_since_register = time() - $row2['register_date'];
	$noob_time_seconds = ((($op_noob_time*24)*60)*60);

	if($row['points'] < $op_noob_points || $time_since_register < $noob_time_seconds) {
		show_error('LN_battle_noob',$modul_name);
		exit();
	}

	if($row['x']!=$endx || $row['y']!=$endy) {
		show_error('LN_spion_e_2',$modul_name);
		exit();
	}
	if($row['islandid'] == $userdata['activeid']) {
		show_error('LN_battle_error_1',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE groupid='$groupid' ");
	$row=$db->fetch_array($result);

	$traveltime=get_duration_time  ($userdata['x'],$userdata['y'],$endx,$endy,$row['speed']);
	$starttime=time();
	$db->query("UPDATE cc".$n."_groups SET to_userid='$target_user_id' ,group_status = 1,starttime=$starttime, traveltime = $traveltime, endx = $endx, endy = $endy WHERE groupid='$groupid'");
	header("LOCATION: battle.php");
	exit();
}
if($action=="abort") {
	$groupid=intval($_GET['gid']);

	$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid = '$userdata[activeid]' AND groupid = $groupid");
	$row=$db->fetch_array($result);

	$old_traveltime=get_duration_time  ($userdata['x'],$userdata['y'],$row['endx'],$row['endy'],$row['speed']);

	$requesttime = $row['traveltime'] - time();

	if($requesttime < 0) $requesttime = 0;

	$back_traveltime = $old_traveltime - $requesttime;
	$starttime=time();
	$db->query("UPDATE cc".$n."_groups SET to_userid='$userdata[userid]',group_status = 2, starttime=$starttime,traveltime = $back_traveltime, endx = $userdata[x], endy = $userdata[y] WHERE groupid='$groupid'");
	header("LOCATION: battle.php");
	exit();
}
if($action=="angriff") {
	$group_id = intval($_POST['groupid']);
	$to_islandid = intval($_POST['to_islandid']);


	require("./battle_ks.php");

	ks_battle($group_id, $to_islandid,0);
	exit();
}
?>
