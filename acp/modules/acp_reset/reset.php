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
//Funktionen zum rekursiven Ordner l�schen...
function dir_delete($verz, $folder = array())
{
	$folder[] = $verz;

	$fp = opendir($verz);

	while ($dir_file = readdir($fp))
	{
		if (($dir_file == '.') || ($dir_file == '..'))
		continue;

		$neu_file = $verz . '/' . $dir_file;

		if (is_dir($neu_file))
		$folder = dir_delete($neu_file, $folder);
		else
		unlink($neu_file);
	}

	closedir($fp);

	return $folder;
}

/**/

function all_delete($dir_file)
{
	if (is_dir($dir_file))
	{
		$array = dir_delete($dir_file);
		$array = array_reverse($array);

		foreach ($array as $elem)
		rmdir($elem);
	}
	elseif (is_file($dir_file))
	unlink($dir_file);
	else
	return false;
}
//Ende Funktionen zum l�schen
if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_reset";
$menu_name="Game Reset";
$tpl->assign( 'menu_name',$menu_name);

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
if($action == 'reset'){
	//Der User wollte wirklich... jetzt gibts kein zurück mehr!
	$db->query("TRUNCATE TABLE `cc1_allianz`");
	$db->query("TRUNCATE TABLE `cc1_allianznews`");
	$db->query("TRUNCATE TABLE `cc1_allianz_bewerbung`");
	$db->query("TRUNCATE TABLE `cc1_allianz_log`");
	$db->query("TRUNCATE TABLE `cc1_allianz_rang`");
	$db->query("TRUNCATE TABLE `cc1_allianz_rang_user`");
	$db->query("TRUNCATE TABLE `cc1_allianz_rechte`");
	$db->query("TRUNCATE TABLE `cc1_banner_mgr`");
	$db->query("TRUNCATE TABLE `cc1_battle`");
	$db->query("TRUNCATE TABLE `cc1_battle_archiv`");
	$db->query("TRUNCATE TABLE `cc1_countries`");
	$db->query("TRUNCATE TABLE `cc1_create_sol`");
	$db->query("TRUNCATE TABLE `cc1_debug`");
	$db->query("TRUNCATE TABLE `cc1_forum`");
	$db->query("TRUNCATE TABLE `cc1_forum_last`");
	$db->query("TRUNCATE TABLE `cc1_forum_posts`");
	$db->query("TRUNCATE TABLE `cc1_forum_topics`");
	$db->query("TRUNCATE TABLE `cc1_groups`");
	$db->query("TRUNCATE TABLE `cc1_groups_inhalt`");
	$db->query("TRUNCATE TABLE `cc1_messages`");
	$db->query("TRUNCATE TABLE `cc1_news`");
	$db->query("TRUNCATE TABLE `cc1_new_land`");
	$db->query("TRUNCATE TABLE `cc1_sessions`");
	$db->query("TRUNCATE TABLE `cc1_spions`");
	$db->query("DELETE FROM `cc1_users` WHERE `serveradmin` != 1");
	$db->query("UPDATE `cc1_crand` SET `used` = '0'");
	all_delete(LITO_ROOT_PATH.'alli_flag');
	all_delete(LITO_ROOT_PATH.'battle_kr');
	all_delete(LITO_ROOT_PATH.'image_user');
	all_delete(LITO_ROOT_PATH.'image_sig');
	all_delete(LITO_ROOT_PATH.'images_tmp');
	include_once(LITO_ROOT_PATH."acp/includes/ftp_class.php");
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('Stellen sie sicher, dass ihre FTP Daten richtig sind!');
		exit;
	}
	$ftp->mk_dir('alli_flag');
	$ftp->mk_dir('battle_kr');
	$ftp->mk_dir('image_user');
	$ftp->mk_dir('image_sig');
	$ftp->mk_dir('images_tmp');
	$ftp->chown_perm(0777, "alli_flag");
	$ftp->chown_perm(0777, "battle_kr");
	$ftp->chown_perm(0777, "image_user");
	$ftp->chown_perm(0777, "images_sig");
	$ftp->chown_perm(0777, "images_tmp");
	$ftp->disconnect();
	$action = 'main';
}
if($action == 'main'){
	template_out('main.html', $modul_name);
}
?>
