<?php
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei
d�rfen NICHT entfernt und NICHT ver�ndert werden.
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
$modul_name="acp_usermgr";

$menu_name="Usermanager";
$tpl->assign( 'menu_name',$menu_name);

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

if($action == 'save'){
	if(!isset($_GET['id'])){
		error_msg('Keine ID &uuml;bergeben');
		exit;
	}
	if(!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['group'])){
		error_msg('Es wurden nicht alle n&ouml;tigen Daten &uuml;bergeben.');
		exit;
	}
	$id=intval($_GET['id']*1);
	$exists = $db->query("SELECT `username` FROM `cc".$n."_users` WHERE (`username` = '".$db->escape_string($_POST['name'])."' OR `email` = '".$db->escape_string($_POST['email'])."') AND `userid` != '".$id."'");
	if($db->num_rows($exists)){
		error_msg('Username oder E-Mail ist bereits vergeben!');
		exit;
	}
	$group = intval($_POST['group']*1);
	$db->query("UPDATE `cc".$n."_users` SET `username` = '".$db->escape_string($_POST['name'])."', `email` = '".$db->escape_string($_POST['email'])."', `group` = '".$group."' WHERE `userid` = '".$id."'");
	if(isset($_POST['passwd']) && $_POST['passwd'] != ''){
		$db->query("UPDATE `cc".$n."_users` SET `password` = '".md5($_POST['passwd'])."' WHERE `userid` = '".$id."'");
	}
	if(isset($_POST['pic'])){
		$db->query("UPDATE `cc".$n."_users` SET `pic` = '' WHERE `userid` = '".$id."'");
	}
	if(isset($_POST['blocked'])){
		$db->query("UPDATE `cc".$n."_users` SET `blocked` = '1' WHERE `userid` = '".$id."'");
	} else {
		$db->query("UPDATE `cc".$n."_users` SET `blocked` = '0' WHERE `userid` = '".$id."'");
	}
	if(isset($_POST['alli'])){
		$db->query("UPDATE `cc".$n."_users` SET `allianzid` = '0' WHERE `userid` = '".$id."'");
	}
	if(isset($_POST['alliadmin'])){
		$db->query("UPDATE `cc".$n."_users` SET `isadmin` = '0' WHERE `userid` = '".$id."'");
	}
	$action = 'main';
}

if($action=="search"){
	if(!isset($_POST['name'])){
		error_msg('Kein Username angegeben!');
		exit();
	}
	$user_q = $db->query("SELECT `userid` FROM `cc".$n."_users` WHERE `username` = '".$db->escape_string($_POST['name'])."'");
	if(!$db->num_rows($user_q)){
		error_msg('User wurde nicht gefunden!');
		exit();
	}
	$action = 'edit';
	$user =$db->fetch_array($user_q);
	$id = $user['userid'];
}

if($action=="chres"){
	if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
		error_msg("ID nicht &uuml;bergeben!");
		exit;
	}
	if(!isset($_POST['cname'])){
		error_msg("Name nicht &uuml;bergeben!");
		exit;
	}
	if(!isset($_POST['cres1']) || !is_numeric($_POST['cres1'])){
		error_msg("Resource 1 nicht &uuml;bergeben!");
		exit;
	}
	if(!isset($_POST['cres2']) || !is_numeric($_POST['cres2'])){
		error_msg("Resource 2 nicht &uuml;bergeben!");
		exit;
	}
	if(!isset($_POST['cres3']) || !is_numeric($_POST['cres3'])){
		error_msg("Resource 3 nicht &uuml;bergeben!");
		exit;
	}
	if(!isset($_POST['cres4']) || !is_numeric($_POST['cres4'])){
		error_msg("Resource 4 nicht &uuml;bergeben!");
		exit;
	}
	$db->query("UPDATE `cc".$n."_countries` SET `name` = '".$db->escape_string($_POST['cname'])."', `res1` = '".intval($_POST['cres1'])."', `res2` = '".intval($_POST['cres2'])."', `res3` = '".intval($_POST['cres3'])."', `res4` = '".intval($_POST['cres4'])."' WHERE `islandid` = '".intval($_GET['id'])."'");
	$action = 'main';
}

if($action=="main") {
	template_out('main.html',$modul_name);
}
if($action == 'list'){
	$users_q = $db->query("SELECT `userid`, `username`, `serveradmin`, `group` FROM `cc".$n."_users`");
	$users = array();
	$i = 0;
	while($user = $db->fetch_array($users_q)){
		$users[$i]['id'] = $user['userid'];
		$users[$i]['name'] = $user['username'];
		$users[$i]['sa'] = $user['serveradmin'];
		$grp_q = $db->query("SELECT `name` FROM `cc".$n."_user_groups` WHERE `id` = '".$user['group']."'");
		if(!$db->num_rows($grp_q))
		$users[$i]['group'] = '<i>keine</i>';
		else {
			$grp = $db->fetch_array($grp_q);
			$users[$i]['group'] = $grp['name'];
		}
		$i++;
	}
	$tpl->assign('users', $users);
	template_out('list.html',$modul_name);
}
if($action == 'edit'){
	if(!isset($id)){
		if(!isset($_GET['id'])){
			error_msg('Keine Userid &uuml;bergeben');
			exit;
		} else
		$id = $_GET['id'];
	}
	$id = $id*1;
	$user_q = $db->query("SELECT `userid`, `username`, `email`, `userpic`, `allianzid`, `is_ali_admin`, `blocked`, `serveradmin`, `group` FROM `cc".$n."_users` WHERE `userid` = '".$id."'");
	if(!$db->num_rows($user_q)){
		error_msg('Der gew&auml;hlte User existiert nicht!');
		exit;
	}
	$user = $db->fetch_array($user_q);
	$grp_q = $db->query("SELECT `name` FROM `cc".$n."_user_groups` WHERE `id` = '".$user['group']."'");
	$grp = $db->fetch_array($grp_q);
	$group = $grp['name'];
	$user_edit = array(
	'id'	=>	$user['userid'],
	'name'	=>	$user['username'],
	'email' =>	$user['email'],
	'pic'	=>	$user['userpic'],
	'alli'	=>	$user['allianzid'],
	'alliadmin'	=>	$user['is_ali_admin'],
	'blocked'	=>	$user['blocked'],
	'sa'	=>	$user['serveradmin'],
	'group'	=>	$group
	);
	$tpl->assign('user', $user_edit);

	//Countries
	$countries_q = $db->query("SELECT `islandid`, `name`, `res1`, `res2`, `res3`, `res4` FROM `cc".$n."_countries` WHERE `userid` = '".$user['userid']."'");
	$countries = array();
	$i = 0;
	while($countrie = $db->fetch_array($countries_q)){
		$countries[$i]['id'] = $countrie['islandid'];
		$countries[$i]['name'] = $countrie['name'];
		$countries[$i]['res1'] = $countrie['res1'];
		$countries[$i]['res2'] = $countrie['res2'];
		$countries[$i]['res3'] = $countrie['res3'];
		$countries[$i]['res4'] = $countrie['res4'];
		$i++;
	}
	$tpl->assign('countries', $countries);
	$grp_av_q = $db->query("SELECT `id`, `name` FROM `cc".$n."_user_groups`");
	$grps_av= array();
	$i = 0;
	while($grp_av = $db->fetch_array($grp_av_q)){
		$grps_av[$i]['id'] = $grp_av['id'];
		$grps_av[$i]['name'] = $grp_av['name'];
		$i++;
	}
	$tpl->assign('grps', $grps_av);
	template_out('edit.html', $modul_name);
}
if($action == 'res'){
	if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
		error_msg('ID nicht &uuml;bergeben!');
		exit;
	}
	$countries_q = $db->query("SELECT `islandid`, `name`, `res1`, `res2`, `res3`, `res4` FROM `cc".$n."_countries` WHERE `islandid` = '".$_GET['id']."'");
	$countries = array();
	$i = 0;
	$countrie = $db->fetch_array($countries_q);
	$countries[$i]['id'] = $countrie['islandid'];
	$countries[$i]['name'] = $countrie['name'];
	$countries[$i]['res1'] = $countrie['res1'];
	$countries[$i]['res2'] = $countrie['res2'];
	$countries[$i]['res3'] = $countrie['res3'];
	$countries[$i]['res4'] = $countrie['res4'];
	$i++;
	$tpl->assign('countrie', $countries[0]);
	template_out('res.html', $modul_name);
}
?>

