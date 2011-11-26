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

@session_start();
require($_SESSION['litotex_start_acp'].'acp/includes/global.php');

if(!isset($_SESSION['userid'])){
	header("LOCATION: ".$_SESSION['litotex_start_url'].'acp/index.php');
	exit();
}

if(isset($_REQUEST['action'])) $action=$_REQUEST['action'];
else $action="main";

$modul_name="acp_map";
require($_SESSION['litotex_start_acp'].'acp/includes/perm.php');
$menu_name="Karteneditor";
$tpl->assign( 'menu_name',$menu_name);

if($action=="main") {


	$result=$db->query("SELECT max(x) as maximum FROM cc".$n."_crand ");
	$land=$db->fetch_array($result);

	$tpl->assign( 'op_map_size',$land['maximum']);
	template_out('map.html',$modul_name);

}


if($action=="make_map") {
	$size = intval(trim($_POST['x']));
	$elemt_1 = intval(trim($_POST['elem1']));
	$elemt_2 = intval(trim($_POST['elem2']));

	if ($size <= 0)	{
		error_msg("Falsche Angabe der Kartengröße (Eingabe:$size)");
		exit();
	}
	trace_msg("Admin map change drop table",112);
	$sql="DROP TABLE IF EXISTS cc".$n."_crand";
	$update=$db->query($sql);

	trace_msg("Admin map change create table",112);
	$sql="CREATE TABLE cc".$n."_crand (crand_id INT( 11 ) NOT NULL AUTO_INCREMENT ,x INT( 5 ) NOT NULL ,y INT( 5 ) NOT NULL ,used TINYINT( 1 ) NOT NULL DEFAULT '0',element_type INT( 2 ) NOT NULL DEFAULT '0',PRIMARY KEY ( crand_id )) ";
	$update=$db->query($sql);

	for($x=1;$x<= $size;$x++) {
		for($y=1;$y<= $size;$y++) {

			$sql="insert into cc".$n."_crand (x,y,used) VALUES('$x','$y','0')";
			$update=$db->query($sql);
		}
	}
	trace_msg("Admin map change create Elemet1",112);
	// perzufall elemente 1 setzen( berge ?? )
	for($x=1;$x<= $elemt_1;$x++) {
		srand(microtime()*1000000);
		$Zufall_x = rand(1,$size);
		$zufall_y = rand(1,$size);

		$sql="update cc".$n."_crand set element_type ='1',used='1' where x='$Zufall_x' and y='$zufall_y' ";
		$update=$db->query($sql);

	}
	trace_msg("Admin map change create Elemet2",112);
	// perzufall elemente 2 setzen( see ?? )
	for($x=1;$x<= $elemt_2;$x++) {
		srand(microtime()*1000000);
		$Zufall_x = rand(1,$size);
		$zufall_y = rand(1,$size);

		$sql="update cc".$n."_crand set element_type ='2',used='1' where x='$Zufall_x' and y='$zufall_y' ";
		$update=$db->query($sql);

	}


	trace_msg("Admin map change change User Pos",112);
	// umsetzen der länder auf neue koordinaten
	$result_l=$db->query("SELECT * FROM cc".$n."_countries");
	while($row_l=$db->fetch_array($result_l)) {
		$countrie_id= $row_l['islandid'];
		// per zufall verschieben
		srand(microtime()*1000000);
		$Zufall_x = rand(1,$size);
		$zufall_y = rand(1,$size);

		$gefunden=0;
		while($gefunden == 0) {
			$result=$db->query("SELECT * FROM cc".$n."_crand where used = '0' and element_type ='0' and x=$Zufall_x and y=$zufall_y");
			$land=$db->fetch_array($result);


			$land_x=$land['x'];
			$land_y=$land['y'];
			if ($land['used'] == '0' ){
				$gefunden=1;
			}else {$gefunden=0;}

		}
		$landkoord="$land_x:$land_y";
		trace_msg("Admin map change change User Pos county:$countrie_id new pos -> $land_x:$land_y" ,112);
		$sql="update cc".$n."_countries set x='$Zufall_x',y='$zufall_y' where islandid='$countrie_id' ";
		$update=$db->query($sql);
		$sql="update cc".$n."_crand set used ='1' where x='$Zufall_x' and y='$zufall_y' ";
		$update=$db->query($sql);
	}


	trace_msg("Admin map change write Options",112);
	$sql="update cc".$n."_menu_admin_opt set value ='$size' where varname='op_map_size'";
	$update=$db->query($sql);
	require(LITO_ROOT_PATH."includes/class_options.php");
	$option=new option(LITO_ROOT_PATH."options/");
	$option->write();

	header("LOCATION: map.php");

	exit();
}

?>
