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


function show_error($error_msg,$from_modul,$load_from_lang=1) {

	global  $tpl,$db,$n,$userdata,$lang_suffix;;
	if ($load_from_lang==1){
		$lang_file=LITO_LANG_PATH.$from_modul.'/lang_'.$lang_suffix.'.php';
		$tpl->config_load($lang_file);

		$tpl ->assign('LITO_ERROR_MSG',  $tpl ->get_config_vars($error_msg));
	}else{
	$tpl ->assign('LITO_ERROR_MSG',  $error_msg);
}

$tpl ->assign('if_login_error',1);
template_out('error.html','core');
exit();

}


/** create an password **/
function password($char) {

	$length=intval($char);
	if ($length < 5) {
		$length=6;
	}
	$password="";
	$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$pool .= "abcdefghijklmnopqrstuvwxyz";
	$pool .= "1234567890";

	srand ((double)microtime()*1000000);
	for ($i = 0; $i < intval($length); $i++) {
		$password .= $pool{rand(0, strlen($pool)-1)};
	}
	return $password ;
	exit();

}

function get_footer() {
	global $db, $time_start;
	$time_end = explode(' ',substr(microtime(),1));
	$time_end  = $time_end[1]+$time_end[0];
	$run_time = $time_end-$time_start;
	return "time: ".number_format($run_time,5,'.','')." sec <br>query count: " .$db->number_of_querys();
	//return "oooo";
}

function is_modul_name_aktive($modul_name) {
	global $db,$n,$userdata;
	$result=$db->query("SELECT activated FROM cc".$n."_modul_admin where modul_name ='$modul_name'");
	$row=$db->fetch_array($result);
	return intval($row['activated']);
}

function is_modul_id_aktive($modul_id) {
	global $db,$n,$userdata;
	$result=$db->query("SELECT activated FROM cc".$n."_modul_admin where modul_admin_id ='$modul_id'");
	$row=$db->fetch_array($result);
	return intval($row['activated']);

}
function c_trim($string) {
	$replace=strip_tags($string);
	$replace=str_replace("'","",$replace);
	return trim($replace);
}


function get_navigation($modulname){
	global $db,$tpl,$lang_suffix,$n,$is_loged_in;
	// get_modulname
	// return array
	// 0 = modulname
	// 1 = modulstartfile
	// 2 = modul ID
	// 3 = Show Error 0/1
	$module=get_modulname(1);


	if (is_modul_id_aktive($module[2])==1){

		include(LITO_MODUL_PATH.$module[0].'/'.$module[1]);

		$navi = new navigation();
		//hauptnavi
		$rrr=$navi->make_navigation($modulname,$module[2],$is_loged_in,0);
		$tpl ->assign('LITO_NAVIGATION', $rrr);
		//navigation left side
		$rrr_1=$navi->make_navigation($modulname,$module[2],$is_loged_in,1);
		$tpl ->assign('LITO_NAVIGATION_1', $rrr_1);

		//navigation right side
		$rrr_2=$navi->make_navigation($modulname,$module[2],$is_loged_in,2);
		$tpl ->assign('LITO_NAVIGATION_2', $rrr_2);

		unset($navi);


	}else{
	if ($module[4] == 0){
		return "";
	}else{
	return "Modul wurde deaktiviert.";

}
}
}


function get_modulname($modul_type){
	// $modul_type
	// 0 =  default modul with Links
	// 1 =  naviagtion Modul
	// 2 =  GAME Members Modul
	// 3 =  GAME Ranking Modul
	// 4 =  GAME Alliance Modul
	// 5 =  GAME Country Modul
	// 6 =  GAME Message Modul
	// 7 =  GAME Map Modul
	// 8 =  GAME UserEditor Modul
	// 9 =  GAME Battle Modul
	// 10 = GAME Buildings Modul
	// 11 = GAME Build Units Modul
	// 12 = GAME Buid Def Modul
	// 13 = GAME Group Modul
	// 14 = GAME Explore Modul
	// 15 = GAME Spion Modul
	// 16 = GAME Techtree Modul
	// 17 = GAME Search Modul
	// 18 = GAME Alliance Forum Modul
	// 19 = GAME Login
	// 20 = GAME Registrieren
	// 21 = GAME News
	// 22 = GAME Screenshot
	// 23 = GAME impressum
	// 24 = GAME Upload_PIC
	// 25 = GAME CoreModul
	// 26 = GAME usr_signature
	global $tpl,$db,$n;
	$result=$db->query("SELECT modul_name,startfile,modul_admin_id,show_error_msg,acp_modul,modul_type   FROM cc".$n."_modul_admin where acp_modul ='0' and modul_type='$modul_type'");
	$row=$db->fetch_array($result);
	return $row;


}

function template_out($template_name,$from_modulname){
	global $db,$tpl,$lang_suffix,$n,$userdata;
	$lang_file=LITO_LANG_PATH.$from_modulname.'/lang_'.$lang_suffix.'.php';
	$tpl->config_load($lang_file);

	if (intval($userdata['rassenid']) > 0 || $is_loged_in==0){

		get_navigation($from_modulname);
	}

	$tpl ->assign('LITO_GLOBAL_IMAGE_URL', LITO_GLOBAL_IMAGE_URL);
	$tpl ->assign('LITO_IMG_PATH', LITO_IMG_PATH_URL.$from_modulname."/");
	$tpl ->assign('LITO_IMG_PATH_URL', LITO_IMG_PATH_URL);
	$tpl ->assign('LITO_MAIN_CSS', LITO_MAIN_CSS);
	$tpl ->assign('GAME_FOOTER_MSG', get_footer());
	$tpl ->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
	$tpl ->assign('LITO_MODUL_PATH_URL', LITO_MODUL_PATH_URL);
	$tpl ->assign('LITO_BASE_MODUL_URL', LITO_MODUL_PATH_URL);

	$tpl ->display($from_modulname."/".$template_name);
	if(isset($_SESSION['ttest']) && $_SESSION['ttest'] == true && isset($_SESSION['ttestid'])){
		echo '<div style="background:white;color:black;bottom:0px;" align="center"><a style="color:black" href="?killdesmod=true">Designmodus beenden</a></div>';
	}
}

function trace_msg($message,$error_type) {
	global $db,$n,$userdata;

	$message = addslashes($message);
	$db->query("INSERT INTO cc".$n."_debug(db_time, db_text ,db_type ,fromuserid) VALUES ('".time()."','$message','".$error_type."','".$userdata['userid']."')");

	return;
}

function get_race($rassenid) {
	global $db,$n;
	$result=$db->query("SELECT * FROM cc".$n."_rassen WHERE rassenid='$rassenid'");
	$row=$db->fetch_array($result);
	return $row['rassenname'];
}


function send_register_mail($mail_send_to,$filename,$modulname,$u_name,$password,$x_pos,$y_pos)
{
	global  $op_admin_email,$op_set_author,$op_set_gamename,$tpl,$op_send_html_mail;


	require_once(LITO_ROOT_PATH."includes/mime_mail.class.php");



	$mime = new MIME_Mail;
	$html="";
	$from_e = $op_admin_email;
	$from_n = $op_set_author;

	$to_e = $mail_send_to;
	$to_n = $mail_send_to;

	$text = "Registrierung bei ".$op_set_gamename;

	$filename_txt=str_replace('.html','.txt',$filename);

	$html= $tpl->fetch(LITO_THEMES_PATH.$modulname.'/'.$filename);
	$txt= $tpl->fetch(LITO_THEMES_PATH.$modulname.'/'.$filename_txt);

	$html =str_replace("[REG_USERNAME]", $u_name ,$html);
	$html =str_replace("[REG_PASSWORD]", $password ,$html);
	$html =str_replace("[REG_X_POS]", $x_pos ,$html);
	$html =str_replace("[REG_Y_POS]", $y_pos ,$html);
	$html =str_replace("[REG_GAME_NAME]",$op_set_gamename,$html);

	$txt =str_replace("[REG_USERNAME]", $u_name ,$txt);
	$txt =str_replace("[REG_PASSWORD]", $password ,$txt);
	$txt =str_replace("[REG_X_POS]", $x_pos ,$txt);
	$txt =str_replace("[REG_Y_POS]", $y_pos ,$txt);
	$txt =str_replace("[REG_GAME_NAME]",$op_set_gamename,$txt);





	$key = "Mailer";
	$val = "Litotex mailer";

	//$mime->addXHeader( $key, $val );
	$mime->addXHeader( "", "");
	$mime->addTo( $to_e, $to_n );
	$mime->setFrom( $from_e, $from_n );
	$mime->setSubject($text);
	$mime->setPriority(3);
	if (intval($op_send_html_mail)==1){
		$mime->setHTMLPart($html);
		
		$mime->sendMail();
	}else{
	$mime->setPlainPart( $txt );
	
	mail($to_e,$text,$txt,"From: $from_e");
	}






}

function get_soldiers_name($tabless,$rasse)
{
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT name FROM cc".$n."_soldiers WHERE tabless='".$tabless."' and race='".$rasse."'");
	$row=$db->fetch_array($result);
	$ret=$row['name'];
	if ($ret==""){
		$ret="unbekannt";
	}
	return $ret;
}

function get_buildings_name($tabless,$rasse)
{
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT name FROM cc".$n."_buildings WHERE tabless='".$tabless."' and race='".$rasse."'");
	$row=$db->fetch_array($result);
	$ret=$row['name'];
	if ($ret==""){
		$ret="unbekannt";
	}
	return $ret;
}

function get_race_id_from_user($fuserid) {
	global $db, $n;
	$result=$db->query("SELECT rassenid,userid FROM cc".$n."_users WHERE userid='".$fuserid."'");
	$row=$db->fetch_array($result);
	return $row['rassenid'];
}

function get_soldiers_speed ($tabless,$rasse){
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT traveltime FROM cc".$n."_soldiers WHERE tabless='".$tabless."' and race='".$rasse."'");
	$row=$db->fetch_array($result);
	$ret=$row['traveltime'];
	if ($ret==""){
		$ret=11;
	}
	return $ret;

}
function get_distance_simple ($startX,$startY,$endX,$endY){
	$x = $endX - $startX ;
	$y = $endY - $startY;

	$distance = sqrt(pow($x, 2) + pow($y, 2));
	return $distance;
}



function get_duration_time ($startX,$startY,$endX,$endY,$Sol_speed){
	global $op_land_duration;
	$duration =ceil(get_distance_simple($startX,$startY,$endX,$endY));
	$an_time=time()+$duration*$op_land_duration;
	$an_time=round($an_time * $Sol_speed / 1000);
	return $an_time;
}

/** get a islandid **/
function get_island($islandid) {
	global $db,$n;
	$result=$db->query("SELECT islandid,name FROM cc".$n."_countries WHERE islandid='$islandid'");
	$row=$db->fetch_array($result);
	return c_trim($row['name']);
}

function timebanner_init($banner_with,$image){
	// make a new Timebanner
	global $tpl;

	$tpl->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
	$timebanner_init= $tpl->fetch(LITO_THEMES_PATH.'core/time_banner_init.html');


	$tpl->assign('TIME_BANNER_INIT', $timebanner_init);

	return"";
}

function make_timebanner($timestart,$timeend,$banner_id,$reload_url,$css="progressBar"){
	// make a new Timebanner
	global $tpl;
	$timecurrent=time();

	$tpl->assign('banner_css', $css);
	$tpl->assign('banner_id', $banner_id);
	$tpl->assign('startzeit', $timestart);
	$tpl->assign('endzeit', $timeend);
	$tpl->assign('NAVIGATE2SITE', $reload_url);

	$tpl->assign('banner_curtime', $timecurrent);
	$time_baner= $tpl->fetch(LITO_THEMES_PATH.'core/time_banner.html');
	return  $time_baner;
}

function make_ingamemail($fromuser,$touser,$subject,$mailtext){
	global $db,$n,$userdata;

	$db->query("INSERT INTO cc".$n."_messages (username,fromuserid,touserid,text,time,isnew,inbox,subject,pri) VALUES ('".$userdata['username']."','".$fromuser."','".$touser."','".$mailtext."','".time()."','1','1','".$subject."','0')");
}

function username($userid) {
	global $db,$n;
	$result=$db->query("SELECT username,userid FROM cc".$n."_users WHERE userid='$userid'");
	$row=$db->fetch_array($result);
	return c_trim($row['username']);
}

function sec2time($sek) {
	$i = sprintf('%d T%s %02d:%s'.'%02d:%s%02d%s',
	$sek / 86400,
	floor($sek / 86400) != 1 ? '':'',
	$sek / 3600 % 24,
	floor($sek / 3600 % 24) != 1 ? '':'',
	$sek / 60 % 60,
	floor($sek / 60 % 60) != 1 ? '':'',
	$sek % 60,
	floor($sek % 60) != 1 ? '':''
	);
	return $i;
}

function bb2html($text)
{

	$text = eregi_replace("\[b\]([^\[]+)\[/b\]","<b>\\1</b>",$text);
	$text = eregi_replace("\[i\]([^\[]+)\[/i\]","<i>\\1</i>",$text);
	$text = preg_replace('/\[url=([^ ]+).*\](.*)\[\/url\]/', '<a href="$1" target=\"_blank\" >$2</a>', $text);
	$text = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$text);
	$text = preg_replace('/\n/', "<br/>\n", $text);
	$text = eregi_replace("\[u\]([^\[]+)\[/u\]","<u>\\1</u>",$text);
	$text = eregi_replace("\[img\]([^\[]+)\[/img\]","<img src=\"\\1\" border=\"0\">",$text);
	$text = eregi_replace("\[mail\]([^\[]+)\[/mail\]","<a href=\"mailto:\\1\">\\1</a>",$text);

	return $text;




}

function html2bb($text)
{
	$bbcode = array("<", ">",
	"[list]", "[*]", "[/list]",
	"[img]", "[/img]",
	"[b]", "[/b]",
	"[u]", "[/u]",
	"[i]", "[/i]",
	'[color="', "[/color]",
	"[size=\"", "[/size]",
	'[url="', "[/url]",
	"[mail=\"", "[/mail]",
	"[code]", "[/code]",
	"[quote]", "[/quote]",
	'"]');
	$htmlcode = array("&lt;", "&gt;",
	"<ul>", "<li>", "</ul>",
	"<img src=\"", "\">",
	"<b>", "</b>",
	"<u>", "</u>",
	"<i>", "</i>",
	"<span style=\"color:", "</span>",
	"<span style=\"font-size:", "</span>",
	'<a href="', "</a>",
	"<a href=\"mailto:", "</a>",
	"<code>", "</code>",
	"<table width=100% bgcolor=lightgray><tr><td bgcolor=white>", "</td></tr></table>",
	'">');
	$newtext = str_replace( $htmlcode,$bbcode, $text);
	$newtext =strip_tags($newtext );

	return $newtext;
}
function get_userid($username) {
	global $db,$n;
	$result=$db->query("SELECT userid,username FROM cc".$n."_users WHERE username='$username'");
	$row=$db->fetch_array($result);
	return $row['userid'];
}

/** get username **/
function is_username($username) {
	global $db,$n;
	$result=$db->query("SELECT userid,username FROM cc".$n."_users WHERE username='$username'");
	$row=$db->fetch_array($result);
	if($row['username']!=$username) $is=0;
	else $is=1;
	return $is;
}

/** get name of group**/
function group($gid) {
	global $db,$n;
	$result=$db->query("SELECT name FROM cc".$n."_groups WHERE groupid='$gid'");
	$row=$db->fetch_array($result);
	return $row['name'];
}

function get_userid_from_countrie($countrie_id) {
	global $db,$n;
	$result=$db->query("SELECT userid,islandid FROM cc".$n."_countries WHERE islandid='$countrie_id'");
	$row=$db->fetch_array($result);
	return $row['userid'];
}

function get_race_id_from_countrie($countrie_id) {
	global $db, $n;
	$result=$db->query("SELECT race,islandid FROM cc".$n."_countries WHERE islandid='".$countrie_id."'");
	$row=$db->fetch_array($result);

	return $row['race'];

}

function get_race_id_from_group($group_id) {
	global $db, $n;
	$result=$db->query("SELECT islandid,groupid FROM cc".$n."_groups WHERE groupid='".$group_id."'");
	$row=$db->fetch_array($result);
	$countrie_id=$row['islandid'];

	$rass_is=get_race_id_from_countrie($countrie_id);
	return $rass_is;

}

function get_countrie_name_from_group_id($group_id,$inclu_koords=0) {
	global $db,$n;
	$result=$db->query("SELECT islandid,groupid FROM cc".$n."_groups WHERE groupid='".$group_id."'");
	$row=$db->fetch_array($result);
	$is_id=$row['islandid'];

	$ret_name=get_countrie_name_from_id($is_id,$inclu_koords);

	return $ret_name;

}


function get_countrie_name_from_id($country_id,$koords=0) {
	global $db,$n;
	$result=$db->query("SELECT name,islandid,x,y FROM cc".$n."_countries WHERE islandid='".$country_id."'");
	$row=$db->fetch_array($result);

	$ret_name=$row['name'];
	if ($koords ==1){
		$ret_name=$ret_name." (" .$row['x'].":".$row['y'].")";
	}

	return $ret_name;

}

function get_user_id_from_group_id($group_id) {
	global $db,$n;
	$result=$db->query("SELECT islandid,groupid FROM cc".$n."_groups WHERE groupid='".$group_id."'");
	$row=$db->fetch_array($result);
	$is_id=$row['islandid'];
	$ret_name=get_userid_from_countrie($is_id);

	return $ret_name;

}

function get_allianz_flag($ali_id){
	$filename_flag="./../../alli_flag/flag_".$ali_id.".png";
	if (file_exists($filename_flag)){
		$_name="<img src=\"$filename_flag\" border=\"1\">";
		return($_name);
	}
	else{
	$filename_flag=LITO_IMG_PATH_URL."alliance/no.png";
	$_name="<img src=\"$filename_flag\" border=\"1\">";
	return($_name);
}

}

function generate_allilink($ali_id)
{

	if ($ali_id <= 0){
		return"";
	}
	$module=get_modulname(4);
	$modul_org="./../".$module[0]."/".$module[1];
	$a_name=allianz($ali_id);

	$user_link="<a href=\"$modul_org?action=get_info&id=$ali_id\">$a_name</a>";
	return  $user_link;
}

function generate_userlink($user_id,$username)
{
	$user_link="<a href=\"../members/members.php?action=profile&id=$user_id\">$username</a>";
	return  $user_link;
}

function generate_messagelink($username,$show_icon=0)
{
	$module=get_modulname(6);
	$msg_modul_org="./../".$module[0]."/".$module[1];
	$icon_url="";
	if ($show_icon==1){
		$icon_url="<div id=\"msglinkimg\"><img src=\"".LITO_IMG_PATH_URL.$module[0]."/newpost.png\" alt=\"Nachricht senden\" title=\"Nachricht senden\" border=\"0\"></div>";
	}

	$msg_link="<div id=\"msglink\"><a href=\"$msg_modul_org?action=send&username=$username\">".$icon_url." Nachricht senden</a></div>";
	return  $msg_link;
}
function generate_messagelink_smal($username)
{
	$module=get_modulname(6);
	$msg_modul_org="./../".$module[0]."/".$module[1];
	$icon_url="<img src=\"".LITO_IMG_PATH_URL.$module[0]."/newpost.png\" alt=\"Nachricht senden\" title=\"Nachricht senden\" border=\"0\">";
	$msg_link="<div id=\"msglink\"><a href=\"$msg_modul_org?action=send&username=$username\">".$icon_url."</a></div>";
	return  $msg_link;
}

function allianz($aid) {
	global $db,$n;
	$result=$db->query("SELECT name,aid FROM cc".$n."_allianz WHERE aid='$aid'");
	$row=$db->fetch_array($result);
	if($row['aid']!=$aid) $value=0;
	else $value=c_trim($row['name']);
	return $value;
}

function get_allianz_points($aid) {
	global $db,$n;
	$result=$db->query("SELECT points,aid FROM cc".$n."_allianz WHERE aid='$aid'");
	$row=$db->fetch_array($result);
	if($row['aid']!=$aid) $value=0;
	else $value=$row['points'];
	return $value;
}


function get_new_msg_count()
{
	global $db,$n,$userdata;
	$result=$db->query("SELECT count(isnew) as anz  FROM cc".$n."_messages WHERE touserid='".$userdata[userid]."' and isnew='1'");
	$row=$db->fetch_array($result);

	return $row['anz'];

}
function is_build_id_present($bid){
	global $db,$n,$userdata;
	$result=$db->query("SELECT name FROM cc".$n."_buildings WHERE bid  ='".$bid."'");
	$row=$db->fetch_array($result);
	if ($row['name']=="" ){
		return 0;
	}else{
	return 1;
}

}
function resreload($countryid){
	global $db,$n,$userdata,$op_store_mulit,$op_set_store_max, $op_res_reload_time,$op_set_res1,$op_set_res2,$op_set_res3,$op_set_res4,$op_mup_res1,$op_mup_res2,$op_mup_res3,$op_mup_res4;
	if (intval($op_res_reload_type==1)){
		return;
	}
	if ($countryid	<=0 ){
		return;
	}



	$result=$db->query("SELECT * FROM cc".$n."_countries WHERE islandid  ='".$countryid."'");
	$row=$db->fetch_array($result);
	$time4ressources = time() - $row['lastressources'];
	$numOfRessources = abs($time4ressources / $op_res_reload_time);
	$numOfRessources1 = floor ($time4ressources / $op_res_reload_time);

	if ($numOfRessources1 >= 1) {
		$store_max = $op_set_store_max * (($row['store'] + 1) * $op_store_mulit);
		$SetRes1 = $row['res1']+(($op_set_res1 + ($row['res1mine'] * $op_mup_res1)) * $numOfRessources);
		$SetRes2 = $row['res2']+(($op_set_res2 + ($row['res2mine'] * $op_mup_res2)) * $numOfRessources);
		$SetRes3 = $row['res3']+(($op_set_res3 + ($row['res3mine'] * $op_mup_res3)) * $numOfRessources);
		$SetRes4 = $row['res4']+(($op_set_res4 + ($row['res4mine'] * $op_mup_res4)) * $numOfRessources);

		if ($SetRes1 > $store_max) {
			$SetRes1 = $store_max;
		}
		if ($SetRes2 > $store_max) {
			$SetRes2 = $store_max ;
		}
		if ($SetRes3 > $store_max) {
			$SetRes3 = $store_max ;
		}
		if ($SetRes4 > $store_max) {
			$SetRes4 = $store_max ;
		}

		$SetRes1 =round($SetRes1 ,0);
		$SetRes2 =round($SetRes2 ,0);
		$SetRes3 =round($SetRes3 ,0);
		$SetRes4 =round($SetRes4 ,0);
		$new_last_res_time=($row['lastressources'] +($numOfRessources1 *$op_res_reload_time));
		//$div =time()-$new_last_res_time;
		//trace_msg ("last_res_time : $new_last_res_time currenttime: ".time()." anzhal:$numOfRessources anzahl_1:$numOfRessources1   last:".$row['lastressources']." new_div:$div --$time4ressources  " ,77);
		trace_msg("Function resreload ($countryid) res1:$SetRes1 res2:$SetRes2 res3:$SetRes3 res4:$SetRes4 storemax:$store_max", 77);
		$db->query("UPDATE cc" . $n . "_countries SET res1='$SetRes1', res2='$SetRes2', res3='$SetRes3', res4='$SetRes4', lastressources='" .$new_last_res_time. "' WHERE islandid='" . $countryid . "'");

		if ($countryid==$userdata['islandid']){
			$userdata['res1']=$SetRes1;
			$userdata['res2']=$SetRes2;
			$userdata['res3']=$SetRes3;
			$userdata['res4']=$SetRes4;
			$userdata['lastressources']=$new_last_res_time;
		}
	}
	return;
}

function get_banner_code (){

	global $db,$n,$userdata;

	if (is_modul_name_aktive("acp_bannermgr")==0){
		return;
	}

	$result=$db->query("SELECT * FROM cc".$n."_banner_mgr where active = 1 ORDER BY RAND() limit 1");// WHERE  userid  ='$user_id'");
	$row=$db->fetch_array($result);
	if (intval($row['banner_id'])> 0 ){
		$result=$db->query("update cc".$n."_banner_mgr set banner_count=banner_count+1 where banner_id='".$row['banner_id']."'");
	}
	return $row['banner_code'];

}

function make_tooltip_text($text){
	$text=str_replace('"',"\'",$text);
	$tt="onmouseover=\"Tip('$text',PADDING, 1,BGCOLOR, '#D3E3F6',TEXTALIGN,'left',SHADOW, true)\" onmouseout=\"UnTip()\"";
	return $tt;
}

?>
