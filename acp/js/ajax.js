function AJAXRequest(surl, ssendmethod, spoststring, sdisplayelement){
    this.url=surl;
    this.sendmethod=ssendmethod;
    this.poststring=spoststring;
    this.displayelement=sdisplayelement;
    this.req=0;
    this.callback = null;
    this.sourceelement = null;
    
    var self = this;
    this.go = function(){
         if (window.XMLHttpRequest) {
            this.req = new XMLHttpRequest();
        }
        else if (window.ActiveXObject) {
            this.req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        //alert(this.req.readystate);
        this.req.onreadystatechange = function(){processajax(self);};
        //req.displayelement = this.displayelement;
        this.req.open(this.sendmethod, this.url, true);
        if (this.sendmethod=='POST')this.req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        this.req.send(this.poststring);
        
    }

    var processajax = function (j){

        var req = j.req;
        if (req.readyState == 4) {
            if (req.status == 200) {
             if (j.displayelement!=null)j.displayelement.innerHTML=req.responseText;
             if (j.callback!=null) j.callback(j.sourceelement, req);
            }
        }
    }
}

var show=true;
var xs = new Array();
function postajax(url,sendmethod,poststring, displayelement){
	
    var u = url;
    var req;
    dispelement = document.getElementById(displayelement);
    var x = new AJAXRequest(url, sendmethod, poststring, dispelement);
    x.go();
}

function postcallback(url, sendmethod, poststring,  callback, sourceelement){
    var x = new AJAXRequest(url, sendmethod, poststring, null);
    x.callback = callback;
    x.sourceelement = sourceelement;
    x.go();
}
function getPostString(postname){
    var b = document.getElementById(postname).value;
    return postname + "=" + escape(b);
}

function getFormString(formid){
    var b="";
    for (i=0;i<formid.elements.length;i++){
        b+=formid.elements[i].id + "=" + escape(formid.elements[i].value) + "&";
    }
    return b;
}

function findParentAjax(el){
    while((el.tagName != "DIV" || el.className!="ajax") && el.parentNode!=null){
        //alert(el.className+ " "+el.id);
        el = el.parentNode;
    }
    return el;
}
function refreshElement(url, sendmethod, poststring, elementid){
    var a = findParentAjax(elementid);
    postajax(url,sendmethod,poststring,a.id);
}

//GH's AjAx
var outputid = "";
var ajax = [];
var number = 0;
function loadPage(URL, vars, method, output) {
	number++;
	if(window.XMLHttpRequest) {
	   ajax[number] = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	   ajax[number] = new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(ajax[number] == null){
		alert("Browser wird nicht unterstuetzt!");
	return false;
	}
	if(URL == null)
		return false;
	if(method == null)
		method = "GET";
	if(method == "POST")
		ajax.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded");
	ajax[number].open(method, URL, false);
	if(output != null){
		setTimeout("outfunc('"+output+"', '"+number+"')", 500);
	}
	ajax[number].send(vars);
}
var counttimes = [];
function outfunc(output, number){
	if(counttimes[number] == undefined)
		counttimes[number] = 0;
	else
		counttimes[number]++;
	if(counttimes[number] > 20){
		document.getElementById(output).innerHTML = "Netzwerkfehler";
		return false;
	}
	if(ajax[number].readyState == 4){
		document.getElementById(output).innerHTML = ajax[number].responseText;
	}else{
		setTimeout("outfunc('"+output+"', '"+number+"')", 500);
	}
}
