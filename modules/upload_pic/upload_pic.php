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
$modul_name="upload_pic";
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

function auto_generate_thumbs_ali($pic_name)
{
	global $db,$n, $userdata;

	$mode=1;





	$uid=$userdata['userid'];
	$time=time();
	$filename=$pic_name;

	$filename_small=LITO_ROOT_PATH."alli_flag/alli_".$uid."_".$time."_image.jpg";
	$filename_small_save=LITO_ROOT_PATH_URL."alli_flag/alli_".$uid."_".$time."_image.jpg";
	$size=getimagesize($filename);
	$breite=$size[0];
	$hoehe=$size[1];
	$pic_type= $size[2];

	if ($pic_type!=2)
	{
		$filename=LITO_IMG_PATH_URL."upload_pic/error.jpg";
		$size=getimagesize($filename);
		$breite=$size[0];
		$hoehe=$size[1];
		$pic_type= $size[2];

	}

	$thumb_w=$breite;
	$thumb_h=$hoehe;


	$bgimg	= imagecreatefromjpeg($filename);


	// funtion zum scalieren
	$orig_w = ImageSX ($bgimg);
	$orig_h = ImageSY ($bgimg);
	if ($mode==1){
		$wmax=468;
		$hmax=60;
	}elseif ($mode==2){
		$wmax=468;
		$hmax=60;
	}


	if ($wmax || $hmax){
		if ($orig_w>$wmax || $orig_h>$hmax)
		{
			$thumb_w=$wmax;
			$thumb_h=$hmax;
			if ($thumb_w/$orig_w*$orig_h>$thumb_h)
			$thumb_w=round($thumb_h*$orig_w/$orig_h);
			else
			$thumb_h=round($thumb_w*$orig_h/$orig_w);
		} else
		{
			$thumb_w=$orig_w;
			$thumb_h=$orig_h;
		}
	}else
	{
		$thumb_w=$orig_w;
		$thumb_h=$orig_h;
	}

	$image	= imagecreatetruecolor(468, 60);

	$blk	= imagecolorallocate($image, 0, 0, 0);
	$wht	= imagecolorallocate($image, 255, 255, 255);
	$red	= imagecolorallocate($image, 255, 0, 0);
	$blue = imagecolorallocate($image, 0, 0, 255);
	$bgcol="000000";


	imagefilledrectangle($image,0,0,$wmax-1,$hmax-1,intval($bgcol,16));

	if ($thumb_w !=  $orig_w){
		$rt=(468/2)-($thumb_w /2);

		imagecopyresampled($image,$bgimg,$rt,0,0,0,$thumb_w,$thumb_h,$orig_w,$orig_h);
	}else{

	imagecopyresampled($image,$bgimg,0,0,0,0,$thumb_w,$thumb_h,$orig_w,$orig_h);
}
$ali_id=$userdata['allianzid'];
$db->unbuffered_query("update cc".$n."_allianz   set  image_path ='$filename_small', imageurl='$filename_small_save' where  aid  = '$ali_id'");
imagejpeg($image, $filename_small, 100);
imagedestroy($image);
imagedestroy($bgimg);

}

function auto_generate_thumbs($pic_name)
{
	global $db,$n, $userdata;

	$mode=1;

	$uid=$userdata['userid'];
	$time=time();
	$filename=$pic_name;
	$filename_small=LITO_ROOT_PATH."image_user/".$uid."_".$time."_image.jpg";
	$filename_small_URL=LITO_ROOT_PATH_URL."image_user/".$uid."_".$time."_image.jpg";
	$size=getimagesize($filename);
	$breite=$size[0];
	$hoehe=$size[1];
	$pic_type= $size[2];

	if ($pic_type!=2)
	{

		$filename=LITO_IMG_PATH_URL."upload_pic/error.jpg";
		$size=getimagesize($filename);
		$breite=$size[0];
		$hoehe=$size[1];
		$pic_type= $size[2];

	}

	$thumb_w=$breite;
	$thumb_h=$hoehe;
	$bgimg	= imagecreatefromjpeg($filename);
	$orig_w = ImageSX ($bgimg);
	$orig_h = ImageSY ($bgimg);
	if ($mode==1){
		$wmax=100;
		$hmax=100;
	}elseif ($mode==2){
		$wmax=200;
		$hmax=200;
	}


	if ($wmax || $hmax){
		if ($orig_w>$wmax || $orig_h>$hmax)
		{
			$thumb_w=$wmax;
			$thumb_h=$hmax;
			if ($thumb_w/$orig_w*$orig_h>$thumb_h)
			$thumb_w=round($thumb_h*$orig_w/$orig_h);
			else
			$thumb_h=round($thumb_w*$orig_h/$orig_w);
		} else
		{
			$thumb_w=$orig_w;
			$thumb_h=$orig_h;
		}
	}else
	{
		$thumb_w=$orig_w;
		$thumb_h=$orig_h;
	}

	$image	= imagecreatetruecolor($thumb_w, $thumb_h);

	$blk	= imagecolorallocate($image, 0, 0, 0);
	$wht	= imagecolorallocate($image, 255, 255, 255);
	$red	= imagecolorallocate($image, 255, 0, 0);
	$blue = imagecolorallocate($image, 0, 0, 255);
	$bgcol="FF0000";

	imagefilledrectangle($image,0,0,$wmax-1,$hmax-1,intval($bgcol,16));
	imagecopyresampled($image,$bgimg,0,0,0,0,$thumb_w,$thumb_h,$orig_w,$orig_h);


	$db->unbuffered_query("update cc".$n."_users set userpic='$filename_small_URL' where  userid = '$uid'");
	imagejpeg($image, $filename_small, 100);
	imagedestroy($image);
	imagedestroy($bgimg);

}

if($action=="main") {
	if ($userdata['userpic']==""){
		$userpic=LITO_IMG_PATH_URL."members/no_user_pic.jpg";
	}else{
	$userpic=$userdata['userpic'];
}
$tpl->assign('USER_USERIMAGE', $userpic);
template_out('pic_uploads.html',$modul_name);
exit();


}

if($action=="img_upload") {


	$uid=$userdata['userid'];
	$filename = "";
	$path = LITO_ROOT_PATH."images_tmp/";
	$banner="";
	$time=time();


	if ($_FILES['userfile']['tmp_name']<> 'none'){
		$file = $_FILES['userfile']['name'];
		if(substr($file, strlen($file)-5) == ".jpeg" OR substr($file, strlen($file)-4) == ".jpg" OR substr($file, strlen($file)-4) == ".JPG" ) {
			$filext="ok";
		}		else{
		$filext="nok";
	}


	if ($filext=="nok"){

		show_error('PIC_UPLOAD_ERROR_1',$modul_name);
		exit();
	}

	$temp = $_FILES['userfile']['tmp_name'];

	$path_parts = pathinfo($file);

	$filename = "gal_".$uid."." . $path_parts["extension"];
	$dest = $path.$filename;



	if ($temp!="") {
		copy($temp, $dest);
		$up_date=time();
		auto_generate_thumbs($dest);

	}else{

	show_error('PIC_UPLOAD_ERROR_2',$modul_name);
	exit();
}
header("LOCATION: ".LITO_ROOT_PATH_URL."/modules/members/members.php?action=edituserdata");
exit();

}
}
if($action=="img_upload_ali") {


	$uid=$userdata['userid'];
	$filename = "";
	$path = LITO_ROOT_PATH."images_tmp/";





	$time=time();
	if ($_FILES['userfile']['tmp_name']<> 'none'){
		$file = $_FILES['userfile']['name'];
		if(substr($file, strlen($file)-5) == ".jpeg" OR substr($file, strlen($file)-4) == ".jpg" OR substr($file, strlen($file)-4) == ".JPG" ) {
			$filext="ok";
		}		else{
		$filext="nok";
	}
	if ($filext=="nok"){

		show_error('PIC_UPLOAD_ERROR_1',$modul_name);
		exit();
	}

	$temp = $_FILES['userfile']['tmp_name'];
	$path_parts = pathinfo($file);
	$filename = "gal_".$uid."." . $path_parts["extension"];
	$dest = $path.$filename;

	if ($temp!="") {
		copy($temp, $dest);
		$up_date=time();
		auto_generate_thumbs_ali($dest);

	}else{
	show_error('PIC_UPLOAD_ERROR_2',$modul_name);
	exit();
}
header("LOCATION: ./../alliance/alliance.php?action=change_ali_text");
exit();

}
}

if($action=="del_pic") {
	$uid=$userdata['userid'];
	$filename=$userdata['userpic'];

	unlink($filename);

	$db->unbuffered_query("update cc".$n."_users set userpic='' where  userid = '$uid'");

	header("LOCATION: upload_pic.php");
	exit();
}


if($action=="alibild") {

	$u_pic=$userdata['userpic'];

	template_out('pic_uploads_ali.html',$modul_name);
	exit();


}

if($action=="del_pic_ali") {
	$uid=$userdata['userid'];
	$ali_id=$userdata['allianzid'];


	$result_e=$db->query("SELECT * FROM cc".$n."_allianz WHERE aid ='".$ali_id."' ");
	while($row_e=$db->fetch_array($result_e)) {

		$allianz_i_url=$row_e['image_path'];
		if ($allianz_i_url !=""){
			unlink($allianz_i_url );
		}
	}

	$db->unbuffered_query("update cc".$n."_allianz set imageurl='' where  aid= '".$ali_id."'");
	header("LOCATION: ./../alliance/alliance.php?action=change_ali_text");
	exit();
}

?>

