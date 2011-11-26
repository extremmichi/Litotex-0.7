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
$modul_name="forum";
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


function get_user_right($forum_id){
	global $db,$n,$userdata;
	$ali_id=$userdata['allianzid'];

	$result_last=$db->query("SELECT alli_id  FROM cc".$n."_forum where si_forum_id ='$forum_id'");
	$row_last=$db->fetch_array($result_last);

	if ($row_last['alli_id'] ==$ali_id){
		return 1;
	} else{
	return 0;
}
}

function get_forum_from_id($forum_id){
	global $db,$n,$userdata;
	$result_last=$db->query("SELECT si_forum_name FROM cc".$n."_forum where si_forum_id ='$forum_id'");
	$row_last=$db->fetch_array($result_last);
	return $row_last['si_forum_name'];
}
function get_topic_from_id($Topic_id){
	global $db,$n,$userdata;
	$result_last=$db->query("SELECT si_topic_title  FROM cc".$n."_forum_topics where si_topic_id  ='$Topic_id'");
	$row_last=$db->fetch_array($result_last);
	return $row_last['si_topic_title'];
}
function get_last_id_from_topic($forum_id,$topic_id){
	global $db,$n,$userdata;
	$result_last=$db->query("SELECT si_post_id  FROM cc".$n."_forum_posts where si_forum_id  ='$forum_id' and si_topic_id ='$topic_id' order by si_post_id DESC Limit 1");
	$row_last=$db->fetch_array($result_last);
	return $row_last['si_post_id'];
}

// **************************************************
//              neue nachrichten suchen
// **************************************************
//letzte ID_dieses Forums suchen
function get_last_post_id_forum($forum_id){
	global $db,$n,$userdata;
	$result_last=$db->query("SELECT si_post_id  FROM cc".$n."_forum_posts where si_forum_id  ='$forum_id' order by si_post_id DESC Limit 1");
	$row_last=$db->fetch_array($result_last);
	return intval($row_last['si_post_id']);
}
// letzte ID aus den angeschauten beiträgen suchen
function get_last_show_id_forum($forum_id){
	global $db,$n,$userdata;
	$uid=$userdata['userid'];
	$result_last=$db->query("SELECT post_id FROM cc".$n."_forum_last where forum_id ='$forum_id' and user_id=$uid order by post_id DESC Limit 1");
	$row_last=$db->fetch_array($result_last);
	return intval($row_last['post_id']);
}

function get_last_post_id_forum_topic($forum_id,$topic_id){
	global $db,$n,$userdata;
	$result_last=$db->query("SELECT si_post_id  FROM cc".$n."_forum_posts where si_forum_id  ='$forum_id' and si_topic_id ='$topic_id' order by si_post_id DESC Limit 1");
	$row_last=$db->fetch_array($result_last);
	return intval($row_last['si_post_id']);
}
// letzte ID aus den angeschauten beiträgen suchen
function get_last_show_id_forum_topic($forum_id,$topic_id){
	global $db,$n,$userdata;
	$uid=$userdata['userid'];
	$result_last=$db->query("SELECT post_id FROM cc".$n."_forum_last where forum_id ='$forum_id' and topic_id  ='$topic_id' and user_id=$uid order by post_id DESC Limit 1");
	$row_last=$db->fetch_array($result_last);
	return intval($row_last['post_id']);
}





if($action=="main") {
	$ali_id=intval($userdata['allianzid']);
	$uid=$userdata['userid'];
	$uname=$userdata['username'];


	if ($ali_id <= 0 ){
		show_error('ln_error_11',$modul_name);
		exit();

	}


	$f_count=0;
	$result=$db->query("SELECT * FROM cc".$n."_forum  where  alli_id ='$ali_id' ORDER BY si_forum_order  DESC");
	while($row=$db->fetch_array($result)) {

		$f_id=$row['si_forum_id'];
		$f_name="<a href=\"forum.php?action=show_forum&f_id=$f_id\">".$row['si_forum_name']."</a>";
		$f_descr=$row['si_forum_desc'];
		$f_pic_name=$row['si_forum_pic'];
		$f_count_topic=$row['si_count_topic'];
		$f_count_post=$row['si_count_post'];

		$Letzte_post_id=get_last_post_id_forum($f_id);
		$Letzte_show_id=get_last_show_id_forum($f_id);


		$new_entry =0;
		$result_t=$db->query("SELECT si_topic_id FROM cc".$n."_forum_topics  where si_forum_id ='$f_id'  ORDER BY si_topic_id  DESC");
		while($row_t=$db->fetch_array($result_t)) {
			$ft_id=$row_t['si_topic_id'];
			$last_topic =get_last_post_id_forum_topic($f_id,$ft_id);
			$last_sgow_topic=get_last_show_id_forum_topic($f_id,$ft_id);
			if ($last_topic > $last_sgow_topic ){
				$new_entry=1;
			}
		}

		if ($new_entry==1){
			$f_pic="<img src=\"".LITO_IMG_PATH_URL.$modul_name."/message_new.gif\">";
		}else{
		$f_pic="<img src=\"".LITO_IMG_PATH_URL.$modul_name."/message.gif\">";
	}


	$forum_over[$f_count]['f_pic']=$f_pic;
	$forum_over[$f_count]['f_name']=$f_name;
	$forum_over[$f_count]['f_count_topic']=$f_count_topic;
	$forum_over[$f_count]['f_count_post']=$f_count_post;
	$forum_over[$f_count]['f_descr']=$f_descr;
	$f_count++;

}
$tpl->assign('forum_data', $forum_over);
template_out('forum_over.html',$modul_name);

exit();
}


if($action=="show_forum") {
	$f_id=intval($_GET['f_id']);
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}

	if (get_user_right($f_id) == 0 ){
		show_error('ln_forum_err_1',$modul_name);
	}

	$topic_count=1;
	$topic_a_count=0;
	$result=$db->query("SELECT * FROM cc".$n."_forum_topics where si_forum_id  = '$f_id' ORDER BY si_topic_last_post_time  DESC");
	while($row=$db->fetch_array($result)) {
		$ft_id=$row['si_topic_id'];

		$ft_title="<a href=\"forum.php?action=show_post&f_id=$f_id&ft_id=$ft_id\">".$row['si_topic_title']."</a>";


		$ft_creator=$row['si_topic_create_uname'];
		$ft_creator_t=$row['si_topic_create_time'];
		$ft_creator_time=date("d.m.Y, H:i",$ft_creator_t);


		$ft_views=$row['si_topic_views'];
		$ft_anzahl=$row['si_posts_count'];
		$ft_last_post_t=$row['si_topic_last_post_time'];
		$ft_last_post_time=date("d.m.Y, H:i",$ft_last_post_t);
		$ft_last_post_name=$row['si_topic_last_post_name'];



		$ft_class="forum_topic_".$topic_count;
		$topic_count=$topic_count+1;
		if ($topic_count>2){
			$topic_count=1;
		}

		$last_topic =get_last_post_id_forum_topic($f_id,$ft_id);
		$last_sgow_topic=get_last_show_id_forum_topic($f_id,$ft_id);


		if ($last_topic > $last_sgow_topic ){
			$f_pic="<img src=\"".LITO_IMG_PATH_URL.$modul_name."/message_new.gif\" title=\"neue Nachrichten\" alt=\"neue Nachrichten\" border=\"0\">";
		}else{
		$f_pic="<img src=\"".LITO_IMG_PATH_URL.$modul_name."/message.gif\" title=\"keine neuen Nachrichten\" alt=\"keine neuen Nachrichten\" border=\"0\">";
	}

	$f_topic[$topic_a_count]['f_pic']=$f_pic;
	$f_topic[$topic_a_count]['ft_title']=$ft_title;
	$f_topic[$topic_a_count]['ft_anzahl']=$ft_anzahl;
	$f_topic[$topic_a_count]['ft_views']=$ft_views;
	$f_topic[$topic_a_count]['ft_creator']=$ft_creator;
	$f_topic[$topic_a_count]['ft_creator_time']=$ft_creator_time;
	$f_topic[$topic_a_count]['ft_last_post_name']=$ft_last_post_name;
	$f_topic[$topic_a_count]['ft_last_post_time']=$ft_last_post_time;



	$topic_a_count++;




}
$ft_parent="<a href=\"forum.php\">Forum</a>". " / ".get_forum_from_id($f_id);
$new_thema="<a href=\"forum.php?action=new_threadid&ft_id=$f_id&f_id=$f_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/new_thema.png\" title=\"neues Thema hinzuf&uuml;gen\" alt=\"neues Thema hinzuf&uuml;gen\" border=\"0\"></a>";

$tpl->assign('forum_t_data', $f_topic);
$tpl->assign('ft_parent', $ft_parent);
$tpl->assign('new_thema', $new_thema);
template_out('forum_topics.html',$modul_name);

exit();
}


if($action=="new_threadid") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}

	$take_action="forum.php?action=create_thread&ft_id=$ft_id&f_id=$f_id";
	$ft_parent="<a href=\"forum.php\">Forum</a>". " / ".get_forum_from_id($ft_id);

	$tpl->assign('ft_parent', $ft_parent);
	$tpl->assign('take_action', $take_action);

	template_out('forum_new_topics.html',$modul_name);

	exit();
}

if($action=="create_thread") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	$ft_titel_new=c_trim($_POST['text_over']);
	$ft_titel_text=c_trim($_POST['text_l']);
	$date=time();
	$uid=$userdata['userid'];
	$uname=$userdata['username'];
	$my_ip="";
	if ($ft_titel_new==""){
		show_error('ln_error_8',$modul_name);
		header("LOCATION: forum.php?action=new_threadid&ft_id=$ft_id");
		exit();
	}
	if ($ft_titel_text==""){
		show_error('ln_error_9',$modul_name);
		header("LOCATION: forum.php?action=new_threadid&ft_id=$ft_id");
		exit();
	}
	if ($uid<=0){
		show_error('ln_error_10',$modul_name);

		exit();
	}


	$ft_titel_text=c_trim($ft_titel_text);


	$db->query("INSERT INTO cc".$n."_forum_topics (si_topic_title,si_topic_create_uid ,si_topic_create_uname ,si_topic_create_time,si_forum_id,si_topic_last_post_time,si_topic_last_post_name,si_topic_last_post_uid) VALUES ('".$ft_titel_new."','".$uid."','".$uname."','".$date."','".$ft_id."','".$date."','".$uname."','".$uid."')");
	$ulast_id=$db->insert_id();
	$db->query("INSERT INTO cc".$n."_forum_posts (si_forum_id ,si_topic_id ,si_poster_id,si_poster_name,si_post_text ,si_post_time,si_poster_ip ) VALUES ('".$f_id."','".$ulast_id."','".$uid."','".$uname."','.".$ft_titel_text."','".$date."','".$my_ip."')");

	$db->unbuffered_query("UPDATE cc".$n."_forum SET si_count_topic  =si_count_topic +1 ,si_count_post=si_count_post+1 WHERE si_forum_id ='".$ft_id."' ");

	header("LOCATION: forum.php?action=show_forum&f_id=$ft_id");
	exit();


}
if($action=="show_post") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	$uid=$userdata['userid'];


	if (get_user_right($f_id) == 0 ){

		show_error('ln_error_10',$modul_name);
		exit();
	}

	$topic_count=1;
	$last_post_id = 0;
	$a_count=0;
	$result=$db->query("SELECT * FROM cc".$n."_forum_posts where si_forum_id = '".$f_id."' and si_topic_id='".$ft_id."' ORDER BY si_post_time ASC");
	while($row=$db->fetch_array($result)) {
		$post_name_t=$row['si_poster_name'];
		$post_new_id=$row['si_post_id'];
		$post_id_t=$row['si_poster_id'];
		$post_name=generate_userlink($post_id_t,$post_name_t);
		$post_text=bb2html($row['si_post_text']);
		$post_date_t=$row['si_post_time'];
		$post_date=date("d.m.Y, H:i",$post_date_t);


		if ($last_post_id < $post_new_id){
			$last_post_id=$post_new_id;
		}

		if ($post_id_t==$userdata['userid']){
			$post_edit_pic="<a href=\"forum.php?action=edit&ft_id=$ft_id&f_id=$f_id&fp_id=$post_new_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/edit.png\" title=\"Bearbeiten\" alt=\"Bearbeiten\" border=\"0\"></a>";
		}else
		{
			$post_edit_pic="";
		}
		// löschen
		$ali_admin=$userdata['isadmin'];
		if ($post_id_t==$userdata['userid'] || $ali_admin == 1){
			$post_del_pic="<a href=\"forum.php?action=delete&ft_id=$ft_id&f_id=$f_id&fp_id=$post_new_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/del.png\" title=\"Löschen\" alt=\"Löschen\" border=\"0\"></a>";
		}else
		{
			$post_del_pic="";
		}

		$forum_posts[$a_count]['post_name']=$post_name;
		$forum_posts[$a_count]['post_date']=$post_date;
		$forum_posts[$a_count]['post_edit_pic']=$post_edit_pic;
		$forum_posts[$a_count]['post_del_pic']=$post_del_pic;
		$forum_posts[$a_count]['post_text']=$post_text;


		$a_count++;
	}
	$sql="DELETE From cc".$n."_forum_last where forum_id ='".$f_id."' AND topic_id ='".$ft_id."' and user_id = '".$uid."'";

	$db->unbuffered_query($sql);
	$db->unbuffered_query("INSERT INTO  cc".$n."_forum_last (forum_id,topic_id,user_id,post_id) VALUES ('".$f_id."','".$ft_id."','".$uid."','".$last_post_id."')");

	$new_thema="<a href=\"forum.php?action=replay_thread&ft_id=$ft_id&f_id=$f_id\"><img src=\"".LITO_IMG_PATH_URL.$modul_name."/antworten.png\" title=\"Antwort hinzuf&uuml;gen\" alt=\"Antwort hinzuf&uuml;gen\" border=\"0\"></a>";
	$ft_parent="<a href=\"forum.php\">Forum</a>". " / <a href=\"forum.php?action=show_forum&f_id=$f_id\">".get_forum_from_id($f_id)."</a> / " .get_topic_from_id($ft_id);



	$db->unbuffered_query("UPDATE cc".$n."_forum_topics SET si_topic_views =si_topic_views +1 WHERE si_topic_id ='".$ft_id."' ");


	$tpl->assign('forum_posts', $forum_posts);
	$tpl->assign('new_thema', $new_thema);
	$tpl->assign('ft_parent', $ft_parent);
	$tpl->assign('take_action', $take_action);

	template_out('forum_posts.html',$modul_name);


	exit();

}

if($action=="replay_thread") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	$ft_parent="<a href=\"forum.php\">Forum</a>". " / <a href=\"forum.php?action=show_forum&f_id=$f_id\">".get_forum_from_id($f_id)."</a> / " .get_topic_from_id($ft_id);

	$take_action="forum.php?action=replay_save&ft_id=$ft_id&f_id=$f_id";

	$tpl->assign('ft_parent', $ft_parent);
	$tpl->assign('take_action', $take_action);

	template_out('forum_new_topics.html',$modul_name);
	exit();


}



if($action=="replay_save") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}

	$ft_titel_text=html2bb($_POST['text_l']);
	$date=time();
	$uid=$userdata['userid'];
	$uname=$userdata['username'];
	$my_ip="";

	if ($ft_titel_text==""){
		show_error('ln_error_9',$modul_name);
		header("LOCATION: forum.php?action=new_threadid&ft_id=$ft_id");
		exit();
	}
	if ($uid<=0){
		show_error('ln_error_10',$modul_name);

		exit();
	}

	$db->query("INSERT INTO cc".$n."_forum_posts (si_forum_id ,si_topic_id ,si_poster_id,si_poster_name,si_post_text ,si_post_time,si_poster_ip ) VALUES ('".$f_id."','".$ft_id."','".$uid."','".$uname."','".$ft_titel_text."','".$date."','".$my_ip."')");
	$db->unbuffered_query("UPDATE cc".$n."_forum_topics SET si_posts_count=si_posts_count  +1, si_topic_last_post_time ='".$date."', si_topic_last_post_name='".$uname."', si_topic_last_post_uid='".$uid."' WHERE si_topic_id ='".$ft_id."' ");
	$db->unbuffered_query("UPDATE cc".$n."_forum SET si_count_post =si_count_post +1 WHERE si_forum_id ='".$f_id."' ");
	header("LOCATION: forum.php?action=show_post&f_id=$f_id&ft_id=$ft_id");
	exit();


}


if($action=="edit") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	$fp_id=intval($_GET['fp_id']);
	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($fp_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	$result=$db->query("SELECT * FROM cc".$n."_forum_posts where si_post_id = '".$fp_id."' ");
	$row=$db->fetch_array($result);
	$com==1;
	$forum==1;

	$allianz_t_l=$row['si_post_text'];


	$ft_parent="<a href=\"forum.php\">Forum</a>". " / <a href=\"forum.php?action=show_forum&f_id=$f_id\">".get_forum_from_id($f_id)."</a> / " .get_topic_from_id($ft_id);
	$com=1;
	$forum=0;
	$take_action="forum.php?action=edit_save&ft_id=$ft_id&f_id=$f_id&fp_id=$fp_id";


	$tpl->assign('ft_parent', $ft_parent);
	$tpl->assign('take_action', $take_action);
	$tpl->assign('allianz_t_l', $allianz_t_l);


	template_out('forum_new_topics.html',$modul_name);
	exit();


}


if($action=="edit_save") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	$fp_id=intval($_GET['fp_id']);

	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($fp_id<=0){
		header("LOCATION: forum.php");
		exit();
	}

	$ft_titel_text=html2bb($_POST['text_l']);
	$date=time();
	$uid=$userdata['userid'];
	$uname=$userdata['username'];
	$my_ip="";
	$date_edit_time=date("d.m.Y, H:i",$date);
	$ft_titel_text=$ft_titel_text."<br><br>geändert am :".$date_edit_time;

	if ($ft_titel_text==""){
		show_error('ln_error_9',$modul_name);
		header("LOCATION: forum.php?action=new_threadid&ft_id=$ft_id");
		exit();
	}
	if ($uid<=0){
		show_error('ln_error_10',$modul_name);
		exit();
	}
	$sql="DELETE From cc".$n."_forum_last where forum_id ='".$f_id."' AND topic_id ='".$ft_id."' and user_id != '".$uid."'";
	$db->unbuffered_query($sql);
	$db->unbuffered_query("UPDATE cc".$n."_forum_posts SET si_post_text ='".$ft_titel_text."' WHERE si_post_id  ='".$fp_id."' and si_poster_id='".$uid."'");
	header("LOCATION: forum.php?action=show_post&f_id=$f_id&ft_id=$ft_id");
	exit();

}

if($action=="delete") {
	$ft_id=intval($_GET['ft_id']);
	$f_id=intval($_GET['f_id']);
	$f_p_id=intval($_GET['fp_id']);


	if ($ft_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	if ($f_id<=0){
		header("LOCATION: forum.php");
		exit();
	}
	$uid=$userdata['userid'];
	$ali_admin=$userdata['isadmin'];

	if (get_user_right($f_id) == 0 ){
		show_error('ln_forum_err_1',$modul_name);

	}

	if ($ali_admin==1){
		$sql="DELETE From cc".$n."_forum_posts where  si_forum_id ='".$f_id."' AND  si_topic_id ='".$ft_id."' and  si_post_id='".$f_p_id."' ";
		$db->unbuffered_query($sql);
	}else{
	$sql="DELETE From cc".$n."_forum_posts where  si_forum_id ='".$f_id."' AND  si_topic_id ='".$ft_id."' and  si_post_id='".$f_p_id."' and  si_poster_id='".$uid."' ";
	$db->unbuffered_query($sql);

}

$sql="Update cc".$n."_forum_topics set si_posts_count= si_posts_count -1  where  si_forum_id ='".$f_id."' AND  si_topic_id ='".$ft_id."'  ";
$db->unbuffered_query($sql);


header("LOCATION: forum.php?action=show_post&f_id=$f_id&ft_id=$ft_id");
exit();

}


?>