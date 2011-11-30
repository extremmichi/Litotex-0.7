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
$modul_name="search";
require("./../../includes/global.php");

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

if($action=="main") {
	template_out('search.html',$modul_name);
	exit();
}

if($action=="user") {
	$user = c_trim($_POST['user']);

	if (strlen($user) <= 1){
		show_error('SEARCH_ERROR_1',$modul_name);
		exit();
	}


	$result=$db->query("SELECT userid FROM cc".$n."_users");
	$numOfUsers=$db->num_rows($result);


	$daten="";
	$result=$db->query("SELECT userid,username,points,allianzid,lastlogin,lastpoints,lastactive,status,umod,userpic FROM cc".$n."_users  WHERE username LIKE '%$user%' ORDER BY points DESC ");
	$i=0;
	while($row=$db->fetch_array($result)) {
		$username= $row['username'];
		$userpoints= $row['points'];

		if($row['lastactive']>(time()-3600)) $online="<span class=\"green\">&nbsp;(Online)</span>";
		else $online="<span class=\"red\">&nbsp;(Offline)</span>";




		$alli=$row['allianzid'];
		$chpt=$row['points']-$row['lastpoints'];

		$lastlog=strftime("%d.%m. %H:%M",$row['lastlogin']);

		if($row['allianzid']==0) $allianzname="";
		else $allianzname=allianz($row['allianzid']);

		$userpic="";

		if ($row['userpic']==""){
			$userpic=LITO_IMG_PATH_URL."members/no_user_pic.jpg";
		}else{
		$userpic=$row['userpic'];
	}
	$daten[$i]['profile_link']=generate_userlink($row['userid'],$row['username']);
	$daten[$i]['name']=$username;
	$daten[$i]['u_points']=$userpoints;
	$daten[$i]['image']=$userpic;
	$daten[$i]['u_online']=$online;
	$daten[$i]['lastlogin']=$lastlog;
	$daten[$i]['alianz']=$allianzname;
	$daten[$i]['message']=generate_messagelink_smal($username);
	$i++;


}
if ($i > 0 ){
	$tpl->assign('daten', $daten);
}

template_out('search.html',$modul_name);

exit();
}



?>
