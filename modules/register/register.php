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
$modul_name="register";
require("./../../includes/global.php");


if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";



if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}


if($action=="main") {
	template_out('register.html',$modul_name);
	exit();
}

if($action=="submit") {


	$username=c_trim(strtolower($_POST['username']));
	$email=c_trim(strtolower($_POST['email']));
	$useregeln=$_POST['regeln'];


	/** check entries **/
	if(!$username || !$email) {
		show_error("REGISTER_ERROR_2",$modul_name);
		exit();
	}

	if (!$useregeln){
		show_error("REGISTER_ERROR_3",$modul_name);
		exit()	;
	}


	if (!preg_match ("/^[0-9a-z_-]{3,15}$/i", $username)) {
		show_error("REGISTER_ERROR_4",$modul_name);
		exit();
	}



	$pos = strpos ($email, "@");
	if ($pos < 1 ) { // Achtung: 3 Gleichheits-Zeichen
		show_error("REGISTER_ERROR_5",$modul_name);
		exit();
	}

	/** check username of exists **/
	$result=$db->query("SELECT username,userid FROM cc".$n."_users WHERE username='$username'");
	$row=$db->fetch_array($result);
	if(strtolower($row['username'])==$username) {
		show_error("REGISTER_ERROR_6",$modul_name);
		exit();
	}


	/** check username of exists **/
	$result=$db->query("SELECT email,userid FROM cc".$n."_users WHERE email='$email'");
	$row=$db->fetch_array($result);
	if(strtolower($row['email'])==$email) {
		show_error("REGISTER_ERROR_7",$modul_name);
		exit();
	}



	$result2=$db->query("SELECT * FROM cc".$n."_badwords WHERE badword='$username'");
	$tmp=$db->fetch_array($result2);

	if(strtolower($tmp['username']) == $username) {
		show_error("REGISTER_ERROR_8",$modul_name);
		exit();
	}


	if ($op_use_badwords==1){
		$bad_found=0;
		$result_bad=$db->query("select badword from cc" . $n . "_badwords ");
		while($row_bad=$db->fetch_array($result_bad)) {
			if (strtolower  ($username) ==strtolower  ($row_bad['badword'])){
				$bad_found=1;
			}
		}
		if ($bad_found==1){
				show_error("BADWORD_FOUND",$modul_name);
				exit();
			}
	}

	/** create an account with all things **/
	$password=password(8);

	$result=$db->query("SELECT * FROM cc".$n."_crand ORDER BY rand()");
	$land=$db->fetch_array($result);

	$x_pos=$land['x'];
	$y_pos=$land['y'];

	$md5_pw=md5($password);
	trace_msg ("User $username registriert (pw:$password)",1);

	$db->query("INSERT INTO cc".$n."_users (username,email,password,register_date) VALUES ('$username','$email','$md5_pw','".time()."')");
	$userid_r=$db->insert_id();
	$db->query("INSERT INTO cc".$n."_countries (res1,res2,res3,res4,userid,lastressources,picid,x,y,size) VALUES ('$op_reg_res1','$op_reg_res2','$op_reg_res3','$op_reg_res4','$userid_r','".time()."','1','$x_pos','$y_pos','".rand($op_min_c_size,$op_max_c_size)."')");
	$islandid_r=$db->insert_id();

	$db->query("UPDATE cc".$n."_crand SET used='1' WHERE x='".$x_pos."' AND y='".$y_pos."'");

	$db->query("UPDATE cc".$n."_users SET activeid='$islandid_r' WHERE userid='$userid_r'");




	send_register_mail($email,"mail_register.html",$modul_name,$username,$password,$x_pos,$y_pos);

	show_error("REGISTER_SUBMIT_OK",$modul_name);


	exit();

}

if($action=="forgott") {
	template_out('register_forgott.html',$modul_name);
	exit();
}

if($action=="submit_forgott") {
	$username=c_trim(strtolower($_POST['username']));

	$email=c_trim(strtolower($_POST['email']));

	if($username =="") {
		show_error("REGISTER_ERROR_2",$modul_name);
		exit();
	}

	if (!preg_match ("/^[0-9a-z_-]{3,15}$/i", $username)) {
		show_error("REGISTER_ERROR_4",$modul_name);
		exit();
	}

	if($email == "") {
		show_error("REGISTER_ERROR_2",$modul_name);
		exit();
	}

	$result=$db->query("SELECT username,email,password FROM cc".$n."_users WHERE username='".$username."'");
	$row=$db->fetch_array($result);

	if($row['username']!=$username) {
		show_error("LN_NOTE_REGISTER_FOROTT_2",$modul_name);

		exit();
	}

	if($row['email'] != $email) {
		show_error("LN_NOTE_REGISTER_FOROTT_3",$modul_name);
		exit();
	}

	$new_password = password(8);
	$md5_pw=md5($new_password);

	$db->query("UPDATE cc".$n."_users SET password='$md5_pw' WHERE username='".$username."'");


	send_register_mail($email,"mail_register_forgott.html",$modul_name,$username,$new_password,0,0);
	show_error("LN_NOTE_REGISTER_FOROTT_4",$modul_name);
	exit();
}


?>
