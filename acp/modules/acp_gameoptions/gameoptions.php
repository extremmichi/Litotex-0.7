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

$modul_name="acp_gameoptions";

require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');

if($action=="main") {

	$result=$db->query("SELECT admin_sub_name ,admin_sub_id  FROM cc".$n."_menu_admin_sub where admin_sub_id ='10'");
	$row=$db->fetch_array($result);
	$menu_name=$row['admin_sub_name'];

	$result=$db->query("SELECT count( op_id ) AS anz FROM cc".$n."_menu_admin_opt");
	$row=$db->fetch_array($result);
	$variablen_count=$row['anz'];


	$sql="SELECT * from  cc".$n."_menu_admin_sub where menu_admin_id 	 = '6' and sub_name_sort >'1' ";
	$result_users=$db->query($sql);


	$como="<select class=\"combo\" name=\"selkat\">";
	while($row_g=$db->fetch_array($result_users)) {
		$como.="<option value=\"".$row_g['admin_sub_id']."\">".$row_g['admin_sub_name']."</option>";
	}
	$como.="</select>";
	$tpl->assign( 'menu_name',$menu_name);
	$tpl->assign('var_count', $variablen_count);
	$tpl->assign('combo_cat', $como);
	template_out('gameoptions.html',$modul_name);
	exit();
}

if($action=="new_save") {

	$new_var_name=c_trim($_POST['varname']);
	$new_var_titel=c_trim($_POST['vartitel']);
	$new_var_type=c_trim($_POST['select_type']);
	$new_var_kat=intval($_POST['selkat']);

	$first_char = substr($new_var_name, 0, 3);

	if ($first_char  <> "op_"){
		error_msg ("Es ist ein Fehler aufgetrten.<br>Bitte darauf achten, das alle Variablen mit 'op_' beginnen !!! ");
		exit;
	}

	if ($new_var_name =="" or $new_var_titel =="" or $new_var_type=="" or $new_var_kat <= 0 ){
		error_msg ("Es ist ein Fehler aufgetrten.");
		exit();
	}
	$db->unbuffered_query("INSERT INTO cc".$n."_menu_admin_opt SET varname='$new_var_name' , title='$new_var_titel',type='$new_var_type', admin_sub_id='$new_var_kat',  	save='1'");


	template_out('gameoptions.html',$modul_name);
	exit();

}

if($action=="sel_cat") {
	$menu_number=intval($_GET['id']);
	if ($menu_number <= 0){
		header("LOCATION: ".LITO_MODUL_PATH_URL.'gameoptions/gameoptions.php');
		exit();
	}

	$result=$db->query("SELECT admin_sub_name ,admin_sub_id  FROM cc".$n."_menu_admin_sub where admin_sub_id ='".$menu_number."'");
	$row=$db->fetch_array($result);
	$menu_name=$row['admin_sub_name'];


	$result=$db->query("SELECT * FROM cc".$n."_menu_admin_opt WHERE invisable=0 AND admin_sub_id='".$menu_number."'");
	$anz_found=$db->num_rows($result);
	if  ($anz_found <= 0 ){
		error_msg ("Keine Daten vorhanden.");
		exit();
	}

	while($row=$db->fetch_array($result)) {
		if($row['type'] == "truefalse") $type = "Ja <input class=\"radio\" type=\"radio\" name=\"".$row['varname']."\" value=\"1\"".(($row['value']==1) ? (" checked") : (""))."> Nein <input type=\"radio\" name=\"".$row['varname']."\" value=\"0\"".(($row['value']==0) ? (" checked") : ("")).">";
		elseif($row['type'] == "text") $type = "<input type=\"text\" class=\"textinput\" value=\"".$row['value']."\" name=\"".$row['varname']."\" size=\"55\">";
		elseif($row['type'] == "textarea") $type = "<textarea rows=\"10\" id=\"".$row['varname']."\"  class=\"textarea\" cols=\"50\" name=\"".$row['varname']."\">".$row['value']."</textarea>";

		else $type = "unbekannter Typ";
		$all="";

		$option_bit[]=$row['title'] ;
		$option_bit[]=$type;


	}


	$tpl->assign( 'menu_name',$menu_name);
	$tpl->assign( 'save_id',$menu_number);
	$tpl->assign( 'data', $option_bit);
	$tpl->assign( 'tr', array('bgcolor="#eeeeee"','bgcolor="#dddddd"'));
	template_out('gameoptions_save.html',$modul_name);
}

if($action=="submitOptions") {
	$optiongroupid=intval($_REQUEST['id']);

	$result=$db->query("SELECT * FROM cc".$n."_menu_admin_opt where admin_sub_id='$optiongroupid' ");


	while($row=$db->fetch_array($result)) {
		$varname=$_POST[$row['varname']];


		$db->query("UPDATE cc".$n."_menu_admin_opt SET value='".$varname."' WHERE varname='".$row['varname']."' AND admin_sub_id='".$optiongroupid."'");
	}


	require(LITO_ROOT_PATH."includes/class_options.php");
	$option=new option(LITO_ROOT_PATH."options/");
	$option->write();

	header("LOCATION: ".LITO_MODUL_PATH_URL.'acp_gameoptions/gameoptions.php?action=sel_cat&id='.$optiongroupid);
	exit();

}



?>
