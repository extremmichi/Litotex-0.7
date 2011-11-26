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

@ session_start();
error_reporting(E_ALL^ E_NOTICE);
session_name("lito");

$sid = session_id();
//$Database index
$n = 1;

if (isset ($_SESSION['litotex_start_g'])) {
	$litotex_path = $_SESSION['litotex_start_g'];
	$litotex_url = $_SESSION['litotex_start_g_url'];
} else {
	if (is_file('./includes/config.php')) {
		require ('./includes/config.php');
	} else if (is_file('./config.php')) {
		require ('./config.php');
	} else if (is_file('./../../includes/config.php')) {
		require ('./../../includes/config.php');
	}else{
	echo ("Litotex System Error");
	exit ();
}
$_SESSION['litotex_start_g'] = $litotex_path;
$_SESSION['litotex_start_g_url'] = $litotex_url;
}

/** get db class **/
define("LITO_INCLUDES_PATH", $litotex_path . 'includes/');
require (LITO_INCLUDES_PATH . 'config.php');
require (LITO_INCLUDES_PATH . 'class_db_mysql.php');


$db = new db($dbhost, $dbuser, $dbpassword, $dbbase);
// get design
if (isset ($_SESSION['userid'])) {
	$db = new db($dbhost, $dbuser, $dbpassword, $dbbase);
	$result_id=$db->query("SELECT design_id FROM cc".$n."_users where userid ='".$_SESSION['userid']."'");
	$row_id=$db->fetch_array($result_id);
	if (intval($row_id['design_id']) > 0 ){
		$theme_1 = $db->query("SELECT `design_name` FROM `cc" . $n . "_desigs` WHERE `design_id` = '".$row_id['design_id']."'");
		$row_theme=$db->fetch_array($theme_1);
		define("LITO_THEMES", $row_theme['design_name']);
	}
}else{
define("LITO_THEMES", 'standard');
}


define("LITO_ROOT_PATH", $litotex_path);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/
define("LITO_ROOT_PATH_URL", $litotex_url);
// e.g.  http://dev.freebg.de/

define("LITO_THEMES_PATH", $litotex_path . 'themes/' . LITO_THEMES . '/');
// e.g.  srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/themes/standard/
define("LITO_THEMES_PATH_URL", $litotex_url . 'themes/' . LITO_THEMES . '/');
// e.g.  http://dev.freebg.de/themes/standard/

define("LITO_IMG_PATH", $litotex_path . 'images/' . LITO_THEMES . '/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/images/standard/
define("LITO_IMG_PATH_URL", $litotex_url . 'images/' . LITO_THEMES . '/');
// e.g.  http://dev.freebg.de/images/standard/

define("LITO_MODUL_PATH", $litotex_path . 'modules/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/modules/
define("LITO_MODUL_PATH_URL", $litotex_url . 'modules/');
// e.g.  http://dev.freebg.de/modules/

define("LITO_LANG_PATH", $litotex_path . 'lang/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/lang/
define("LITO_LANG_PATH_URL", $litotex_url . 'lang/');
// e.g.  http://dev.freebg.de/lang/

define("LITO_MAIN_CSS", $litotex_url . 'css/' . LITO_THEMES );
define("LITO_JS_URL", $litotex_url . 'js/');
define("LITO_GLOBAL_IMAGE_URL", $litotex_url . 'images/');

$lang_suffix = "de";


/** get options **/
require (LITO_ROOT_PATH . 'options/options.php');

/** get functions list **/
require ('functions.php');

require (LITO_INCLUDES_PATH . 'smarty/Smarty.class.php'); // Smarty class laden und pr�fen

$time_start = explode(' ', substr(microtime(), 1));
$time_start = $time_start[1] + $time_start[0];


$tpl = new smarty;
$tpl->template_dir = LITO_THEMES_PATH;
$tpl->compile_dir = LITO_ROOT_PATH . 'templates_c/'.LITO_THEMES;
$tpl->cache_dir = LITO_ROOT_PATH . 'cache/'.LITO_THEMES;

setlocale(LC_ALL, array (
'de_DE',
'de_DE@euro',
'de',
'ger'
));

$lang_file=LITO_LANG_PATH.'core/lang_'.$lang_suffix.'.php';
$tpl->config_load($lang_file);

$tpl->assign('GAME_TITLE_TEXT', $op_set_gamename);
$is_loged_in = 0;




if (isset ($_SESSION['userid'])) {
	$is_loged_in = 1;
	// if Game Online or Offline
	if ($op_set_offline==1 && $modul_name != "logout" ){
		show_error($op_set_offline_message,"core",0);
		exit();
	}

	// load Userdata array
	$result = $db->query("SELECT u.*,c.* FROM cc" . $n . "_users AS u, cc" . $n . "_countries AS c WHERE u.userid='" . $_SESSION['userid'] . "' AND u.activeid=c.islandid");
	$userdata = $db->fetch_array($result);

	$db->unbuffered_query("UPDATE cc" . $n . "_users SET lastactive='" . time() . "' WHERE userid='" . $userdata['userid'] . "'");

	//disabled Admingame login
	if (intval($db->num_rows($result)) == 0 && $modul_name != "logout") {
		show_error('ADMIN_LOGIN', "core");
		exit ();
	}
	if (intval($userdata['serveradmin']) == 1 && $modul_name != "logout" ) {
		show_error('ADMIN_LOGIN', "core");
		exit ();
	}
	//END disabled Admingame login

	// check race
	if ($userdata['rassenid'] == 0  && $modul_name != "members" && $modul_name != "logout" && $modul_name != "navigation" && $modul_name != "ajax_core" ) {
		header("LOCATION: ".LITO_MODUL_PATH_URL.'members/members.php?action=race_choose');
		exit ();
	}

	// set max Store
	$store_max = $op_set_store_max * (($userdata['store'] + 1) * $op_store_mulit);
	if ($userdata['res1'] > $store_max) {
		$db->query("UPDATE cc" . $n . "_countries SET res1='$store_max' WHERE islandid='" . $userdata['activeid'] . "'");
	}
	if ($userdata['res2'] > $store_max) {
		$db->query("UPDATE cc" . $n . "_countries SET res2='$store_max' WHERE islandid='" . $userdata['activeid'] . "'");
	}
	if ($userdata['res3'] > $store_max) {
		$db->query("UPDATE cc" . $n . "_countries SET res3='$store_max' WHERE islandid='" . $userdata['activeid'] . "'");
	}
	if ($userdata['res4'] > $store_max) {
		$db->query("UPDATE cc" . $n . "_countries SET res4='$store_max' WHERE islandid='" . $userdata['activeid'] . "'");
	}
	// check allianz
	if ($userdata['allianzid'] != 0) {
		$result = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid='" . $userdata['allianzid'] . "'");
		$allianz = $db->fetch_array($result);
	}

	/** check islandid by start **/
	if ($userdata['activeid'] == 0) {
		$result = $db->query("SELECT islandid,userid FROM cc" . $n . "_countries WHERE userid='" . $userdata['userid'] . "' ORDER BY islandid ASC LIMIT 1");
		$row = $db->fetch_array($result);
		/** update userdata and reload **/
		$db->unbuffered_query("UPDATE cc" . $n . "_users SET activeid='" . $row['islandid'] . "' WHERE userid='" . $userdata['userid'] . "'");
	}
	resreload($userdata['islandid']);
	$banner=get_banner_code();

	$tpl->assign('GLOBAL_BANNERCODE', $banner);
	$tpl->assign('GLOBAL_STORE_SIZE', $store_max);
	$tpl->assign('CURRENT_LAND_NAME', $userdata['name']);
	$tpl->assign('CURRENT_LAND_POS', $userdata['x'] . ":" . $userdata['y']);
	$tpl->assign('CURRENT_LAND_RES1', $userdata['res1']);
	$tpl->assign('CURRENT_LAND_RES2', $userdata['res2']);
	$tpl->assign('CURRENT_LAND_RES3', $userdata['res3']);
	$tpl->assign('CURRENT_LAND_RES4', $userdata['res4']);
	$tpl->assign('GLOBAL_RES1_NAME', $op_set_n_res1);
	$tpl->assign('GLOBAL_RES2_NAME', $op_set_n_res2);
	$tpl->assign('GLOBAL_RES3_NAME', $op_set_n_res3);
	$tpl->assign('GLOBAL_RES4_NAME', $op_set_n_res4);
}
$tpl->assign('IS_LOGED_IN', $is_loged_in);
?>
