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
require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');

if (!isset ($_SESSION['userid'])) {
	header("LOCATION: " . $_SESSION['litotex_start_url'] . 'acp/index.php');
	exit ();
}

if (isset ($_REQUEST['action']))
$action = $_REQUEST['action'];
else
$action = "main";
$modul_name = "acp_nav_edit";

$menu_name="Navigationmanager";
$tpl->assign( 'menu_name',$menu_name);


require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
if ($action == 'new') {
	if(!isset($_GET['design_id']))
	$design_id = 1;
	else
	$design_id = $_GET['design_id'] * 1;

	if(!isset($_POST['ingame']))
	$_POST['ingame'] = 0;
	if (isset ($_POST['title']) && isset ($_POST['url']) && isset ($_POST['ingame'])) {
		$lastpos = $db->query("SELECT `sort_order` FROM `cc" . $n . "_menu_game` ORDER BY `sort_order` DESC");
		$lastpos = $db->fetch_array($lastpos);
		if (isset ($lastpos['position']))
		$lastpos = $lastpos['position'] + 1;
		else
		$lastpos = 1;
		$_POST['ingame'] = $_POST['ingame'] * 1;
		$db->query("INSERT INTO `cc" . $n . "_menu_game` (`menu_game_name`, `menu_game_link`, `sort_order`, `menu_art_id`, `ingame`, `modul_id`, `design_id`) VALUES ('" . $db->escape_string($_POST['title']) . "', '" . $db->escape_string($_POST['url']) . "', '" . $lastpos . "', 0, " . $_POST['ingame'] . ", 12, ".$design_id.")");
	}
	$action = 'main';
}
if($action == 'delete'){
	if(!isset($_GET['id']))
	die('Es ist ein schwerer Fehler aufgetreten!');

	$id = $_GET['id'] * 1;
	$db->query("DELETE FROM `cc" . $n . "_menu_game` WHERE `menu_game_id` = '" . $id . "'");
	$action = 'main';
}

if($action == 'change'){
	if(!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_POST['change_title']) || !isset($_POST['change_url']))
	die('Es ist ein schwerer Fehler aufgetreten!');
	if(!isset($_POST['change_ingame']))
	$_POST['change_ingame'] = false;
	else
	$_POST['change_ingame'] = $_POST['change_ingame']*1;
	$db->query("UPDATE `cc" . $n . "_menu_game` SET `menu_game_name` = '".$db->escape_string($_POST['change_title'])."', `menu_game_link` = '".$db->escape_string($_POST['change_url'])."', `ingame` = '".$_POST['change_ingame']."' WHERE `menu_game_id` = '" . $_GET['id'] . "'");
	$action = 'main';
}

if($action == 'change_select'){
	if(isset($_GET['id']))
	$change = $_GET['id'];
	else
	$change = false;
	$action = 'main';
} else
$change = false;

if ($action == "main") {

	if(!isset($_GET['design_id']))
	$design_id = 1;
	else
	$design_id = $_GET['design_id'] * 1;

	$navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = ".$design_id." ORDER BY `sort_order` ASC");
	$id = 0;
	while ($row = $db->fetch_array($navi_db)) {
		$navi[$row['menu_art_id']][$id]['id'] = $row['menu_game_id'];
		$navi[$row['menu_art_id']][$id]['title'] = $row['menu_game_name'];
		$navi[$row['menu_art_id']][$id]['url'] = $row['menu_game_link'];
		$navi[$row['menu_art_id']][$id]['ingame'] = $row['ingame'];
		if($change == $row['menu_game_id']){
			$navi[$row['menu_art_id']][$id]['change'] = true;
		}
		else
		$navi[$row['menu_art_id']][$id]['change'] = false;
		$id++;
	}
	if(!isset($navi[0]))
	$navi[0] = array();
	if(!isset($navi[1]))
	$navi[1] = array();
	if(!isset($navi[2]))
	$navi[2] = array();
	$tpl->assign('design_id', $design_id);
	$tpl->assign('navi_up', $navi[0]);
	$tpl->assign('navi_left', $navi[1]);
	$tpl->assign('navi_right', $navi[2]);
	$designs = array();
	$designs_q = $db->query("SELECT `design_id`, `design_name` FROM `cc".$n."_desigs`");
	$i = 0;
	while($design = $db->fetch_array($designs_q)){
		$designs[$i]['id'] = $design['design_id'];
		$designs[$i]['name'] = $design['design_name'];
		$i++;
	}
	$tpl->assign('designs', $designs);
	template_out('list.html', $modul_name);
} else
if ($action == 'move') {
	if (isset ($_GET['order'])) {
		$order = explode(';', $_GET['order']);
		$pos_style = 0;
		foreach ($order as $pos => $id) {
			if (!is_numeric($id)) {
				if ($id == 'ign')
				continue;
				switch ($id) {
					case 'up' :
					$pos_style = 0;
					break;
					case 'left' :
					$pos_style = 1;
					break;
					case 'right' :
					$pos_style = 2;
					break;
					default :
					continue;
				}
			}
			$db->query("UPDATE `cc" . $n . "_menu_game` SET `menu_art_id` = '" . $pos_style . "', `sort_order` = '" . $pos . "' WHERE `menu_game_id` = '" . $id . "'");
		}
	}
	echo 'Gespeichert!';
} else
echo $action;
?>
