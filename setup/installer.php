<?PHP
@session_start();

define("LITO_ROOT_PATH", $_SESSION['ftp_ordner']);
$cur_pos=$_REQUEST['id'];

require("./ftp_class.php"); 


	$ftp_server=$_SESSION['ftp_server'];
	$ftp_user=$_SESSION['ftp_user'];
	$ftp_kennwo=$_SESSION['ftp_kennwo'];
	$ftp_port=$_SESSION['ftp_port'];
	$ftp_ordner=$_SESSION['ftp_ordner'];
	

	$ftp = new ftp($ftp_server, $ftp_user, $ftp_kennwo,$ftp_ordner, $ftp_port);
	
	if ($cur_pos==0){
	 //$ftp->mk_dir($ftp_ordner."/cache"); 
	 $ftp->mk_dir("cache"); 

		//$ftp->chown_perm(0777,$ftp_ordner."/cache");
		$ftp->chown_perm(0777,"cache");
		$inhalt=file('./../dirlist.txt');
		foreach($inhalt as $filed){
			$filed=str_replace("\n", "", $filed);
				//$new_d=$ftp_ordner."/".$filed;
				$new_d=$filed;
				
				$ftp->mk_dir($new_d);
			}
		
		}

$inhalt=file('./../filelist.txt');
$pfad_info = pathinfo($inhalt[$cur_pos]);

echo("installiere: ". $pfad_info["basename"] );
$path_use = explode('/', $pfad_info['dirname'], 3);
$pfad_info["basename"] = str_replace("\n", "", $pfad_info["basename"]);
//$new_ftp_path=$ftp_ordner."/".$pfad_info['dirname']."/".$pfad_info["basename"];
$new_ftp_path=$pfad_info['dirname']."/".$pfad_info["basename"];
$ftp->write_contents($new_ftp_path, file_get_contents("../".$pfad_info['dirname']."/".$pfad_info["basename"]), false);
$ftp->disconnect();
exit();


?>