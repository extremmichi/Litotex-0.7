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

	if (!isset ($_SESSION['litotex_start_acp'])){
		header('LOCATION: ./../../index.php');

		}


	require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

if(!isset($_SESSION['userid'])){
 	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
 	exit();
 	}

 if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
  else $action="main";

$modul_name="acp_modulmgr";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
 $menu_name="Modulmanager";
$tpl->assign( 'menu_name',$menu_name);
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
all_delete(LITO_ROOT_PATH.'acp/tmp/_sample_modul');

if($action=="main") {

		$sql="SELECT * from  cc".$n."_modul_admin where acp_modul='1' ";
		$result_modules=$db->query($sql);
	  $out_a = array();
	  while($row=$db->fetch_array($result_modules)) {
	  $out_a[] = $row;
	  }

		$sql="SELECT * from  cc".$n."_modul_admin where acp_modul='0' ";
		$result_modules_b=$db->query($sql);
	  $out_b = array();
	  while($row_b=$db->fetch_array($result_modules_b)) {
	  $out_b[] = $row_b;
	  }


	  	$tpl->assign('modules', $out_a);
	  	$tpl->assign('modules_b', $out_b);

		 	template_out('modulmgr.html',$modul_name);
	  	exit();
}

if($action=="activate") {
	$modul_id=intval($_GET['id']);
	$db->query("UPDATE cc".$n."_modul_admin SET activated='1' WHERE modul_admin_id='$modul_id' AND disable_allowed='1'");
	header("LOCATION: ".LITO_MODUL_PATH_URL.'acp_modulmgr/modulmgr.php');

}

if($action=="deactivate") {
$modul_id=intval($_GET['id']);
	$db->query("UPDATE cc".$n."_modul_admin SET activated='0' WHERE modul_admin_id='$modul_id' AND disable_allowed='1'");
	header("LOCATION: ".LITO_MODUL_PATH_URL.'acp_modulmgr/modulmgr.php');
}

if($action=="get_info"){
	$m_name=c_trim($_GET['uname']);
	$setup_filename="";

	$sql="SELECT * FROM cc".$n."_modul_admin where modul_name='".$m_name."'";
		$result=$db->query($sql);
		$row=$db->fetch_array($result);
	  $more_info="";

	$setup_filename=LITO_MODUL_PATH.$m_name."/setup.php";

		if (is_file($setup_filename)){

					$ini_array = parse_ini_file($setup_filename, TRUE);

	  			$more_info="Copyright ".$ini_array['info']['modul_copyright']." by ". $ini_array['info']['modul_autor'];
	  			if ($ini_array['info']['modul_autor_website'] != "" ){
	  			$more_info.="<br><a href=\"".$ini_array['info']['modul_autor_website']."\" target=\"_blank\">".$ini_array['info']['modul_autor_website']."</a>";
	  			}else{
	  			$more_info.="<br>";
	  			}
	  			$more_info.="<br>".$ini_array['info']['modul_autor_mail'];

	  	}else{
	  	$more_info="not available";
	  	}



		if (intval($row['new_upd_available'])==0 ){
		$upd_info="no update available";
		}else{
		$upd_info="update available";
		}


		$tpl->assign('modul_s', $row['startfile']);
		$tpl->assign('modul_name', $m_name);
		$tpl->assign('modul_v', $row['current_version']);
		$tpl->assign('modul_desc', $row['modul_description']);
		$tpl->assign('modul_upd', $upd_info);
		$tpl->assign('modul_more', $more_info);

			template_out('modulinfo.html',$modul_name);
	  	exit();
}
if($action == 'upload'){
	if(!isset($_FILES['upload_mod'])){
		error_msg('Es wurde keine Datei hochgeladen.');
		exit;
	}
	if($_FILES['upload_mod']['type'] != 'application/zip' && $_FILES['upload_mod']['type'] != 'application/x-zip-compressed' && $_FILES['upload_mod']['type'] != 'application/x-compressed' && $_FILES['upload_mod']['type'] != 'multipart/x-zip' && $_FILES['upload_mod']['type'] != 'application/x-zip'){
		error_msg('Es werden nur ZIP Dateien unterst&uuml;tzt. '.$_FILES['upload_mod']['type']);
		exit;
	}
	include_once(LITO_ROOT_PATH.'acp/includes/pclzip.lib.php');
	include_once(LITO_ROOT_PATH."acp/includes/ftp_class.php");
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('Stellen sie sicher, dass ihre FTP Daten richtig sind!');
		exit;
	}
	$old_tree = $ftp->list_files('acp/tmp');
	$zip = new PclZip($_FILES['upload_mod']['tmp_name']);
	$zip->extract(PCLZIP_OPT_PATH, LITO_ROOT_PATH.'acp/tmp');
	$new_tree = $ftp->list_files('acp/tmp');
	foreach($new_tree as $node){
		if(!in_array($node, $old_tree))
			$new = $node;
	}
	if(!isset($new)){
		error_msg('Der Name des neuen Ordners konnte nicht ermittelt werden! Bitte versuchen sie den Inhalt des acp/tmp Ordners zu l�schen');
		exit;
	}
	if($ftp->exists('acp/tmp/'.$new . '_up')){
		$ftp->req_remove('acp/tmp/'.$new . '_up');
	}
	$ftp->copy_req('acp/tmp/'.$new, 'acp/tmp/'.$new . '_up');
	all_delete(LITO_ROOT_PATH.'acp/tmp/'.$new);
	$ftp->disconnect();
	$action = 'scan_new';
}
if($action == 'remote_update'){
	if(!isset($_GET['mod'])){
		error_msg('Kein Modulname �bergeben!');
		exit;
	}
	$url = "http://update.freebg.de/updinfo.php?action=gu&m=".$_GET['mod']."&v=0.0.0&n=".LITO_ROOT_PATH_URL;
	if(!intval(ini_get('allow_url_fopen'))){
		error_msg('Bitte aktivieren sie "allow_url_fopen" in ihrer PHP.ini!');
		exit;
	}
	$cu = fopen($url, 'r');
	$ret = fread($cu, 10000);
	fclose($cu);
	if(!preg_match("!^http://update\.freebg\.de!", $ret)){
		error_msg('Modul wurde anscheinden nicht gefunden, R�ckgabe des Servers: '.$ret.'!');
		exit;
	}
	$file = fopen($ret, 'r');
	$local = fopen(LITO_ROOT_PATH."acp/tmp/".$_GET['mod'].'.zip', 'w');
	while($read = fread($file, 1000000)){
		fwrite($local, $read);
	}
	fclose($file);
	fclose($local);
	include_once(LITO_ROOT_PATH.'acp/includes/pclzip.lib.php');
	include(LITO_ROOT_PATH."acp/includes/ftp_class.php");
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('Stellen sie sicher, dass ihre FTP Daten richtig sind!');
		exit;
	}
	$old_tree = $ftp->list_files('acp/tmp');
	$zip = new PclZip(LITO_ROOT_PATH."acp/tmp/".$_GET['mod'].'.zip');
	$zip->extract(PCLZIP_OPT_PATH, LITO_ROOT_PATH.'acp/tmp');
	$new_tree = $ftp->list_files('acp/tmp');
	foreach($new_tree as $node){
		if(!in_array($node, $old_tree))
			$new = $node;
	}
	if(!isset($new)){
		error_msg('Der Name des neuen Ordners konnte nicht ermittelt werden! Bitte versuchen sie den Inhalt des acp/tmp Ordners zu l�schen');
		exit;
	}
	if($ftp->exists('acp/tmp/'.$new . '_up')){
		$ftp->req_remove('acp/tmp/'.$new . '_up');
	}
	$ftp->copy_req('acp/tmp/'.$new, 'acp/tmp/'.$new . '_up');
	all_delete(LITO_ROOT_PATH.'acp/tmp/'.$new);
	unlink(LITO_ROOT_PATH."acp/tmp/".$_GET['mod'].'.zip');
	$ftp->mv($new . '_up/'.basename($new), $new);
	$ftp->req_remove($new.'_up');
	include_once(LITO_ROOT_PATH."acp/includes/package_class.php");
	$pm = new package(basename($new.'_up'), $ftp);
		if (!$pm->initialized)
			die('Schwerer Fehler!');
		$pm->install();
		$tpl->assign('debug', $pm->debug());
		$ftp->req_remove($new);
		template_out('action.html',$modul_name);
	$ftp->disconnect();
}

if($action == "remote"){
	if(!isset($_POST['remote'])){
		error_msg('Kein Modulname �bergeben!');
		exit;
	}
	$url = "http://update.freebg.de/updinfo.php?action=gu&m=".$_POST['remote']."&v=0.0.0&n=".LITO_ROOT_PATH_URL;
	if(!intval(ini_get('allow_url_fopen'))){
		error_msg('Bitte aktivieren sie "allow_url_fopen" in ihrer PHP.ini!');
		exit;
	}
	$cu = fopen($url, 'r');
	$ret = fread($cu, 10000);
	fclose($cu);
	if(!preg_match("!^http://update\.freebg\.de!", $ret)){
		error_msg('Modul wurde anscheinden nicht gefunden, R�ckgabe des Servers: '.$ret.'!');
		exit;
	}
	$file = fopen($ret, 'r');
	$local = fopen(LITO_ROOT_PATH."acp/tmp/".$_POST['remote'].'.zip', 'w');
	while($read = fread($file, 1000000)){
		fwrite($local, $read);
	}
	fclose($file);
	fclose($local);
	include_once(LITO_ROOT_PATH.'acp/includes/pclzip.lib.php');
	include(LITO_ROOT_PATH."acp/includes/ftp_class.php");
	$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
	if(!$ftp->lito_root){
		error_msg('Stellen sie sicher, dass ihre FTP Daten richtig sind!');
		exit;
	}
	$old_tree = $ftp->list_files('acp/tmp');
	$zip = new PclZip(LITO_ROOT_PATH."acp/tmp/".$_POST['remote'].'.zip');
	$zip->extract(PCLZIP_OPT_PATH, LITO_ROOT_PATH.'acp/tmp');
	$new_tree = $ftp->list_files('acp/tmp');
	foreach($new_tree as $node){
		if(!in_array($node, $old_tree))
			$new = $node;
	}
	if(!isset($new)){
		error_msg('Der Name des neuen Ordners konnte nicht ermittelt werden! Bitte versuchen sie den Inhalt des acp/tmp Ordners zu l�schen');
		exit;
	}
	if($ftp->exists('acp/tmp/'.$new . '_up')){
		$ftp->req_remove('acp/tmp/'.$new . '_up');
	}
	$ftp->copy_req('acp/tmp/'.$new, 'acp/tmp/'.$new . '_up');
	all_delete(LITO_ROOT_PATH.'acp/tmp/'.$new);
	$ftp->disconnect();
	unlink(LITO_ROOT_PATH."acp/tmp/".$_POST['remote'].'.zip');
	$action = 'scan_new';
}

if($action=="scan_new") {

	// first scan acp modules
  $return = array();
  $allow_url_fopen = intval(ini_get('allow_url_fopen'));
	$tpl->assign('url_open', $allow_url_fopen);
	$MODDIR = LITO_ROOT_PATH."acp/tmp/";
	$setup_filename="";
	$modules_acp_counter=0;
	$modules_acp_counter_new=0;
	$modules_game_counter_new=0;
	$new_found_inhalt=array();
	$new_found=array();
	if(!is_dir($MODDIR)){
		return false;
		}
	$scandir = opendir($MODDIR);
	while($mod = readdir($scandir)){
		if($mod == '.' || $mod == '..')
			continue;
		$return[] = $mod;
		$modules_acp_counter++;
		$setup_filename=$MODDIR.$mod."/setup.php";

		if (is_file($setup_filename)){

			$ini_array = parse_ini_file($setup_filename, TRUE);
			$new_modul_name=c_trim($ini_array['info']['modul_name']);
			$new_modul_version=c_trim($ini_array['info']['modul_version']);
			$new_modul_filename=c_trim($ini_array['info']['modul_filename']);
			$new_type=c_trim($ini_array['info']['modul_acp']);

	 		if (is_modul_installed($new_modul_name,$new_modul_version)==0 ){
  			$new_found_inhalt=array($new_modul_name,$ini_array['info']['modul_description'],$new_modul_version,$mod);
	  			array_push($new_found,$new_found_inhalt);
				if ($new_type == 1){
							$modules_acp_counter_new++;
					}else{
							$modules_game_counter_new++;
					}


  		}

	}
	}
	closedir($scandir);
	$tpl->assign('modules', $new_found);
	$tpl->assign('modul_acp_count', $modules_acp_counter);
	$tpl->assign('modul_acp_count_new', $modules_acp_counter_new);
	$tpl->assign('modul_game_count_new', $modules_game_counter_new);
	template_out('modulmgrscan.html',$modul_name);
	exit();

}

if($action=="install") {
	$m_name=c_trim($_GET['id']);
	$setup_filename=LITO_ROOT_PATH."acp/tmp/".$m_name."/setup.php";
	if (is_file($setup_filename)){

		include(LITO_ROOT_PATH."acp/includes/ftp_class.php");
		include(LITO_ROOT_PATH."acp/includes/package_class.php");

		if(!isset($ftp) || !is_a($ftp, 'ftp'))
			$ftp = new ftp($ftphost, $ftpuser, $ftppassword, $ftproot, $ftpport);
		$pm = new package($m_name, $ftp);
		if (!$pm->initialized)
			die('Schwerer Fehler!');
		$pm->install();
		$tpl->assign('debug', $pm->debug());
		template_out('action.html',$modul_name);
	}else{
	error_msg("Die Datei ". $setup_filename . "konnte nicht gefunden werden");
	exit();
	}

}

if($action=="update") {

		$sql="SELECT * from  cc".$n."_modul_admin order by acp_modul";
		$result_modules=$db->query($sql);

	 $array="";
	  $out_a = array();
	  while($row=$db->fetch_array($result_modules)) {
	  $out_a[] = $row;
	  if ($array ==""){
	  	$array=$row['modul_admin_id'];
	  }else{
	  	$array.=",".$row['modul_admin_id'];
		}
	  }


   	$allow_url_fopen = intval(ini_get('allow_url_fopen'));

		$tpl->assign('modules', $out_a);
		$tpl->assign('url_open', $allow_url_fopen);

		$tpl->assign('ARRAY', $array);
		template_out('modulmgr_update.html',$modul_name);
	exit();
}


?>

