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
require("./../../includes/global.php");

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="buildings";

if(!isset($_SESSION['userid'])) {
	show_error('LOGIN_ERROR','core');
	exit();
}

if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

$lang_file=LITO_LANG_PATH.$modul_name.'/lang_'.$lang_suffix.'.php';
$tpl->config_load($lang_file);
$building1 = $tpl ->get_config_vars('BUILDING_1');
$building9 = $tpl ->get_config_vars('BUILDING_9');
$building10 = $tpl ->get_config_vars('BUILDING_10');
$building11 = $tpl ->get_config_vars('BUILDING_11');
$buildingabort = $tpl ->get_config_vars('BUILDING_ABORT');
$buildingerror1 = $tpl ->get_config_vars('BUILDING_ERROR_1');

$file = LITO_MODUL_PATH_URL.$modul_name.'/buildings.php';

if($action=="main") {
	timebanner_init(200,1);
	if($userdata['isbuilding'] == "1" AND $userdata['endbuildtime'] > time()) {
		$show_bau=1;

	}elseif($userdata['isbuilding'] == "1" AND $userdata['endbuildtime'] <= time()){

		if (is_build_id_present($userdata['bid'])==0){
			$db->query("UPDATE cc".$n."_countries SET  bid='0', isbuilding='0', startbuildtime='0', endbuildtime='0' WHERE islandid='".$userdata['islandid']."'");
			header("LOCATION: buildings.php");
			exit();
		}

		$result=$db->query("SELECT * FROM cc".$n."_buildings WHERE bid='".$userdata['bid']."'");
		$row=$db->fetch_array($result);

		$build_count=$db->num_rows($result);

		if ($build_count > 0 ) {
			$db->query("UPDATE cc".$n."_countries SET ".$row['tabless']."=".$row['tabless']."+'1', bid='0', isbuilding='0', startbuildtime='0', endbuildtime='0' WHERE islandid='".$userdata['islandid']."'");
		}

		header("LOCATION: buildings.php");
		exit();
	}

	$new_found_inhalt = array();
	$new_found = array();
	$result_buildings=$db->query("SELECT * FROM cc".$n."_buildings where race = ".$userdata['rassenid']." and (tabless !='' and tabless !='0' ) and( require1 <= '".$userdata['build_town']."' and require2 <= '".$userdata['build_explore']."')ORDER BY p ASC");
	while($row_buildings=$db->fetch_array($result_buildings)) {
		$in_bau = $userdata['bid'];
		$build_bid = $row_buildings['bid'];
		$es_wird_gebaut = 0;

		if(isset($show_bau) AND $show_bau==1){
			if($in_bau==$build_bid){
				$es_wird_gebaut = 1;
				$cancelURL = "buildings.php?bid=$in_bau&action=del";
				$message = make_timebanner($userdata['startbuildtime'],$userdata['endbuildtime'],$build_bid,"buildings.php")."<br><a href=\"$cancelURL\">".$buildingabort."</a>";
			}
			else{
			$es_wird_gebaut = 1;
			$message = $building1;
		}
	}else{
	$res1 = $row_buildings['res1']*($userdata[$row_buildings['tabless']]+1);
	$res2 = $row_buildings['res2']*($userdata[$row_buildings['tabless']]+1);
	$res3 = $row_buildings['res3']*($userdata[$row_buildings['tabless']]+1);
	$res4 = $row_buildings['res4']*($userdata[$row_buildings['tabless']]+1);

	if($userdata['res1']>=$res1 && $userdata['res2']>=$res2 && $userdata['res3']>=$res3 && $userdata['res4']>=$res4) {
		$message="<a href='".$file."?bid=$row_buildings[bid]&action=build&cxid=$sid'>$building9</a>";
	}else{
	$message = $building11;
	$tofew_res1 = $res1 - $userdata['res1'];  if($tofew_res1>0) $message = $message."<br>".$op_set_n_res1.": ".$tofew_res1;
	$tofew_res2 = $res2 - $userdata['res2'];  if($tofew_res2>0) $message = $message."<br>".$op_set_n_res2.": ".$tofew_res2;
	$tofew_res3 = $res3 - $userdata['res3'];  if($tofew_res3>0) $message = $message."<br>".$op_set_n_res3.": ".$tofew_res3;
	$tofew_res4 = $res4 - $userdata['res4'];  if($tofew_res4>0) $message = $message."<br>".$op_set_n_res4.": ".$tofew_res4;
}
$message = $message."<br><br><a href='".$file."?bid=$row_buildings[bid]&action=remove&&cxid=$sid'>$building10</a>";
}
$size=$userdata[$row_buildings['tabless']];
$size_new=$userdata[$row_buildings['tabless']]+1;
$buildtime=sec2time($row_buildings['time']*$size_new);

$row_buildings['res1']=$row_buildings['res1']*($userdata[$row_buildings['tabless']]+1);
$row_buildings['res2']=$row_buildings['res2']*($userdata[$row_buildings['tabless']]+1);
$row_buildings['res3']=$row_buildings['res3']*($userdata[$row_buildings['tabless']]+1);
$row_buildings['res4']=$row_buildings['res4']*($userdata[$row_buildings['tabless']]+1);

$build_name=$row_buildings['name'];

$build_description=$row_buildings['description'];

if($build_name ==""){
	$build_name ="<span class=\"redfont\">".$buildingerror1."</span>";
}
if($build_description ==""){
	$build_description ="<span class=\"redfont\">".$buildingerror1."</span>";
}
if($size_new==""){
	$size="<span class=\"redfont\">".$buildingerror1."</span>";
}

$kost1=$op_set_n_res1.": ".$row_buildings['res1'];
$kost2=$op_set_n_res2.": ".$row_buildings['res2'];
$kost3=$op_set_n_res3.": ".$row_buildings['res3'];
$kost4=$op_set_n_res4.": ".$row_buildings['res4'];

if(trim($row_buildings['tabless']) ==""){
	$message="<span class=\"redfont\">".$buildingerror1."</span>";
}
$image = "\"".$row_buildings['buildpic']."\"";
$new_found_inhalt=array($image,$build_name,$size,$build_description,$row_buildings['res1'],$row_buildings['res2'],$row_buildings['res3'],$row_buildings['res4'],$buildtime,$row_buildings['size'],$message,$userdata['size'],$userdata['usesize']);
array_push($new_found,$new_found_inhalt);
}

$tpl->assign('daten', $new_found);
template_out('buildings.html',$modul_name);
exit();
}


if($action=="build") {
	$bid=intval($_GET['bid']);
	if(!$bid) {
		show_error('BUILDING_ERROR_2',$modul_name);
		exit();
	}
	$result=$db->query("SELECT * FROM cc".$n."_buildings WHERE bid='$bid'");
	$row=$db->fetch_array($result);

	if($row['size']>$userdata['size']-$userdata['usesize']) {
		show_error('BUILDING_ERROR_3',$modul_name);
		exit();
	}

	if($userdata['build_town'] < $row['require1'] || $userdata['build_explore']<$row['require2']) {
		show_error('BUILDING_ERROR_4',$modul_name);
		exit();
	}

	$res1=$row['res1']*($userdata[$row['tabless']]+1);
	$res2=$row['res2']*($userdata[$row['tabless']]+1);
	$res3=$row['res3']*($userdata[$row['tabless']]+1);
	$res4=$row['res4']*($userdata[$row['tabless']]+1);
	$size=$userdata[$row['tabless']]+1;

	$startbuildtime = time();
	$endbuildtime = $startbuildtime + ($row['time']*$size);

	if($userdata['res1']>=$res1 && $userdata['res2']>=$res2 && $userdata['res3']>=$res3 && $userdata['res4']>=$res4 && $userdata['bid']==0) {
		$db->query("UPDATE cc".$n."_countries SET bid='$bid', res1=res1-'$res1', res2=res2-'$res2', res3=res3-'$res3', res4=res4-'$res4', startbuildtime='$startbuildtime', endbuildtime='$endbuildtime', isbuilding='1', usesize=usesize+".$row['size']." WHERE islandid='".$userdata['activeid']."'");
		header("LOCATION: buildings.php?cxid=$sid");
		exit();
	}
	show_error('BUILDING_ERROR_5',$modul_name);
	exit();
}

if($action=="remove") {
	$bid = intval($_REQUEST['bid']);
	if(!$bid) {
		show_error('BUILDING_ERROR_2',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_buildings WHERE bid='$bid'");
	$row=$db->fetch_array($result);

	if($userdata[$row['tabless']]==0) {
		show_error('BUILDING_ERROR_6',$modul_name);
		exit();
	}

	if($userdata['usesize']==0) {
		$row['size'] = "0";
	}

	$res1=$row['res1']*($userdata[$row['tabless']]+1)*($op_credit_demolition/100);
	$res2=$row['res2']*($userdata[$row['tabless']]+1)*($op_credit_demolition/100);
	$res3=$row['res3']*($userdata[$row['tabless']]+1)*($op_credit_demolition/100);
	$res4=$row['res4']*($userdata[$row['tabless']]+1)*($op_credit_demolition/100);

	$db->query("UPDATE cc".$n."_countries SET usesize=usesize-'".$row['size']."', $row[tabless]=$row[tabless]-'1', res1=res1+'$res1', res2=res2+'$res2', res3=res3+'$res3', res4=res4+'$res4' WHERE islandid='".$userdata['activeid']."'");

	header("LOCATION: buildings.php");
	exit();
}

if($action=="del") {
	$bid=intval($_GET['bid']);

	$result_b=$db->query("SELECT bid FROM cc".$n."_countries WHERE islandid='".$userdata['activeid']."' ");
	$row_in_b=$db->fetch_array($result_b);
	$in_bau=intval($row_in_b['bid']);
	if($in_bau <= 0){
		show_error('BUILDING_ERROR_7',$modul_name);
		exit();
	}

	$in_bau=intval($row_in_b['bid']);
	if($in_bau != $bid){
		show_error('BUILDING_ERROR_2',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_buildings WHERE bid='$bid' ");
	$row=$db->fetch_array($result);
	$in_bau=$row['bid'];

	$bau_size=$userdata[$row['tabless']]+1;
	$bau_size=$bau_size*$bau_size;
	$unew_size = $row['size'];

	$res1=$row['res1']*($userdata[$row['tabless']]+1)*($op_credit_cancel/100);
	$res2=$row['res2']*($userdata[$row['tabless']]+1)*($op_credit_cancel/100);
	$res3=$row['res3']*($userdata[$row['tabless']]+1)*($op_credit_cancel/100);
	$res4=$row['res4']*($userdata[$row['tabless']]+1)*($op_credit_cancel/100);

	$db->query("UPDATE cc".$n."_countries SET bid='0', usesize=usesize-'".$unew_size."', isbuilding='0', startbuildtime='0', endbuildtime='0', res1=res1+'$res1', res2=res2+'$res2', res3=res3+'$res3', res4=res4+'$res4' WHERE islandid='".$userdata['activeid']."'");
	header("LOCATION: buildings.php");
	exit();
}

?>

