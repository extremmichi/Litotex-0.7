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


function make_signature($id){
	global $db,$tpl,$userdata,$n;
	$modul_name="usr_signature";



	if (is_modul_name_aktive($modul_name)==0){
		return;
	}


	$font = LITO_IMG_PATH.$modul_name."/verdanab.ttf";
	$font1 = LITO_IMG_PATH.$modul_name."/tahoma.ttf";
	$img_template = LITO_IMG_PATH.$modul_name."/sig_vor.png";

	$time_start = explode(' ', substr(microtime(), 1));
	$time_start = $time_start[1] + $time_start[0];
	$img_count=0;
	if ($id == 0 ){
		$result=$db->query("SELECT * FROM cc".$n."_users where lastlogin >'0' and serveradmin != '1'");
	}else{
	$result=$db->query("SELECT * FROM cc".$n."_users where lastlogin >'0' and serveradmin != '1' and userid='$id'");
}

while($sigdata=$db->fetch_array($result)) {
	$img_count++;
	$sig_user_id=$sigdata['userid'];
	$sig_username=$sigdata['username'];
	$sig_points=$sigdata['points'];
	$sig_ali_name="keine";
	$ali_id=intval($sigdata['allianzid']);
	$ali_point_count=0;
	if ($ali_id > 0 ){
		$sig_ali_name=allianz(intval($sigdata['allianzid']));
		$flag_filename_flag=LITO_ROOT_PATH."alli_flag/flag_".$ali_id.".png";
		$flag_filename_flag_url=LITO_ROOT_PATH_URL."alli_flag/flag_".$ali_id.".png";
		$ali_point_count=get_allianz_points($ali_id);
	}



	$sig_country_count=0;
	
	$signatur = imagecreatefrompng($img_template);
	imagecolorallocate ($signatur, 0, 0, 0);
	$textfarbe = ImageColorAllocate ($signatur, 255, 255, 255);
	$x1=12;
	$y1=20;
	$x2=130;
	$y2=20;
	$x3=12;
	$y3=50;
	$x4=130;
	$y4=50;
	$x5=310;
	$y5=55;

	imagettftext( $signatur, 10, 0, $x1, $y1, $textfarbe, $font, urldecode($sig_username) ); //Spielername
	imagettftext( $signatur, 10, 0, $x2, $y2, $textfarbe, $font, $sig_points); //Spielerpunkte
	imagettftext( $signatur, 10, 0, $x3, $y3, $textfarbe, $font, urldecode($sig_ali_name) ); //Allyname
	imagettftext( $signatur, 10, 0, $x4, $y4, $textfarbe, $font, $ali_point_count); //Allypunkte
	imagettftext( $signatur, 8, 0, $x5, $y5, $textfarbe, $font, $op_set_game_url); //L�nder

	if ($ali_id > 0 ){
		if (is_file($flag_filename_flag)){

			$src = imagecreatefrompng($flag_filename_flag);
			imagecopy($signatur, $src, 430, 3, 0, 0, 15, 10);
		}
	}

	$save_f_name=LITO_ROOT_PATH."images_sig/game_sig_".$sig_user_id.".png";
	if (is_file($save_f_name)){
		unlink($save_f_name);
	}

	ImagePNG ($signatur,$save_f_name,0);
}

$time_end = explode(' ',substr(microtime(),1));
$time_end  = $time_end[1]+$time_end[0];
$run_time = $time_end-$time_start;
$end_msg= "Signature DONE  time: ".number_format($run_time,5,'.','')." sec. $img_count operation";
Trace_msg("$end_msg",777);


}





?>

