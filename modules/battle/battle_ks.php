<?PHP

function mtime() {

	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);

}

function ntime($stime, $etime) {

	$ntime = $etime-$stime;
	return $ntime;

}

function ks_battle($group_id,$land_id,$fromadmin){
	global $db,$n,$userdata,$tpl,$header,$footer,$op_res_steal,$op_battle_trace;

	include("./battlescript.php");
	$modul_name="battle";
	$stime=mtime();

	$verteidiger_user_id=get_userid_from_countrie($land_id);

	$angreifer_race=get_race_id_from_group($group_id);
	$vert_race=get_race_id_from_countrie($land_id);

	$angreifer_name=$userdata['username'];
	$verteidiger_name=username($verteidiger_user_id);

	$angreifer_land=get_countrie_name_from_group_id($group_id,1);
	$verteidiger_land=get_countrie_name_from_id($land_id,1);

	$angreifer_user_id=get_user_id_from_group_id($group_id);

	trace_msg("angreifer group id:$group_id race id:$angreifer_race",90);
	trace_msg("verteidiger land id:$land_id race id:$vert_race",90);

	$kampfscript = new kampfs();

	$sql="SELECT cc".$n."_groups_inhalt . * , cc".$n."_soldiers . * FROM cc".$n."_groups_inhalt INNER JOIN cc".$n."_soldiers ON cc".$n."_groups_inhalt.type = cc".$n."_soldiers.tabless WHERE cc".$n."_soldiers.race ='".$angreifer_race."' AND cc".$n."_groups_inhalt.group_id ='".$group_id."'";

	$result_g=$db->query($sql);
	$count_ang=0;
	while($row_g=$db->fetch_array($result_g)) {

		$g_id=$row_g['groups_inhalt_id'];
		$AP=$row_g['AP'];
		$VP=$row_g['VP'];
		$count_ang=$row_g['anzahl'];
		$unit_type=$row_g['type'];

		$kampfscript->setunits_angreifer($g_id, $AP*$count_ang, $VP*$count_ang,$AP,$VP,$count_ang,$unit_type);

	}

	$count_vert=0;
	$result_sol_verteidiger=$db->query("SELECT * FROM cc".$n."_soldiers where race='$vert_race'");
	while($row_sol_vert=$db->fetch_array($result_sol_verteidiger)){
		$name=$row_sol_vert['tabless'];
		$id_vert=$row_sol_vert['sid'];
		$AP=$row_sol_vert['AP'];
		$VP=$row_sol_vert['VP'];

		$result_l=$db->query("SELECT $name FROM cc".$n."_countries where islandid='$land_id'");

		$row_l=$db->fetch_array($result_l);
		$anzahl_einheiten=$row_l[$name];
		$count_vert+=$anzahl_einheiten;
		if ($anzahl_einheiten > 0 ){
			$unit_type=$row_sol_vert['tabless'];
			$kampfscript->setunits_verteidiger($id_vert, $AP*$anzahl_einheiten, $VP*$anzahl_einheiten,$AP,$VP,$anzahl_einheiten,$unit_type);

		}
	}
	$all_einheiten_angr_count=0;
	$all_einheiten_vert_count=0;
	$verlust_angreifer=0;
	$verlust_verteidiger=0;


	if (intval($op_battle_trace) > 0){
		$trace_fname=LITO_ROOT_PATH."battle_kr/".$land_id."_".time().".txt";
		$kampfscript->set_trace($trace_fname);
	}
	$kampfscript->calc();
	$all_einheiten_angr_count=$kampfscript->anzahl_angreifer_vor;
	$all_einheiten_vert_count=$kampfscript->anzahl_verteidiger_vor;
	$verlust_angreifer=$kampfscript->anzahl_angreifer- $kampfscript->anzahl_angreifer_end;
	$verlust_verteidiger=$kampfscript->anzahl_verteidiger - $kampfscript->anzahl_verteidiger_end;
	$all_einheiten=$kampfscript->anzahl_angreifer_vor+$kampfscript->anzahl_verteidiger_vor;


	$attack_msg="";
	$einheit_ang="";
	$einheiten_ang_ap=0;
	$einheiten_ang_vp=0;

	foreach($kampfscript->angreifer AS $name => $value){
		$einheiten_name=$kampfscript->angreifer[$name]["unit_type"];
		$einheiten_name_org=$einheiten_name;
		$einheiten_new_count=$kampfscript->angreifer[$name]["new_units_count"];
		$einheiten_anzahl=$kampfscript->angreifer[$name]["unit_count"];
		$einheiten_name=get_soldiers_name($einheiten_name,$angreifer_race);
		$einheit_ang.=$einheiten_name."<br>";
		$einheit_ang_count.=intval($einheiten_anzahl)."<br>";
		$einheiten_ang_ap+=$kampfscript->angreifer[$name]["ap_single"]*$einheiten_anzahl;
		$einheiten_ang_vp+=$kampfscript->angreifer[$name]["vp_single"]*$einheiten_anzahl;

		if ($fromadmin==0){
			$db->query("update cc".$n."_groups_inhalt set anzahl='".$einheiten_new_count."' where group_id='".$group_id."' and type='".$einheiten_name_org."'");
		}
	}

	if ($fromadmin==0){

		$db->query("delete from cc".$n."_groups_inhalt where group_id='".$group_id."' and anzahl <='0'");
		$result_sum_group=$db->query("SELECT sum(anzahl)as all_i_groups FROM cc".$n."_groups_inhalt where group_id='".$group_id."'");
		$rowsum_group=$db->fetch_array($result_sum_group);
		$anz_goup_inhalt=$rowsum_group['all_i_groups'];
		if ($anz_goup_inhalt <= 0 ){
			$db->query("delete from cc".$n."_groups where groupid='".$group_id."'");
		}
	}

	$einheiten_vert_ap=0;
	$einheiten_vert_vp=0;
	if ($count_vert > 0) {
		foreach($kampfscript->verteidiger AS $name => $value){
			$einheiten_name=$kampfscript->verteidiger[$name]["unit_type"];
			$einheiten_name_org=$einheiten_name;
			$einheiten_anzahl=$kampfscript->verteidiger[$name]["unit_count"];
			$einheiten_new_count=$kampfscript->verteidiger[$name]["new_units_count"];
			$einheiten_name=get_soldiers_name($einheiten_name,$vert_race);
			$einheit_vert.=$einheiten_name."<br>";
			$einheit_vert_count.=intval($einheiten_anzahl)."<br>";
			$einheiten_vert_ap+=$kampfscript->verteidiger[$name]["ap_single"]*$einheiten_anzahl;
			$einheiten_vert_vp+=$kampfscript->verteidiger[$name]["vp_single"]*$einheiten_anzahl;
			if ($fromadmin==0){
				$db->query("update cc".$n."_countries set ".$einheiten_name_org." ='".$einheiten_new_count."' where islandid='".$land_id."'");
				$db->query("update cc".$n."_countries set ".$einheiten_name_org." ='0' where islandid='".$land_id."' and ".$einheiten_name_org." <'0'");
			}
		}
	}

	$dauer_berechnung=	sprintf("%.05f",ntime($stime,mtime()));
	$last_msg="Es wurden ".intval($all_einheiten) ." Einheiten in ".$dauer_berechnung." Sekunden berechnet.";

	unset($kampfscript);


	$date=date("d.m.Y H:i:s",time());

	$last_archive_id=0;
	$result=$db->query("SELECT archive_id FROM cc".$n."_battle_archiv order by archive_id  DESC limit 1");
	$row=$db->fetch_array($result);
	$last_archive_id= $row['archive_id'];
	$kr_number=$last_archive_id+1;
	$random=password(5);
	$random1=time();
	$battle_url="battle_id_".$random."_".$random1.".html";
	$db->query("INSERT INTO cc".$n."_battle_archiv(ang_username,ang_land,vert_username,vert_land,battle_time,battle_url) VALUES ('$angreifer_name','$angreifer_land','$verteidiger_name','$verteidiger_land','".time()."','$battle_url')");
	$message_topic="Kampfreport vom : ".date("d.m.Y H:i:s",time()) ;

	$tpl->assign('date', $date);
	$tpl->assign('angreifer_name', $angreifer_name);
	$tpl->assign('angreifer_land', $angreifer_land);
	$tpl->assign('verteidiger_name', $verteidiger_name);
	$tpl->assign('verteidiger_land', $verteidiger_land);
	$tpl->assign('einheit_ang', $einheit_ang);
	$tpl->assign('einheit_ang_count', $einheit_ang_count);
	$tpl->assign('einheit_vert', $einheit_vert);
	$tpl->assign('einheit_vert_count', $einheit_vert_count);

	$tpl->assign('all_einheiten_angr_count', $all_einheiten_angr_count);
	$tpl->assign('einheiten_ang_ap', $einheiten_ang_ap);
	$tpl->assign('all_einheiten_vert_count', $all_einheiten_vert_count);
	$tpl->assign('einheiten_vert_ap', $einheiten_vert_ap);
	$tpl->assign('einheiten_ang_vp', $einheiten_ang_vp);
	$tpl->assign('einheiten_vert_vp', $einheiten_vert_vp);
	$tpl->assign('verlust_angreifer', $verlust_angreifer);
	$tpl->assign('verlust_verteidiger', $verlust_verteidiger);

	$tpl->assign('last_msg', $last_msg);
	$tpl->assign('kr_number', $kr_number);


	$k_report = $tpl->fetch(LITO_THEMES_PATH.'battle/battle_ks_web.html');


	$Datei = LITO_ROOT_PATH."battle_kr/$battle_url";
	$Datei_url = LITO_ROOT_PATH_URL."battle_kr/$battle_url";

	$message_text="Du wurdest angegriffen.<br>Den Kampfreport findest du hier:<br> [url=".$Datei_url."]Kampfreport Nr.:".$kr_number."[/url]";

	if ($fromadmin==0){
		make_ingamemail(0,$angreifer_user_id,$message_topic,$message_text);
		make_ingamemail(0,$verteidiger_user_id,$message_topic,$message_text);
	}

	$erstellen = fopen($Datei, "w");
	fwrite($erstellen, $k_report);
	fclose($erstellen);


	if ($all_einheiten_vert_count<= 0 ){
		$max_steal=intval($op_res_steal);
		if ($max_steal> 0 ){
			resreload($land_id);
			$result=$db->query("SELECT res1,res2,res3,res4,islandid FROM cc".$n."_countries WHERE islandid='".$land_id."'");
			$row=$db->fetch_array($result);
			$land_res1=$row['res1'];
			$land_res2=$row['res2'];
			$land_res3=$row['res3'];
			$land_res4=$row['res4'];


			$per_res1=round($land_res1*($max_steal/100),0);
			$per_res2=round($land_res2*($max_steal/100),0);
			$per_res3=round($land_res3*($max_steal/100),0);
			$per_res4=round($land_res4*($max_steal/100),0);

			if ($per_res1 < 0 )$per_res1=0;
			if ($per_res2 < 0 )$per_res2=0;
			if ($per_res3 < 0 )$per_res3=0;
			if ($per_res4 < 0 )$per_res4=0;

			$result=$db->query("update cc".$n."_countries  set res1=res1-'".$per_res1."',res2=res2-'".$per_res2."',res3=res3-'".$per_res3."',res4=res4-'".$per_res4."' WHERE islandid='".$land_id."'");
			$result=$db->query("update cc".$n."_groups  set res1=res1+'".$per_res1."',res2=res2+'".$per_res2."',res3=res3+'".$per_res3."',res4=res4+'".$per_res4."' WHERE groupid='".$group_id."'");


		}
		// group back


		$result=$db->query("SELECT * FROM cc".$n."_groups WHERE islandid = '$userdata[activeid]' AND groupid = $group_id");
		$row=$db->fetch_array($result);

		$old_traveltime=get_duration_time  ($userdata['x'],$userdata['y'],$row['endx'],$row['endy'],$row['speed']);

		$requesttime = $row['traveltime'] - time();

		if($requesttime < 0) $requesttime = 0;

		$back_traveltime = $old_traveltime - $requesttime;
		$starttime=time();
		$db->query("UPDATE cc".$n."_groups SET group_status = 2, starttime=$starttime,traveltime = $back_traveltime, endx = $userdata[x], endy = $userdata[y] WHERE groupid='$group_id'");


	}


	template_out('battle_ks.html',$modul_name);

	exit();
}

?>