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
$modul_name="map";
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


	$x=$userdata['x'];
	$y=$userdata['y'];

	if(isset($_GET['x'])) {
		$x=intval($_GET['x']);
	}

	if(isset($_GET['y'])) {
		$y=intval($_GET['y']);
	}


	if(isset($_POST['x_pos'])) {
		$x=intval($_POST['x_pos']);
	}

	if(isset($_POST['y_pos'])) {
		$y=intval($_POST['y_pos']);
	}


	$usr_x=$x;
	$usr_y=$y;

	$max_x=$x+2;
	$max_y=$y+2;

	$min_x=$x-2;
	$min_y=$y-2;


	$navi_oben="<a href=\"javascript:go_map(-1,0)\"><img border= \"0\" src=\"".LITO_IMG_PATH_URL."map/pfeil_up.png\" width=\"50\" height=\"50\" /></a>";
	$navi_rechts="<a href=\"javascript:go_map(0,1)\"><img border=\"0\" src=\"".LITO_IMG_PATH_URL."map/pfeil_right.png\" width=\"50\" height=\"50\" /></a>";
	$navi_unten="<a href=\"javascript:go_map(1,0)\"><img border=0 src=\"".LITO_IMG_PATH_URL."map/pfeil_down.png\" width=\"50\" height=\"50\" /></a>";
	$navi_links="<a href=\"javascript:go_map(0,-1)\"><img border=\"0\" src=\"".LITO_IMG_PATH_URL."map/pfeil_left.png\" width=\"50\" height=\"50\" /></a>";



	$where="";
	$x_count=0;
	$y_count=0;
	$land=array();
	for($starty=$min_y;$starty <= $max_y;$starty++) {
		$y_count=0;
		for($startx=$min_x;$startx <= $max_x;$startx++) {
			$land[$startx][$starty]="<div class='land_0'>$startx:$starty</div>";
			if ($startx <= 0 || $starty<= 0){

			}else{

		}

		if ($where==""){
			$where="(x='$startx' and y='$starty')";
		}else{
		$where.=" OR (x='$startx' and y='$starty')";
	}


	$y_count++;
}
$x_count++;
}

$sql="SELECT userid,name,size,usesize,islandid,x,y,picid,name FROM cc".$n."_countries where $where";

$result=$db->query($sql);
while($row=$db->fetch_array($result)) {
	$x=$row['x'];
	$y=$row['y'];

	$cur_name=username($row['userid']);
	$land_name=$row['name'];
	$land_size=$row['size'];
	$land_cursize=$row['usesize'];
	$tt="onmouseover=\"Tip('Landname:$land_name<br>Besitzer:$cur_name<br>Größe:$land_size<br>Bebaut:$land_cursize',TITLE,'Info ',FADEIN,400,SHADOW, true,OPACITY, 100,BGCOLOR,'#D3E3F6',BORDERWIDTH,'0',PADDING, 0)\" onmouseout=\"UnTip()\"";
	if ($row['picid']==1) {
		$land[$x][$y]="<div class='land_1' $tt>$x:$y</div>";
	}elseif ($row['picid']==2) {
		$land[$x][$y]="<div class='land_2' $tt>$x:$y</div>";
	} elseif ($row['picid']==3) {
		$land[$x][$y]="<div class='land_3' $tt>$x:$y</div>";

	} elseif ($row['picid']==4) {
		$land[$x][$y]="<div class='land_4' $tt>$x:$y</div>";

	}elseif ($row['picid']==0) {
		$land[$x][$y]="<div class='land_1' $tt>$x:$y</div>";

	}
}



$sql="SELECT * FROM cc".$n."_crand where (element_type <> '0') and ($where)";
$result=$db->query($sql);
while($row=$db->fetch_array($result)) {
	$element=$row['element_type'];
	$x=$row['x'];
	$y=$row['y'];


	if ($element==1){
		$tt="onmouseover=\"Tip('Seegebiet',PADDING, 2,BGCOLOR,'#DDDDDD',TEXTALIGN,'center',FADEIN,400,SHADOW, true,OPACITY, 100,FONTCOLOR,'#000000')\" onmouseout=\"UnTip()\"";
		$land[$x][$y]="<div class='land_f' $tt >$x:$y </div>";
	}elseif($element==2){
		$tt="onmouseover=\"Tip('Berge',PADDING, 2,BGCOLOR,'#DDDDDD',TEXTALIGN,'center',FADEIN,400,SHADOW, true,OPACITY, 100,FONTCOLOR,'#000000')\" onmouseout=\"UnTip()\"";
		$land[$x][$y]="<div class='land_b' $tt>$x:$y</div>";
	}

}


$return_div="";
foreach($land as $key1 => $value1) {
	foreach($value1 as $key2 => $value2) {
		$return_div.=$land[$key1][$key2];
	}
}

$tpl->assign('usr_x', $usr_x);
$tpl->assign('usr_y', $usr_y);

$tpl->assign('return_div', $return_div);
$tpl->assign('navi_oben', $navi_oben);
$tpl->assign('navi_links',$navi_links);
$tpl->assign('navi_rechts', $navi_rechts);
$tpl->assign('navi_unten', $navi_unten);

template_out('map.html',$modul_name);
exit();
}

if($action=="goto") {


	$x=intval($_GET['x']);
	$y=intval($_GET['y']);


	$max_x=$x+2;
	$max_y=$y+2;

	$min_x=$x-2;
	$min_y=$y-2;



	$navi_links="<a href=\"javascript:go_map(-1,0)\">links</a>";


	$where="";
	$x_count=0;
	$y_count=0;

	$land=array();
	for($starty=$min_y;$starty <= $max_y;$starty++) {
		$y_count=0;
		for($startx=$min_x;$startx <= $max_x;$startx++) {
			$land[$startx][$starty]="<div class='land_0'>$startx:$starty</div>";
			if ($startx <= 0 || $starty<= 0){

			}else{

		}

		if ($where==""){
			$where="(x='$startx' and y='$starty')";
		}else{
		$where.=" OR (x='$startx' and y='$starty')";
	}


	$y_count++;
}
$x_count++;
}

$sql="SELECT islandid,x,y,picid,name,userid,size,usesize FROM cc".$n."_countries where $where";

$result=$db->query($sql);
while($row=$db->fetch_array($result)) {
	$x=$row['x'];
	$y=$row['y'];
	$cur_name=username($row['userid']);
	$land_name=$row['name'];
	$land_size=$row['size'];
	$land_cursize=$row['usesize'];
	$tt="onmouseover=\"Tip('Landname:$land_name<br>Besitzer:$cur_name<br>Größe:$land_size<br>Bebaut:$land_cursize',TITLE,'Info ',BGCOLOR,'#D3E3F6',BORDERWIDTH,'0',FADEIN,400,SHADOW, true,OPACITY, 100,PADDING, 0)\" onmouseout=\"UnTip()\"";

	if ($row['picid']==1) {
		$land[$x][$y]="<div class='land_1' $tt>$x:$y</div>";
	}elseif ($row['picid']==2) {
		$land[$x][$y]="<div class='land_2' $tt>$x:$y</div>";
	} elseif ($row['picid']==3) {
		$land[$x][$y]="<div class='land_3' $tt>$x:$y</div>";

	} elseif ($row['picid']==4) {
		$land[$x][$y]="<div class='land_4' $tt>$x:$y</div>";

	}elseif ($row['picid']==0) {
		$land[$x][$y]="<div class='land_1' $tt>$x:$y</div>";

	}

}

$sql="SELECT * FROM cc".$n."_crand where (element_type <> '0') and ($where)";
$result=$db->query($sql);
while($row=$db->fetch_array($result)) {
	$element=$row['element_type'];
	$x=$row['x'];
	$y=$row['y'];

	if ($element==1){
		$tt="onmouseover=\"Tip('Seegebiet',PADDING, 2,BGCOLOR,'#DDDDDD',TEXTALIGN,'center',FADEIN,400,SHADOW, true,OPACITY, 80,FONTCOLOR,'#000000')\" onmouseout=\"UnTip()\"";
		$land[$x][$y]="<div class='land_f' $tt>$x:$y</div>";
	}elseif($element==2){
		$tt="onmouseover=\"Tip('Berge',PADDING, 2,BGCOLOR,'#DDDDDD',TEXTALIGN,'center',FADEIN,400,SHADOW, true,OPACITY, 80,FONTCOLOR,'#000000')\" onmouseout=\"UnTip()\"";
		$land[$x][$y]="<div class='land_b' $tt>$x:$y</div>";
	}

}
$return_div="";
foreach($land as $key1 => $value1) {
	foreach($value1 as $key2 => $value2) {
		$return_div.=$land[$key1][$key2];
	}
}

echo($return_div);
exit();
}



?>