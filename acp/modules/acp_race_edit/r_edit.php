<?PHP
/*
************************************************************
Litotex Browsergame - Engine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberrechtlich geschützt.

Für jegliche Fehler oder Schäden, die durch diese Software
auftreten könnten, übernimmt der Autor keine Haftung.

Alle Copyright - Hinweise innerhalb dieser Datei
dürfen WEDER entfernt, NOCH verändert werden.
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

$modul_name="acp_race_edit";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
$menu_name="Rasseneditor";
$tpl->assign( 'menu_name',$menu_name);


if($action=="main") {

	$out_a = array();
	$result=$db->query("select * from cc".$n."_rassen" );
	while($row=$db->fetch_array($result)) {
		$out_a[] = $row;
	}

	$tpl->assign('modules', $out_a);
	template_out('r_edit.html',$modul_name);
	exit();
}

if($action=="save_rass") {
	if(isset($_GET['r_id'])){
		$save_id=	$_GET['r_id'];
		if ($save_id < 1 || $save_id > 4){
			error_msg("$ln_error_17");
		}
		$save_name=trim($_POST['rassname']);
		$save_description=trim($_POST['description']);
		$save_description_en=trim($_POST['description_en']);

		$update=$db->query("UPDATE cc".$n."_rassen SET rassenname = '".$save_name."', descriprion = '".$save_description."', descriprion_en= '".$save_description_en."' WHERE rassenid='".$save_id."'");
		header("LOCATION: r_edit.php");

	}
}


?>
