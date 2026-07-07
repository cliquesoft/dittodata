// _web.us.js	Sets up the persistent ajax connection via web.us
//
// Created	2018-01-17 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2018-01-22 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.
//
// NOTES:
// - Using this function will require the addition of the _web.us_simple.sh -OR- _web.us_robust.sh file



var reqWebus;						// used to process AJAX requests


function webus(objXHR,strType,strQuery) {
// (re)creates the connection to the server-side script to maintain a persistent
// connection for bus communication
// objXHR	the variable to pass to this function that stores the connection info
// strType	the request type: get, post
// strQuery	the entire query including the server-side script and any parameters
	var xhrOffset=0;								// used to get the last command (since they are all clumped together)
	var intQuit=0;									// used to stop this function from continously calling itself

	if (window.XMLHttpRequest)						// for all browsers except microsoft
		{ objXHR = new XMLHttpRequest(); }
	else if (window.ActiveXObject)					// for Microsoft
		{ objXHR = new ActiveXObject("Microsoft.XMLHTTP"); }
	else {
		if (verbose) { alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); }
		return 1;
	}

	if (! objXHR) { alert('No XHR could be setup for this communication.'); return 0; }

	if (strType == 'get' || strType == '') {				// start a GET request
		objXHR.open("GET", strQuery, true);			// NOTE: the 'true' value is for asynchronous communication
		objXHR.send();
	} else {							// start a POST request
		var postparts=strQuery.split('?');
		objXHR.open("POST", postparts[0], true);
		objXHR.send(postparts[1]);
	}

	objXHR.onreadystatechange = function() {
		// since any communication can come in at any time, we must use this 'readyState' for processing
		if (objXHR.readyState == 3 && objXHR.responseText != '' && objXHR.responseText != '\n') {
			var strLast=objXHR.responseText.substring(xhrOffset).trim();
			if (strLast.indexOf('(')) { var strFunc=strLast.substring(0, strLast.indexOf('(')); } else { var strFunc=''; }

			if (strLast == 'QUIT') { intQuit=1; }
			else if (strLast.substring(0,7) == 'ERROR: ') { alert(strLast); }
			else if (strFunc != '') { eval(strLast); }
			xhrOffset = objXHR.responseText.length;		// store the new starting point for the next communication
		}

		// upon receiving the end of the communication, re-establish the connection to the web.us server
		if (objXHR.readyState == 4 && objXHR.status==200 && intQuit == 0)
			{ setTimeout(webus(objXHR,strType,strQuery),100); }
	}
}


function webusProgressBar(strID, intWidth) {
// makes any adjustments to progress bar sizes
// strID	the id of the meter to adjust
// intWidth	the width the make the meter, MUST include a metric such as '%', 'px', etc
	document.getElementById(strID).style.width = intWidth;
}


function webusProgressInfo(strID, strInfo) {
// updates the info for anything (e.g. 1234/2345, 1250/2345 block copied)
// strID	the id of the object to adjust
// strInfo	the string to update in the display
	document.getElementById(strID).innerHTML = strInfo;
}
