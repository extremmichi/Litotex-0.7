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

$modul_name="acp_news";



if($action=="main") {
	$menu_name="News";
	$tpl->assign( 'menu_name',$menu_name);
	$new_found_inhalt=array();
	$new_found=array();
	$result_news=$db->query("SELECT * FROM cc".$n."_news order by news_id ");

	while($row_g=$db->fetch_array($result_news)) {
		$tt_text=$row_g['text'];
		$tt_text=str_replace("\"","\'",$tt_text);
		$new_found_inhalt=array($row_g['news_id'],$row_g['date'],$tt_text,$row_g['heading'],$row_g['activated']);
		array_push($new_found,$new_found_inhalt);
	}
	$tpl->assign('daten', $new_found);
	template_out('news.html',$modul_name);
	exit();
}

if($action=="new") {
	$menu_name="News eintragen";
	$tpl->assign( 'menu_name',$menu_name);
	$tpl->assign('ACTION_SAVE','save');
	template_out('news_new.html',$modul_name);
	exit();
}

if($action=="save") {
	if(empty($_POST['new_news'])||empty($_POST['new_news'])) {
		error_msg($l_emptyfield_error);
	} else {
		$text = mysql_real_escape_string(trim($_POST['new_news']));
		$heading = mysql_real_escape_string(trim($_POST['heading']));
		if ($heading==""){
			$heading="no input";
		}
		$date = date("d.m.Y, H:i");
		$order = array("\r\n", "</p>");
		$replace = '<p>';
		$text= str_replace($order, $replace, $text);
		$text=nl2br($text);
		$sql="INSERT into cc".$n."_news (user_id,heading,date,text) VALUES('$_SESSION[userid]','$heading','$date','".$text."')";
		$update=$db->query($sql);
	}
	header("LOCATION: ".LITO_MODUL_PATH_URL."acp_news/news.php");
	exit();
}

if($action=="edit") {
	$news_id=intval($_GET['id']);
	$result_news=$db->query("SELECT * FROM cc".$n."_news where news_id ='$news_id'");
	$row=$db->fetch_array($result_news);
	$tpl->assign('NEWS_OVER',$row['heading']);
	$tpl->assign('ACTION_SAVE','update&id='.$news_id);
	$tpl->assign('NEWS_TEXT_LANG', $row['text']);
	template_out('news_new.html',$modul_name);
}

if($action=="update") {
	$news_id=intval($_GET['id']);
	if(empty($_POST['new_news'])||empty($_POST['new_news'])) {
		error_msg($l_emptyfield_error);
	} else {
		$text = mysql_real_escape_string(trim($_POST['new_news']));
		$heading = mysql_real_escape_string(trim($_POST['heading']));
		if ($heading==""){
			$heading="no input";
		}
		$date = date("d.m.Y, H:i");
		$sql="update cc".$n."_news set user_id='$_SESSION[userid]' ,heading= '$heading' ,date='$date',text='".nl2br($text)."' where news_id ='$news_id'";
		$update=$db->query($sql);
	}
	header("LOCATION: ".LITO_MODUL_PATH_URL."acp_news/news.php");
	exit();
}

if($action=="activate") {
	$news_id=intval($_GET['id']);
	$result_news=$db->query("SELECT activated FROM cc".$n."_news where news_id ='$news_id'");
	$row=$db->fetch_array($result_news);
	$active=$row['activated'];
	if ($active==0){
		$sql="update cc".$n."_news set activated='1' where news_id ='$news_id'";
	}else{
	$sql="update cc".$n."_news set activated='0' where news_id ='$news_id'";
}
$update=$db->query($sql);
header("LOCATION: ".LITO_MODUL_PATH_URL."acp_news/news.php");
}

if($action=="delete") {
	$news_id=intval($_GET['id']);
	$sql="delete from cc".$n."_news where news_id ='$news_id'";
	$update=$db->query($sql);
	header("LOCATION: ".LITO_MODUL_PATH_URL."acp_news/news.php");
}
?>
