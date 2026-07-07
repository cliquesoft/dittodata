// overview.js	functions pertaining to the 'Overview' tab.
//
// created	2007/05/12 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// updated	2012/05/30 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid CPL license has been purchased, this software is distributed
// using the AGPLv3 license as found on the Cliquesoft website located at:
// http://www.cliquesoft.org.




// copy the below line of code in your html document and uncomment it.  If these variables names
// will not work, you can call them something else, just be sure to use the same name when calling
// the functions listed in this .js file.
//var slReq,clReq,elReq,mlReq,clrReq;					// these are the variable names used in the liblogs.js file


// ---- DO NOT EDIT BELOW THIS LINE ----


var schCall=false,comCall=false,errCall=false,clrCall=false;		// these variables are used when you only want the XML request to be called once, regardless of how many times the called object was clicked


function loadSchLog(objXMLreq,strLogName,strDivName,strObj2Disable) {
// this function loads the passed log file.
// objXMLreq	the variable name to use for the xml request
// strLogName	the name of the log to load (this is sent to the cgi script)
// strDivName	the name of the div to load the log into
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (schCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	schCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/overview.sh?loadLog="+strLogName);

	document.getElementById(strDivName).innerHTML = "";		// resets the div contents to null
	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";	// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Loading the scheduled backup jobs log timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			schCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {	// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		} else {								// otherwise, everything went ok, so parse the xml database
		   var lines = objXMLreq.responseXML.getElementsByTagName("line");
		   for (var i=0; i<lines.length; i++)
			{ document.getElementById(strDivName).innerHTML += lines[i].firstChild.data + "<br />"; }
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		schCall=false;
		document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function loadComLog(objXMLreq,strLogName,strDivName,strObj2Disable) {
// this function loads the passed log file.  See the 'loadSchLog' function for the parameter definitions.

   if (comCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	comCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	objXMLreq.open("GET", "other/overview.sh?loadLog="+strLogName);

	document.getElementById(strDivName).innerHTML = "";		// resets the div contents to null
	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";	// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Loading the completed backup jobs log timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			comCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {	// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		} else {								// otherwise, everything went ok, so parse the xml database
		   var lines = objXMLreq.responseXML.getElementsByTagName("line");
		   for (var i=0; i<lines.length; i++)
			{ document.getElementById(strDivName).innerHTML += lines[i].firstChild.data + "<br />"; }
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		comCall=false;
		document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function loadErrLog(objXMLreq,strLogName,strDivName,strObj2Disable) {
// this function loads the passed log file.  See the 'loadSchLog' function for the parameter definitions.

   if (errCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	errCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	objXMLreq.open("GET", "other/overview.sh?loadLog="+strLogName);

	document.getElementById(strDivName).innerHTML = "";		// resets the div contents to null
	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";	// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Loading the errors log timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			errCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		document.getElementById(strDivName).innerHTML = objXMLreq.responseText;
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		errCall=false;
		document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function archiveErr(objXMLreq,strDivName,strObj2Disable) {
// this function archives the issues that need attention.

   if (clrCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	clrCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	objXMLreq.open("GET", "other/overview.sh?archiveErr=1");

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";	// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Archiving the issues that need attention timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			clrCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		clrCall=false;
		document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}

