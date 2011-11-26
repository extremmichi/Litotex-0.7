<?PHP


class tpl {

 var $templatefolder = ""; //The default templatefolder is emtpy
 var $expression = "";
 var $packid = 1;


 function tpl($packid) {
  $this->packid = $packid;
 }


 function get($templatefile) {
 	
 	$t_name="./setup_tmp/setup/template/".$this->packid."_".$templatefile.".php";
 

    if(file_exists($t_name)) {
  		@include($t_name);
	}
	else {
		$this->template2error($templatefile);
		exit();
}
  return $template[$templatefile];
 }


 function output($template) {
  print($template);
 }


 function template2error($templatefile) {
  echo "Template '$templatefile' not found!\n<br>";
 }

}

?>
