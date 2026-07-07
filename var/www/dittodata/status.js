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
//var ljReq,rjReq,sjReq,prReq,poReq;					// these are the variable names used in the libbackup.js file


// ---- DO NOT EDIT BELOW THIS LINE ----


//var pollReq,pollCall=false;						// this is used for the pollJob function (since this function wouldn't be called from an external source)

// UPDATE: 06-01-2012 as a "multiplexer" for ajax requests
   var aryPollJobs = new Array( );					// contains the dynamically named request variables used in the 'pollJob' function


function addJob(PID,DAT,strName,strSource,strTarget,strType) {
// this function executes the backup job now and lists it on the "status" tab.  DAT = date and time.
   document.getElementById('scn4').innerHTML += "<ul id='"+PID+"' class='ulStatus'><li>Job name: <b>"+strName+"</b></li><li>Source: "+strSource+"</li><li>Type: "+strType+"</li><li>Started on: "+DAT+"</li><li>Target: "+strTarget+"</li><li>Status: <label id='bytes"+PID+"'>Connecting...</label></li></ul>";
}


function pollJob(PID,PPID,strJobname) {
// this function polls a job on a random basis to determine what the current status is (whether the job is running all the way through or the user has initiated a stop)
   var randNum = Math.floor(Math.random() * 2000) + 1000;		// selects a random number between 1000 - 2000 (1-2 seconds)
   var objXMLreq;

   if (document.getElementById(PID).innerHTML == "") { return true; }

   if (window.XMLHttpRequest) {objXMLreq = new XMLHttpRequest();} else {objXMLreq = new ActiveXObject("Microsoft.XMLHTTP");}	// create the object
   if (objXMLreq) {
	objXMLreq.open("GET", "other/status.sh?pollJob="+PID+"&ppidJob="+PPID);

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 4) {
		// can't do the error code because this deals in text, not xml
		var strResponse = objXMLreq.responseText;

		document.getElementById('bytes'+PID).innerHTML = strResponse;
		if ( strResponse != "\nBackup job completed!\n" )
		   { setTimeout("pollJob("+PID+","+PPID+",\""+strJobname+"\")", randNum); }
	   }
	}
	objXMLreq.send(null);
   }
}


function stopJob(objXMLreq,strObj2Disable) {
// this function performs ajax communication to the server to stop a selected backup job immediately

   if (sjCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	sjCall = true;
   }

   if (window.XMLHttpRequest)						// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)					// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   if (objXMLreq) {
	objXMLreq.open("GET", "other/status.sh?stopJob="+intJobPID);

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {				// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";		// change the mouse cursor back to the default to indicate the job was completed
			alert("The backup job stop request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			sjCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {	// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		} else if (objXMLreq.responseXML.getElementsByTagName("success").item(0)) {						// add any jobs that are currently running
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		   document.getElementById('bytes'+intJobPID).innerHTML = "Backup job stopping...";
		   intJobPID = 0;							// reset the variable to indicate nothing is selected any more
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		sjCall=false;
		document.body.style.cursor = "default";					// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function clearJobs() {
// clears the completed jobs from the 'Status' tab
   $('#scn4 > ul').each(function () {					// cycles each 'ul' within the 'div' and removes all that are completed
	if ( $(this).text().indexOf("Backup job completed!") >= 0 ) { $(this).remove(); }
   });
}

