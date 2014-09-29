
// GLOBAL VARIABLE

function winPop(target_url,win_name,width,height) {
  var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width='width + ',height='height + ',top=0,left=0');
  new_win.focus();
}

var WebAjaxReqXML = null;
 
///////////////////////////////////////////////////
function WebAjaxReqXMLResults()
 {
 if(WebAjaxReqXML.readyState == 4)
   {
   document.getElementById('report_demo').options.length = 0;
   if(WebAjaxReqXML.status != 200)
     {
     document.getElementById('report_demo').options[document.getElementById('report_demo').options.length] = new Option("Error reading from server", "false");
     alert('Unable to read data from server. Status '+WebAjaxReqXML.status);
     return;
     }
   //
   numItems = document.getElementById('report_demo').options.length;
   document.getElementById('report_demo').options[numItems] = new Option("Started date - Ended date","false");
   //
   var txt = WebAjaxReqXML.responseText.toLowerCase();
   var pos=0;
   var i=0;
   for(i=0; i<999; i++)
     {
     var startDate = null; 
     var endedDate = null; 
     var x1 = txt.indexOf('<a href="',pos);
     if(x1<0) { break; }
     var x2 = txt.indexOf('">',x1);
     var s1 = txt.substring(x1+9,x2);
     if(s1.indexOf('auto_')==0)
        {
        var sp = s1.split("_");
        try { startDate = sp[1] + '/' + sp[2] + '/' + sp[3] + ' - ' + sp[4] + ':' + sp[5]; } catch(e) { }
        var fromDate = new Date(sp[1], sp[2], sp[3], sp[4], sp[5]);
        ///
        /// 18-Dec-2006 20:13
        /// 789 123456789 123
        var x3 = txt.indexOf('right',x2)+0;
        endedDate = txt.substring(x3+15,x3+17);
        var d2 = txt.substring(x3+7,x3+9);
        var m2 = txt.substring(x3+10,x3+13);
        var y2 = txt.substring(x3+14,x3+18);
        var h2 = txt.substring(x3+19,x3+21);
        var mi2 = txt.substring(x3+22,x3+24);
        if(m2=='jan') { m2='01'; }
        if(m2=='feb') { m2='02'; }
        if(m2=='mar') { m2='03'; }
        if(m2=='apr') { m2='04'; }
        if(m2=='may') { m2='05'; }
        if(m2=='jun') { m2='06'; }
        if(m2=='jul') { m2='07'; }
        if(m2=='aug') { m2='08'; }
        if(m2=='sep') { m2='09'; }
        if(m2=='oct') { m2='10'; }
        if(m2=='nov') { m2='11'; }
        if(m2=='dec') { m2='12'; }
        endedDate = y2 + '/' + m2 + '/' + d2 + ' - ' + h2 + ':' + mi2;
        var toDate = new Date(y2, m2, d2, h2, mi2);
        ///
        // var desc = fromDate.toLocaleString() + ' ----> ' + toDate.toLocaleString();
        var desc = startDate + ' ----> ' + endedDate;
        ///
        numItems = document.getElementById('report_demo').options.length; 
        document.getElementById('report_demo').options[numItems] = new Option(desc,s1); 
        }
     pos=x2;
     }
   }
 }
 
//////////////////////////////////////////////////////////////////
function WebAjax(AjaxURL)
 {
 WebAjaxReqXML = null;
 try { document.domain = "battle.no"; } catch(e) { }
 if(WebAjaxReqXML == null) try { WebAjaxReqXML = new XMLHttpRequest(); } catch(e) { }
 if(WebAjaxReqXML == null) try { WebAjaxReqXML = new ActiveXObject("Msxml2.XMLHTTP"); } catch(e) { }
 if(WebAjaxReqXML == null) try { WebAjaxReqXML = new ActiveXObject("Microsoft.XMLHTTP"); } catch(e) { }
 if(WebAjaxReqXML)
   {
   var x = new Date().getTime(); // PREVENT IE CACHING
   WebAjaxReqXML.open('GET', AjaxURL + '?sn='+x, true);
   WebAjaxReqXML.setRequestHeader("Connection", "close");
   WebAjaxReqXML.setRequestHeader("Cache-Control", "no-store, no-cache, must-revalidate");
   WebAjaxReqXML.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
   WebAjaxReqXML.setRequestHeader("Pragma", "no-cache");
   WebAjaxReqXML.onreadystatechange = WebAjaxReqXMLResults;
   WebAjaxReqXML.send(null);
   }
 else
   {
   alert("Your browser does not support Ajax");
   }
 }
 
//////////////////////////////////////////////////////////////////
function ubbc(tArea, open, end) {
  if(open == "link") { 
    var txt = prompt("Enter URL for the link.","http://");
    if (txt) { open = '[a href='+txt+']'; }
    else { return false; }
  }

  if(open == "img") 
    { 
    var txt = prompt("Enter URL for the IMG.","http://"); 
    if (txt) { open = '[img]'+txt+''; }
    else { return false; }
    }
  if(open == "[ul]")
    {
    var list_item = prompt("Enter a list item. Press \"Cancel\" when you are done.","");
    if (list_item)
      {
      while (list_item)
        {
        open = open+"\n[li]"+list_item+"[/li]";
        list_item = prompt("Enter a list item. Press \"Cancel\" when you are done.","");
        }
      open = open+"\n";
      }
    else { return false; }
    }

  var isIE = (document.all)? true : false;
  var open = (open)? open : "";
  var end = (end)? end : "";
  if (isIE)
    {
    tArea.focus();
    var curSelect = document.selection.createRange();
    if (arguments[3])
      { 
      curSelect.text = open + arguments[3] + "]" + curSelect.text + end;
      }
    else
      {
      curSelect.text = open + curSelect.text + end;
      }
    }
  else if (!isIE && typeof tArea.selectionStart != "undefined")
    {
    var selStart = tArea.value.substr(0, tArea.selectionStart);
    var selEnd = tArea.value.substr(tArea.selectionEnd, tArea.value.length);
    var curSelection = tArea.value.replace(selStart, '').replace(selEnd, '');
    if (arguments[3])
      {
      tArea.value = selStart + open + arguments[3] + "]" + curSelection + end + selEnd;
      }
    else
      {
      tArea.value = selStart + open + curSelection + end + selEnd;
      }
    }
  else
    { 
    tArea.value += (arguments[3])? open + arguments[3] + "]" + end : open + end;
    }
  try { tArea.focus(); } catch(e) {}
  }

function openWindow(theURL,winName,features) {
    var w = window.open(theURL,winName,features);
}

function hideLoadingPage() {
  document.getElementById ? document.getElementById('loading').style.display = 'none' // DOM
  : document.all ? document.all.loading.style.display = 'none'  // IE 4
  : null; // unsupported
}

function change(name){
     ID = document.getElementById(name);
    
     if(ID.style.display == "")
          ID.style.display = "none";
     else
          ID.style.display = "";
      }
