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
require("./../../includes/global.php");

$modul_name="message";

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if(!isset($_SESSION['userid'])) {
	show_error('LOGIN_ERROR','core');
	exit();
}

if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

if (isset ($_REQUEST['action']))
$action = $_REQUEST['action'];
else
$action = "main";


if ($action == "main") {

	/** check folder **/
	if (isset ($_REQUEST['folder']))
	$folder = intval($_REQUEST['folder']);
	else
	$folder = "inbox";

	if ($folder == "archive"){
		$box = "inbox='0' AND archive='1'";
		$is_archiv=1;
	}
	else{
	$box = "inbox='1' AND archive='0'";
	$is_archiv=0;
}
$result = $db->query("SELECT * FROM cc" . $n . "_messages WHERE $box AND touserid='" . $userdata['userid'] . "' ORDER BY time DESC");

$i=0;
while ($row = $db->fetch_array($result)) {
	$time = date("d.m.Y, H:i", $row['time']);
	$row['subject'] = c_trim($row['subject']);


	$is_archiv=$row['archive'];
	if ($row['pri'] == 1)
	$pri = "<span class=\"red\">!</span>";
	elseif ($row['pri'] == 2) $pri = "<span class=\"green\">!</span>";
	else
	$pri = "";
	$i++;

	$inhalt[$i]['profile_link']=generate_userlink($row['fromuserid'],$row['username']);

	$inhalt[$i]['subject']=$row['subject'];
	$inhalt[$i]['fromuserid']=$row['fromuserid'];
	$inhalt[$i]['username']=$row['username'];
	$inhalt[$i]['pmid']=$row['pmid'];
	$inhalt[$i]['newpm']=$row['isnew'];
	$inhalt[$i]['message_time']=$time;
	$inhalt[$i]['is_archive']=$is_archiv;
	$inhalt[$i]['pri']=$pri ;


	$tpl->assign('message_inbox', $inhalt);
}
$message_count=$db->num_rows($result);
// anzahl archiv nachrichten suchen
$result=$db->query("SELECT * FROM cc" . $n . "_messages WHERE inbox='0' AND archive='1' AND touserid='" . $userdata['userid'] . "' ORDER BY time DESC");
$archiv_count=$db->num_rows($result);

$tpl->assign('MESSAGE_COUNT', $message_count);
$tpl->assign('ARCHIV_COUNT', $archiv_count);
template_out('message_inbox.html',$modul_name);
exit();
}

if ($action == "send") {
	$pmid=0;
	$subject_r="";
	$username_r="";
	$message_r ="";
	$pmid = intval($_GET['pmid']);
	$username_r = c_trim($_GET['username']);
	if ($pmid) {
		$result = $db->query("SELECT * FROM cc" . $n . "_messages WHERE pmid='$pmid' AND touserid='" . $userdata['userid'] . "'");
		$row = $db->fetch_array($result);
		$subject_r = "Re: $row[subject]";
		$message_r = "\n\n---- Nachricht vom " . date("d.m.Y, H:i", $row['time']) . " ----\n".$row['text']."\n\n------------";
	}
	$tpl->assign('M_U_SEND', $username_r);
	$tpl->assign('M_U_SUB', $subject_r);
	$tpl->assign('M_U_MESSAGE', $message_r );



	template_out('message_send.html',$modul_name);
	exit ();
}

if ($action == "submit_send") {
	$username = c_trim($_POST['username']);
	$text = html2bb($_POST['text']);
	$user_array = explode(",", $_POST['username']);
	$subject = c_trim($_POST['subject']);
	$pri = intval($_POST['pri']);

	if (!$username || !$text || !$subject) {
		show_error('ln_message_1',$modul_name);
		exit ();
	}
	for ($i = 0; $i < count($user_array); $i++) {
		$userid_c = get_userid($user_array[$i]);
		if (!is_username($user_array[$i])) {
			show_error('ln_users_e_1',$modul_name);
			exit ();
		}
		$db->query("INSERT INTO cc" . $n . "_messages (username,fromuserid,touserid,text,time,isnew,inbox,subject,pri) VALUES ('" . $userdata['username'] . "','" . $userdata['userid'] . "','".$userid_c."','".mysql_real_escape_string ($text)."','" . time() . "','1','1','".mysql_real_escape_string ($subject)."','".$pri."')");

	}
	header("LOCATION: message.php");
	exit ();
}

if ($action == "lookup") {
	$pmid = intval($_GET['pmid']);
	$result = $db->query("SELECT * FROM cc" . $n . "_messages WHERE touserid='" . $userdata['userid'] . "' AND pmid='$pmid'");
	$row = $db->fetch_array($result);
	if ($row['pmid'] != $pmid) {
		show_error('ln_message_e_notfound',$modul_name);
		exit ();
	}
	$db->query("UPDATE cc" . $n . "_messages SET isnew='0' WHERE pmid='$pmid' AND touserid='" . $userdata['userid'] . "' AND isnew='1'");

	$text = bb2html($row['text']);

	$row['subject'] = c_trim($row['subject']);
	$time = date("d.m.Y, H:i", $row['time']);
	$M_USER_ID=$row['fromuserid'];
	$M_USER_NAME=$row['username'];

	if ($op_use_badwords==1){
		$result_bad=$db->query("select badword from cc" . $n . "_badwords where in_mail ='1'");
		while($row_bad=$db->fetch_array($result_bad)) {
			$text  = str_replace($row_bad['badword'], "**ZENSIERT**", $text);
		}
	}


	$tpl->assign('M_USER_ID', $M_USER_ID);
	$tpl->assign('M_USER_NAME', $M_USER_NAME);
	$tpl->assign('M_SUBJECT', $row['subject']);
	$tpl->assign('M_TEXT', $text);
	$tpl->assign('M_ID', $row['pmid']);
	$tpl->assign('M_DATE', $time );
	$link = generate_userlink($row['fromuserid'],$row['username']);
	$tpl->assign('PROFIL_LINK', $link);


	template_out('message_read.html',$modul_name);
	exit();
}

if ($action == "remove") {
	$pmid = intval($_GET['pmid']);
	if (!$pmid) {
		show_error('ln_system_error_m',$modul_name);
		exit ();
	}
	$db->unbuffered_query("DELETE FROM cc" . $n . "_messages WHERE pmid='$pmid' AND touserid='".$userdata['userid']."'");
	header("LOCATION: message.php");
	exit ();
}

if ($action == "OK") {


	$todo =intval($_GET['todo']);
	$checkbox=$_GET['checkbox'];



	if ($checkbox=="" && $todo  < 3 ){
		header("LOCATION: message.php");
		exit();
	}

	if ($todo =="1" ){
		$xyz = 0;
		foreach ($checkbox as $pmid => $box) {
			if ($pmid == true) {
				$xyz = $xyz +1;
				settype($pmid, integer);
				$db->unbuffered_query("DELETE FROM cc" . $n . "_messages WHERE pmid='".$pmid."' AND touserid='".$userdata['userid']."'");
				$update = mysql_query($change);
			}
		}
		header("LOCATION: message.php");
		exit ();
	}elseif($todo =="2")	{
		$xyz = 0;
		foreach ($checkbox as $pmid => $box) {
			if ($pmid == true) {
				$xyz = $xyz +1;
				settype($pmid, integer);
				$db->unbuffered_query("UPDATE cc" . $n . "_messages SET archive='1', inbox='0' WHERE pmid='".$pmid."' AND touserid='".$userdata['userid']."'");
				$update = mysql_query($change);
			}
		}
		header("LOCATION: message.php");
		exit ();
	}elseif($todo =="3")	{
		$sql="DELETE FROM cc" . $n . "_messages WHERE touserid='" . $userdata['userid'] . "'";

		$db->unbuffered_query("DELETE FROM cc" . $n . "_messages WHERE touserid='" . $userdata['userid'] . "' AND archive='0'");
		header("LOCATION: message.php");
		exit ();
	}

	header("LOCATION: message.php");
	exit ();
}



if ($action == "move") {

	$pmid = intval($_GET['pmid']);
	$to = c_trim($_GET['to']);
	if (!$to || !$pmid) {
		show_error('ln_system_error_m',$modul_name);
		exit ();
	}
	if ($to == "archive") {
		$db->unbuffered_query("UPDATE cc" . $n . "_messages SET archive='1', inbox='0' WHERE pmid='".$pmid."' AND touserid='".$userdata[userid]."'");
		header("LOCATION: message.php$SID_1");
	}
	exit ();
}



?>