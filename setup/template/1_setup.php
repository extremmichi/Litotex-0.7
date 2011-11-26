<?PHP

/**
Templatename: setup

Templatepackid: 1

Generate at 09:42:05, 17.09.2008

**/


$template['setup']="<?xml version=\\\"1.0\\\" encoding=\\\"UTF-8\\\"?> <!DOCTYPE HTML PUBLIC \\\"-//W3C//DTD XHTML 1.0 Transitional//EN\\\">
<html>
<head>
<title>Litotex Setup</title>
<meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html; charset=iso-8859-1\\\">
<script language=\\\"JavaScript\\\" src=\\\"./setup_tmp/js/scriptaculous/prototype.js\\\"></script>
<script language=\\\"JavaScript\\\" src=\\\"./setup_tmp/js/scriptaculous/effects.js\\\"></script>
<script language=\\\"JavaScript\\\" src=\\\"./setup_tmp/js/scriptaculous/window.js\\\"></script>
<style type=\\\"text/css\\\">
<!--
.over {
	top: -480px;
  left: -20px;
	position: relative;
	height: 25px;
	width: 600px;
	overflow: visible;
	z-index: 2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #000000;
	text-align: left;
        
}
.overone {
top: -490px;
 left: -20px;
	position: relative;
	height: 15px;
	width: 600px;
	overflow: visible;
	z-index: 2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #000000;
	text-align: left;
}
.content {
	top: -475px;
	margin-top: 10px;
	left: 95px;
padding-left:5px;
	position: relative;
	height: 300px;
	width: 435px;
	overflow: visible;
	z-index: 2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #000000;
	text-align: left;
	background-color: #FFFFFF;
}
.button {
	top: -475px;
	left: 200px;
	position: relative;
	height: 40px;
	width: 230px;
	overflow: visible;
	z-index: 3;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 20px;
	color: #000000;
	text-align: right;

}
.smallfont {
	font-size : 10px;
	color : #3a3a3c;
}
.normalfont {
	font-size : 12px;
	color : #3a3a3c;
}
.normalfont_o{
	font-size : 12px;
	color : #F05A28;
}
.textinput { 
	font-size : 12px;
	width: 120px;
	border : 1px solid #999999;
	color : #000000;
	background : #fff url(./setup_tmp/setup/textbg.gif) repeat-x;
}
.buttons {
	font-family: Verdana, Geneva, sans-serif;
	color: #004080;
	font-size: 10px;
	font-weight: bold;
	border: 1px solid #7E8389;
	background : url(./setup_tmp/setup/bgbutton.gif) repeat-x;	
}
.error{
	font-size : 13px;
	color : #FF0000;
}
.barContainer {
	height: 25px;
	width: 200px;
border: 1px solid #000;
	 display:block ;
}
.percent{
	text-align: center;

}
.progress_text{
	font-size: 10px;
	clear:left;

	
}
.progressBar {
 display:block ;	
	width: 200px;
	border: 1px solid #000;
	color: #FFFFFF;
	background-image:url(./setup_tmp/images/standard/progress_orange.png);
	height: 10px !important;
	background-repeat: no-repeat;
	float:left;
}

-->
</style>
</head>

<body>
<script type=\\\"text/javascript\\\">
var filecount = \$filecounter;
var cur_file_count_pos=0;

function startinstall(){
	
	if (cur_file_count_pos > filecount ){
		document.getElementById(\\\"resp_id\\\").innerHTML=\\\"done\\\";
		document.getElementById('submit').style.visibility = 'visible';
		document.getElementById('frm_id').action = \\\"setup.php?step=3\\\";
		document.getElementById('submit').onclick = '';
		return false;
		} 
	
	if (cur_file_count_pos < 1){
	//document.getElementById('submit').style.display = 'none';
	document.getElementById('submit').style.visibility = 'hidden';
	
	}
  new Ajax.Updater('copy_files','./setup_tmp/setup/installer.php?id='+cur_file_count_pos,{ method : 'get', onSuccess : function(resp){ install_response(resp);} } );
  return false;

}
function install_response(resp){
   document.getElementById(\\\"resp_id\\\").innerHTML=resp.responseText;
   
   cur_file_count_pos=cur_file_count_pos+1;

   progressBar(filecount -cur_file_count_pos);
 
   startinstall();
   
   return false;

}

function progressBar(rest)
{
	maxwidth = 2;
	percentage =Math.round(100-(rest/(filecount /100) ));
	if (percentage < 0 )percentage=0;  
	if (percentage > 100 ) { 
	percentage=100;
	}else{
		width = percentage * maxwidth;
	}
  	new Effect.Morph('progressBar', {style:'width:' + width + 'px ; '});   
  	document.getElementById('percent').innerHTML = percentage + '%' ;
	
}



</script>

<form id=\\\"frm_id\\\" action=\\\"\$action\\\" method=\\\"post\\\">
<div id=\\\"Layer1\\\">
  <div align=\\\"center\\\"><img src=\\\"./setup_tmp/setup/setup.png\\\" width=\\\"650\\\" height=\\\"440\\\" vspace=\\\"30\\\"> 
    <p class=\\\"over\\\">\$over</p>
    <div id=\\\"Layer1\\\" class=\\\"overone\\\">\$over_one</div>
	  
    <div id=\\\"Layer2\\\" class=\\\"content\\\">\$content</div>
    <br>
		
    <div id=\\\"cont\\\" class=\\\"button\\\">\$button</div>
    <br>
  </div>
</div>

</form>
</body>
</html>
";

?>