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
//var slReq,clReq,elReq,mlReq,clrReq;						// these are the variable names used in the liblogs.js file


// ---- DO NOT EDIT BELOW THIS LINE ----


var modCall=false,archCall=false;						// these variables are used when you only want the XML request to be called once, regardless of how many times the called object was clicked


function loadLog(objXMLreq,strLogName,strDivName,strObj2Disable) {
// this function loads the passed log file.  See the 'loadSchLog' function for the parameter definitions.

   if (modCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	modCall = true;
   }

   if (window.XMLHttpRequest)								// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)							// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	objXMLreq.open("GET", "other/log.sh?loadLog="+strLogName);

	document.getElementById(strDivName).innerHTML = "";			// resets the div contents to null
	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {						// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";				// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Loading the module log timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			modCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		alert("You should now see the job log under the 'Log' tab.");
		document.getElementById('hidLogFile').value = strLogName;
		document.getElementById(strDivName).innerHTML = objXMLreq.responseText;
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		modCall=false;
	   }
	}
	objXMLreq.send(null);
   }
   document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
}


function archiveLog(objXMLreq,strDivName,strObj2Disable) {
// this function archives the log currently loaded on the 'Log' tab.

   if (archCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	archCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.getElementById(strDivName).style.cursor = "wait";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
   if (objXMLreq) {
	objXMLreq.open("GET", "other/log.sh?archiveLog="+document.getElementById('hidLogFile').value);

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.getElementById(strDivName).style.cursor="default";	// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
			alert("Archiving the issues that need attention timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			archCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		archCall=false;
		document.getElementById(strDivName).style.cursor="default";		// if the module log was selected, change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}

