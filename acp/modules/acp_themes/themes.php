<?php
/*
 * Created on 21.05.2009
 * By: Joans Schwabe (GH1234)
 * j.s@cascaded-web.com
 */
error_reporting(E_ALL);
@session_start();
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');
require($_SESSION['litotex_start_acp'].'acp/includes/ftp_class.php');
if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}


if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_themes";
$menu_name="Templatemanager";
$tpl->assign( 'menu_name',$menu_name);
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

if($action == 'zip'){
	if(!isset($_GET['id'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id']*1;
	$nstd_q = $db->query("SELECT `design_id`, `aktive`, `design_name` FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	if(!$db->num_rows($nstd_q)){
		error_msg('Das Template wurde nicht in der Datenbank gefunden!');
		exit;
	}
	$nstd = $db->fetch_array($nstd_q);
	require($_SESSION['litotex_start_acp'].'acp/includes/pclzip.lib.php');
	$zip = new PclZip($_SESSION['litotex_start_acp'].'cache/dl.zip');
	$cache = @file_get_contents($_SESSION['litotex_start_acp'].'cache/dl.zip');
	@unlink($_SESSION['litotex_start_acp'].'cache/dl.zip');
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('FTP Daten scheinen nicht zu stimmen!');
		exit;
	}
	$ftp->write_contents($_SESSION['litotex_start_acp'].'cache/dl.zip', $cache);
	$ftp->disconnect();
	header('Location:../../../cache/dl.zip');
	if(!is_dir($_SESSION['litotex_start_acp'].'themes/'.$nstd['design_name']) || !is_dir($_SESSION['litotex_start_acp'].'images/'.$nstd['design_name']) || !is_dir($_SESSION['litotex_start_acp'].'css/'.$nstd['design_name'])){
		error_msg('Es ist ein Fehler aufgetreten, Es existsieren nicht alle Ordner des zu speichernen Designes!');
		exit;
	}
	$return = $zip->create(array($_SESSION['litotex_start_acp'].'themes/'.$nstd['design_name'], $_SESSION['litotex_start_acp'].'images/'.$nstd['design_name'], $_SESSION['litotex_start_acp'].'css/'.$nstd['design_name']), PCLZIP_OPT_REMOVE_PATH, $_SESSION['litotex_start_acp']);
	if($return == 0){
		error_msg('Es ist ein Fehler aufgetreten, dieser konnte nicht n&auml;her bestimmt werden!');
		exit;
	}
	$action = 'main';
}

if($action == 'changestd'){
	if(!isset($_GET['id'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id']*1;
	$nstd_q = $db->query("SELECT `design_id` FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	if(!$db->num_rows($nstd_q)){
		error_msg('Das Template wurde nicht in der Datenbank gefunden!');
		exit;
	}
	$db->query("UPDATE `cc".$n."_desigs` SET `aktive` = 0");
	$db->query("UPDATE `cc".$n."_desigs` SET `aktive` = 1 WHERE `design_id` = '".$id."'");
	$db->query("UPDATE `cc".$n."_users` SET `design_id` = '".$id."'");
	$action = 'main';
}

if($action == 'changealt'){
	if(!isset($_GET['id'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id']*1;
	$nstd_q = $db->query("SELECT `design_id`, `alternate_permit` FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	if(!$db->num_rows($nstd_q)){
		error_msg('Das Template wurde nicht in der Datenbank gefunden!');
		exit;
	}
	$nstd = $db->fetch_array($nstd_q);
	if($nstd['alternate_permit'] == 0)
		$alt = 1;
	else
		$alt = 0;
	$db->query("UPDATE `cc".$n."_desigs` SET `alternate_permit` = " . $alt . " WHERE `design_id` = '".$id."'");
	$action = 'main';
}

if($action == 'remove'){
	if(!isset($_GET['id'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id']*1;
	$nstd_q = $db->query("SELECT `design_id`, `aktive`, `design_name` FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	if(!$db->num_rows($nstd_q)){
		error_msg('Das Template wurde nicht in der Datenbank gefunden!');
		exit;
	}
	$nstd = $db->fetch_array($nstd_q);
	if($nstd['aktive'] == 1 || $nstd['design_id'] == 1){
		error_msg('Sie versuchen das Standardtemplate zu löschen, das ist nicht möglich!');
		exit;
	}
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('FTP Daten scheinen nicht zu stimmen!');
		exit;
	}
	$ftp->req_remove('themes/'.$nstd['design_name']);
	$ftp->req_remove('images/'.$nstd['design_name']);
	$ftp->req_remove('css/'.$nstd['design_name']);
	$ftp->disconnect();
	$db->query("DELETE FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	$aktive_q = $db->query("SELECT `design_id` FROM `cc".$n."_desigs` WHERE `aktive` = 1");
	$aktive = $db->fetch_array($aktive_q);
	$db->query("UPDATE `cc".$n."_users` SET `design_id` = '".$aktive['design_id']."' WHERE `design_id` = '".$id."'");
	$action='main';
}

if($action == 'new'){
	if(!(isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['description']) && isset($_POST['author']) && isset($_POST['copy']) && isset($_POST['web']))){
		error_msg('Es wurden nicht alle nötigen Daten übergeben.');
		exit;
	}
	if(!preg_match('!^[a-z_\-]*$!', $_POST['name'])){
		error_msg('Der neue Name darf nur Buchstaben (a-z), Unterstriche (_) und Minus (-) enthalten!');
		exit;
	}
	$cp_q = $db->query("SELECT * FROM `cc".$n."_desigs` WHERE `design_name` = '".$_POST['name']."'");
	if($db->num_rows($cp_q)){
		error_msg('Das Zieltemplate ist bereits in der Datenbank!');
		exit;
	}
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('FTP Daten scheinen nicht zu stimmen!');
		exit;
	}
	$ftp->mk_dir('themes/'.$_POST['name']);
	$ftp->mk_dir('images/'.$_POST['name']);
	$ftp->mk_dir('css/'.$_POST['name']);
	$ftp->mk_dir('templates_c/'.$_POST['name']);
	$ftp->chown_perm(0777, 'templates_c/'.$_POST['name']);
	$ftp->disconnect();

	
	$db->query("INSERT INTO `cc".$n."_desigs` (`design_name`, `design_author`, `design_copyright`, `design_author_web`, `design_author_mail`, `design_description`, `aktive`, `alternate_permit`) VALUES ('".$_POST['name']."', '".$db->escape_string($_POST['author'])."', '".$db->escape_string($_POST['copy'])."', '".$db->escape_string($_POST['web'])."', '".$db->escape_string($_POST['mail'])."', '".$db->escape_string($_POST['description'])."', 0, 0)");
	$newid = $db->insert_id();
	//Standartdesign
	$std = $db->query("SELECT `design_id` FROM `cc".$n."_desigs` WHERE `aktive` = 1");
	$std = $db->fetch_array($std);
	$navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = ".$std['design_id']." ORDER BY `sort_order` ASC");

	while($element = $db->fetch_array($navi_db)){
		$db->query("INSERT INTO `cc" . $n . "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('".$element['menu_game_name']."', '".$element['menu_game_link']."', '".$element['modul_id']."', '".$element['sort_order']."', '".$element['menu_art_id']."', '".$element['ingame']."', '".$element['optional_parameter']."', '".$newid."')");
	}
	$action = 'main';
}

if($action == 'dub'){
	if(!isset($_GET['id']) || !isset($_GET['new'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$id = $_GET['id']*1;
	if(!preg_match('!^[a-z_\-]*$!', $_GET['new'])){
		error_msg('Der neue Name darf nur Buchstaben (a-z), Unterstriche (_) und Minus (-) enthalten!');
		exit;
	}
	$nstd_q = $db->query("SELECT * FROM `cc".$n."_desigs` WHERE `design_id` = '".$id."'");
	if(!$db->num_rows($nstd_q)){
		error_msg('Das Template wurde nicht in der Datenbank gefunden!');
		exit;
	}
	$cp_q = $db->query("SELECT * FROM `cc".$n."_desigs` WHERE `design_name` = '".$_GET['new']."'");
	if($db->num_rows($cp_q)){
		error_msg('Das Zieltemplate ist bereits in der Datenbank!');
		exit;
	}
	$nstd = $db->fetch_array($nstd_q);
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('FTP Daten scheinen nicht zu stimmen!');
		exit;
	}
	if(!$ftp->exists('themes/'.$nstd['design_name'])){
		error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
		exit;
	}
	if(!$ftp->exists('images/'.$nstd['design_name'])){
		error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
		exit;
	}
	if(!$ftp->exists('css/'.$nstd['design_name'])){
		error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
		exit;
	}
	$sourcet = 'themes/'.$nstd['design_name'];
	$sourcei = 'images/'.$nstd['design_name'];
	$sourcec = 'css/'.$nstd['design_name'];
	if($ftp->exists('themes/'.$_GET['new'])){
		error_msg('Das Zeil Template existiert bereits!');
		exit;
	}
	if($ftp->exists('images/'.$_GET['new'])){
		error_msg('Das Zeil Template existiert bereits!');
		exit;
	}
	if($ftp->exists('css/'.$_GET['new'])){
		error_msg('Das Zeil Template existiert bereits!');
		exit;
	}
	$destt = 'themes/'.$_GET['new'];
	$desti = 'images/'.$_GET['new'];
	$destc = 'css/'.$_GET['new'];
	$ftp->mk_dir('template_c/'.$_GET['new']);
	
	$ftp->copy_req($sourcet, $destt);
	$ftp->copy_req($sourcei, $desti);
	$ftp->copy_req($sourcec, $destc);
	$ftp->disconnect();
	$db->query("INSERT INTO `cc".$n."_desigs` (`design_name`, `design_author`, `design_copyright`, `design_author_web`, `design_author_mail`, `design_description`, `aktive`, `alternate_permit`) VALUES ('".$_GET['new']."', '".$nstd['design_author']."', '".$nstd['design_copyright']."', '".$nstd['design_author_web']."', '".$nstd['design_author_mail']."', '".$nstd['design_description']."', 0, 0)");
	$newid = $db->insert_id();
	$navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = ".$nstd['design_id']." ORDER BY `sort_order` ASC");

	while($element = $db->fetch_array($navi_db)){
		$db->query("INSERT INTO `cc" . $n . "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('".$element['menu_game_name']."', '".$element['menu_game_link']."', '".$element['modul_id']."', '".$element['sort_order']."', '".$element['menu_art_id']."', '".$element['ingame']."', '".$element['optional_parameter']."', '".$newid."')");
	}
	$action = 'main';
}

if($action == 'test'){
	if(!isset($_GET['id'])){
		error_msg('Es wurde keine ID &uuml;bergeben!');
		exit;
	}
	$_GET['id'] = $_GET['id']*1;
	$db->query("UPDATE `cc".$n."_users` SET `design_id` = '".$_GET['id']."' WHERE `userid` = '".$_SESSION['userid']."'");
	header("Location:".LITO_ROOT_PATH_URL);
}

if($action=="main") {
	$themes_q = $db->query("SELECT `design_id`, `design_name`, `design_author`, `design_copyright`, `design_author_mail`, `design_author_web`, `design_description`, `aktive`, `alternate_permit` FROM `cc".$n."_desigs`");
	$themes = array();
	$i = 0;
	while($theme = $db->fetch_array($themes_q)){
		$themes[$i]['id'] = $theme['design_id'];
		$themes[$i]['name'] = $theme['design_name'];
		$themes[$i]['author'] = $theme['design_author'];
		$themes[$i]['copy'] = $theme['design_copyright'];
		$themes[$i]['mail'] = $theme['design_author_mail'];
		if(!preg_match('!^http://!', $theme['design_author_web']))
			$theme['design_author_web'] =  'http://' . $theme['design_author_web'];
		$themes[$i]['web'] = $theme['design_author_web'];
		$themes[$i]['description'] = $theme['design_description'];
		$themes[$i]['aktive'] = $theme['aktive'];
		$themes[$i]['alt'] = $theme['alternate_permit'];
		$i++;
	}
	$tpl->assign('themes', $themes);
	template_out('main.html', $modul_name);
}
?>

