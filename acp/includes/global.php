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
error_reporting(E_ALL);
session_name("litoid");

$sid = session_id();
//$Database index
$n = 1;

if (isset ($_SESSION['litotex_start_acp'])) {
	$litotex_path = $_SESSION['litotex_start_acp'];
	$litotex_url = $_SESSION['litotex_start_url'];
} else {

	if (is_file('../includes/config.php')) {
		require ('../includes/config.php');
	} else
	if (is_file('../config.php')) {
		require ('../config.php');
	} else {
		header("LOCATION: ./../../index.php");
		exit ();
	}

	$_SESSION['litotex_start_acp'] = $litotex_path;
	$_SESSION['litotex_start_url'] = $litotex_url;
}
$dir = dirname(dirname(dirname(__FILE__)));

$basedir = str_replace("\\", "/", $_SERVER["SCRIPT_FILENAME"]);
$basedir = substr($basedir, 0, strrpos($basedir, "/")) . "/";



define("LITO_THEMES", 'standard');
// e.g.  standard

define("LITO_ROOT_PATH", $litotex_path);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/
define("LITO_ROOT_PATH_URL", $litotex_url);
// e.g.  http://dev.freebg.de/

define("LITO_THEMES_PATH", $litotex_path . 'acp/themes/' . LITO_THEMES . '/');
// e.g.  srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/themes/standard/
define("LITO_THEMES_PATH_URL", $litotex_url . 'acp/themes/' . LITO_THEMES . '/');
// e.g.  http://dev.freebg.de/acp/themes/standard/

define("LITO_IMG_PATH", $litotex_path . 'acp/images/' . LITO_THEMES . '/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/images/standard/
define("LITO_IMG_PATH_URL", $litotex_url . 'acp/images/' . LITO_THEMES . '/');
// e.g.  http://dev.freebg.de/acp/images/standard/

define("LITO_MODUL_PATH", $litotex_path . 'acp/modules/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/modules/
define("LITO_MODUL_PATH_URL", $litotex_url . 'acp/modules/');
// e.g.  http://dev.freebg.de/acp/modules/

define("LITO_LANG_PATH", $litotex_path . 'acp/lang/');
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/lang/
define("LITO_LANG_PATH_URL", $litotex_url . 'acp/lang/');
// e.g.  http://dev.freebg.de/acp/lang/

define("LITO_INCLUDES_PATH", $litotex_path . 'includes/');
define("LITO_MAIN_CSS", $litotex_url . 'acp/css/litotex.css');
define("LITO_JS_URL", $litotex_url . 'acp/js/');
define("LITO_GLOBAL_IMAGE_URL", $litotex_url . 'acp/images/');

$lang_suffix = "de";


/** get options **/
require (LITO_ROOT_PATH . 'options/options.php');

require (LITO_INCLUDES_PATH . 'config.php');

/** get db class **/
require (LITO_INCLUDES_PATH . 'class_db_mysql.php');

/** get functions list **/
require ('functions.php');

require (LITO_INCLUDES_PATH . 'smarty/Smarty.class.php'); // Smarty class laden und pr�fen

if (intval($op_use_ftp_mode==1)){
	define("C_FTP_METHOD", '1');
	}



$db = new db($dbhost, $dbuser, $dbpassword, $dbbase);

$time_start = explode(' ', substr(microtime(), 1));
$time_start = $time_start[1] + $time_start[0];

$tpl = new smarty;

$tpl->template_dir = LITO_THEMES_PATH;
$tpl->compile_dir = LITO_ROOT_PATH . 'acp/templates_c';
$tpl->cache_dir = LITO_ROOT_PATH . 'acp/cache';

setlocale(LC_ALL, array (
'de_DE',
'de_DE@euro',
'de',
'ger'
));

if (isset ($_SESSION['userid'])) {
	$result = $db->query("SELECT * FROM cc" . $n . "_users WHERE userid='" . $_SESSION['userid'] . "'");
	$userdata = $db->fetch_array($result);


	$tpl->assign('if_user_login', 1);
	$tpl->assign('LOGIN_USERNAME', $userdata['username']);
} else {
	$tpl->assign('if_user_login', 0);
	$tpl->assign('LOGIN_USERNAME', "unbekannt");
}

$tpl->assign('if_login_error', 0);
$tpl->assign('if_disable_menu', 0);
$tpl->assign('menu_name', '');

$tpl->assign('GAME_TITLE_TEXT', $op_set_gamename);

$tpl->assign('GLOBAL_RES1_NAME', $op_set_n_res1);
$tpl->assign('GLOBAL_RES2_NAME', $op_set_n_res2);
$tpl->assign('GLOBAL_RES3_NAME', $op_set_n_res3);
$tpl->assign('GLOBAL_RES4_NAME', $op_set_n_res4);
?>
