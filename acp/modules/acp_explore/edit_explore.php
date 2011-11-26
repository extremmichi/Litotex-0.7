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
require("./../../includes/global.php");


if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_explore";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
$menu_name="Forschungseditor";
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
	$db->query("DELETE FROM `cc".$n."_explore` WHERE `race` = '".$to."'");
	$units_q = $db->query("SELECT * FROM `cc".$n."_explore` WHERE `race` = '".$from."'");
	while($unit = $db->fetch_array($units_q)){
		$db->query("INSERT INTO `cc".$n."_explore` (`name`, `race`, `tabless`, `time`, `points`, `required`, `description`, `res1`, `res2`, `res3`, `res4`, `explorePic`, `p`) VALUES ('".$db->escape_string($unit['name'])."', '".$to."', '".$unit['tabless']."', '".$unit['time']."', '".$unit['points']."', '".$unit['required']."', '".$unit['description']."', '".$unit['res1']."', '".$unit['res2']."', '".$unit['res3']."', '".$unit['res4']."', '".$unit['explorePic']."', '".$unit['p']."')");
	}
	$action = 'main';
}
if($action=="main") {

	$ras="";
	$load_name="";
	$load_ress_1="";
	$load_ress_2="";
	$load_ress_3="";
	$load_ress_4="";
	$load_buildtime="";
	$load_points="";
	$load_required="";
	$load_b_pic="";
	$description="";
	$make_aktion="";
	$error_buildings="";
	$positions=0;

	$make_aktion="action=new&cxid=$sid" ;
	$build_option=make_explore_option_choice("build_option","");
	$not_show=0;

	$load_b_pic=LITO_IMG_PATH_URL."acp_explore/keins.png";


	$result=$db->query("SELECT * FROM cc".$n."_rassen");
	$race_count=0;
	$out_race= array();
	while($row_ras=$db->fetch_array($result)) {
		$race_count++;
		$out_race[] = $row_ras;
	}
	$tpl->assign('race_all', $out_race);



	$result_options=$db->query("SELECT * FROM cc".$n."_explore_option");
	$out_units= array();
	$count=0;
	while($row_b_option=$db->fetch_array($result_options)) {
		$name=$row_b_option['description'];
		$name_tag=$row_b_option['tabless'];
		$value[0]=$name;
		for($i=1;$i <= $race_count;$i++) {
			//werte f�r die rassen suchen
			$value[$i]="undefiniert";
			$result=$db->query("SELECT * FROM cc".$n."_explore where race='$i' and tabless='$name_tag'");
			$count=0;
			while($row_ras=$db->fetch_array($result)) {
				$new_b_id=$row_ras['eid'];
				$del_link="<a href=\"edit_explore.php?action=del&del_id=$new_b_id\"><img src=\"".LITO_IMG_PATH_URL."acp_explore/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
				$value[$i]=$del_link."<a href=\"edit_explore.php?id=".$new_b_id."\">".$row_ras['name']."</a> ";




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
		$result_loader=$db->query("SELECT * FROM cc".$n."_explore where eid='$lade_id'");

		while($row_load=$db->fetch_array($result_loader)) {
			$load_name	=$row_load['name'];
			$load_ress_1=$row_load['res1'];
			$load_ress_2=$row_load['res2'];
			$load_ress_3=$row_load['res3'];
			$load_ress_4=$row_load['res4'];
			$load_required=$row_load['required'];
			$load_points=$row_load['points'];
			$load_buildtime=$row_load['time'];
			$load_b_pic=$row_load['explorePic'];
			$ras=make_race_choice("rasse",$row_load['race']);
			$build_option=make_explore_option_choice("build_option",$row_load['tabless']);
			$make_aktion="action=update&cxid=$sid&id=$lade_id" ;
			$description=$row_load['description'];
			$positions=$row_load['p'];

			if ($load_b_pic=="" ){
				$load_b_pic=LITO_IMG_PATH_URL."acp_explore/keins.png";
			}
		}
	}else{

	$ras=make_race_choice("rasse",0);
}

//load error buildings
$result_error=$db->query("SELECT * FROM cc".$n."_explore where race='' or tabless='' or tabless='0'");
$count=0;
$error_buildings="";
while($row_b_error=$db->fetch_array($result_error)) {
	$name=$row_b_error['name'];
	$name_id=$row_b_error['eid'];
	$del_link="<a href=\"edit_explore.php?action=del&del_id=$name_id\"><img src=\"".LITO_IMG_PATH_URL."acp_explore/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
	$new_name=$del_link."<a href=\"edit_explore.php?id=$name_id\">$name</a> ";
	$error_buildings.=$new_name."<br><br>";

}



$tpl->assign('ras', $ras);
$tpl->assign('load_name', $load_name);
$tpl->assign('load_ress_1', $load_ress_1);
$tpl->assign('load_ress_2', $load_ress_2);
$tpl->assign('load_ress_3', $load_ress_3);
$tpl->assign('load_ress_4', $load_ress_4);
$tpl->assign('buildtime', $load_buildtime);
$tpl->assign('load_points', $load_points);
$tpl->assign('load_required', $load_required);
$tpl->assign('load_b_pic', $load_b_pic);
$tpl->assign('description', $description);
$tpl->assign('make_aktion', $make_aktion);
$tpl->assign('error_buildings', $error_buildings);
$tpl->assign('build_option', $build_option);
$tpl->assign('positions', $positions);



template_out('edit_explore.html',$modul_name);
exit();
}



if($action=="update") {
	$name=trim($_POST['explorename']);
	$explorepic=trim($_POST['buildpic']);

	$gold=intval($_POST['kost1']);
	$stone=intval($_POST['kost2']);
	$oil=intval($_POST['kost3']);
	$exp=intval($_POST['kost4']);

	$race=intval($_POST['rasse']);
	$buildtime=intval($_POST['buildtime']);
	$points=intval($_POST['points']);

	$required=intval($_POST['required']);
	$explore_id =intval($_GET['id']);
	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);
	$positions=intval($_POST['positions']);

	$kurz=get_explore_tabless_name($b_option);
	if ($explore_id <= 0 ){
		error_msg("Ung�ltige Eingabe");
		exit();
	}


	$update=$db->query("UPDATE cc".$n."_explore set tabless='' and race='' where tabless='$b_option' and race='$race'");

	$update=$db->query("UPDATE cc".$n."_explore SET p='$positions',tabless='$b_option', name = '$name',res1='$gold',res2='$stone',res3='$oil',res4='$exp',time='$buildtime',race='$race',points='$points',required='$required',explorePic='$explorepic' ,description='$description' WHERE eid='$explore_id' ");

	header("LOCATION: edit_explore.php");

}

if($action=="new") {
	$name=trim($_POST['explorename']);
	$explorepic=trim($_POST['explorepic']);

	$gold=intval($_POST['kost1']);
	$stone=intval($_POST['kost2']);
	$oil=intval($_POST['kost3']);
	$exp=intval($_POST['kost4']);

	$race=intval($_POST['rasse']);
	$buildtime=intval($_POST['buildtime']);
	$points=intval($_POST['points']);
	$required=intval($_POST['required']);

	$b_option=trim($_POST['build_option']);
	$description=trim($_POST['descr']);

	$kurz=get_explore_tabless_name($b_option);
	if ($kurz==""){
		error_msg("Bitte einen Kurznamen eintragen!");
		exit();
	}

	$ret=if_spalte_exist($kurz,"cc".$n."_countries");
	if ($ret== 0 ){
		$update=$db->query("ALTER TABLE cc".$n."_countries ADD ".$kurz." INT( 10 ) NOT NULL DEFAULT '0'");
	}

	$update=$db->query("UPDATE cc".$n."_explore set tabless='' and race='' where tabless='$b_option' and race='$race'");
	$update=$db->query("Insert Into cc".$n."_explore (description,name,race,tabless,res1,res2,res3,res4,time,points,required,explorePic)VALUES ('$description','$name','$race','$kurz','$gold','$stone','$oil','$exp','$buildtime','$points','$required','$explorepic') ");

	header("LOCATION: edit_explore.php");

}

if($action=="del") {
	$del_id=intval($_GET['del_id']);

	if ($del_id<= 0 ){
		error_msg("Vorgang kann nicht ausgef&uuml;hrt werden!");
		exit();
	}
	// search for old
	$result=$db->query("SELECT tabless,eid FROM cc".$n."_explore WHERE eid='".$del_id."'");
	$row=$db->fetch_array($result);
	$old_kurz=$row['tabless'];
	if ($old_kurz != ''){


		$update=$db->query("delete from cc".$n."_explore where eid='".$del_id."'");


	}

	header("LOCATION: edit_explore.php");

}

?>
