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

/** error_page function show an error by text **/

function error_msg($message) {
	global $tpl;
	$tpl->assign('LITO_ERROR',$message);
	template_out("error.html","acp_core");
}



function get_footer() {
	global $db,$time_start;
	$time_end = explode(' ',substr(microtime(),1));
	$time_end  = $time_end[1]+$time_end[0];
	$run_time = $time_end-$time_start;
	return "time: ".number_format($run_time,7,'.','')." sec <br/>query count: " .$db->number_of_querys();

}



function c_trim($string) {
	$replace=strip_tags($string);
	$replace=mysql_real_escape_string($replace);
	return trim($replace);
}


// Funtion zum Traces von Informationen
// Diese Sind im Admin bereich sichtbar
// $error_type ist ebenfalls unter dem Admin Programm sichtbar
function Trace_Msg($message,$error_type) {
	global $db,$n,$userdata;

	$message = addslashes($message);
	$db->query("INSERT INTO cc".$n."_debug(db_time, db_text ,db_type ,fromuserid) VALUES ('".time()."','$message','".$error_type."','".$userdata['userid']."')");

	return;
}

function get_navigation(){

	include(LITO_MODUL_PATH.'acp_navigation/navigate.php');

	$navi = new navigation();
	$rrr=$navi->make_navigate();
	return $rrr;
}


function template_out($template_name,$from_modulname){
	global $tpl,$lang_suffix;

	$ret=is_modul_name_aktive($from_modulname);
	if ($ret==0){
		error_msg("Dieses Modul wurde vom Administrator deaktiviert.<br>Module has been disabled by the administrator.");
		exit();
	}

	$lang_file=LITO_LANG_PATH.$from_modulname.'/lang_'.$lang_suffix.'.php';
	$tpl->config_load($lang_file);
	$tpl ->assign('LITO_NAVIGATION', get_navigation());




	$tpl ->assign('LITO_GLOBAL_IMAGE_URL', LITO_GLOBAL_IMAGE_URL);
	$tpl ->assign('LITO_IMG_PATH', LITO_IMG_PATH_URL.$from_modulname."/");
	$tpl ->assign('LITO_MAIN_CSS', LITO_MAIN_CSS);
	$tpl ->assign('GAME_FOOTER_MSG', get_footer());
	$tpl ->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
	$tpl ->assign('LITO_MODUL_PATH_URL', LITO_MODUL_PATH_URL);
	$tpl ->assign('LITO_BASE_MODUL_URL', LITO_MODUL_PATH_URL);


	$tpl ->display($from_modulname."/".$template_name);
}

function is_modul_name_aktive($modul_name) {
	global $db,$n,$userdata;
	$result=$db->query("SELECT activated FROM cc".$n."_modul_admin where modul_name ='$modul_name'");
	$row=$db->fetch_array($result);
	return intval($row['activated']);
}


function is_modul_id_aktive($modul_id) {
	global $db,$n,$userdata;
	$result=$db->query("SELECT activated FROM cc".$n."_modul_admin where modul_admin_id ='$modul_id'");
	$row=$db->fetch_array($result);
	return intval($row['activated']);

}

function is_modul_installed($modul_name,$modul_version){

	global $tpl,$db,$n;
	$sql="SELECT modul_admin_id FROM cc".$n."_modul_admin where modul_name='".mysql_real_escape_string($modul_name)."' and current_version ='".mysql_real_escape_string($modul_version)."'";
	$result=$db->query($sql);
	$row=$db->fetch_array($result);
	return $row['modul_admin_id'];
}

function make_soldier_option_choice($name,$sel_name	){
	global $db,$n;
	global $op_set_n_res1;
	global $op_set_n_res2;
	global $op_set_n_res3;
	global $op_set_n_res4;

	$out="<select name=\"$name\" class=\"combo\">";
	$out.="<option value=\"0\">keine Optionen</option>";
	$result=$db->query("SELECT * FROM cc".$n."_soldiers_option order by s_option_id");
	while($row=$db->fetch_array($result)) {
		$name_description=$row['description'];
		$name_description=str_replace("op_set_n_res1",$op_set_n_res1,$name_description);
		$name_description=str_replace("op_set_n_res2",$op_set_n_res2,$name_description);
		$name_description=str_replace("op_set_n_res3",$op_set_n_res3,$name_description);
		$name_description=str_replace("op_set_n_res4",$op_set_n_res4,$name_description);


		if (trim($sel_name) == trim($row['tabless'])){
			$out.="<option value=\"".$row['tabless']."\" selected>".$name_description."</option>";
		}else{
		$out.="<option value=\"".$row['tabless']."\">".$name_description."</option>";
	}

}
$out.="</select>";
return $out;
}



function make_explore_option_choice($name,$sel_name	){
	global $db,$n;
	global $op_set_n_res1;
	global $op_set_n_res2;
	global $op_set_n_res3;
	global $op_set_n_res4;

	$out="<select name=\"$name\" class=\"combo\">";
	$out.="<option value=\"0\">keine Optionen</option>";
	$result=$db->query("SELECT * FROM cc".$n."_explore_option order by e_option_id");
	while($row=$db->fetch_array($result)) {
		$name_description=$row['description'];
		$name_description=str_replace("op_set_n_res1",$op_set_n_res1,$name_description);
		$name_description=str_replace("op_set_n_res2",$op_set_n_res2,$name_description);
		$name_description=str_replace("op_set_n_res3",$op_set_n_res3,$name_description);
		$name_description=str_replace("op_set_n_res4",$op_set_n_res4,$name_description);


		if (trim($sel_name) == trim($row['tabless'])){
			$out.="<option value=\"".$row['tabless']."\" selected>".$name_description."</option>";
		}else{
		$out.="<option value=\"".$row['tabless']."\">".$name_description."</option>";
	}

}
$out.="</select>";
return $out;
}

function make_soldier_type_choice($name,$sel_type	){
	$out="<select name=\"$name\" class=\"combo\">";
	if ($sel_type==0){
		$out.=" <option value=\"0\" selected>Angriff</option>";
	}else{
	$out.=" <option value=\"0\">Angriff</option>";
}
if ($sel_type==1){
	$out.=" <option value=\"1\" selected>Verteidigung</option>";
}else{
$out.=" <option value=\"1\">Verteidigung</option>";
}
if ($sel_type==2){
	$out.=" <option value=\"2\" selected>Landerweierung</option>";
}else{
$out.=" <option value=\"2\">Landerweierung</option>";
}
if ($sel_type==3){
	$out.=" <option value=\"3\" selected>Spion</option>";
}else{
$out.=" <option value=\"3\">Spion</option>";
}



$out.="</select>";

return $out;

}


function make_race_choice($name,$sel_id	){
	global $db,$n,$userdata,$tpl;

	$out="<select name=\"$name\" class=\"combo\">";
	$out.="<option value=\"0\">nicht festgelegt</option>";
	$result=$db->query("SELECT * FROM cc".$n."_rassen order by rassenid");
	while($row=$db->fetch_array($result)) {

		if (intval($sel_id) == intval($row['rassenid'])){
			$out.="<option value=\"".$row['rassenid']."\" selected>".$row['rassenname']."</option>";
		}else{
		$out.="<option value=\"".$row['rassenid']."\">".$row['rassenname']."</option>";
	}

}
$out.="</select>";
return $out;
}

function get_buildings_tabless_name($name){
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT tabless FROM cc".$n."_buildings_option WHERE tabless='".$name."'");
	$row=$db->fetch_array($result);
	return $row['tabless'];
}
function get_soldiers_tabless_name($name){
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT tabless FROM cc".$n."_soldiers_option WHERE tabless='".$name."'");
	$row=$db->fetch_array($result);
	return $row['tabless'];
}

function get_explore_tabless_name($name){
	global $db,$n,$userdata,$tpl;
	$result=$db->query("SELECT tabless FROM cc".$n."_explore_option WHERE tabless='".$name."'");
	$row=$db->fetch_array($result);
	return $row['tabless'];


}

function make_build_option_choice($name,$sel_name	){
	global $db,$n,$userdata,$tpl;
	global $op_set_n_res1;
	global $op_set_n_res2;
	global $op_set_n_res3;
	global $op_set_n_res4;

	$out="<select name=\"$name\" class=\"combo\">";
	$out.="<option value=\"0\">keine Optionen</option>";
	$result=$db->query("SELECT * FROM cc".$n."_buildings_option order by b_option_id");
	while($row=$db->fetch_array($result)) {
		$name_description=$row['description'];
		$name_description=str_replace("op_set_n_res1",$op_set_n_res1,$name_description);
		$name_description=str_replace("op_set_n_res2",$op_set_n_res2,$name_description);
		$name_description=str_replace("op_set_n_res3",$op_set_n_res3,$name_description);
		$name_description=str_replace("op_set_n_res4",$op_set_n_res4,$name_description);


		if (trim($sel_name) == trim($row['tabless'])){
			$out.="<option value=\"".$row['tabless']."\" selected>".$name_description."</option>";
		}else{
		$out.="<option value=\"".$row['tabless']."\">".$name_description."</option>";
	}

}
$out.="</select>";
return $out;
}

function if_spalte_exist($spaltenname,$tabelnnenname){
	global $db,$n,$userdata,$tpl;
	$Sql="show columns from $tabelnnenname like '$spaltenname'";
	$result=$db->query($Sql);
	$result=$db->num_rows($result);
	return $result;
}

function compare_versions_sinus( $local_version, $remote_version ){
	// Copyright by sinus
	//0 = error
	//1= $local_version < $remote_version
	//2= $local_version = $remote_version
	//3= $local_version > $remote_version
	// Variablen definieren
	$aNewExpL = $aExpL = explode( ".", $local_version );
	$aNewExpR = $aExpR = explode( ".", $remote_version );
	$iExpL = count( $aExpL );
	$iExpR = count( $aExpR );
	$iMax = intval( max( $iExpL, $iExpR ) );

	// Alle Paare durchlaufen, pr�fen und auff�llen
	for( $x=0; $x!=$iMax; $x++ )
	{
		// Lokalen Versionseintrag
		if( isset( $aExpL[ $x ] ) )
		{
			if( !is_numeric( $aExpL[ $x ] ) )
			return 0;
		}
		else
		$aNewExpL[] = 0;
		// Remote Versionseintrag
		if( isset( $aExpR[ $x ] ) )
		{
			if( !is_numeric( $aExpR[ $x ] ) )
			return 0;
		}
		else
		$aNewExpR[] = 0;
	}

	// Versionsvergleich
	if( implode( ".", $aNewExpL ) != implode( ".", $aNewExpR ) )
	{
		for( $x=0; $x!=$iMax; $x++)
		{
			if( $aNewExpL[ $x ] != $aNewExpR[ $x ] )
			{
				if( $aNewExpL[ $x ] > $aNewExpR[ $x ] )
				return 3; // Lokal > Remote
				else
				return 1; // Lokal < Remote
			}
		}
	}
	else
	return 2; // Lokal = Remote
}


?>
