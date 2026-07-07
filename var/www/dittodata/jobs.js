// jobs.js	functions pertaining to the 'Jobs' tab.
//
// created	2007/10/02 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// updated	2012/07/18 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid CPL license has been purchased, this software is distributed
// using the AGPLv3 license as found on the Cliquesoft website located at:
// http://www.cliquesoft.org.




// copy the below line of code in your html document and uncomment it.  If these variables names
// will not work, you can call them something else, just be sure to use the same name when calling
// the functions listed in this .js file.
//var ljReq,rjReq,sjReq,prReq,poReq;				// these are the variable names used in the libbackup.js file


// ---- DO NOT EDIT BELOW THIS LINE ----


var ljsCall=false,ljCall=false,prCall=false,rjCall=false,saveCall=false,delCall=false,renCall=false,cpyCall=false,newCall=false;	// these variables are used when you only want the XML request to be called once, regardless of how many times the called object was clicked
var pageLoad=0;							// indicates the page is initially loading (to load the first backup scheme in the listbox).  Simply change this value to 1 to indicate the page is being loaded.


function loadJobs(objXMLreq,strCombobox,boolLoadStatus) {
// this function loads all the saved jobs into the "saved jobs" combobox.
// objXMLreq	this is the variable name to use for the XML request
// strCombobox	the name of the combobox to use to store all the results from the cgi call (returns all the job names)
// boolLoadStatus	setting this to true loads the status of any running jobs in a div

   if (ljsCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	ljsCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	document.getElementById(strCombobox).disabled = true;
	objXMLreq.open("GET", "other/jobs.sh?loadJobs=yes");

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("Loading the backup jobs timed out.  Try\nreloading the weblication.");
			document.getElementById(strCombobox).disabled = false;
			ljsCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {		// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		} else if (objXMLreq.responseXML.getElementsByTagName("job").item(0)) {		// if there are jobs to process, then process them (otherwise, don't do anything)
		   document.getElementById(strCombobox).options.length=0;			// resets the list to 0
		   var jobs = objXMLreq.responseXML.getElementsByTagName("job");
		   for (var i=0; i<jobs.length; i++)
			{ Add2List(jobs[i].getAttribute("name"), document.getElementById(strCombobox), jobs[i].getAttribute("name"), 1, 1); }
		   if (pageLoad == 1)								// if this function is being called at the page load, the load the first scheme as well
			{ loadJob(objXMLreq,document.getElementById(strCombobox).options[0].text,strCombobox); }
// LEFT OFF - commented out for conversion to default.sh 5-23-2012
//		   if (boolLoadStatus)
//			{ loadStatus(reqJobStatus,document.getElementById(strCombobox).options.selectedIndex); }			// the loads all the jobs on the "Status" tab
		}
		ljsCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function loadJob(objXMLreq,strJobName,strObj2Disable) {
// this function loads the selected jobs parameters into the form.
// objXMLreq	this is the variable name that was defined (above) that should have been copied and uncommented in your html file.
// strJobName	the name of the job you want to load.
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)
   var i, j, temp;

   if (ljCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	ljCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?loadJob="+escape(strJobName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("Loading of the selected backup job timed\nout.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			ljCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {		// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		} else {									// otherwise, everything went ok, so parse the xml database
		   var jobs = objXMLreq.responseXML.getElementsByTagName("job");

		   document.getElementById('cmbIncludeList').options.length=0;			// reset the data to restore in the 'include' listbox
		   document.getElementById('cmbExcludeList').options.length=0;
		   for (i=0; i<jobs.length; i++) {
			var job = jobs[i];
			var strLoopJob = job.getAttribute("name");

			if (strJobName == strLoopJob) {
			   var ans = objXMLreq.responseXML.getElementsByTagName("job").item(i);	// stores the correct job

			   document.getElementById('hidJobFile').value = job.getAttribute("file");

			   temp = job.getAttribute("type");
			   for (j=0; j<document.getElementById('cmbType').options.length; j++)
				{ if (document.getElementById('cmbType').options[j].value == temp) { document.getElementById('cmbType').selectedIndex = j; break; } }
			   temp = job.getAttribute("enabled");
			   for (j=0; j<document.getElementById('cmbStatus').options.length; j++)
				{ if (document.getElementById('cmbStatus').options[j].value == temp) { document.getElementById('cmbStatus').selectedIndex = j; break; } }
			   document.getElementById('chkVerify').checked = job.getAttribute("verify");
			   temp = job.getAttribute("compression");
			   for (j=0; j<document.getElementById('cmbCompression').options.length; j++)
				{ if (document.getElementById('cmbCompression').options[j].value == temp) { document.getElementById('cmbCompression').selectedIndex = j; break; } }
//			   document.getElementById('chkBatteries').checked = job.getAttribute("batteries");

			   var schedule = ans.getElementsByTagName("schedule").item(0);
			   document.getElementById('txtSchDate').value = schedule.getAttribute("date");
			   document.getElementById('txtSchTime').value = schedule.getAttribute("time");
			   document.getElementById('txtSchFreq').value = schedule.getAttribute("freq");

			   var storage = ans.getElementsByTagName("storage").item(0);		// selects the first <storage> tag in the xml file under the correct parent <job> tag
			   document.getElementById('radStoretype'+storage.getAttribute("method")).checked = true;
			   if (storage.getAttribute("method") == 0) {
				document.getElementById('txtStoreAmt').value = storage.getAttribute("value");
				document.getElementById('txtStoreQuota').value = "";		// erase the value of the other textbox
			   }
			   if (storage.getAttribute("method") == 1) {
				document.getElementById('txtStoreAmt').value = "";		// erase the value of the other textbox
				document.getElementById('txtStoreQuota').value = storage.getAttribute("value");
			   }

			   var fs = ans.getElementsByTagName("fs").item(0);
			   document.getElementById('txtSource').value = fs.getAttribute("source");
			   document.getElementById('txtSourceUser').value = fs.getAttribute("sourceUser");
			   document.getElementById('txtSourcePass').value = fs.getAttribute("sourcePass");
			   document.getElementById('txtSourceDom').value = fs.getAttribute("sourceDom");
			   document.getElementById('txtTarget').value = fs.getAttribute("target");
			   document.getElementById('txtTargetUser').value = fs.getAttribute("targetUser");
			   document.getElementById('txtTargetPass').value = fs.getAttribute("targetPass");
			   document.getElementById('txtTargetDom').value = fs.getAttribute("targetDom");
			   document.getElementById('txtTargetDir').value = fs.getAttribute("targetDir");

			   var contact = ans.getElementsByTagName("contact").item(0);
			   document.getElementById('txtCname').value = contact.getAttribute("cname");
			   document.getElementById('txtCemail').value = contact.getAttribute("cemail");

			   var sync = ans.getElementsByTagName("sync").item(0);
			   document.getElementById('txtSyncTarget').value = sync.getAttribute("target");
			   document.getElementById('txtSyncPort').value = sync.getAttribute("port");
			   document.getElementById('txtSyncDir').value = sync.getAttribute("dir");
			   document.getElementById('txtSyncTunnelUser').value = sync.getAttribute("tunnelUser");
			   document.getElementById('txtSyncTunnelPass').value = sync.getAttribute("tunnelPass");

			   var params = ans.getElementsByTagName("params");
			   for (j=0; j<params.length; j++) {
				if (params[j].getAttribute("bin") == 'tar' && params[j].hasChildNodes()) {
				   document.getElementById('chkTarParams').checked = true;
				   document.getElementById('txtTarParams').value = params[j].firstChild.data;
				} else if (params[j].getAttribute("bin") == 'tar') {
				   document.getElementById('chkTarParams').checked = false;
				   document.getElementById('txtTarParams').value = '';

				} else if (params[j].getAttribute("bin") == 'rsync' && params[j].hasChildNodes()) {
				   document.getElementById('chkRsyncParams').checked = true;
				   document.getElementById('txtRsyncParams').value = params[j].firstChild.data;
				} else if (params[j].getAttribute("bin") == 'rsync') {
				   document.getElementById('chkRsyncParams').checked = false;
				   document.getElementById('txtRsyncParams').value = '';
				}
			   }

			   var notes = ans.getElementsByTagName("notes").item(0);
			   if (notes.hasChildNodes()) { document.getElementById('txtNotes').value = notes.firstChild.data; } else { document.getElementById('txtNotes').value = ""; }

			   var include = ans.getElementsByTagName("i");
			   document.getElementById('cmbIncludeList').options.length=0;		// resets the records in the "file/dirs to be backed up" listbox to 0
			   for (j=0; j<include.length; j++) {
				var item = include[j];
				Add2List(item.firstChild.data, document.getElementById('cmbIncludeList'), item.firstChild.data, 1, 1);
			   }

			   var exclude = ans.getElementsByTagName("e");
			   document.getElementById('cmbExcludeList').options.length=0;		// resets the records in the "file/dirs to be backed up" listbox to 0
			   for (j=0; j<exclude.length; j++) {
				item = exclude[j];
				Add2List(item.firstChild.data, document.getElementById('cmbExcludeList'), item.firstChild.data, 1, 1);
			   }
			   break;								// once the correct one is found, stop looping
			}
		   }
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		ljCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function runJob(objXMLreq,strJobName,strObj2Disable) {
// this function performs ajax communication to the server to start a backup job immediately
// objXMLreq	this is the variable name that was defined (above) that should have been copied and uncommented in your html file.
// strJobName	the name of the job that needs to run right now.
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   var duplicate=0;
   $('#scn4 > ul').each(function () {				// cycles each 'ul' within the 'div' to search for the same job already running
	if ( $(this).text().indexOf("Job name: "+strJobName) >= 0 ) {		// if the iterate 'ul' is a possible existing execution of the job, then...
	   if ( $(this).text().indexOf("Backup job completed!") == -1 ) {	// if it is CURRENTLY running (instead of a prior finished one), then exit.
		alert("This job already appears to be running, please wait until it's completed before running again.");
		duplicate=1;
		return false;
	   }
	}
   });
   if (duplicate) { return false; }


   if (rjCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	rjCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	objXMLreq.open("GET", "other/jobs.sh?runJob="+escape(document.getElementById('hidJobFile').value)+"&n="+escape(strJobName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("Execution of the backup job timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			rjCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("error").item(0)) {		// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("message").item(0).firstChild.data);
		   objXMLreq = false;								// "unlock" the function
		} else if (objXMLreq.responseXML.getElementsByTagName("info").item(0)) {	// add any jobs that are currently running
		   var jobInfo = objXMLreq.responseXML.getElementsByTagName("info").item(0);
		   if (jobInfo.getAttribute("pid") == "") {					// this section of the 'if' is used as a safety net
			alert('The "'+strJobName+'" job failed to start.');
		   } else {									// otherwise, we have a successful execution, so...
			addJob(jobInfo.getAttribute("pid"),jobInfo.getAttribute("date"),document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].value,document.getElementById("txtSource").value,document.getElementById("txtTarget").value,"backup, "+document.getElementById("cmbType").options[document.getElementById("cmbType").selectedIndex].value);
			pollJob(jobInfo.getAttribute("pid"),jobInfo.getAttribute("ppid"),strJobName);		// keep tabs on the running job until it completes (that function will remove it from the list)
			alert("You can now see the job progress under the 'Status' tab.");
		   }
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		rjCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
   return true;
}


function newJob(objXMLreq,strJobName,strObj2Disable) {
// this function adds a new backup job to the device.
// objXMLreq	this is the variable name to use for the XML request
// strJobName	the name of the job that is going to be copied
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (newCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	newCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?newJob="+escape(strJobName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("The copy request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			newCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("f").item(0)) {			// if there was an error, display it to the user
		   document.body.style.cursor = "default";					// change the mouse cursor back to the default to indicate the job was completed
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		} else {									// otherwise, everything went ok, so parse the xml database
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		   Add2List('All data',document.getElementById('cmbIncludeList'),'.',1,0);	// add a default value to backup all data on the share
		   Add2List(strJobName,document.getElementById('cmbJobList'),strJobName,1,0);
		   document.getElementById("main").reset();					// reset the values on the form; NOTE: this is required to reset the form before loading the job below!
		   for (var i=0; i<document.getElementById('cmbJobList').options.length; i++)	// switch which listbox item is selected
			{ if (document.getElementById('cmbJobList').options[i].text == strJobName) {document.getElementById('cmbJobList').selectedIndex=i;} }
		   loadJob(objXMLreq,strJobName,'cmbJobList');					// this is mainly used to populate the 'hidJobFile' form object
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		newCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function renameJob(objXMLreq,strJobName,strNewName,strObj2Disable) {
// this function deletes the currently viewed backup job from the xml database.
// objXMLreq	this is the variable name to use for the XML request
// strJobName	the old name of the job that is going to be renamed
// strNewName	the new name of the job that is going to be renamed
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (renCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	renCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?renameJob="+escape(strJobName)+"&new="+escape(strNewName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("The rename request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			renCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("f").item(0)) {			// if there was an error, display it to the user
		   document.body.style.cursor = "default";					// change the mouse cursor back to the default to indicate the job was completed
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		} else {									// otherwise, everything went ok, so parse the xml database
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		   ListReplace('text',strJobName,strNewName,document.getElementById('cmbJobList'),strNewName,1,0);
		   for (var i=0; i<document.getElementById('cmbJobList').options.length; i++)	// switch which listbox item is selected
			{ if (document.getElementById('cmbJobList').options[i].text == strNewName) {document.getElementById('cmbJobList').selectedIndex=i;} }
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		renCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function copyJob(objXMLreq,strJobName,strNewName,strObj2Disable) {
// this function deletes the currently viewed backup job from the xml database.
// objXMLreq	this is the variable name to use for the XML request
// strJobName	the name of the job that is going to be copied
// strNewName	the name of the copied job
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (cpyCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	cpyCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?copyJob="+escape(strJobName)+"&new="+escape(strNewName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("The copy request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			cpyCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("f").item(0)) {			// if there was an error, display it to the user
		   document.body.style.cursor = "default";					// change the mouse cursor back to the default to indicate the job was completed
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		} else {									// otherwise, everything went ok, so parse the xml database
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		   Add2List(strNewName,document.getElementById('cmbJobList'),strNewName,1,0);
		   for (var i=0; i<document.getElementById('cmbJobList').options.length; i++)	// switch which listbox item is selected
			{ if (document.getElementById('cmbJobList').options[i].text == strNewName) {document.getElementById('cmbJobList').selectedIndex=i;} }
		   loadJob(objXMLreq,strNewName,'cmbJobList');
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		cpyCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function delJob(objXMLreq,strJobName,strJobFile,strCombobox,strObj2Disable) {
// this function deletes the currently viewed backup job from the xml database.
// objXMLreq	this is the variable name to use for the XML request
// strJobName	the name of the job that needs to deleted.
// strJobFile	the file that actually contains the execution syntax of the backup job
// strCombobox	the name of the combobox to use to store all the results from the cgi call (returns all the job names)
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (delCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	delCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?delJob="+escape(strJobName)+"&jobFile="+escape(strJobFile));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("The delete request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			delCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("f").item(0)) {			// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		} else if (objXMLreq.responseXML.getElementsByTagName("s").item(0)) {		// if there wasn't an error, then...
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		   loadJobs(objXMLreq,strCombobox,false);					// load all the backup jobs again (so the newly created one will be in the list)
// LEFT OFF - do we need the below setTimeout?
//		   setTimeout(function() {
//			document.getElementById('cmbJobList').selectedIndex = 0;
//			loadJob(objXMLreq,document.getElementById(strCombobox).options[0].text,strCombobox);
//			document.body.style.cursor = "default";					// change the mouse cursor back to the default to indicate the job was completed
//		   }, 2000);
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		delCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function saveJob(objXMLreq,strJobFile,strFormName,strObj2Disable) {
// this function saves the currently viewed backup job.
// objXMLreq	this is the variable name to use for the XML request
// strJobFile	the file that actually contains the execution syntax of the backup job
// strFormName	the form name containing all the objects that need to be submitted in an ajax form submission
// strObj2Disable	if this is left blank, nothing will be disabled, otherwise you can pass the name of any object to disable (ie the button that was clicked to call this function)

   if (saveCall != false) {
	// the below can be uncommented to visually inform the user to wait, otherwise it will just be a silent refusal
	alert("This request has already been called.  Please\nwait until it's completed before trying again.");
	return false;
   } else {
	saveCall = true;
   }

   if (window.XMLHttpRequest)					// for all browsers that follow specifications
	{ objXMLreq = new XMLHttpRequest(); }
   else if (window.ActiveXObject)				// for Microsoft
	{ objXMLreq = new ActiveXObject("Microsoft.XMLHTTP"); }
   else
	{ alert("Unfortunately your browser doesn't support ajax requests.\nPlease upgrade your browser to a current version."); return false; }

   document.body.style.cursor = "wait";				// change the mouse cursor to indicate that a process is happening
   if (objXMLreq) {
	if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = true; }
	objXMLreq.open("GET", "other/jobs.sh?saveJob="+escape(strJobFile)+ajaxSumbitURI(strFormName));

	objXMLreq.onreadystatechange = function() {
	   if (objXMLreq.readyState == 1) {			// this if statement creates a way to abort the request and let the user know what happened.
		setTimeout(function() {
		   if (objXMLreq.readyState != 4) {
			document.body.style.cursor = "default";	// change the mouse cursor back to the default to indicate the job was completed
			alert("The delete request timed out.  Please try again.");
			if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
			saveCall=false;
			objXMLreq.abort();
		   }
		}, 20000);
	   }
	   if (objXMLreq.readyState == 4) {
		if (objXMLreq.responseXML.getElementsByTagName("f").item(0)) {			// if there was an error, display it to the user
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		} else if (objXMLreq.responseXML.getElementsByTagName("s").item(0)) {		// if there wasn't an error, then...
		   alert(objXMLreq.responseXML.getElementsByTagName("msg").item(0).firstChild.data);
		}
		if (strObj2Disable != "") { document.getElementById(strObj2Disable).disabled = false; }
		saveCall=false;
		document.body.style.cursor = "default";						// change the mouse cursor back to the default to indicate the job was completed
	   }
	}
	objXMLreq.send(null);
   }
}


function showNewJob(strAction) {
// displays the popup to allow a user to create a new job
   document.getElementById('divPopup').className = document.getElementById('divPopup').className = 'PopupMin';
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');

   switch(strAction) {
	case "req":
		var HTML = '';

		HTML =	"<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>" +
			"<h3>&nbsp;New Job&nbsp;</h3>" +
			"<div class='divBody'>" +
			"	<p>";
		if (document.getElementById('cmbFrequency').value == 'daily')
			{ HTML += "		Using the below options, select which days of the week you would like to job to run."; }
		else if (document.getElementById('cmbFrequency').value == 'monthly')
			{ HTML += "		Using the below chart, select which days of the month you would like to job to run."; }
		else
			{ HTML += "		Using the below options, specify how often you would like to job to run 24/7/365."; }

		HTML +=	"	</p>" +
			"	<center><input type='textbox' id='txtName' maxlength='32' placeholder='Job Name' class='textbox' /></center>" +
//			"<ul class='freq'>" +
//			"	<li><input type='textbox' id='txtFreq' maxlength='3' placeholder='###' class='textbox' title='The numeric value of the frequency to run' />" +
//			"	<li><select id='cmbFreq' size='1' class='listbox'><option value='Minutes'>Minutes</option><option value='Hours'>Hours</option><option value='Days'>Days</option><option value='Months'>Months</option></select>" +
//			"</ul>" +
			"</div>" +
			"<div class='divButtons'>" +
			"	<input type='button' id='btnSave' value='Save' class='button OTButton space' onClick=\"saveNewJob('req');\" />" +
			"</div>";

		togglePopup('show');
		document.getElementById('divPopup').innerHTML = HTML;
		if (Mobile) {		// if we are on a mobile device, move to the top (so the popup and overlay are shown correctly) along with disabling scrolling on the main document
			document.body.scrollTop = document.documentElement.scrollTop = 0;	// https://stackoverflow.com/questions/4210798/how-to-scroll-to-top-of-page-with-javascript-jquery
			document.body.style.overflow = 'hidden';
		}
		break;
   }
}


function saveNewJob(strAction) {
   switch(strAction) {
	case "req":
		if (! Add2List('cmbJobList',document.getElementById('txtName').value,document.getElementById('txtName').value,1,0,0,1)) {
			document.getElementById('txtName').focus();
			return false;
		}

		togglePopup('hide');
		break;
   }
}


function showShedule(strAction) {
// displays the popup to allow a user to define the job schedule
   document.getElementById('divPopup').className = document.getElementById('divPopup').className = 'PopupMin';
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');

   switch(strAction) {
	case "req":
		var HTML = '';

		HTML =	"<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>" +
			"<h3>&nbsp;Shedule&nbsp;</h3>" +
			"<div class='divBody'>" +
			"	<p>";
		if (document.getElementById('cmbFrequency').value == 'daily')
			{ HTML += "		Using the below options, select which days of the week you would like to job to run."; }
		else if (document.getElementById('cmbFrequency').value == 'monthly')
			{ HTML += "		Using the below chart, select which days of the month you would like to job to run."; }
		else
			{ HTML += "		Using the below options, specify how often you would like to job to run 24/7/365."; }

		HTML +=	"	</p>";

		if (document.getElementById('cmbFrequency').value == 'daily') {
			HTML +=	"<table id='tblDOW' class='dow'>" +
				"	<tr><td>Sun</td><td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td></tr>" +
				"</table>";
		} else if (document.getElementById('cmbFrequency').value == 'monthly') {
			HTML +=	"<table id='tblDOM' class='dom'>" +
				"	<tr><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td><td>14</td><td>15</td></tr>" +
				"	<tr><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td><td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td></tr>" +
				"</table>";
		} else {
			HTML +=	"<ul class='freq'>" +
				"	<li><input type='textbox' id='txtFreq' maxlength='3' placeholder='###' class='textbox' title='The numeric value of the frequency to run' />" +
				"	<li><select id='cmbFreq' size='1' class='listbox'><option value='Minutes'>Minutes</option><option value='Hours'>Hours</option><option value='Days'>Days</option><option value='Months'>Months</option></select>" +
				"</ul>";
		}

		HTML +=	"</div>" +
			"<div class='divButtons'>" +
			"	<input type='button' id='btnSave' value='Save' class='button OTButton space' onClick=\"saveSchedule();\" />" +
			"</div>";

		togglePopup('show');
		document.getElementById('divPopup').innerHTML = HTML;
		if (Mobile) {		// if we are on a mobile device, move to the top (so the popup and overlay are shown correctly) along with disabling scrolling on the main document
			document.body.scrollTop = document.documentElement.scrollTop = 0;	// https://stackoverflow.com/questions/4210798/how-to-scroll-to-top-of-page-with-javascript-jquery
			document.body.style.overflow = 'hidden';
		}

		if (document.getElementById('cmbFrequency').value == 'daily') {			// add the function call to each <td>
			for (var i=0; i<7; i++) { document.getElementById('tblDOW').rows[0].cells[i].addEventListener('click', function(){toggleCell(this)}); }
		} else if (document.getElementById('cmbFrequency').value == 'monthly') {
			for (var i=0; i<2; i++ ) {
				Row = document.getElementById('tblDOM').rows[i];
				for (var j=0; j<15; j++) { Row.cells[j].addEventListener('click', function(){toggleCell(this)}); }
			}
		}

		loadSchedule();
		break;
   }
}


function toggleCell(objThis) {
// toggles the selected Day Of Month in the popup
	if (objThis.className.indexOf('selected') > -1) { objThis.className = ''; }
	else { objThis.className = 'selected'; }
}


function loadSchedule() {
// loads the schedule selection to the popup
	var Elm = document.getElementById('txtSchedule');
	var Cell;
	var Values;

	if (document.getElementById('cmbFrequency').value == 'daily') {
		Values = Elm.value.split(', ');
		for (var i=0; i<Values.length; i++) {
			for (var j=0; j<7; j++) {
				Cell = document.getElementById('tblDOW').rows[0].cells[j];
				if (Values[i] == Cell.innerHTML) { Cell.className = 'selected'; break; }
			}
		}
	} else if (document.getElementById('cmbFrequency').value == 'monthly') {
		Values = Elm.value.split(',');
		for (var i=0; i<Values.length; i++) {
outter1:
			for (var j=0; j<2; j++ ) {
				Row = document.getElementById('tblDOM').rows[j];
inner1:
				for (var k=0; k<15; k++) {
					Cell = document.getElementById('tblDOM').rows[j].cells[k];
					if (Values[i] == Cell.innerHTML) { Cell.className = 'selected'; break outter1; }	// now break to the outter 'for' loop	http://stackoverflow.com/questions/183161/best-way-to-break-from-nested-loops-in-javascript
				}
			}
		}
	} else {
		Values = Elm.value.split(' ');
		document.getElementById('txtFreq').value = Values[0];
		selListbox('cmbFreq',Values[1]);
	}
}


function saveSchedule() {
// saves the schedule selection to the main form
	var Elm = document.getElementById('txtSchedule');
	var Cell;

	Elm.value = '';					// erase any prior value
	if (document.getElementById('cmbFrequency').value == 'daily') {
		for (var i=0; i<7; i++) {
			Cell = document.getElementById('tblDOW').rows[0].cells[i];
			if (Cell.className.indexOf('selected') > -1) {
				if (Elm.value == '') { Elm.value = Cell.innerHTML; }
				else { Elm.value += ', '+Cell.innerHTML; }
			}
		}
	} else if (document.getElementById('cmbFrequency').value == 'monthly') {
		for (var i=0; i<2; i++ ) {
			Row = document.getElementById('tblDOM').rows[i];
			for (var j=0; j<15; j++) {
				Cell = document.getElementById('tblDOM').rows[i].cells[j];
				if (Cell.className.indexOf('selected') > -1) {
					if (Elm.value == '') { Elm.value = Cell.innerHTML; }
					else { Elm.value += ','+Cell.innerHTML; }
				}
			}
		}
	} else {
		Elm.value = document.getElementById('txtFreq').value+' '+document.getElementById('cmbFreq').value;
	}

	togglePopup('hide');
}

