function read_messag(message_id,elem){
	postajax('ajax_helper.php?action=make_new&new_id='+message_id,'GET','',elem);
}
function read_updates(message_id,elem){
	
	postajax('ajax_helper.php?action=make_new&new_id='+message_id,'GET','',elem);
}

