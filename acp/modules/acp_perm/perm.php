<?php
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
$modul_name="acp_perm";
$menu_name="Gruppenmanager";
$tpl->assign( 'menu_name',$menu_name);

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

if($action == "save_mod"){
	if(!isset($_POST['perm'])){
		error_msg('Schwerer Fehler! Formulardaten wurden nicht übergeben!');
		exit;
	}
	foreach($_POST['perm'] as $id => $perm){
		$perm = $perm*1;
		$id = $id*1;
		$db->query("UPDATE `cc".$n."_modul_admin` SET `perm_lvl` = '".$perm."' WHERE `modul_admin_id` = '".$id."'");
	}
	$action = 'listmod';
}

if($action == "save_grp"){
	if((!isset($_POST['perm']) || !isset($_POST['name'])) && (!isset($_POST['new_name']) || !isset($_POST['new_lvl']))){
		error_msg('Schwerer Fehler! Formulardaten wurden nicht übergeben!');
		exit;
	}
	if(isset($_POST['perm']) && isset($_POST['name'])){
		foreach($_POST['perm'] as $id => $perm){
			$perm = $perm*1;
			$id = $id*1;
			$db->query("UPDATE `cc".$n."_user_groups` SET `perm_lvl` = '".$perm."', `name` = '".$db->escape_string($_POST['name'][$id])."' WHERE `id` = '".$id."'");
		}
	}
	if(isset($_POST['new_name']) && $_POST['new_name'] != '' && isset($_POST['new_lvl']) && $_POST['new_lvl'] != ''){
		$_POST['new_lvl'] = $_POST['new_lvl']*1;
		$db->query("INSERT INTO `cc".$n."_user_groups` (`perm_lvl`, `name`) VALUES ('".$_POST['new_lvl']."', '".$db->escape_string($_POST['new_name'])."')");
	}
	$action = 'listgroup';
}

if($action=="main") {
	template_out('main.html',$modul_name);
}
if($action == "listmod")
{
	$mods_q = $db->query("SELECT `modul_admin_id`, `modul_name`, `modul_description`, `acp_modul`, `perm_lvl` FROM `cc".$n."_modul_admin` WHERE `acp_modul` = '1'");
	$mods = array();
	$i = 0;
	while($mod = $db->fetch_array($mods_q)){
		$mods[$i]['id'] = $mod['modul_admin_id'];
		$mods[$i]['name'] = $mod['modul_name'];
		$mods[$i]['description'] = $mod['modul_description'];
		$mods[$i]['perm'] = $mod['perm_lvl'];
		$i++;
	}
	$tpl->assign('mods', $mods);
	template_out('listmod.html',$modul_name);
}
if($action == "listgroup"){
	$groups_q = $db->query("SELECT `id`, `name`, `perm_lvl` FROM `cc".$n."_user_groups`");
	$groups = array();
	$i = 0;
	while($group = $db->fetch_array($groups_q)){
		$groups[$i]['id'] = $group['id'];
		$groups[$i]['name'] = $group['name'];
		$groups[$i]['perm'] = $group['perm_lvl'];
		$i++;
	}
	$tpl->assign('groups', $groups);
	template_out('listgroup.html',$modul_name);
}
?>
