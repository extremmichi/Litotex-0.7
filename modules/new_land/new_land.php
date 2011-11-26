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
$modul_name="new_land";
require_once("./../../includes/global.php");

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



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
$newland11 = $tpl ->get_config_vars('NEW_LAND_11');
$newland12 = $tpl ->get_config_vars('NEW_LAND_12');
$newland13 = $tpl ->get_config_vars('NEW_LAND_13');
$newland14 = $tpl ->get_config_vars('NEW_LAND_14');
$newland15 = $tpl ->get_config_vars('NEW_LAND_15');
$newland16 = $tpl ->get_config_vars('NEW_LAND_16');

if($action=="main") {
	$new_found_inhalt = array();
	$new_found = array();
	$new_found_inhalt_2 = array();
	$new_found_2 = array();
	if ($userdata['size']>$op_max_c_size) {
		$msize = $op_max_c_size;
	}
	if (isset($msize) AND $msize<$op_max_c_size) {
		$msize = $op_max_c_size;
	}
	$new_found_inhalt_2=array($userdata['sol_kolo'],$userdata['size'],$op_max_c_size);
	array_push($new_found_2,$new_found_inhalt_2);

	$result=$db->query("SELECT * FROM cc".$n."_new_land WHERE islandid='$userdata[islandid]' AND userid='$userdata[userid]' order by endtime");
	while($row=$db->fetch_array($result)) {
		$requesttime=$row['endtime']-time();
		if($requesttime>0) {
			timebanner_init(200,1);
			$timebanner = make_timebanner($row['starttime'],$row['endtime'],$row['koloid'],"new_land.php");
			$sol_name=get_soldiers_name("sol_kolo",$userdata['rassenid']);
		} else {
			$result3=$db->query("SELECT * FROM cc".$n."_crand WHERE x='$row[x]' AND y='$row[y]' AND used='0'");
			$map=$db->fetch_array($result3);
			if ($map) {
				$rand=rand(1,15);
				trace_msg("landgründung zufall: $rand win : 3,7,9,12",113);
				if($rand==3 || $rand==7 || $rand==9 || $rand==12) {
					$db->query("INSERT INTO cc".$n."_countries (race,res1,res2,res3,res4,userid,lastressources,picid,x,y,size) VALUES ('$userdata[rassenid]','$op_reg_res1','$op_reg_res2','$op_reg_res3','$op_reg_res4','$userdata[userid]','".time()."','".rand(1,4)."','$map[x]','$map[y]','".rand($op_min_c_size,$op_max_c_size)."')");
					$db->query("UPDATE cc".$n."_crand SET used='1' WHERE x='$map[x]' AND y='$map[y]'");
					make_ingamemail(0,$userdata['userid'],$newland11,$newland12);
				} else {
					$die=rand(1,10); //set the die value $die=rand(1,xx);
					trace_msg("landgründung $die 1 <> 1  come back",113);
					if($die!=1) {
						$db->query("UPDATE cc".$n."_countries SET sol_kolo=sol_kolo+'1' WHERE islandid='$userdata[islandid]'");
					} else {
						$add_mes = $newland13;
					}
					make_ingamemail(0,$userdata['userid'],$newland14,$newland16);
				}
			} else {
				$die=rand(1,10);
				trace_msg("landgründung no land found $die 1 <> 1  come back",113);
				if($die!=1) {
					$db->query("UPDATE cc".$n."_countries SET sol_kolo=sol_kolo+'1' WHERE islandid='$userdata[islandid]'");
				} else {
					$add_mes = $newland13;
				}
				make_ingamemail(0,$userdata['userid'],$newland14,$newland15);
			}
			$db->query("DELETE FROM cc".$n."_new_land WHERE koloid='$row[koloid]'");
			header("LOCATION: new_land.php");
		}
		$new_found_inhalt=array($sol_name,$row['x'],$row['y'],$timebanner);
		array_push($new_found,$new_found_inhalt);
	}
	$tpl->assign('daten', $new_found);
	$tpl->assign('daten2', $new_found_2);
	template_out('new_land.html',$modul_name);
	exit();
}


if($action=="send") {
	$do = $_POST['send'];
	if($userdata['sol_kolo']==0) {
		show_error('NEW_LAND_ERROR_1',$modul_name);
		exit();
	}

	if ($do == "send") {
		$x=intval($_POST['x']);
		$result=$db->query("Select max(x) as max_x from cc".$n."_crand");
		$row=$db->fetch_array($result);
		if($x<0 || !isset($x) || $x>$row['max_x']) {
			show_error('NEW_LAND_ERROR_2',$modul_name);
			exit();
		}
		$y=intval($_POST['y']);
		$result=$db->query("Select max(y) as max_y from cc".$n."_crand");
		$row=$db->fetch_array($result);
		if($y<0 || !isset($y) || $x>$row['max_y']) {
			show_error('NEW_LAND_ERROR_2',$modul_name);
			exit();
		}

		$result=$db->query("SELECT * FROM cc".$n."_countries WHERE userid='$userdata[userid]'");
		$numOfLands=$db->num_rows($result);
		$result2=$db->query("SELECT * FROM cc".$n."_new_land WHERE userid='".$userdata['userid']."'");
		$toNum=$db->num_rows($result2);
		if($numOfLands+$toNum>=$op_max_lands) {
			show_error('NEW_LAND_ERROR_3',$modul_name);
			exit();
		}

		//neue Berechnung für die Länge der Reise
		$sol_speed=get_soldiers_speed("sol_kolo",$userdata['rassenid']);
		$starttime = time();
		$an_time=get_duration_time($userdata['x'],$userdata['y'],$x,$y,$sol_speed);

		$db->query("INSERT INTO cc".$n."_new_land (userid,x,y,starttime,endtime,islandid) VALUES ('$userdata[userid]','$x','$y','".$starttime."','".$an_time."','$userdata[islandid]')");
	} else {
		if($userdata['size'] > $op_max_c_size) {
			show_error('NEW_LAND_ERROR_4',$modul_name);
			exit();
		}

		if($userdata['size'] == $op_max_c_size) {
			show_error('NEW_LAND_ERROR_4',$modul_name);
			exit();
		} elseif($userdata['size']+10 > $op_max_c_size){
			$userdata['size'] = $op_max_c_size;
		} else {
			$userdata['size']+=10;
		}

		$db->query("UPDATE cc".$n."_countries SET size=".$userdata['size']." WHERE islandid=".$userdata['islandid']);
	}
	$db->query("UPDATE cc".$n."_countries SET sol_kolo=sol_kolo-'1' WHERE islandid='$userdata[islandid]'");
	header("LOCATION: new_land.php");
	exit();
}

?>
