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
$modul_name="alliance";
require("./../../includes/global.php");

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

/*
1= Allianztext �ndern
2= Allianzr�nge �ndern
3= Allianzr�nge vergeben
4= Allianznews �ndern
5= Allianzfahne �ndern
6= Rundmail schreiben
7= Forumeinstellungen �ndern
*/

function get_alianz_members($allianzid) {
 global $db,$n,$userdata;
   $sql_s ="SELECT count(userid) as anzahl FROM cc".$n."_users WHERE allianzid='$allianzid'";
   $result=$db->query($sql_s);
   $row_ali=$db->fetch_array($result);
   $maxallianzmembers=$row_ali['anzahl'];	
   return $maxallianzmembers;
	}

function flag_save($ali_id){
	global $db,$n, $userdata;
	$allifahne=$db->query("SELECT fahne FROM cc".$n."_allianz WHERE aid='$ali_id'");
	$fahnerow=$db->fetch_array($allifahne);
	$fahcol=$fahnerow['fahne'];


	$filename_flag=LITO_ROOT_PATH."alli_flag/flag_".$ali_id.".png";


	$image	= imagecreatetruecolor(20, 15);
	$image2 = imagecreatetruecolor(15, 10);
	$f[1]	= imagecolorallocate($image, 255, 255, 255);
	$f[2]	= imagecolorallocate($image, 50, 50, 50);
	$f[3]	= imagecolorallocate($image, 255, 0, 0);
	$f[4]	= imagecolorallocate($image, 0, 255, 0);
	$f[5]	= imagecolorallocate($image, 0, 0, 255);
	$f[6]	= imagecolorallocate($image, 255, 255, 0);
	$f[7]	= imagecolorallocate($image, 255, 170, 0);
	$f[8]	= imagecolorallocate($image, 0, 255, 255);
	$f[9]	= imagecolorallocate($image, 150, 150, 150);
	imagefill($image, 1, 1, $f[1]);

	if ($fahcol) {
		imagefilledrectangle($image, 0, 0, 6.7, 5, $f[$fahcol[0]]);
		imagefilledrectangle($image, 6.6, 0, 13.4, 5, $f[$fahcol[1]]);
		imagefilledrectangle($image, 13.4, 0, 20, 5, $f[$fahcol[2]]);
		imagefilledrectangle($image, 0, 5, 6.7, 10, $f[$fahcol[3]]);
		imagefilledrectangle($image, 6.6, 5, 13.4, 10, $f[$fahcol[4]]);
		imagefilledrectangle($image, 13.4, 5, 20, 10, $f[$fahcol[5]]);
		imagefilledrectangle($image, 0, 10, 6.7, 15, $f[$fahcol[6]]);
		imagefilledrectangle($image, 6.6, 10, 13.4, 15, $f[$fahcol[7]]);
		imagefilledrectangle($image, 13.4, 10, 20, 15, $f[$fahcol[8]]);
	}
	imagecopyresampled($image2,$image,0,0,0,0,15,10,20,15);
	imagecopyresampled($image,$image2,0,0,0,0,20,15,15,10);

	if (file_exists($filename_flag)) {
		unlink($filename_flag);
	}
	imagepng($image2, $filename_flag);

	imagedestroy($image);
	imagedestroy($image2);


}

function get_msg_count($boradid)
{
	global $db,$n, $userdata;
	$alle_msg=0;
	$result=$db->query("SELECT count(messageid) as uuuu FROM cc".$n."_amessage WHERE allianzid='$userdata[allianzid]' AND boardid='$boradid' ");
	while($row=$db->fetch_array($result)) {
		$alle_msg=$row['uuuu'];
	}
	return $alle_msg;
}

function get_rang_from_user($usersid)
{
	global $db,$n, $userdata;
	$ali_id=$userdata['allianzid'];
	$users_rang_is=-1;
	$result_s=$db->query("SELECT rang_id  FROM cc".$n."_allianz_rang_user WHERE  user_id ='$usersid' and allianz_id  ='$ali_id'");
	while($row_s=$db->fetch_array($result_s)) {
		$users_rang_is=$row_s['rang_id'];
	}
	return $users_rang_is;
}
function get_rang_id_from_user($usersid,$allianzid)
{
	global $db,$n, $userdata;
	$users_rang_is="";
	$result_s=$db->query("SELECT rang_id  FROM cc".$n."_allianz_rang_user WHERE  user_id ='$usersid' and allianz_id  ='$allianzid'");
	while($row_s=$db->fetch_array($result_s)) {
		$users_rang_is=$row_s['rang_id'];

	}
	return $users_rang_is;
}

function get_rang_name_from_allianz_rang($allianz_rang_id,$alli_id)
{
	global $db,$n, $userdata;
	$name="";
	$result_s=$db->query("SELECT rangname FROM cc".$n."_allianz_rang  WHERE  allianz_rang_id ='$allianz_rang_id' and allianz_id ='$alli_id'");
	while($row_s=$db->fetch_array($result_s)) {
		$name=c_trim(($row_s['rangname']));
	}
	return $name;
}

function is_allowed($right_name){
	global $db,$n, $userdata;
	$ret=0;
	$ali_id=$userdata['allianzid'];
	$userid_id=$userdata['userid'];

	if($userdata['is_ali_admin']==1) {
		return "1";
		exit();
	}else{
	$this_rang=get_rang_from_user($userid_id);
	$result_rights=$db->query("SELECT * FROM cc".$n."_allianz_rechte WHERE allianz_id='$ali_id' and rang_id ='$this_rang' ");
	while($row_right=$db->fetch_array($result_rights)) {
		if ($row_right[$right_name]==1){
			return "1";
			exit();
		}
	}
}
return $ret;
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$lang_file=LITO_LANG_PATH.$modul_name.'/lang_'.$lang_suffix.'.php';
$tpl->config_load($lang_file);
$ln_allianz_c_r_2 = $tpl ->get_config_vars('ln_allianz_c_r_2');
$ln_allianz_c_r_13 = $tpl ->get_config_vars('ln_allianz_c_r_13');
$ln_allianz_c_r_14 = $tpl ->get_config_vars('ln_allianz_c_r_14');
$ln_allianz_c_r_5 = $tpl ->get_config_vars('ln_allianz_c_r_5');
$ln_allianz_c_r_6 = $tpl ->get_config_vars('ln_allianz_c_r_6');
$ln_allianz_c_r_8 = $tpl ->get_config_vars('ln_allianz_c_r_8');
$ln_allianz_c_r_15 = $tpl ->get_config_vars('ln_allianz_c_r_15');
$ln_allianz_c_r_16 = $tpl ->get_config_vars('ln_allianz_c_r_16');
$ln_allianz_index_2 = $tpl ->get_config_vars('ln_allianz_index_2');
$ln_allianz_index_3 = $tpl ->get_config_vars('ln_allianz_index_3');
$ln_allianz_php_10 = $tpl ->get_config_vars('ln_allianz_php_10');
$ln_allianz_php_11 = $tpl ->get_config_vars('ln_allianz_php_11');
$ln_allianz_php_8 = $tpl ->get_config_vars('ln_allianz_php_8');
$ln_allianz_php_9 = $tpl ->get_config_vars('ln_allianz_php_9');


$ln_allianz_php_5 = $tpl ->get_config_vars('ln_allianz_php_5');
$ln_allianz_b_in_1 = $tpl ->get_config_vars('ln_allianz_b_in_1');
$ln_login_e_6 = $tpl ->get_config_vars('ln_login_e_6');
$ln_login_e_7 = $tpl ->get_config_vars('ln_login_e_7');

$module=get_modulname(6);
$msg_modul_org="./../".$module[0]."/".$module[1];

$modul=get_modulname(18);
$ali_modul_org="./../".$modul[0]."/".$modul[1];


if($action=="main") {


	$show_menue="";
	$user_id=$userdata['userid'];
	$ali_id=$userdata['allianzid'];


	if($userdata['allianzid']==0) {
		template_out('alliance_join.html',$modul_name);
		exit();
	} else {



		$result=$db->query("SELECT userid,username,allianzid,is_ali_admin,points FROM cc".$n."_users WHERE allianzid='$allianz[aid]'");
		while($row=$db->fetch_array($result)) {

			$name="k.A";
			$rang_id=get_rang_id_from_user($row['userid'],$ali_id);
			$name =get_rang_name_from_allianz_rang($rang_id,$ali_id);

			$members.="$row[username] <b>$row[points]</b>".(($row['is_ali_admin']==1) ? (" (Admin)") : (" ($name)"))."\n<br>\n";
			$pm_array[]=$row['username'];
		}
		$pm_user=implode(",",$pm_array);




		$result_ssi=$db->query("SELECT aid, max_members FROM cc".$n."_allianz WHERE aid='$userdata[allianzid]'");
		$row_ali=$db->fetch_array($result_ssi);
		$ali_flag=get_allianz_flag($userdata[allianzid]);
		$allianz['name']=c_trim($allianz['name']);
		$banner_ali=trim($allianz['imageurl']);
		if ($banner_ali==""){
			$banner_ali=LITO_IMG_PATH_URL.$modul_name."/no_ali_banner.png";
		}
		$maxallianzmembers=$op_max_ali_members;




		$tpl->assign('banner_ali', $banner_ali);
		$tpl->assign('members', $members);
		$tpl->assign('ali_flag', $ali_flag);
		$tpl->assign('a_name',  $allianz['name']);
		$tpl->assign('maxallianzmembers',  $maxallianzmembers);
		$tpl->assign('a_members',  $allianz[members]);



		if($userdata['is_ali_admin']==1) {
			// bewerbungen anzeigen
			$count=0;

			$result_bewerbungen=$db->query("SELECT * FROM cc".$n."_allianz_bewerbung WHERE allianz_id ='$userdata[allianzid]' order by datum");
			while($row_bewerb=$db->fetch_array($result_bewerbungen)) {
				$be_id=$row_bewerb['bewerber_id'];
				$t_bewerbung_id=$row_bewerb['bewerbung_id'];
				$be_name=username($be_id);
				$be_datum =date("d.m.Y H:i:s",$row_bewerb['datum']);
				$b_text=$row_bewerb['bewerber_text'];
				$ok_a="<a href=\"alliance.php?action=bewerben_accept&id=$t_bewerbung_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/ok.png\" ALT=\"$ln_login_e_6\" border=\"0\" TITLE=\"$ln_login_e_6\"></a> ";
				$del_a="<a href=\"alliance.php?action=bewerben_cancel&id=$t_bewerbung_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/nok.png\" ALT=\"$ln_login_e_7\" border=\"0\" TITLE=\"$ln_login_e_7\"></a> ";

				$bew_array[$count]['be_id']=	$be_id;
				$bew_array[$count]['be_name']=	$be_name;
				$bew_array[$count]['be_datum']=	$be_datum;
				$bew_array[$count]['ok_a']=	$ok_a;
				$bew_array[$count]['del_a']=	$del_a;
				$bew_array[$count]['b_text']=	$b_text;
				$count++;


			}
			$tpl->assign('ali_data_bew', $bew_array);


			$show_menue="";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_ali_text\">$ln_allianz_c_r_2</li></a>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_rang\">$ln_allianz_c_r_13</li></a>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_rang_user\">$ln_allianz_c_r_14</a></li>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_news\">$ln_allianz_c_r_5</a></li>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=fahne\">$ln_allianz_c_r_6</a></li>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_forum\">$ln_allianz_c_r_8</a></li>";
			$show_menue=$show_menue."<li><a href=\"".$ali_modul_org."\">$ln_allianz_c_r_15</a></li>";
			$show_menue=$show_menue."<li><a href=\"".$msg_modul_org."?action=send&username=$pm_user\">$ln_allianz_c_r_16</li></a>";
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=kick\">$ln_allianz_index_2</a></li>";
			$show_menue=$show_menue."<li><a href=\"#\"  onclick=\"delalli()\">$ln_allianz_index_3</a></li>";




			$tpl->assign('show_menue', $show_menue);
			template_out('ali_admin_menu.html',$modul_name);


			exit();
		}else
		{
			$urang= get_rang_from_user($user_id);
			$result_rang=$db->query("SELECT * FROM cc".$n."_allianz_rechte WHERE allianz_id='$ali_id' and rang_id ='$urang'");
			$show_menue="";
			while($row_rang=$db->fetch_array($result_rang)) {

				if ($row_rang['change_text']==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_ali_text\">$ln_allianz_c_r_2</a></li>";
				}
				if ($row_rang[change_rang]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_rang\">$ln_allianz_c_r_13</a></li>";
				}
				if ($row_rang[give_rang]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_rang_user\">$ln_allianz_c_r_14</a></li>";
				}
				if ($row_rang[change_news]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_news\">$ln_allianz_c_r_5</a></li>";
				}
				if ($row_rang[change_fahne]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=fahne\">$ln_allianz_c_r_6</a></li>";
				}
				if ($row_rang[write_rundmail]==1){
					$show_menue=$show_menue."<li><a href=\"".$msg_modul_org."?action=send&username=$pm_user\">$ln_allianz_c_r_16</a></li>";
				}
				if ($row_rang[change_forum]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=change_forum\">$ln_allianz_c_r_8</a></li>";
				}
				if ($row_rang[use_bord]==1){
					$show_menue=$show_menue."<li><a href=\"".$ali_modul_org."\">$ln_allianz_c_r_15</a></li>";
				}
				if ($row_rang[use_kasse]==1){
					$show_menue=$show_menue."<li><a href=\"alliance.php?action=alli_bank\">Allianzkasse</a></li>";
				}

			}
			$show_menue=$show_menue."<li><a href=\"alliance.php?action=leave\">$ln_allianz_index_4</a></li>";

			template_out('ali_admin_menu.html', $modul_name);
			exit();
		}
	}
}
if($action=="change_rang_user") {
	$erlaubt=is_allowed("give_rang");
	$ali_id=$userdata['allianzid'];

	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}

	$user_sel_id=intval(c_trim($_GET['uid']));
	$rang_sel_id=intval(c_trim($_GET['id']));
	if ($user_sel_id >0 && $rang_sel_id> 0 ){
		$db->unbuffered_query("delete from cc".$n."_allianz_rang_user WHERE allianz_id ='$ali_id' and user_id ='$user_sel_id'");
		$db->query("INSERT INTO cc".$n."_allianz_rang_user(allianz_id  ,rang_id ,user_id  ) VALUES ('$ali_id','$rang_sel_id','$user_sel_id')");
		header("LOCATION: alliance.php?action=change_rang_user");
		exit();
	}
	if ($user_sel_id >0 && $rang_sel_id ==-1 ){
		$db->unbuffered_query("delete from cc".$n."_allianz_rang_user WHERE allianz_id ='$ali_id' and user_id ='$user_sel_id'");
		header("LOCATION: alliance.php?action=change_rang_user");
		exit();
	}


	$result=$db->query("SELECT userid,username  FROM cc".$n."_users WHERE  allianzid='$ali_id'");
	$counter=0;
	$array_count =0;
	while($row=$db->fetch_array($result)) {
		$counter=$counter+1;
		$uname=$row['username'];
		$uid=$row['userid'];
		$user_rank_id=get_rang_from_user($uid);

		$all_rang_names="<select name='rang_names' class=\"button\" ONCHANGE='location.href=this.options[this.selectedIndex].value'>";
		$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang_user&id=-1&uid=$uid'>kein Rang</option>";
		$result_rang=$db->query("SELECT * FROM cc".$n."_allianz_rang WHERE allianz_id='$ali_id'");
		while($row_rang=$db->fetch_array($result_rang)) {
			$t_id=$row_rang['allianz_rang_id'];

			if ($t_id==$user_rank_id){
				$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang_user&id=$row_rang[allianz_rang_id]&uid=$uid' selected>$row_rang[rangname]</option>" ;
			}else{
			$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang_user&id=$row_rang[allianz_rang_id]&uid=$uid'>$row_rang[rangname]</option>" ;
		}
	}

	$rank_array[$array_count]['counter']=$counter;
	$rank_array[$array_count]['uname']=$uname;
	$rank_array[$array_count]['all_rang_names']=$all_rang_names;
	$array_count++;
}


$tpl->assign('ali_data_rank', $rank_array);
template_out('ali_user_rang.html',$modul_name);


exit();

}


if($action=="change_rang") {
	$erlaubt=is_allowed("change_rang");
	$ali_id=$userdata['allianzid'];

	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$gewaehlt=intval(c_trim($_GET['id']));
	$tt_id=$gewaehlt;
	$all_rang_names="<select name='rang_names' class=\"button\" ONCHANGE='location.href=this.options[this.selectedIndex].value'>";
	$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang'>$ln_allianz_php_3</option>";
	$result=$db->query("SELECT * FROM cc".$n."_allianz_rang WHERE allianz_id='$ali_id'");
	while($row=$db->fetch_array($result)) {
		$t_id=$row['allianz_rang_id'];

		//exit();
		if ($t_id==$gewaehlt){
			$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang&id=$row[allianz_rang_id]' selected>$row[rangname]</option>" ;
		}else{
		$all_rang_names=$all_rang_names ."<option value='alliance.php?action=change_rang&id=$row[allianz_rang_id]'>$row[rangname]</option>" ;
	}
}
$all_rang_names=$all_rang_names."</select>";

if ($gewaehlt > 0 ){
	$result_rang=$db->query("SELECT * FROM cc".$n."_allianz_rechte WHERE rang_id ='$gewaehlt' and allianz_id ='$ali_id'");
	$row_rang=$db->fetch_array($result_rang);
	$change_text_b="";
	$change_rang_b="";
	$give_rang_b="";
	$change_news_b="";
	$change_fahne_b="";
	$write_rundmail_b="";
	$change_forum_b="";
	$use_bord_b="";
	if ($row_rang[change_text]==1)$change_text_b="checked";
	if ($row_rang[change_rang]==1)$change_rang_b="checked";
	if ($row_rang[give_rang]==1)$give_rang_b="checked";
	if ($row_rang[change_news]==1)$change_news_b="checked";
	if ($row_rang[change_fahne]==1)$change_fahne_b="checked";
	if ($row_rang[write_rundmail]==1)$write_rundmail_b="checked";
	if ($row_rang[change_forum]==1)$change_forum_b="checked";
	if ($row_rang[use_bord]==1)$use_bord_b="checked";
	if ($row_rang[use_kasse]==1)$use_kasse_b="checked";


}
echo($change_text_b."<br>");


$tpl->assign('tt_id', $tt_id);
$tpl->assign('change_text_b', $change_text_b);
$tpl->assign('change_rang_b', $change_rang_b);
$tpl->assign('give_rang_b', $give_rang_b);
$tpl->assign('change_news_b', $change_news_b);
$tpl->assign('change_fahne_b', $change_fahne_b);
$tpl->assign('write_rundmail_b', $write_rundmail_b);
$tpl->assign('change_forum_b', $change_forum_b);
$tpl->assign('use_bord_b', $use_bord_b);
$tpl->assign('use_kasse_b', $use_kasse_b);




$tpl->assign('all_rang_names', $all_rang_names);

template_out('ali_create_rang.html',$modul_name);



}


if($action=="update_rang") {
	$ali_id=$userdata['allianzid'];
	$erlaubt=is_allowed("change_rang");
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$gewaehlt=intval((c_trim($_POST['nur'])));
	$change_text_P=(c_trim($_POST['change_text']));
	$change_rang_P=(c_trim($_POST['change_rang']));
	$rang_P=(c_trim($_POST['rang']));
	$change_news_P=(c_trim($_POST['change_news']));
	$change_fahne_P=(c_trim($_POST['change_fahne']));
	$write_rundmail_P=(c_trim($_POST['write_rundmail']));
	$change_forum_P=(c_trim($_POST['change_forum']));
	$use_bord_P=(c_trim($_POST['use_bord']));
	$use_kasse_P=(c_trim($_POST['use_kasse']));




	if ($change_text_P != "" )$change_text_P=1; else $change_text_P=0;
	if ($change_rang_P != "" )$change_rang_P=1; else $change_rang_P=0;
	if ($rang_P != "" )$rang_P=1; else $rang_P=0;
	if ($change_news_P != "" )$change_news_P=1; else $change_news_P=0;
	if ($change_fahne_P != "" )$change_fahne_P=1; else $change_fahne_P=0;
	if ($write_rundmail_P != "" )$write_rundmail_P=1; else $write_rundmail_P=0;
	if ($change_forum_P != "" )$change_forum_P=1; else $change_forum_P=0;
	if ($use_bord_P != "" )$use_bord_P=1; else $use_bord_P=0;
	if ($use_kasse_P != "" )$use_kasse_P=1; else $use_kasse_P=0;

	$db->unbuffered_query("delete from cc".$n."_allianz_rechte WHERE allianz_id ='$ali_id' and rang_id ='$gewaehlt'");
	$db->query("INSERT INTO cc".$n."_allianz_rechte (allianz_id ,rang_id ,change_text ,change_rang,give_rang ,change_news,change_fahne ,write_rundmail,change_forum, use_bord,use_kasse  ) VALUES ('$ali_id','$gewaehlt','$change_text_P','$change_rang_P','$rang_P','$change_news_P','$change_fahne_P','$write_rundmail_P','$change_forum_P','$use_bord_P','$use_kasse_P')");
	header("LOCATION: alliance.php?action=change_rang&id=$gewaehlt");


}


if($action=="new_rang") {
	$erlaubt=is_allowed("change_rang");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$neuer_rang=c_trim($_POST['new_rang']);
	if ($neuer_rang==""){
		show_error ('ln_allianz_e_1',$modul_name);
		exit();

	}
	$db->query("INSERT INTO cc".$n."_allianz_rang (allianz_id ,rangname ) VALUES ('$ali_id','$neuer_rang')");

	header("LOCATION: alliance.php?action=change_rang");
	exit();
}

if($action=="change_ali_text") {
	$erlaubt=is_allowed("change_text");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$result_e=$db->query("SELECT * FROM cc".$n."_allianz WHERE aid ='$ali_id' ");
	while($row_e=$db->fetch_array($result_e)) {
		$allianz_t=$row_e['text'];
		$description=$row_e['text_long'];
		$allianz_i_url=trim($row_e['imageurl']);
		if ($allianz_i_url==""){

			$allianz_i_url=LITO_IMG_PATH_URL.$modul_name."/no_ali_banner.png";
		}
	}

	$tpl->assign('allianz_i_url', $allianz_i_url);
	$tpl->assign('allianz_t', $allianz_t);
	$tpl->assign('description', $description);
	template_out('ali_text.html',$modul_name);

	exit();
}
if($action=="change_ali_text_s") {
	$erlaubt=is_allowed("change_text");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$text=c_trim($_POST['text']);
	$text_l=html2bb($_POST['descr']);
	$db->query("UPDATE cc".$n."_allianz SET text='".$text."',text_long='".$text_l."'  WHERE aid='".$ali_id."'");

	$password=c_trim($_POST['password']);
	if($password!="") {
		$db->query("UPDATE cc".$n."_allianz SET password='$password' WHERE aid='".$userdata['allianzid']."'");
	}
	header("LOCATION: alliance.php?cxid=$cxid");
	exit();
}
if($action=="change_news_s") {
	$erlaubt=is_allowed("change_news");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$text_l=html2bb($_POST['descr']);
	$change_date=time();
	$db->query("delete from  cc".$n."_allianznews WHERE allianz_id ='$ali_id'");
	$db->query("INSERT INTO cc".$n."_allianznews (allianz_id , a_news_text,change_date ) VALUES ('$ali_id','$text_l','$change_date')");
	header("LOCATION: alliance.php");
	exit();
}
if($action=="change_news") {
	$erlaubt=is_allowed("change_news");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$result_e=$db->query("SELECT * FROM cc".$n."_allianznews WHERE allianz_id  ='$ali_id' ");
	while($row_e=$db->fetch_array($result_e)) {
		$description=$row_e['a_news_text'];
		$c_date=$row_e['change_date'];
		$change_date=date("d.m.Y (H:i:s)",$c_date);
	}

	$tpl->assign('description', $description);
	$tpl->assign('change_date', $change_date);

	template_out('ali_news.html',$modul_name);

	exit();
}
if($action=="fahne") {
	$erlaubt=is_allowed("change_fahne");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$ali_flag_pic=get_allianz_flag($ali_id);
	$result_e=$db->query("SELECT fahne FROM cc".$n."_allianz WHERE aid ='$ali_id' ");
	while($allianz=$db->fetch_array($result_e)) {
		$f1[$allianz['fahne'][0]]="selected";
		$f2[$allianz['fahne'][1]]="selected";
		$f3[$allianz['fahne'][2]]="selected";
		$f4[$allianz['fahne'][3]]="selected";
		$f5[$allianz['fahne'][4]]="selected";
		$f6[$allianz['fahne'][5]]="selected";
		$f7[$allianz['fahne'][6]]="selected";
		$f8[$allianz['fahne'][7]]="selected";
		$f9[$allianz['fahne'][8]]="selected";

	}




	$tpl->assign('ali_flag_pic', $ali_flag_pic);
	$tpl->assign('f1', $f1);
	$tpl->assign('f2', $f2);
	$tpl->assign('f3', $f3);
	$tpl->assign('f4', $f4);
	$tpl->assign('f5', $f5);
	$tpl->assign('f6', $f6);
	$tpl->assign('f7', $f7);
	$tpl->assign('f8', $f8);
	$tpl->assign('f9', $f9);
	template_out('ali_flag.html',$modul_name);

	exit();
}
if($action=="fahne_s") {
	$erlaubt=is_allowed("change_fahne");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}

	$fahne=$_POST['1'].$_POST['2'].$_POST['3'].$_POST['4'].$_POST['5'].$_POST['6'].$_POST['7'].$_POST['8'].$_POST['9'];
	$db->query("UPDATE cc".$n."_allianz SET fahne='$fahne' WHERE aid='$ali_id'");
	flag_save($ali_id);
	header("LOCATION: alliance.php?action=fahne");

}


if($action=="change_forum"){
	$erlaubt=is_allowed("change_forum");
	$ali_id=$userdata['allianzid'];
	$allianz_boards="";
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}
	$ali_count=0;
	$result_e=$db->query("SELECT * FROM cc".$n."_forum   WHERE alli_id ='$ali_id' ");
	while($row_e=$db->fetch_array($result_e)) {
		$katname= $row_e['si_forum_name'];
		$description= $row_e['si_forum_desc'];
		$aktion="delete";
		$forum_id = $row_e['si_forum_id'];

		$ali_forum[$ali_count]['katname']=$katname;
		$ali_forum[$ali_count]['description']=$description;
		$ali_forum[$ali_count]['aktion']=$aktion;
		$ali_forum[$ali_count]['forum_id']=$forum_id ;
		$ali_count++;

	}


	$tpl->assign('ali_forum', $ali_forum);
	template_out('ali_board_a.html',$modul_name);

	exit();
}
if($action=="change_forum_s") {
	$erlaubt=is_allowed("change_forum");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}

	$boards=c_trim($_POST['boards']);
	$description=c_trim($_POST['descript']);


	$db->query("Insert into cc".$n."_forum (si_forum_name,si_forum_desc,alli_id) VALUES ('$boards','$description','$ali_id')");

	header("LOCATION: alliance.php?action=change_forum");
	exit();

}

if($action=="use_bord") {

	$erlaubt=is_allowed("use_bord");
	$ali_id=$userdata['allianzid'];
	$ali_user_id=$userdata['userid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}


	$db->unbuffered_query("UPDATE cc".$n."_users SET newallianzmessage='0' WHERE allianzid='$ali_id' and userid='$ali_user_id'");
	if(isset($_REQUEST['boardid'])) $boardid=intval($_REQUEST['boardid']);
	else $boardid="";

	if($boardid) {
		$msg_count=0;
		$result=$db->query("SELECT * FROM cc".$n."_amessage WHERE allianzid='$userdata[allianzid]' AND boardid='$boardid' ORDER BY time DESC");
		while($row=$db->fetch_array($result)) {
			$title=c_trim($row['title']);
			$text=c_trim($row['text']);
			$username=c_trim($row['username']);
			$dates=date("d.m.Y H:i:s",$row['time']);

			eval ("\$allianz_message_bit .= \"".$tpl->get("alliance_message_bit")."\";");
		}
		eval("\$tpl->output(\"".$tpl->get("ali_board_show")."\");");
		exit();
	}
	else {
		$board=explode("\n",$allianz['boards']);
		for($i=0;$i<count($board);$i++) {
			$allianzboardid=$i+1;
			$anzahl_msg=0;
			$anzahl_msg=get_msg_count($allianzboardid);
			$boardname=c_trim($board[$i]);
			eval ("\$allianz_board_bit .= \"".$tpl->get("alliance_board_bit")."\";");
		}

		eval("\$tpl->output(\"".$tpl->get("ali_board")."\");");
		exit();
	}

}



if($action=="kick") {
	if($userdata['is_ali_admin']==0) {
		show_error('ln_allianz_e_12',$modul_name);
		exit();
	}
	$kick_counter=0;
	$result=$db->query("SELECT * FROM cc".$n."_users WHERE allianzid='".$allianz['aid']."'");
	while($row=$db->fetch_array($result)) {
		$username=$row['username'];
		$punke=$row['points'];
		$id=$row['userid'];

		$kicker[$kick_counter]['name']=$username;
		$kicker[$kick_counter]['points']=$punke;
		$kicker[$kick_counter]['id']=$id;

		$kick_counter++;

	}
	$tpl->assign('kicker', $kicker);
	template_out('alliance_kick.html',$modul_name);
	exit();
}

if($action=="dokick") {
	if($userdata['is_ali_admin']==0) {
		show_error('ln_allianz_e_12',$modul_name);
		exit();
	}

	$kuserid=intval($_GET['kuserid']);
	if ($kuserid==$userdata['userid']){
		show_error('ln_allianz_php_4',$modul_name);
		exit();
	}

	$db->query("UPDATE cc".$n."_users SET allianzid='0', is_ali_admin='0', newallianzmessage= '0' WHERE userid='$kuserid' AND allianzid='$allianz[aid]'");
	$db->query("UPDATE cc".$n."_allianz SET members=members-1 WHERE aid='".$userdata['allianzid']."'");
	$db->query("DELETE from  cc".$n."_allianz_rang_user  WHERE user_id ='$kuserid'");

	header("LOCATION: alliance.php");
	exit();
}


if($action=="remove") {
	if($userdata['is_ali_admin']==0) {
		show_error('ln_allianz_e_12',$modul_name);
		exit();
	}
	$db->query("DELETE FROM cc".$n."_allianz WHERE aid='".$userdata['allianzid']."'");
	$db->query("UPDATE cc".$n."_users SET allianzid='0', is_ali_admin='0' , newallianzmessage= '0' WHERE allianzid='$allianz[aid]'");


	$db->query("DELETE FROM cc".$n."_allianz_rang WHERE allianz_id ='".$userdata['allianzid']."'");
	$db->query("DELETE FROM cc".$n."_allianz_rang_user WHERE allianz_id='".$userdata['allianzid']."'");
	$db->query("DELETE FROM cc".$n."_allianz_rechte WHERE allianz_id ='".$userdata['allianzid']."'");



	$db->query("DELETE FROM cc".$n."_allianz_log WHERE ali_id ='".$userdata['allianzid']."'");


	header("LOCATION: alliance.php");
	exit();
}

if($action=="leave") {
	if($userdata['is_ali_admin']==1) {
		show_error('ln_allianz_e_11',$modul_name);
		exit();
	}
	$db->query("UPDATE cc".$n."_allianz SET members=members-1 WHERE aid='$userdata[allianzid]'");
	$db->query("UPDATE cc".$n."_users SET allianzid='0' , newallianzmessage= '0' WHERE userid='$userdata[userid]'");
	$db->query("DELETE FROM cc".$n."_allianz_rang_user WHERE user_id  ='".$userdata['userid']."'");


	header("LOCATION: alliance.php");
	exit();
}

if($action=="join") {
	$allianz=c_trim($_POST['allianz']);
	$password=c_trim($_POST['password']);

	if(!$allianz || !$password) {
		show_error('ln_allianz_e_3',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_allianz WHERE name='$allianz' and space ='0' ");
	$row=$db->fetch_array($result);

	if($row['name']!=$allianz) {
		show_error('ln_allianz_e_4',$modul_name);
		exit();
	}

	if($row['password']!=$password) {
		show_error('ln_allianz_e_5',$modul_name);
		exit();
	}



	/** set max members 25 (old value=25) **/
	$anzahl_members_max=$op_max_ali_members;
	$anzahl_curent =get_alianz_members($row['aid']);

	if ($anzahl_curent >= $anzahl_members_max ){
		show_error('ln_allianz_e_7',$modul_name);
		exit();
	}

	$db->query("UPDATE cc".$n."_allianz SET members=members+'1' WHERE name='$allianz'");
	$db->query("UPDATE cc".$n."_users SET allianzid='$row[aid]' WHERE userid='".$userdata['userid']."'");
	header("LOCATION: alliance.php");
	exit();
}


if($action=="create") {
	$allianz=c_trim($_POST['allianz']);
	$password=c_trim($_POST['password']);

	if(!$allianz || !$password) {
		show_error('ln_allianz_e_8',$modul_name);;
		exit();
	}

	if(strlen($allianz)<3 || strlen($allianz)>20) {
		show_error('ln_allianz_e_9',$modul_name);
		exit();
	}

	$result=$db->query("SELECT * FROM cc".$n."_allianz WHERE name='$allianz'");
	$row=$db->fetch_array($result);

	if($row['name']==$allianz) {
		show_error('ln_allianz_e_10',$modul_name);
		exit();
	}

	$db->query("INSERT INTO cc".$n."_allianz (name,members,password,rassenid,space) VALUES ('$allianz','1','$password','".$userdata['rassenid']."','0')");
	$id=$db->insert_id();
	$db->query("UPDATE cc".$n."_users SET allianzid='$id', is_ali_admin='1' WHERE userid='".$userdata['userid']."'");


	header("LOCATION: alliance.php");
	exit();
}


if($action=="post") {

	$erlaubt=is_allowed("use_bord");
	$ali_id=$userdata['allianzid'];
	if ($erlaubt==0){
		show_error ('ln_allianz_php_2',$modul_name);
		exit();
	}

	$title=c_trim($_POST['title']);
	$text=c_trim($_POST['text']);
	$boardid=intval($_REQUEST['boardid']);
	if(!$text || !$title) {
		error_page($ln_allianz_e_1);
		exit();
	}
	$db->query("INSERT INTO cc".$n."_amessage (allianzid,text,title,username,time,boardid,fromuserid) VALUES ('$userdata[allianzid]','$text','$title','$userdata[username]','".time()."','$boardid','".$userdata['userid']."')");
	$db->unbuffered_query("UPDATE cc".$n."_users SET newallianzmessage='1' WHERE allianzid='".$userdata['allianzid']."'");

	header("LOCATION: alliance.php?action=use_bord&boardid=$boardid");
	exit();
}

if($action=="delpost") {

	if($userdata['is_ali_admin']==0) {
		error_page("$ln_allianz_php_2");
		exit();
	}

	$id=intval($_GET['id']);
	if(!$id) {
		error_page("Fehler keine ID");
		exit();
	}
	$result=$db->query("SELECT * FROM cc".$n."_amessage WHERE messageid='$id' AND allianzid='".$userdata['allianzid']."'");
	$row=$db->fetch_array($result);
	if($row['fromuserid'] != $userdata['userid'] && $userdata['is_ali_admin'] == 0) {
		error_page($ln_allianz_e_2);
		exit();
	}
	$db->query("DELETE FROM cc".$n."_amessage WHERE messageid='$id' AND allianzid='$userdata[allianzid]'");
	header("LOCATION: alliance.php?action=use_bord");
	exit();
}



if($action=="bewerben") {
	$id=intval($_GET['id']);

	if (intval($userdata['allianzid']) > 0 ){
		show_error('ln_allianz_e_7',$modul_name);
		exit();
	}


	$result_e=$db->query("SELECT * FROM cc".$n."_allianz WHERE aid ='$id' ");
	while($row_e=$db->fetch_array($result_e)) {
		$a_name=$row_e['name'];
	}

	$tpl->assign('id', $id);
	$tpl->assign('aliname', $a_name);

	template_out('ali_application.html',$modul_name);

	exit();
}
if($action=="bewerben_go") {
	$id=intval($_GET['id']);

	if (intval($userdata['allianzid']) > 0 ){
		show_error('ln_allianz_e_7',$modul_name);
		exit();
	}

	$b_text=c_trim($_POST['b_text']);
	$uid =$userdata['userid'];
	$uid_name =$userdata['username'];
	$b_date=time();
	$ali_name =allianz($id);

	$ad_id=0;
	$result=$db->query("SELECT * FROM cc".$n."_users WHERE allianzid='$id' and is_ali_admin ='1' ");
	while($row=$db->fetch_array($result)) {
		$ad_id=$row['userid'];
	}
	if ($ad_id > 0 ){

		$db->unbuffered_query("Insert INTO cc".$n."_allianz_bewerbung (allianz_id ,bewerber_id ,datum,bewerber_text) VALUES('$id','$uid','$b_date','$b_text')");
		// benachrichtigung des admins
		$bewerbungs_text="$ln_allianz_php_5";

		make_ingamemail($userdata['userid'],$ad_id,$ln_allianz_b_in_1,$bewerbungs_text);
		make_ingamemail($userdata['userid'],$userdata['userid'],$ln_allianz_b_in_1,"Deine Bewerbung bei ".$ali_name ." wurde abgesendet");


	}else{
	show_error ('ln_allianz_php_7',$modul_name);
	exit();
}

$modul=get_modulname(6);
$msg_modul_org="./../".$modul[0]."/".$modul[1];
header("LOCATION: ".$msg_modul_org);
}

if($action=="bewerben_accept") {
	$id=intval($_GET['id']);
	$my_aid=$userdata['allianzid'];
	$bewerber_id=0;
	$uid =$userdata['userid'];
	$uid_name =$userdata['username'];
	$result=$db->query("SELECT * FROM cc".$n."_allianz_bewerbung WHERE bewerbung_id ='$id' and allianz_id  ='$my_aid' ");
	while($row=$db->fetch_array($result)) {

		$bewerber_allianz_id=$row['allianz_id'];
		$bewerber_id=$row['bewerber_id'];
		if ($bewerber_allianz_id > 0 ){

			$result_pw=$db->query("SELECT password FROM cc".$n."_allianz WHERE aid ='$bewerber_allianz_id'");
			$row_pw=$db->fetch_array($result_pw);
			$kennwort=$row_pw['password'];

			$bewerbungs_text="$ln_allianz_php_8: $kennwort";

			make_ingamemail($uid,$bewerber_id,$ln_allianz_php_9,$bewerbungs_text);



			$db->unbuffered_query("Delete from cc".$n."_allianz_bewerbung where bewerbung_id  ='$id' ");
			header("LOCATION: alliance.php");
		}




	}

}

if($action=="bewerben_cancel") {
	$id=intval($_GET['id']);
	$my_aid=$userdata['allianzid'];
	$bewerber_id=0;
	$uid =$userdata['userid'];
	$uid_name =$userdata['username'];
	$result=$db->query("SELECT * FROM cc".$n."_allianz_bewerbung WHERE bewerbung_id ='$id' and allianz_id  ='$my_aid' ");
	while($row=$db->fetch_array($result)) {

		$bewerber_allianz_id=$row['allianz_id'];
		$bewerber_id=$row['bewerber_id'];
		if ($bewerber_allianz_id > 0 ){


			$bewerbungs_text="$ln_allianz_php_10";

			make_ingamemail($uid,$bewerber_id,$ln_allianz_php_11,$bewerbungs_text);


			$db->unbuffered_query("Delete from cc".$n."_allianz_bewerbung where bewerbung_id  ='$id' ");
			header("LOCATION: alliance.php");
		}




	}
}

if( $action == "change_forum_del" ) {
	// Thx to [GodLesZ]

	if( is_allowed( "change_forum" ) == 0 ){
		error_page( $ln_allianz_php_2 );
		exit();
	}

	$ali_id = $userdata['allianzid'];
	$forumID = intval($_GET['forumid']);

	$db->query( "DELETE FROM `cc".$n."_forum` WHERE `si_forum_id` = '".$forumID."'" );
	$db->query( "DELETE FROM `cc".$n."_forum_last` WHERE `forum_id` = '".$forumID."'" );
	$db->query( "DELETE FROM `cc".$n."_forum_posts` WHERE `si_forum_id` = '".$forumID."'" );
	$db->query( "DELETE FROM `cc".$n."_forum_topics` WHERE `si_forum_id` = '".$forumID."'" );

	header( "LOCATION: alliance.php?action=change_forum" );
	exit();
}

if($action=="get_info") {
	$id=intval($_GET['id']);

	if(!$id) {
		show_error('ln_allianz_e_4',$modul_name);
		exit();
	}
	$result=$db->query("SELECT * FROM cc".$n."_allianz WHERE aid='$id'");
	$row=$db->fetch_array($result);

	$banner=trim($row['imageurl']);
	if ($banner==""){
		$banner=LITO_IMG_PATH_URL.$modul_name."/no_ali_banner.png";
	}

	$description=bb2html($row['text_long']);

	$ibit="";
	$result=$db->query("SELECT userid,username,is_ali_admin,status FROM cc".$n."_users WHERE allianzid='$id' ORDER BY is_ali_admin DESC");
	while($i=$db->fetch_array($result)) {

		$ibit.=generate_messagelink_smal($i[username])." " . generate_userlink($i[userid],$i[username]);
		if ($i['is_ali_admin']==1) {
			$ibit.=" (Leiter)";
		}
		$ibit.=" $img<br>";
	}

	$is_in_ali=0;
	if (intval($userdata['allianzid']) > 0 ){
		$is_in_ali=1;
	}

	$tpl->assign('is_in_ali', $is_in_ali);
	$tpl->assign('banner', $banner);
	$tpl->assign('name', $row['name']);
	$tpl->assign('text', $row['text']);
	$tpl->assign('points', intval($row['points']));
	$tpl->assign('ibit', $ibit);
	$tpl->assign('description', $description);
	$tpl->assign('ali_id', $row['aid']);


	template_out('alliance.html',$modul_name);



}

?>
