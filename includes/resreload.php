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

$time_start = explode(' ', substr(microtime(), 1));
$time_start = $time_start[1] + $time_start[0];

$serverid=0;

require("./config.php");

require("./class_db_mysql.php");

require("./../options/options.php");
require("./functions.php");

if (intval($op_res_reload_type==0)){
	return;
}


$db=new db($dbhost,$dbuser,$dbpassword,$dbbase);
$n=1;


$time_start=time();
trace_msg("resreload update start:".date("d.m.Y (H:i:s)",time()),888);


$result=$db->query("SELECT * FROM cc".$n."_countries");
while($row=$db->fetch_array($result)) {



	$store_max = $op_set_store_max * (($row['store'] + 1) * $op_store_mulit);
	$numOfRessources=1;

	$SetRes1 = $row['res1']+(($op_set_res1 + ($row['res1mine'] * $op_mup_res1)) * $numOfRessources);
	$SetRes2 = $row['res2']+(($op_set_res2 + ($row['res2mine'] * $op_mup_res2)) * $numOfRessources);
	$SetRes3 = $row['res3']+(($op_set_res3 + ($row['res3mine'] * $op_mup_res3)) * $numOfRessources);
	$SetRes4 = $row['res4']+(($op_set_res4 + ($row['res4mine'] * $op_mup_res4)) * $numOfRessources);

	if ($SetRes1 > $store_max) {
		$SetRes1 = $store_max;
	}
	if ($SetRes2 > $store_max) {
		$SetRes2 = $store_max ;
	}
	if ($SetRes3 > $store_max) {
		$SetRes3 = $store_max ;
	}
	if ($SetRes4 > $store_max) {
		$SetRes4 = $store_max ;
	}

	$tr_msg="crontab country_id: ".$row['islandid'] ."  res1:$SetRes1 res2:$SetRes2 res3:$SetRes3 res4:$SetRes4 storemax:$store_max";
	trace_msg($tr_msg, 77);
	print ("$tr_msg <br>");
	$db->query("UPDATE cc" . $n . "_countries SET res1='$SetRes1', res2='$SetRes2', res3='$SetRes3', res4='$SetRes4', lastressources='" . time() . "' WHERE islandid='" . $row['islandid'] . "'");


}
$time_end = explode(' ',substr(microtime(),1));
$time_end  = $time_end[1]+$time_end[0];
$run_time = $time_end-$time_start;
$end_msg= "ResourceReload DONE  time: ".number_format($run_time,5,'.','')." sec. ";

Trace_msg("$end_msg",888);
print ("$end_msg");

?>
