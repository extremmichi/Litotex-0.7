<?PHP /* ************************************************************ Litotex
Browsergame - Engine http://www.Litotex.de http://www.freebg.de

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
$modul_name="techtree";
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
	$race_id = $userdata['rassenid'];
	if (isset($_GET['type'])){
		$techtree_type=$_GET['type'];
	}else{
	$techtree_type=0;
}



function get_name_from_explore ($explore_name,$race_id){
	global $db,$n;
	$result=$db->query("SELECT name FROM cc".$n."_explore WHERE tabless='$explore_name' and race='$race_id'");
	$row=$db->fetch_array($result);
	return  $row['name'];
}



if ($techtree_type == 0 ){

	$result = $db->query( "SELECT * FROM `cc".$n."_buildings`WHERE `race` = '".$race_id ."' ORDER BY p,`race`, `require1`, `require2`" );
	$i=0;
	while( ( $row = $db->fetch_array( $result ) ) ) {
		$name	= $row['name'] ;
		$single=$row['tabless'] ;
		$image	= $row['buildpic'];
		$req1	= ( intVal( $row['require1'] ) > 0 ? intVal( $row['require1'] ) : '0' );
		$req2	= ( intVal( $row['require2'] ) > 0 ? intVal( $row['require2'] ) : '0' );

		$inhalt[$i]['name']=$name	." (" .$userdata[$single].")";
		$inhalt[$i]['image']=$image;
		$inhalt[$i]['req1']=get_buildings_name('build_town',$userdata['race'])." ".$req1;
		$inhalt[$i]['req1'].="<br>".get_buildings_name('build_explore',$userdata['race'])." ".$req2;
		$inhalt[$i]['req2']=$userdata['build_town']."<br>".$userdata['build_explore'];
		$inhalt[$i]['description']=$row['description'];
		$i++;
	}



} else if( $techtree_type== "1" ) {

	$i=0;
	$result = $db->query( "SELECT * FROM `cc".$n."_soldiers` WHERE `race` = '".$race_id ."' ORDER BY `race`, `required`, `required_level`" );
	while( ( $row = $db->fetch_array( $result ) ) ) {

		$name	= $row['name'] ;
		$single=$row['tabless'] ;
		$image	= $row['solpic'];
		$req1	= $row['required'] ;
		$req_lv	= intVal( $row['required_level'] );

		$inhalt[$i]['name']=$name	." (" .$userdata[$single].")";
		$inhalt[$i]['description']=$row['description'];
		$inhalt[$i]['image']=$image;
		$inhalt[$i]['req1']=get_name_from_explore ($req1,$race_id) ." ( ". $req_lv . ")";
		$inhalt[$i]['req2']=$userdata[$req1];


		$i++;
	}



} else if( $techtree_type== "2" ) {
	/* Build the Tabless Names */
	$Tabless = array();
	$i=0;
	$result = $db->query( "SELECT * FROM `cc".$n."_explore` WHERE race=  '".$race_id ."'" );
	while( $row = $db->fetch_array( $result ) ){
		$single=$row['tabless'];
		$name	= $row['name'];
		$image	= $row['explorePic'];
		$req	= $row['required'];
		$req_lv	= $userdata[$single];
		$b_name=get_buildings_name('build_explore',$userdata['race']);
		$inhalt[$i]['name']=$name	." (" .$userdata[$single].")";
		$inhalt[$i]['image']=$image;
		$inhalt[$i]['req1']=$b_name." " .$req;
		$inhalt[$i]['req2']=$userdata['build_explore'];
		$inhalt[$i]['description']=$row['description'];
		$i++;

	}

}


$tpl->assign('daten', $inhalt);
template_out('techtree.html',$modul_name);
exit();
}

?>
