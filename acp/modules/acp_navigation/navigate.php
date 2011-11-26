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

class navigation

{

	var  $modul_name="acp_navigation";
	function make_navigate()
	{

		global $tpl,$db,$n;

		$modul_path=$_SESSION['litotex_start_url'].'acp/modules/navigate/';

		$JS_PATH =LITO_JS_URL.$this->modul_name.'/';
		$IMG_PATH=LITO_IMG_PATH_URL.$this->modul_name.'/';


		$nav_box="";

		$sql="SELECT * from  cc".$n."_menu_admin order by menu_order ASC";
		$result_users=$db->query($sql);

		while($row_g=$db->fetch_array($result_users)) {
			$nav_box.="<a class=\"menuitem submenuheader\" href=\"#\"><img class=\"icons\" src=\"".$row_g['menu_icon']."\" border=\"0\" />".$row_g['menu_name']."</a><div class=\"submenu\"><ul>";

			$sql="SELECT * from cc".$n."_menu_admin_sub where menu_admin_id='".$row_g['adm_menu_id']."'order by sub_name_sort ASC";
			$result_menu_kat=$db->query($sql);

			while($row_g_kat=$db->fetch_array($result_menu_kat)) {
				$is_aktiv=1;
				if(intval($row_g_kat['modul_admin_id']) > 0) {
					$is_aktiv=is_modul_id_aktive($row_g_kat['modul_admin_id']);
				}
				if($is_aktiv ==1){
					if ( intval($row_g_kat['menu_admin_id']) == 6 ){
						if (intval($row_g_kat['sub_name_sort']==1) ){
							$nav_box.="<li><a href=\"".$row_g_kat['admin_sub_link']."\" >".$row_g_kat['admin_sub_name']."</a></li>";
						}else{
						$nav_box.="<li><a href=\"".$row_g_kat['admin_sub_link']."?action=sel_cat&id=".$row_g_kat['admin_sub_id']."\" >".$row_g_kat['admin_sub_name']."</a></li>";
					}
				}else{
				$nav_box.="<li><a href=\"".$row_g_kat['admin_sub_link']."\" >".$row_g_kat['admin_sub_name']."</a></li>";
			}
		}
	}
	$nav_box.="	</ul></div>";
}



$navi = $tpl->fetch(LITO_THEMES_PATH.$this->modul_name.'/navigation.html');


$navi =str_replace("[LITO_CATEGORY]", $nav_box ,$navi);
$navi =str_replace("[JS_PATH]", $JS_PATH, $navi );
$navi =str_replace("[IMG_PATH]", $IMG_PATH, $navi );
$navi =str_replace("[LITO_BASE_MODUL_URL]", LITO_MODUL_PATH_URL, $navi );

return $navi;
}

}
?>
