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



@session_start();
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_building";

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

$menu_name="Geb&auml;udeeditor";
$tpl->assign( 'menu_name',$menu_name);
if($action == 'cp'){
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
	$race_to_q = $db->query("SELECT `rassenid` FROM `cc".$n."_rassen` WHERE `rassenid` = '".$to."'");
	$race_from_q = $db->query("SELECT `rassenid` FROM `cc".$n."_rassen` WHERE `rassenid` = '".$from."'");
	if(!$db->num_rows($race_from_q) || !$db->num_rows($race_to_q)){
		error_msg('Rasse existiert nicht.');
		exit;
	}
	$db->query("DELETE FROM `cc".$n."_buildings` WHERE `race` = '".$to."'");
	$units_q = $db->query("SELECT * FROM `cc".$n."_buildings` WHERE `race` = '".$from."'");
	while($unit = $db->fetch_array($units_q)){
		$db->query("INSERT INTO `cc".$n."_buildings` (`name`, `name_en`, `tabless`, `time`, `require1`, `require2`, `p`, `exp`, `res1`, `res2`, `res3`, `res4`, `description`, `description_en`, `size`, `buildpic`, `points`, `race`) VALUES ('".$db->escape_string($unit['name'])."', '".$db->escape_string($unit['name_en'])."', '".$unit['tabless']."', '".$unit['time']."', '".$unit['require1']."', '".$unit['require2']."', '".$unit['p']."', '".$unit['exp']."', '".$unit['res1']."', '".$unit['res2']."', '".$unit['res3']."', '".$unit['res4']."', '".$db->escape_string($unit['description'])."', '".$db->escape_string($unit['description_en'])."', '".$unit['size']."', '".$unit['buildpic']."', '".$unit['points']."', '".$to."')");
	}
	$action = 'main';
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
	$load_req_level="";
	$load_b_pic="";
	$description="";
	$error_buildings="";
	$load_reg1="";
	$load_reg2="";
	$id="";
	$positions=0;






	$make_aktion="action=new&cxid=$sid" ;

	$build_option=make_build_option_choice("build_option","");

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



	$result_options=$db->query("SELECT * FROM cc".$n."_buildings_option");
	$out_units= array();
	$count=0;
	while($row_b_option=$db->fetch_array($result_options)) {
		$name=$row_b_option['description'];
		$name_tag=$row_b_option['tabless'];

		$name=str_replace("op_set_n_res1",$op_set_n_res1,$name);
		$name=str_replace("op_set_n_res2",$op_set_n_res2,$name);
		$name=str_replace("op_set_n_res3",$op_set_n_res3,$name);
		$name=str_replace("op_set_n_res4",$op_set_n_res4,$name);


		$value[0]=$name;
		for($i=1;$i <= $race_count;$i++) {

			$value[$i]="undefiniert";
			$result=$db->query("SELECT * FROM cc".$n."_buildings where race='$i' and tabless='$name_tag'");
			$count=0;
			while($row_ras=$db->fetch_array($result)) {
				$new_b_id=$row_ras['bid'];
				$del_link="<a href=\"edit_building.php?action=del&del_id=$new_b_id\"><img src=\"".LITO_IMG_PATH_URL."acp_building/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
				$value[$i]=$del_link."<a href=\"edit_building.php?id=".$new_b_id."\">".$row_ras['name']."</a> ";




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
		$result_loader=$db->query("SELECT * FROM cc".$n."_buildings where bid='$lade_id' ");

		while($row_load=$db->fetch_array($result_loader)) {
			$load_name	=$row_load['name'];
			$load_ress_1=$row_load['res1'];
			$load_ress_2=$row_load['res2'];
			$load_ress_3=$row_load['res3'];
			$load_ress_4=$row_load['res4'];
			$load_size=$row_load['size'];
			$load_points=$row_load['points'];
			$load_buildtime=$row_load['time'];
			$load_b_pic=$row_load['buildpic'];
			$positions=$row_load['p'];
			if ($load_b_pic=="" ){
				$load_b_pic=LITO_IMG_PATH_URL."acp_building/keins.png";
			}
			$ras=make_race_choice("rasse",$row_load['race']);
			$build_option=make_build_option_choice("build_option",$row_load['tabless']);
			$make_aktion="action=update&cxid=$sid&id=$lade_id" ;
			$description=$row_load['description'];
			$load_reg1=$row_load['require1'];
			$load_reg2=$row_load['require2'];
		}
	}else{

	$ras=make_race_choice("rasse",0);
}

//load error buildings
$result_error=$db->query("SELECT * FROM cc".$n."_buildings where race='' or tabless='' or tabless='0'");
$count=0;
$error_buildings="";
while($row_b_error=$db->fetch_array($result_error)) {
	$name=$row_b_error['name'];
	$name_id=$row_b_error['bid'];
	$del_link="<a href=\"edit_building.php?action=del&del_id=$name_id\"><img src=\"".LITO_IMG_PATH_URL."acp_building/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
	$new_name=$del_link."<a href=\"edit_building.php?id=$name_id\">$name</a> ";
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
$tpl->assign('load_req_level', $load_req_level);
$tpl->assign('load_b_pic', $load_b_pic);
$tpl->assign('description', $description);
$tpl->assign('make_aktion', $make_aktion);
$tpl->assign('required', $required);
$tpl->assign('load_reg1', $load_reg1);
$tpl->assign('load_reg2', $load_reg2);
$tpl->assign('positions', $positions);


$tpl->assign('error_buildings', $error_buildings);



template_out('edit_building.html',$modul_name);
exit();
}



if($action=="update") {
	$upd_id=intval($_GET['id']);
	if ($upd_id <= 0 ){
		error_msg("Daten konnten nicht gespeichert werden.");
		exit();
	}
	$name=trim($_POST['buildingname']);

	$url=trim($_POST['url']);
	$buildpic=trim($_POST['buildpic']);

	$kost1=intval($_POST['kost1']);
	$kost2=intval($_POST['kost2']);
	$kost3=intval($_POST['kost3']);
	$kost4=intval($_POST['kost4']);

	$race=intval($_POST['rasse']);

	$buildtime=intval($_POST['buildtime']);
	$size=intval($_POST['load_size']);
	$points=intval($_POST['points']);

	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);
	$require1=intval($_POST['required1']);
	$require2=intval($_POST['required2']);
	$positions=intval($_POST['positions']);

	$kurz=get_buildings_tabless_name($b_option);

	$update=$db->query("UPDATE cc".$n."_buildings set tabless='' and race='' where tabless='$b_option' and race='$race'");


	$update=$db->query("UPDATE cc".$n."_buildings SET p='$positions',require2='$require2',require1='$require1' ,description='$description',tabless='$b_option',name = '$name',res1='$kost1',res2='$kost2',res3='$kost3',res4='$kost4',time='$buildtime',race='$rasse',points='$points',size='$size',buildpic='$buildpic',race='$race' WHERE bid='$upd_id' ");

	header("LOCATION: edit_building.php");

}

if($action=="new") {
	$name=trim($_POST['buildingname']);

	$buildpic=trim($_POST['buildpic']);

	$kost1=intval($_POST['kost1']);
	$kost2=intval($_POST['kost2']);
	$kost3=intval($_POST['kost3']);
	$kost4=intval($_POST['kost4']);

	$race=intval($_POST['rasse']);

	$time=intval($_POST['buildtime']);
	$size=intval($_POST['load_size']);
	$points=intval($_POST['points']);

	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);
	$require1=intval($_POST['required1']);
	$require2=intval($_POST['required2']);

	$kurz=get_buildings_tabless_name($b_option);
	if ($kurz==""){
		error_msg("Bitte ein Kürzel eintragen !!!");
		exit();
	}

	$update=$db->query("UPDATE cc".$n."_buildings set tabless='' and race='' where tabless='$b_option' and race='$race'");
	$update=$db->query("Insert Into cc".$n."_buildings (require1,require2,description,tabless,name,time,points,res1,res2,res3,res4,size,buildpic,race)VALUES ('$require1','$require2','$description','$kurz','$name','$time','$points','$kost1','$kost2','$kost3','$kost4','$size','$buildpic','$race') ");
	//countries erweitern

	$ret=if_spalte_exist($kurz,"cc".$n."_countries");
	if ($ret== 0 ){
		$update=$db->query("ALTER TABLE cc".$n."_countries ADD ".$kurz." INT( 10 ) NOT NULL DEFAULT '0'");
	}
	header("LOCATION: edit_building.php");

}

if($action=="del") {
	$del_id=intval($_GET['del_id']);

	if ($del_id<= 0 ){
		error_msg("Operation kann nicht ausgeführt werden.");
		exit();
	}
	// search for old
	$result=$db->query("SELECT tabless,bid FROM cc".$n."_buildings WHERE bid='".$del_id."'");
	$row=$db->fetch_array($result);
	$old_kurz=$row['tabless'];
	if ($old_kurz != ''){


		$update=$db->query("delete from cc".$n."_buildings where bid='".$del_id."'");


	}

	header("LOCATION: edit_building.php");

}

?>
