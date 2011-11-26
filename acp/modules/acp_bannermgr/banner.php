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

$modul_name="acp_bannermgr";
$menu_name="Bannermanager";
$tpl->assign( 'menu_name',$menu_name);

if($action=="main") {


	$inhalt= array();
	$result=$db->query("SELECT * FROM cc".$n."_banner_mgr");
	$i=0;
	while($row=$db->fetch_array($result)) {
		$code=str_replace('"',"\'",$row['banner_code']);
		$inhalt[$i]['active']=$row['active'];
		$inhalt[$i]['banner_code']=$code;
		$inhalt[$i]['banner_label']=$row['banner_label'];
		$inhalt[$i]['banner_count']=$row['banner_count'];
		$inhalt[$i]['banner_id']=$row['banner_id'];

		$i++;
	}
	$tpl->assign('banner_output', $inhalt);
	$tpl->assign('ACTION_SAVE','new');
	template_out('banner.html',$modul_name);

}
if($action=="new") {
	$b_label=$_POST['banner_label'];
	$b_code=$_POST['banner_code'];

	if ($b_code==""){
		error_msg("Du hast keinen Bannercode eingetragen");
		exit();
	}
	if ($b_label	==""){
		$sql="SELECT max(banner_id) as maxi_b FROM cc".$n."_banner_mgr";
		$result=$db->query($sql);
		$row=$db->fetch_array($result);
		$b_label="Banner Nr.:" .intval($row['maxi_b'])+1;
	}


	$b_code=str_replace("'",'"',$b_code);

	$db->query("INSERT INTO cc".$n."_banner_mgr(banner_code, banner_label ,banner_count  	,active) VALUES ('".$b_code."','$b_label','0','0')");


	header("LOCATION: banner.php");
}

if($action=="edit") {
	$news_id=intval($_GET['id']);
	$result_news=$db->query("SELECT * FROM cc".$n."_banner_mgr where banner_id ='$news_id'");
	$row=$db->fetch_array($result_news);

	$code=$row['banner_code'];

	$tpl->assign('banner_label',$row['banner_label']);
	$tpl->assign('banner_code',$code);
	$tpl->assign('ACTION_SAVE','update&id='.$row['banner_id']);
	template_out('banner.html',$modul_name);
}

if($action=="activate") {
	$news_id=intval($_GET['id']);

	$result_news=$db->query("SELECT active  FROM cc".$n."_banner_mgr where banner_id  ='$news_id'");
	$row=$db->fetch_array($result_news);
	$active=$row['active'];
	if ($active==0){
		$sql="update cc".$n."_banner_mgr set active='1' where banner_id ='$news_id'";
	}else{
	$sql="update cc".$n."_banner_mgr set active='0' where banner_id ='$news_id'";
}
$update=$db->query($sql);
header("LOCATION: banner.php");

}

if($action=="delete") {
	$banner_id=intval($_GET['id']);
	$sql="delete from cc".$n."_banner_mgr where banner_id ='$banner_id'";
	$update=$db->query($sql);
	header("LOCATION: banner.php");
}



if($action=="update") {
	$banner_id =intval($_GET['id']);
	$b_label=$_POST['banner_label'];
	$b_code=$_POST['banner_code'];

	if ($b_code==""){
		error_msg("Du hast keinen Bannercode eingetragen");
		exit();
	}
	if ($b_label	==""){
		$sql="SELECT max(banner_id) as maxi_b FROM cc".$n."_banner_mgr";
		$result=$db->query($sql);
		$row=$db->fetch_array($result);
		$b_label="Banner Nr.:" .intval($row['maxi_b'])+1;
	}

	$db->query("update  cc".$n."_banner_mgr set banner_code='".$b_code."', banner_label ='$b_label',banner_count='0',active='0' where banner_id ='$banner_id' ");

	header("LOCATION: banner.php");
	exit();
}

?>
