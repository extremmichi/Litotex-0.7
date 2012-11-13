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
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_units";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
$menu_name="Einheiteneditor";
$tpl->assign( 'menu_name',$menu_name);

if($action == "cp"){ //By GH1234 AK Jonas Schwabe
	if(!isset($_POST['cpfrom']) || !isset($_POST['cpto'])){
		error_msg('Es wurden nicht alle nötigen Daten übergeben.');
		exit;
	}
	$from = $_POST['cpfrom'] * 1;
	$to = $_POST['cpto'] * 1;
	if($from == $to){
		error_msg('Es ist nicht möglich auf die gleiche Rasse zu kopieren! Dies würde zu Datenverlust führen.');
		exit;
	}
	//Überprüfe ob die Rassen existieren... soll ja vorkommen...
	$race_to_q = $db->query("SELECT `rassenid` FROM `cc".$n."_rassen` WHERE `rassenid` = '".$to."'");
	$race_from_q = $db->query("SELECT `rassenid` FROM `cc".$n."_rassen` WHERE `rassenid` = '".$from."'");
	if(!$db->num_rows($race_from_q) || !$db->num_rows($race_to_q)){
		error_msg('Rasse existiert nicht.');
		exit;
	}
	$db->query("DELETE FROM `cc".$n."_soldiers` WHERE `race` = '".$to."'");
	$units_q = $db->query("SELECT * FROM `cc".$n."_soldiers` WHERE `race` = '".$from."'");
	while($unit = $db->fetch_array($units_q)){
		$db->query("INSERT INTO `cc".$n."_soldiers` (`name`, `tabless`, `res1`, `res2`, `res3`, `res4`, `stime`, `description`, `AP`, `VP`, `race`, `traveltime`, `required`, `required_level`, `points`, `solpic`, `sol_type`) VALUES ('".$db->escape_string($unit['name'])."', '".$unit['tabless']."', '".$unit['res1']."', '".$unit['res2']."', '".$unit['res3']."', '".$unit['res4']."', '".$unit['stime']."', '".$db->escape_string($unit['description'])."', '".$unit['AP']."', '".$unit['VP']."', '".$to."', '".$unit['traveltime']."', '".$unit['required']."', '".$unit['required_level']."', '".$unit['points']."', '".$unit['solpic']."', '".$unit['sol_type']."')");
	}
	$action = "main";
}

if($action=="main") {

	$ras="";
	$build_option="";
	$sol_type_def="";
	$load_name="";
	$load_ress_1="";
	$load_ress_2="";
	$load_ress_3="";
	$load_ress_4="";
	$load_size="";
	$load_points="";
	$load_buildtime="";
	$load_ap="";
	$load_vp="";
	$load_req_level="";
	$load_b_pic="";
	$description="";
	$error_buildings="";
	$id="";
	$positions=0;






	$make_aktion="action=new&cxid=$sid" ;
	$build_option=make_soldier_option_choice("build_option","");
	$required=make_explore_option_choice("required","");
	$sol_type_def=make_soldier_type_choice("sol_type","");
	$not_show=0;

	$load_b_pic=LITO_IMG_PATH_URL."acp_units/keins.png";


	$result=$db->query("SELECT * FROM cc".$n."_rassen");
	$race_count=0;
	$out_race= array();
	while($row_ras=$db->fetch_array($result)) {
		$race_count++;
		$out_race[] = $row_ras;
	}
	$tpl->assign('race_all', $out_race);



	$result_options=$db->query("SELECT * FROM cc".$n."_soldiers_option");
	$out_units= array();
	$count=0;
	while($row_b_option=$db->fetch_array($result_options)) {
		$name=$row_b_option['description'];
		$name_tag=$row_b_option['tabless'];
		$value[0]=$name;
		for($i=1;$i <= $race_count;$i++) {
			//werte f�r die rassen suchen
			$value[$i]="undefiniert";
			$result=$db->query("SELECT * FROM cc".$n."_soldiers where race='$i' and tabless='$name_tag' and required !='' and required_level > 0");
			$count=0;
			while($row_ras=$db->fetch_array($result)) {
				$new_b_id=$row_ras['sid'];
				$del_link="<a href=\"edit_units.php?action=del&del_id=$new_b_id\"><img src=\"".LITO_IMG_PATH_URL."acp_units/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
				$value[$i]=$del_link."<a href=\"edit_units.php?id=".$new_b_id."\">".$row_ras['name']."</a> ";




			}

		}


		array_push($out_units,$value);

	}

	$tpl->assign( 'units',$out_units);
	$lade_id=0;
	if (isset($_GET['id'])){
		$lade_id=intval($_GET['id']);
	}
	if ($lade_id > 0 ){
		$not_show=1;
		$result_loader=$db->query("SELECT * FROM cc".$n."_soldiers where sid='$lade_id '");

		while($row_load=$db->fetch_array($result_loader)) {
			$load_name	=$row_load['name'];
			$load_ress_1=$row_load['res1'];
			$load_ress_2=$row_load['res2'];
			$load_ress_3=$row_load['res3'];
			$load_ress_4=$row_load['res4'];
			$load_size=$row_load['traveltime'];
			$load_points=$row_load['points'];
			$load_buildtime=$row_load['stime'];
			$load_b_pic=$row_load['solpic'];
			$positions=$row_load['p'];
			if ($load_b_pic=="" ){
				$load_b_pic=LITO_IMG_PATH_URL."acp_units/keins.png";
			}
			$load_ap=$row_load['AP'];
			$load_vp=$row_load['VP'];
			$load_req_level=$row_load['required_level'];
			$required=make_explore_option_choice("required",$row_load['required']);
			$ras=make_race_choice("rasse",$row_load['race']);
			$build_option=make_soldier_option_choice("build_option",$row_load['tabless']);
			$make_aktion="action=update&cxid=$sid&id=$lade_id" ;
			$description=$row_load['description'];
			$sol_type_def=make_soldier_type_choice("sol_type",$row_load['sol_type']);
		}
	}else{

	$ras=make_race_choice("rasse",0);
}

//load error buildings
$result_error=$db->query("SELECT * FROM cc".$n."_soldiers where race='' or tabless='' or tabless='0' or required='' or required_level='0'");
$count=0;
$error_buildings="";
while($row_b_error=$db->fetch_array($result_error)) {
	$name=$row_b_error['name'];
	$name_id=$row_b_error['sid'];
	$del_link="<a href=\"edit_units.php?action=del&del_id=$name_id\"><img src=\"".LITO_IMG_PATH_URL."acp_units/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
	$new_name=$del_link."<a href=\"edit_units.php?id=$name_id\">$name</a> ";
	$error_buildings.=$new_name."<br><br>";

}


$tpl->assign('ras', $ras);
$tpl->assign('build_option', $build_option);
$tpl->assign('sol_type_def', $sol_type_def);
$tpl->assign('load_name', $load_name);
$tpl->assign('load_ress_1', $load_ress_1);
$tpl->assign('load_ress_2', $load_ress_2);
$tpl->assign('load_ress_3', $load_ress_3);
$tpl->assign('load_ress_4', $load_ress_4);
$tpl->assign('load_size', $load_size);
$tpl->assign('load_points', $load_points);
$tpl->assign('load_buildtime', $load_buildtime);
$tpl->assign('load_ap', $load_ap);
$tpl->assign('load_vp', $load_vp);
$tpl->assign('load_req_level', $load_req_level);
$tpl->assign('load_b_pic', $load_b_pic);
$tpl->assign('description', $description);
$tpl->assign('make_aktion', $make_aktion);
$tpl->assign('required', $required);
$tpl->assign('error_buildings', $error_buildings);
$tpl->assign('positions', $positions);



template_out('edit_units.html',$modul_name);
exit();
}



if($action=="update") {
	$name=trim($_POST['buildingname']);

	$rasse=intval($_POST['rasse']);

	$gold=intval($_POST['kost1']);
	$stone=intval($_POST['kost2']);
	$oil=intval($_POST['kost3']);
	$exp=intval($_POST['kost4']);

	//$bauzeit=intval($_POST['bauzeit']);
	$reisezeit=intval($_POST['traveltime']);

	$einmheiten_id =intval($_GET['id']);

	$ap=intval($_POST['value_ap']);
	$vp=intval($_POST['value_vp']);
	$point=intval($_POST['points']);
	$build_time=intval($_POST['buildtime']);


	$required=trim($_POST['required']);
	$required_level=intval($_POST['required_level']);
	$build_pic=trim($_POST['buildpic']);

	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);
	$positions=intval($_POST['positions']);
	$sol_type=intval($_POST['sol_type']);

	$kurz=get_soldiers_tabless_name($b_option);



	$update=$db->query("UPDATE cc".$n."_soldiers set tabless='' and race='' where tabless='$b_option' and race='$rasse'");

	$update=$db->query("UPDATE cc".$n."_soldiers SET p='$positions',sol_type='$sol_type', solpic='$build_pic',points='$point',required_level='$required_level',tabless='$kurz',description='$description',name='$name',res1='$gold',res2='$stone',res3='$oil',res4='$exp',stime='$build_time',race='$rasse',traveltime='$reisezeit',AP='$ap',VP='$vp',required='$required' WHERE sid='$einmheiten_id' ");

	header("LOCATION: edit_units.php");

}

if($action=="new") {
	$name=trim($_POST['buildingname']);

	$rasse=intval($_POST['rasse']);

	$gold=intval($_POST['kost1']);
	$stone=intval($_POST['kost2']);
	$oil=intval($_POST['kost3']);
	$exp=intval($_POST['kost4']);

	//$bauzeit=intval($_POST['bauzeit']);
	$reisezeit=intval($_POST['traveltime']);

	$ap=intval($_POST['value_ap']);
	$vp=intval($_POST['value_vp']);
	$point=intval($_POST['points']);
	$build_time=intval($_POST['buildtime']);


	$required=trim($_POST['required']);
	$required_level=intval($_POST['required_level']);
	$build_pic=trim($_POST['buildpic']);

	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);

	$sol_type=intval($_POST['sol_type']);

	$kurz=get_soldiers_tabless_name($b_option);

	$update=$db->query("UPDATE cc".$n."_soldiers set tabless='' and race='' where tabless='$b_option' and race='$rasse'");


	$update=$db->query("insert Into cc".$n."_soldiers (sol_type,description,tabless,required_level,points,solpic,name,res1,res2,res3,res4,stime,race,traveltime,AP,VP,required)VALUES ('$sol_type','$description','$kurz','$required_level','$point','$build_pic','$name','$gold','$stone','$oil','$exp','$build_time','$rasse','$reisezeit','$ap','$vp','$required') ");


	$ret=if_spalte_exist($kurz,"cc".$n."_countries");
	if ($ret== 0 ){
		$update=$db->query("ALTER TABLE cc".$n."_countries ADD ".$kurz." INT( 10 ) NOT NULL DEFAULT '0'");
	}
	header("LOCATION: edit_units.php");

}

if($action=="del") {
	$del_id=intval($_GET['del_id']);

	if ($del_id<= 0 ){
		admin_error_page("$ln_error_20");
		exit();
	}
	// search for old
	$result=$db->query("SELECT tabless,sid FROM cc".$n."_soldiers WHERE sid='".$del_id."'");
	$row=$db->fetch_array($result);
	$old_kurz=$row['tabless'];
	if ($old_kurz != ''){


		$update=$db->query("delete from cc".$n."_soldiers where sid='".$del_id."'");


	}

	header("LOCATION: edit_units.php");

}

?>
