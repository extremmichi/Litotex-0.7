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

@session_start();
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_core";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
if($action=="main") {

	$result=$db->query("SELECT count( userid ) AS anz_user FROM cc".$n."_users");
	$row=$db->fetch_array($result);
	$spieler_count=$row['anz_user'];

	$result=$db->query("SELECT count( userid ) AS anz_block FROM cc".$n."_users where blocked = '1'");
	$row=$db->fetch_array($result);
	$spieler_count_ban=intval($row['anz_block']);

	$this_time =time()-3600;
	$result=$db->query("SELECT count( userid ) AS anz_online FROM cc".$n."_users where lastactive >= '$this_time'");
	$row=$db->fetch_array($result);
	$spieler_count_active=$row['anz_online'];


	$result=$db->query("SELECT x FROM cc".$n."_crand ORDER BY `cc1_crand`.`x` DESC LIMIT 1");
	$row=$db->fetch_array($result);
	$land_size=$row['x'];

	$result=$db->query("SELECT  count( islandid ) AS anz_land FROM cc".$n."_countries ");
	$row=$db->fetch_array($result);
	$land_count=$row['anz_land'];

	$result=$db->query("SELECT  count( crand_id  ) AS anz_land_free FROM cc".$n."_crand where used='0' and element_type='0'");
	$row=$db->fetch_array($result);
	$land_count_free=$row['anz_land_free'];

	$tpl->assign( 'data', array('Anzahl Spieler',$spieler_count,'Anzahl gesperte Spieler',$spieler_count_ban,'Anzahl active Spieler',$spieler_count_active) );

	$tpl->assign( 'land', array('Kartengr&ouml;&szlig;e',$land_size."*".$land_size,'Anzahl L&auml;nder',$land_count,'Anzahl freie L&auml;nder',$land_count_free) );

	$tpl->assign( 'tr', array('bgcolor="#eeeeee"','bgcolor="#dddddd"'));



	template_out('admin.html',$modul_name);
}



?>
