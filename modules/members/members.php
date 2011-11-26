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
$modul_name="members";
require("./../../includes/global.php");

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if(!isset($_SESSION['userid'])) show_error("ups",members);


if (intval($userdata['rassenid']) == 0 && $action != "save_race"){
	$action="race_choose";
}


$module=get_modulname(2);
$msg_modul_org="./../".$module[0]."/".$module[1];

if($action=="main") {
	$daten[]="";
	$i=0;
	$result_land=$db->query("SELECT * FROM cc".$n."_countries WHERE userid='".$userdata['userid']."'");

	while($row_land=$db->fetch_array($result_land)) {

		$daten[$i]['name']=$row_land['name'];
		$daten[$i]['res1']=$row_land['res1'];
		$daten[$i]['res2']=$row_land['res2'];
		$daten[$i]['res3']=$row_land['res3'];
		$daten[$i]['res4']=$row_land['res4'];
		$daten[$i]['islandid']=$row_land['islandid'];


		if ($row_land['endbuildtime'] > 0 ){
			$daten[$i]['build']=sec2time($row_land['endbuildtime']-time());
		}else{
		$daten[$i]['build']="";
	}

	if ($row_land['endexploretime'] > 0 ){
		$daten[$i]['explore']=sec2time($row_land['endexploretime']-time());
	}else{
	$daten[$i]['explore']="-";
}

$result_e=$db->query("SELECT endtime FROM cc".$n."_create_sol WHERE island_id='".$row_land['islandid']."' and sol_type='0'");
$row_e=$db->fetch_array($result_e);

if ($row_e['endtime'] > 0 ){
	$daten[$i]['units']=sec2time($row_e['endtime']-time());
}else{
$daten[$i]['units']="-";
}

$result_d=$db->query("SELECT endtime FROM cc".$n."_create_sol WHERE island_id='".$row_land['islandid']."' and sol_type='1'");
$row_d=$db->fetch_array($result_d);

if ($row_d['endtime'] > 0 ){
	$daten[$i]['def']=sec2time($row_d['endtime']-time());
}else{
$daten[$i]['def']="-";
}


$i++;
}

//prüfen auf allianznews
$ali_id=$userdata['allianzid'];
$ali_news_show="";
$ali_news_text="";
if ($ali_id>0){

	$result_e=$db->query("SELECT * FROM cc".$n."_allianznews WHERE allianz_id  ='$ali_id' ");
	while($row_e=$db->fetch_array($result_e)) {
		$news_t= bb2html($row_e['a_news_text']);
		$c_date=$row_e['change_date'];
		$change_date=date("d.m.Y (H:i:s)",$c_date);
	}
	$ali_news_text =trim($news_t);


	$tpl->assign('ali_news_show',$ali_news_text);
	$tpl->assign('ali_news_date', $change_date);

}

$userrace=get_race($userdata['rassenid']);

$cur_res_reload_time=round($op_res_reload_time/60);

$production_res1=$op_set_res1+($userdata['res1mine']*$op_mup_res1);
$production_res2=$op_set_res3+($userdata['res2mine']*$op_mup_res3);
$production_res3=$op_set_res2+($userdata['res3mine']*$op_mup_res2);
$production_res4=$op_set_res4+($userdata['res4mine']*$op_mup_res4);

$next4ressources=($userdata['lastressources']+$op_res_reload_time)-time();


$allianzname=generate_allilink($userdata['allianzid']);
$is_allianz=0;
if ($userdata['allianzid']!=0){
	$is_allianz=1;
}


$tpl->assign('next4ressources',sec2time($next4ressources));
$tpl->assign('daten', $daten);

$tpl->assign('cur_res_reload_time', $cur_res_reload_time);

$tpl->assign('production_res1', $production_res1);
$tpl->assign('production_res2', $production_res2);
$tpl->assign('production_res3', $production_res3);
$tpl->assign('production_res4', $production_res4);

$tpl->assign('is_allianz', $is_allianz);
$tpl->assign('M_USER_RACE', $userrace);
$tpl->assign('M_LANDSIZE_MAX', $op_max_c_size);
$tpl->assign('M_LANDSIZE', $userdata['size']);
$tpl->assign('M_A_NAME', $allianzname);
$tpl->assign('M_KOORDS', $userdata['x'] .":". $userdata['y']);
$tpl->assign('M_C_NAME', $userdata['name']);
$tpl->assign('M_POINTS', $userdata['points']);
template_out('members.html',$modul_name);
exit();
}


if($action=="rename") {

	$tpl->assign('M_RENAME', $userdata['name']);
	template_out('members_rename.html',$modul_name);
	exit();
}


if($action=="submit_rename") {
	$name=c_trim($_POST['name']);
	if(!$name) {
		show_error("L_NO_LANDNAME_ERROR",$modul_name);
		exit();
	}
	if(strlen($name)>20) {
		show_error("L_LANDNAME_SIZSE_ERROR",$modul_name);

		exit();
	}
	$db->unbuffered_query("UPDATE cc".$n."_countries SET name='".$name."' WHERE islandid='".$userdata['activeid']."'");
	header("LOCATION: members.php");
	exit();
}



if($action=="info") {
	if ($userdata['size']>$op_mainbuild_controll) {
		$msize = $userdata['towncenter']*50;
	}
	if ($msize < $op_mainbuild_controll) {
		$msize = $op_mainbuild_controll;
	}
	if($bid=="b2") $infotext=$ln_townsize;
	if($bid=="b8") $infotext=$ln_capacity_1.($userdata['store']*$op_set_store_max+$op_set_store_max).$ln_capacity_2;
	eval("\$tpl->output(\"".$tpl->get("members_infos")."\");");
	exit();
}

if($action=="edituserdata") {

	if ($userdata['userpic']==""){
		$userpic=LITO_IMG_PATH_URL.$modul_name."/no_user_pic.jpg";
	}else{
	$userpic=$userdata['userpic'];
}

$description=$userdata['description'];

$newsletter_ok= intval($userdata['newsletter']);
$urlaub_ok= intval($userdata['umod']);

if ($newsletter_ok== 1){
	$tpl->assign('USER_IS_NEWSLETTER_OK',"Selected");
	$tpl->assign('USER_IS_NEWSLETTER_NOK',"");
}else{
$tpl->assign('USER_IS_NEWSLETTER_OK',"");
$tpl->assign('USER_IS_NEWSLETTER_NOK',"Selected");
}


if ($urlaub_ok== 1){
	$tpl->assign('USER_IS_URLAUB_OK',"Selected");
	$tpl->assign('USER_IS_URLAUB_NOK',"");
}else{
$tpl->assign('USER_IS_URLAUB_OK',"");
$tpl->assign('USER_IS_URLAUB_NOK',"Selected");
}


$out="<select name=\"coose_design\" class=\"combo\">";

$result=$db->query("SELECT * FROM cc".$n."_desigs where alternate_permit ='1' ");
while($row=$db->fetch_array($result)) {
	$name_description=$row['design_name'];
	if ($userdata['design_id'] == $row['design_id']){
		$out.="<option value=\"".$row['design_id']."\" selected>".$name_description."</option>";
	}else{
	$out.="<option value=\"".$row['design_id']."\">".$name_description."</option>";
}

}
$out.="</select>";

$module=get_modulname(26);
$sig_modul_org="./../".$module[0]."/".$module[1];

include($sig_modul_org);
make_signature($userdata[userid]);


$signature_image="";
$img_path=LITO_ROOT_PATH."images_sig/game_sig_".$userdata['userid'].".png";
if (is_file($img_path)){
	$img_path_url=LITO_ROOT_PATH_URL."images_sig/game_sig_".$userdata['userid'].".png";
	$signature_image="<img src=\"".$img_path_url."\" border=\"0\" >";
	$signature_html="<a href='".$op_set_game_url."'><img src='".$img_path_url."' border='0' ></a> ";
	$signature_bb="[img]".$img_path_url."[/img]";
}


$tpl->assign('signature_html', $signature_html);
$tpl->assign('signature_bb', $signature_bb);
$tpl->assign('signature_image', $signature_image);
$tpl->assign('USER_DESIGN', $out);
$tpl->assign('USER_DESC', $description);
$tpl->assign('USER_MSN', $userdata['msn']);
$tpl->assign('USER_ICQ', $userdata['icq']);
$tpl->assign('USER_USERMAILS', $userdata['email']);
$tpl->assign('USER_USERIMAGE', $userpic);
$tpl->assign('USER_USERNAME', $userdata['username']);
template_out('members_userdata.html',$modul_name);
exit();
}



if($action=="saveuserdata") {
	$umod1=intval($_POST['urlaub']);
	$email=c_trim($_POST['email']);
	$password_old=c_trim($_POST['password_old']);
	$password_new_first=c_trim($_POST['password_new_first']);
	$password_new_second=c_trim($_POST['password_new_second']);
	$newsletter=intval($_POST['newsletter']);
	$icq=c_trim($_POST['icq']);
	$msn=c_trim($_POST['msn']);
	$description=($_POST['description']);
	$design_id =intval($_POST['coose_design']);

	if ($design_id <= 0 ){
		$design_id =1;
	}

	$db->unbuffered_query("UPDATE cc".$n."_users SET design_id = '".$design_id."', umod  ='".$umod1."',msn='".$msn."',icq='".$icq."',description='".$description."',email='".$email."', newsletter='".$newsletter."', grafik='".$grafik."' WHERE userid='".$userdata['userid']."'");

	if($password_old != "" OR $password_new_first !="" OR $password_new_first !=""){
		$result=$db->query("SELECT password FROM cc".$n."_users WHERE userid='$userdata[userid]'");
		$row=$db->fetch_array($result);
		if($row['password']==md5($password_old)){
			if($password_new_first==$password_new_second){
				$md5_pw=md5($password_new_first);
				$db->query("UPDATE cc".$n."_users SET password='".$md5_pw."' WHERE userid='".$userdata['userid']."'");
			} else {
				
				show_error("ln_members_e_2",$modul_name);
			}
		} else {
		
				show_error("ln_members_e_1",$modul_name);
		}
	}
	header("LOCATION: members.php?action=edituserdata");
	exit();
}

if($action=="save_race") {
	$rassenid=intval($_GET['race_id']);
	if($rassenid==0) {
		show_error("L_NO_RACE_SELECT",$modul_name);

		exit();
	}
	$race_name=trim(get_race($rassenid));
	if ($race_name =="" ){
		show_error("L_NO_RACE_SELECT",$modul_name);
	}

	if (intval($userdata['rassenid']) > 0 ){


		show_error("L_RACE_OK",$modul_name);
	}

	$db->query("UPDATE cc".$n."_users SET rassenid='".$rassenid."' WHERE userid='".$userdata['userid']."'");
	$db->query("UPDATE cc".$n."_countries SET race='".$rassenid."' WHERE userid='".$userdata['userid']."'");
	header("LOCATION: members.php");
	exit();
}

if($action=="land_change") {

	$i=intval($_GET['i']);
	$result=$db->query("SELECT islandid,userid FROM cc".$n."_countries WHERE islandid='$i'");
	$islanddata=$db->fetch_array($result);
	if($islanddata['userid']==$userdata['userid'] && $islanddata['islandid']==$i) {
		$db->query("UPDATE cc".$n."_users SET activeid='$i' WHERE userid='".$userdata['userid']."'");
		header("LOCATION: members.php?cxid=$sid");
		exit();
	}
	show_error("L_LAND_NOT_FOUNT_ERROR",$modul_name);
	exit();
}

if($action=="profile") {
	$id=intval($_GET['id']);
	if(!$id) {
		show_error("PROFILE_E_1",$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_users WHERE userid='".$id."'");
	$row=$db->fetch_array($result);

	if($row['userid']!=$id) {
		show_error("PROFILE_E_2",$modul_name);
		exit();
	}

	$tpl->config_load($lang_file);
	$members_koords = $tpl ->get_config_vars('MEMBERS_KOORDS');
	$members_points = $tpl ->get_config_vars('MEMBERS_POINT');
	$profile_on = $tpl ->get_config_vars('PROFILE_ONLINE');
	$profile_off = $tpl ->get_config_vars('PROFILE_OFFLINE');

	$result=$db->query("SELECT rassenname FROM cc".$n."_rassen WHERE rassenid='$row[rassenid]'");
	$userrasse=$db->fetch_array($result);

	$result=$db->query("SELECT userid,name,islandid,points,x,y FROM cc".$n."_countries WHERE userid='$id'");
	while($i=$db->fetch_array($result)) {

		$module=get_modulname(7);
		$map_modul_org="./../".$module[0]."/".$module[1];
		$land_link="<a href=\"$map_modul_org?x=$i[x]&y=$i[y]\">".$i[name]." ".$members_koords." ($i[x]:$i[y])</a>";
		$ibit.=$land_link." $i[points] ".$members_points."<br>";
	}

	$userallianzname=generate_allilink($row['allianzid']);
	if($row['lastactive']>(time()-3600))
	$online="<span class=\"green\">".$profile_on."</span>";
	else
	$online="<span class=\"red\">".$profile_off."</span>";



	if (trim($row['userpic'])==""){
		$upic="./images/no_user_pic.jpg";
	}else{
	$upic=$row['userpic'];
}
$description=bb2html($row['description']);

$message_link =generate_messagelink($row['username'],1);

$tpl->assign('description', $row['description']);
$tpl->assign('message_link', $message_link);
$tpl->assign('username', $row['username']);
$tpl->assign('rasse', $userrasse['rassenname']);
$tpl->assign('userallianzname', $userallianzname);
$tpl->assign('points', $row['points']);
$tpl->assign('ibit', $ibit);
$tpl->assign('online', $online);
$tpl->assign('description', $description);
template_out('members_profile.html',$modul_name);
}

if($action=="race_choose") {
	$new_found_inhalt = array ();
	$new_found = array ();
	$result = $db->query("SELECT * FROM cc" . $n . "_rassen ORDER BY rassenname");
	$count = 0;
	while ($row_g = $db->fetch_array($result)) {
		$count++;

		$new_found_inhalt = array (
		$row_g['rassenid'],
		$row_g['rassenname'],
		$row_g['descriprion'],
		$count
		);
		array_push($new_found, $new_found_inhalt);
	}

	$tpl->assign('daten_race_coose', $new_found);
	template_out('race_choose.html', 'members');
	exit ();

}
?>
