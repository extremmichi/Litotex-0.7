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

$modul_name="acp_badwords";
$menu_name="Badwordmanager";
$tpl->assign( 'menu_name',$menu_name);

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

if($action == 'delete'){
	if(!isset($_GET['id'])){
		error_msg('Keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id'] * 1;
	$db->query("DELETE FROM `cc".$n."_badwords` WHERE `badword_id` = '".$id."'");
	$action = 'main';
}

if($action == 'change'){
	if(!isset($_GET['id'])){
		error_msg('Keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id'] * 1;
	$tpl->assign('change', $id);
	$action = 'main';
}

if($action == 'save'){
	if(!isset($_GET['id'])){
		error_msg('Keine ID &uuml;bergeben!');
		exit;
	}
	if(!isset($_POST['title'])){
		error_msg('Kein Titel &uuml;bergeben!');
		exit;
	}
	if(!isset($_POST['in_mail']))
	$_POST['in_mail'] = 0;
	else
	$_POST['in_mail'] = 1;
	$id = $_GET['id'] * 1;
	$db->query("UPDATE `cc".$n."_badwords` SET `badword` = '".$db->escape_string($_POST['title'])."', `in_mail` = '".$db->escape_string($_POST['in_mail'])."' WHERE `badword_id` = '".$id."'");
	$action = 'main';
}

if($action == 'new'){
	if(!isset($_POST['title'])){
		error_msg('Kein Titel &uuml;bergeben!');
		exit;
	}
	if(!isset($_POST['in_mail']))
	$_POST['in_mail'] = 0;
	else
	$_POST['in_mail'] = 1;
	$db->query("INSERT INTO `cc".$n."_badwords` (`badword`, `in_mail`) VALUES ('".$db->escape_string($_POST['title'])."', '".$db->escape_string($_POST['in_mail'])."')");
	$action ='main';
}

if($action == 'main'){
	$badwords = array();
	$words = $db->query("SELECT `badword_id`, `badword`, `in_mail` FROM `cc".$n."_badwords`");
	$i = 0;
	while($badword = $db->fetch_array($words)){
		$badwords[$i]['id'] = $badword['badword_id'];
		$badwords[$i]['title'] = $badword['badword'];
		$badwords[$i]['in_mail'] = $badword['in_mail'];
		$i++;
	}
	$tpl->assign('badwords', $badwords);
	template_out('list.html', $modul_name);
}
?>
