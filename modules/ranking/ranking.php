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
$modul_name="ranking";
require("./../../includes/global.php");
require_once("./../../includes/functions.php");
if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if(!isset($_SESSION['userid'])) {
	show_error("ups",$modul_name);
	exit();
}

if(isset($_POST['user'])) {
	$user = $_POST['user'];
	$result = $db->query("SELECT userid FROM cc".$n."_users WHERE username LIKE '%$user%'");
	$row = $db->fetch_array($result);
	header("LOCATION: users.php?id=$row[userid]");
	exit();
}

$module=get_modulname(6);
$msg_modul_org="./../".$module[0]."/".$module[1];

if($action=="main") {
	$i=0;
	$nn=0;
	$result = $db->query("SELECT * FROM cc".$n."_users WHERE blocked='0' AND serveradmin='0' ORDER BY points DESC LIMIT 10");
	while($row = $db->fetch_array($result)) {
		$nn++;
		if($row['lastactive']>(time()-3600)) $online="<span class=\"green\"><b>&nbsp;(Online)</b></span>";
		else $online="";

		$allianzname=generate_allilink($row['allianzid']);

		$resultr=$db->query("SELECT rassenname FROM cc".$n."_rassen WHERE rassenid='$row[rassenid]'");
		$userrasse=$db->fetch_array($resultr);
		if (trim($row['userpic'])==""){
			$userpic="<img src=\"".LITO_IMG_PATH_URL."members/no_user_pic.jpg\">";
		}else{
		$userpic=$userpic="<img src=" . $row['userpic'] . ">";
	}
	$resultl=$db->query("SELECT islandid FROM cc".$n."_countries WHERE userid='$row[userid]'");
	$numoflands=$db->num_rows($resultl);
	$inhalt[$i]['profile_link']=generate_userlink($row['userid'],$row['username']);
	$inhalt[$i]['username']=$row['username'];
	$inhalt[$i]['position']=$nn;
	$inhalt[$i]['image']=$userpic;
	$inhalt[$i]['race']=$userrasse['rassenname'];
	$inhalt[$i]['ali_name']=$allianzname;
	$inhalt[$i]['ali_id']=$row['allianzid'];
	$inhalt[$i]['points']=$row['points'];
	$inhalt[$i]['country_count']=$numoflands;
	$inhalt[$i]['online']=$online;
	$inhalt[$i]['user_id']=$row['userid'];
	$inhalt[$i]['message_modul_name']=generate_messagelink($row['username'],1);


	$i++;
}
$tpl->assign('daten', $inhalt);
template_out('rank_topten.html',$modul_name);
exit();
}

if($action=="user") {
	$pagesbit=0;
	if(isset($_REQUEST['page'])) $page=intval($_REQUEST['page']);
	else $page=0;

	$start=$page*30;
	$bpage=$page-1;
	$fpage=$page+1;

	$result=$db->query("SELECT userid FROM cc".$n."_users");
	$numOfUsers=$db->num_rows($result);

	$maxpage=ceil($numOfUsers/30)-1;

	$i=0+$start; //start

	$result=$db->query("SELECT userid,username,points,allianzid,lastlogin,lastpoints,lastactive,status,umod FROM cc".$n."_users WHERE blocked='0' ORDER BY points DESC LIMIT $start,30");

	$nn=0;
	while($row=$db->fetch_array($result)) {
		$i++; //add one to I
		if($row['lastactive']>(time()-3600)) $online="<span class=\"green\"><b>&nbsp;(on)</b></span>";
		else $online="<span class=\"red\">&nbsp;(off)</span>";


		$alli=$row['allianzid'];
		$chpt=$row['points']-$row['lastpoints'];
		if ($chpt > 0) {
			$chpt="(+$chpt)";
		} elseif ($chpt < 0) {
			$chpt="($chpt)";
		} else {
			$chpt="";
		}
		$lastlog=strftime("%d.%m. %H:%M",$row['lastlogin']);

		$allianzname=generate_allilink($row['allianzid']);

		$nn++;

		$inhalt[$nn]['username']=generate_messagelink_smal($row['username'])." " . generate_userlink($row['userid'],$row['username']) .$online;
		$inhalt[$nn]['ali_name']=$allianzname;
		$inhalt[$nn]['ali_id']=$row['allianzid'];
		$inhalt[$nn]['points']=$row['points'] ." ".$chpt;
		$inhalt[$nn]['online']=$online;
		$inhalt[$nn]['user_id']=$row['userid'];
		$inhalt[$nn]['login']=$lastlog;
		$inhalt[$nn]['platz']=$i;


	}
	for($i=0;$i < $maxpage+1;$i++) {
		$pagesbit.="<option value=\"$i\">".($i+1)."</option>";
	}
	$tpl->assign('page', $pagesbit);
	$tpl->assign('daten', $inhalt);
	template_out('ranking.html',$modul_name);
	exit();
}


if($action=="allianz") {
	if(isset($_REQUEST['page'])) $page=intval($_REQUEST['page']);
	else $page=0;
	$start=$page*30;
	$bpage=$page-1;
	$fpage=$page+1;
	$allianz=1;
	$result=$db->query("SELECT aid FROM cc".$n."_allianz");
	$numOfUsers=$db->num_rows($result);
	$maxpage=ceil($numOfUsers/30)-1;

	$i=0+$start; //start
	$nn=0;
	$result=$db->query("SELECT * FROM cc".$n."_allianz ORDER BY points DESC LIMIT $start,30");
	while($row=$db->fetch_array($result)) {
		$alli=$row['aid'];
		if ($row['text']) {
			$infotext=substr($row['text'],0 ,30);
			if (strlen($row['text'])>30) {
				$infotext .= "...";
			}
		} else {
			$infotext=$ln_rankings_no_allianz_infos;
		}
		$i++;
		$nn++;
		$inhalt[$nn]['ali_name']=generate_allilink($alli);
		$inhalt[$nn]['ali_test']=$infotext;
		$inhalt[$nn]['points']=$row['points'];
		$inhalt[$nn]['platz']=$i;
		$ali_flag=get_allianz_flag($row['aid']);
		$inhalt[$nn]['ali_flag']=$ali_flag;
		$inhalt[$nn]['ali_anz']=$row['members'];
	}
	for($i=0;$i<$maxpage+1;$i++) {
		$pagesbit.="<option value=\"$i\">".($i+1)."</option>";
	}

	$tpl->assign('page', $pagesbit);
	$tpl->assign('daten', $inhalt);

	template_out('ranking_ali.html',$modul_name);
	exit();
}


?>
