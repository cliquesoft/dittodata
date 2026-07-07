// jobs.js	functions pertaining to the 'Jobs' tab.
//
// created	2007/10/02 by Dave Henderson (support@cliquesoft.org)
// updated	2022/05/19 by Dave Henderson (support@cliquesoft.org)
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
//	https://stackoverflow.com/questions/21489270/can-you-add-an-object-to-an-array-inside-its-constructor




var reqJobs;							// used to request the "Overview" tab I/O via AJAX
var Job = {}							// this object contains all the JobDef objects of the currently running jobs

function JobDef(iRJN,eDAT,iPID,iPPID,sName,sType,sSource,sTarget) {
// This is the object constructor function.  It defines the object prototype for the array Properties.
	this.RJN = iRJN;					// the random job number used to communicate with the GUI
	this.DAT = eDAT;					// date and time (DAT) of running job
	this.PID = iPID;					// PID of running job
	this.PPID = iPPID;					// parent PID of running job
	this.Name = sName;					// name of job
	this.Type = sType;					// type of job
	this.Source = sSource;					// source device/host
	this.Target = sTarget;					// target device/host

	Job[iRJN] = this;					// the iRJN is the random job number assigned
}




function Jobs(sAction) {
// performs various tasks related to the jobs of the project
// sAction	init (the jobs listing)		load (a job)	create (a job)	save (a job)	rename (a job)	copy (a job)	delete (a job)	run (a job)
// Param2	[Callback] (string or function)	[Callback]	[Callback]	[Callback]	[Callback]	[Callback]	[Callback]	[Callback]
	if (sAction.indexOf('|') == -1) {			// if a redirect hasn't been passed (e.g. 'busy|load'), then we were only passed an action so assign blank value to sRedirect
		var sRedirect = '';
	} else {						// otherwise we were given a redirect, so assign each value
		var sRedirect = sAction.split('|')[1];
		sAction = sAction.split('|')[0];
	}
	var CB = arguments[1] ? arguments[1] : null;		// store any passed callback

	switch(sAction) {
		case "init":
			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=load&T=jobs&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'','','','','',function(){Jobs('s_init',CB)},function(){Jobs('fail|init',CB)},function(){Jobs('busy|init',CB)},function(){Jobs('timeout|init',CB)},function(){Jobs('inactive|init',CB)});
			break;
		case "s_init":
			var j = XML.getElementsByTagName('job');
			var sCombobox = document.getElementById('cmbJobList');

			sCombobox.options.length = 0;		// remove all prior jobs in the list
			for (var I=0; I<j.length; I++)		// now populate with all the current jobs
				{ Add2List(sCombobox,j[I].firstChild.data,j[I].firstChild.data,1,0,0,0); }
			sCombobox.selectedIndex = -1;		// de-select any option initially
			break;


		case "load":
			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=load&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text),'','','','','',function(){Jobs('s_load',CB)},function(){Jobs('fail|load',CB)},function(){Jobs('busy|load',CB)},function(){Jobs('timeout|load',CB)},function(){Jobs('inactive|load',CB)});
			break;
		case "s_load":
			var job = XML.getElementsByTagName("job")[0];
			selListbox('cmbType',job.getAttribute("type"));
			selListbox('cmbStatus',job.getAttribute("enabled"));
			selListbox('cmbCompression',job.getAttribute("compression"));

			var schedule = XML.getElementsByTagName("schedule")[0];
			if (schedule.getAttribute("hour") < 13) {
				document.getElementById('txtHour').value = schedule.getAttribute("hour");
				selListbox('cmbAMPM','am');
			} else {
				document.getElementById('txtHour').value = (schedule.getAttribute("hour") - 12);
				selListbox('cmbAMPM','pm');
			}
			document.getElementById('txtMinute').value = schedule.getAttribute("min");
			selListbox('cmbFrequency',schedule.getAttribute("freq"));
			document.getElementById('txtSchedule').value = schedule.getAttribute("list");

			var cron = XML.getElementsByTagName("cron")[0];
			selListbox('cmbCron',cron.getAttribute("flags"));

			var storage = XML.getElementsByTagName("storage")[0];
			document.getElementById('radStoretype'+storage.getAttribute("type")).checked = true;
			if (storage.getAttribute("type") == 0) {
				document.getElementById('txtStoreAmt').value = storage.getAttribute("value");
				document.getElementById('txtStoreQuota').value = "";		// erase the value of the other textbox
			}
			if (storage.getAttribute("type") == 1) {
				document.getElementById('txtStoreAmt').value = "";		// erase the value of the other textbox
				document.getElementById('txtStoreQuota').value = storage.getAttribute("value");
			}

			var contact = XML.getElementsByTagName("contact")[0];
			document.getElementById('txtPrescript').value = job.getAttribute("prescript");
			document.getElementById('txtPostscript').value = job.getAttribute("postscript");
			selListbox('cmbAlert',contact.getAttribute("alert"));
			document.getElementById('txtAlertExec').value = contact.getAttribute("exec");

			var fs = XML.getElementsByTagName("fs")[0];
			document.getElementById('txtSource').value = fs.getAttribute("source");
			document.getElementById('txtSourceParm').value = fs.getAttribute("sourceParm");
			document.getElementById('txtSourceUser').value = fs.getAttribute("sourceUser");
			document.getElementById('txtSourcePass').value = fs.getAttribute("sourcePass");
			document.getElementById('txtSourceDomn').value = fs.getAttribute("sourceDomn");
			document.getElementById('txtTarget').value = fs.getAttribute("target");
			document.getElementById('txtTargetParm').value = fs.getAttribute("targetParm");
			document.getElementById('txtTargetUser').value = fs.getAttribute("targetUser");
			document.getElementById('txtTargetPass').value = fs.getAttribute("targetPass");
			document.getElementById('txtTargetDomn').value = fs.getAttribute("targetDomn");
			document.getElementById('txtTargetDir').value = fs.getAttribute("targetDir");
			selListbox('cmbSudo',fs.getAttribute("sudo"));

			var tags = XML.getElementsByTagName("tags")[0];
			document.getElementById('txtTag1').value = tags.getAttribute("tag1");
			document.getElementById('txtTag2').value = tags.getAttribute("tag2");
			document.getElementById('txtTag3').value = tags.getAttribute("tag3");
			document.getElementById('txtTag4').value = tags.getAttribute("tag4");

			var params = XML.getElementsByTagName("params")[0];
			if (params.firstChild)
				{ document.getElementById('txtParameters').value = params.firstChild.data; }
			else
				{ document.getElementById('txtParameters').value = ''; }

			temp = job.getAttribute("enabled");
			for (j=0; j<document.getElementById('cmbStatus').options.length; j++)
				{ if (document.getElementById('cmbStatus').options[j].value == temp) { document.getElementById('cmbStatus').selectedIndex = j; break; } }

			var notes = XML.getElementsByTagName("notes")[0];
			if (notes.firstChild)
				{ document.getElementById('txtNotes').value = notes.firstChild.data; }
			else
				{ document.getElementById('txtNotes').value = ''; }
// VER2
//			document.getElementById('chkVerify').checked = job.getAttribute("verify");
//			document.getElementById('chkBatteries').checked = job.getAttribute("batteries");
//			document.getElementById('chkSudo').checked = (fs.getAttribute("sudo") === 'true');	// https://stackoverflow.com/questions/263965/how-can-i-convert-a-string-to-boolean-in-javascript

			Data('reset');
			Data('populate',document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text);		// now populate the divFolders with the contents of the SOURCE

			var include = XML.getElementsByTagName("i");
			if (include.length > 0 ) { document.getElementById('cmbIncludeList').options.length = 0; }		// since Data('reset') add '-ALL DATA-', this will remove that entry if this job has included items
			for (i=0; i<include.length; i++) {
				var item = include[i];
				if (item.firstChild.data == '.')						// if "all data" is returned, then...
					{ Add2List('cmbIncludeList', item.firstChild.data, '-ALL DATA-', 1, 0, 0, 0); }
				else										// otherwise, add what has been specified by the user
					{ Add2List('cmbIncludeList', item.firstChild.data, item.firstChild.data, 1, 0, 0, 0); }
			}
			var exclude = XML.getElementsByTagName("e");
			for (i=0; i<exclude.length; i++) {
				item = exclude[i];
				Add2List('cmbExcludeList', item.firstChild.data, item.firstChild.data, 1, 0, 0, 0);
			}
			break;


		case "create":
			alert("Please note that you will be prompted for the new job name when saving the configuration.");

			document.getElementById("formJob").reset();						// reset the values on the form; NOTE: this is required to reset the form before loading the job below!
			document.getElementById('cmbJobList').selectedIndex = -1;
			document.getElementById('cmbIncludeList').options.length=0;				// reset the data to restore in the 'include' listbox
			document.getElementById('cmbExcludeList').options.length=0;
			Add2List('cmbIncludeList','.','All data',1,0,0,0);					// add a default value to backup all data on the share
			selListbox('cmbJobList',P2);								// now we select the entry that was just added

			sAction = 's_create';									// this is so that any passed callback would execute
			break;


		case "save":
			// if we are saving a new job, then...
			if (document.getElementById('cmbJobList').selectedIndex == -1) {
				var jobname = prompt('What name would you like to give the new backup job?');
				if (jobname == '' || jobname == null) {
					alert('You must enter a name before creating a new backup job, exiting.');
					return false;
				}
				if (ListExists('cmbJobList',jobname,jobname,0,0)) {
					alert('There is already a job with that name, please use another value.');
					return false;
				}
			} else { var jobname = document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text; }

			// make some checks before continuing
			if (document.getElementById('txtHour').value == '') { alert("You must provide the job execution hour before continuing."); return false; }
			if (document.getElementById('txtMinute').value == '') { alert("You must provide the job execution minute before continuing."); return false; }
			if (document.getElementById('txtSchedule').value == '') { alert("You must provide the job execution schedule before continuing."); return false; }
			if (document.getElementById('radStoretype0').checked && ! parseInt(document.getElementById('txtStoreAmt').value)) { alert("You must provide the number of backups to retain before continuing."); return false; }
			if (document.getElementById('radStoretype1').checked && ! parseInt(document.getElementById('txtStoreQuota').value)) { alert("You must provide the amount of backups to retain before continuing."); return false; }
			if (document.getElementById('cmbAlert').selectedIndex > 0 && document.getElementById('txtAlertExec').value == '') { alert("You must provide the contact execution value before continuing."); return false; }
			if (document.getElementById('txtSource').value == '' || document.getElementById('txtTarget').value == '') { alert('You must provide a source and target value before continuing.'); return false; }
			if (document.getElementById('txtSource').value.substring(0,2) == '//' && document.getElementById('txtSourceUser').value == '') { alert("You must provide the source backup username value before continuing."); return false; }
			if (document.getElementById('txtSource').value.substring(0,2) == '//' && document.getElementById('txtSourcePass').value == '') { alert("You must provide the source backup password value before continuing."); return false; }
			if (document.getElementById('txtSource').value.substring(0,1) == '@' && document.getElementById('txtSourceUser').value == '') { alert("You must provide the source backup username value before continuing."); return false; }
			if (document.getElementById('txtSource').value.substring(0,1) == '@' && document.getElementById('txtSourcePass').value == '') { alert("You must provide the source backup password value before continuing."); return false; }
			if (document.getElementById('txtSource').value.substring(0,1) == '%' && document.getElementById('txtSourceUser').value == '') { alert("You must provide the source backup username value before continuing."); return false; }
			if (document.getElementById('txtSource').value.substring(0,1) == '%' && document.getElementById('txtSourcePass').value == '') { alert("You must provide the source backup password value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,2) == '//' && document.getElementById('txtTargetUser').value == '') { alert("You must provide the target backup username value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,2) == '//' && document.getElementById('txtTargetPass').value == '') { alert("You must provide the target backup password value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,1) == '@' && document.getElementById('txtTargetUser').value == '') { alert("You must provide the target backup username value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,1) == '@' && document.getElementById('txtTargetPass').value == '') { alert("You must provide the target backup password value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,1) == '%' && document.getElementById('txtTargetUser').value == '') { alert("You must provide the target backup username value before continuing."); return false; }
			if (document.getElementById('txtTarget').value.substring(0,1) == '%' && document.getElementById('txtTargetPass').value == '') { alert("You must provide the target backup password value before continuing."); return false; }
			document.getElementById('txtStoreAmt').value = parseInt(document.getElementById('txtStoreAmt').value);		// save only the numbers in the value
			document.getElementById('txtStoreQuota').value = parseInt(document.getElementById('txtStoreQuota').value);

			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=save&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(jobname),'formJob','','','','',function(){Jobs('s_save',CB)},function(){Jobs('fail|save',CB)},function(){Jobs('busy|save',CB)},function(){Jobs('timeout|save',CB)},function(){Jobs('inactive|save',CB)});
			break;
		case "s_save":
			alert("The backup job has been saved successfully!");
			if (document.getElementById('cmbJobList').selectedIndex == -1) { Add2List('cmbJobList',DATA['name'],DATA['name'],1,0,1,1); }
			break;


		case "rename":
			if (document.getElementById('cmbJobList').selectedIndex == -1) {
				alert("You must select a job from the 'Saved jobs' list before you can continue.");
				return false;
			}

			var jobname = prompt('What would you like to rename this job to?');
			if (jobname == '' || jobname == null){
				alert('You must enter a name before renaming the selected backup job, exiting.');
				return false;
			}
			if (ListExists('cmbJobList',jobname,jobname,0,0)) {
				alert('There is already a job with that name, please use another value.');
				return false;
			}

			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=rename&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text)+'&new='+escape(jobname),'','','','','',function(){Jobs('s_rename',CB)},function(){Jobs('fail|rename',CB)},function(){Jobs('busy|rename',CB)},function(){Jobs('timeout|rename',CB)},function(){Jobs('inactive|rename',CB)});
			break;
		case "s_rename":
			alert("The backup job has been renamed successfully!");
			ListReplace2('cmbJobList',DATA['name'],DATA['name'],0,0,0);
			break;


		case "copy":
			if (document.getElementById('cmbJobList').selectedIndex == -1) {
				alert("You must select a job from the 'Saved jobs' list before you can continue.");
				return false;
			}

			var jobname = prompt('What would you like the name to be for the new job?');
			if (jobname == '' || jobname == null){
				alert('You must enter a name before copying the selected backup job, exiting.');
				return false;
			}
			if (ListExists('cmbJobList',jobname,jobname,0,0)) {
				alert('There is already a job with that name, please use another value.');
				return false;
			}

			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=copy&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text)+'&new='+escape(jobname),'','','','','',function(){Jobs('s_copy',CB)},function(){Jobs('fail|copy',CB)},function(){Jobs('busy|copy',CB)},function(){Jobs('timeout|copy',CB)},function(){Jobs('inactive|copy',CB)});
			break;
		case "s_copy":
			alert("The backup job has been copied successfully!");
			Add2List('cmbJobList',DATA['name'],DATA['name'],1,0,1,0);
			break;


		case "delete":
			if (document.getElementById('cmbJobList').selectedIndex == -1) {
				alert("You must select a job from the 'Saved jobs' list before you can continue.");
				return false;
			}

			if (confirm('Are you sure you want to delete this job?') == false) { return true; }

			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=delete&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text),'','','','','',function(){Jobs('s_delete',CB)},function(){Jobs('fail|delete',CB)},function(){Jobs('busy|delete',CB)},function(){Jobs('timeout|delete',CB)},function(){Jobs('inactive|delete',CB)});
			break;
		case "s_delete":
			alert("The backup job has been deleted successfully!");
			ListRemove('cmbJobList',0);
			// now reset the page
			document.getElementById("formJob").reset();						// reset the values on the form; NOTE: this is required to reset the form before loading the job below!
			document.getElementById('cmbJobList').selectedIndex = -1;
			document.getElementById('cmbIncludeList').options.length=0;				// reset the data to restore in the 'include' listbox
			document.getElementById('cmbExcludeList').options.length=0;
			Add2List('cmbIncludeList','.','All data',1,0,0,0);					// add a default value to backup all data on the share
			break;


		case "run":
			if (document.getElementById('cmbJobList').selectedIndex == -1) {
				alert("You must select a job from the 'Saved jobs' list before you can continue.");
				return false;
			}

			Status('init',function(){
				// start the job only if web.us gets started succesfully!
				ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=run&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&job='+escape(document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text),'','','','','',function(){Jobs('s_run',CB)},function(){Jobs('fail|run',CB)},function(){Jobs('busy|run',CB)},function(){Jobs('timeout|run',CB)},function(){Jobs('inactive|run',CB)});
			});
			break;
		case "s_run":
			// create an object containing all the job data
			new JobDef(DATA['rjn'], DATA['date'], DATA['pid'], DATA['ppid'], document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text, document.getElementById("cmbType").options[document.getElementById("cmbType").selectedIndex].value, document.getElementById("txtSource").value, document.getElementById("txtTarget").value);

			// add the job to the 'Status' tab
			Status('add',DATA['rjn'],null);
			alert("You can now see the job progress under the 'Status' tab.");
			break;


		case "fail":
			alert("There was a problem performing that action with the job, check the logs for details.");
			break;
		case "busy":
		case "inactive":
		case "timeout":
			Jobs(sRedirect,CB);
			break;

		default:
			alert("You have passed an invalid action value ("+sAction+") to the API call.");
			return false;
	}

	if (sAction.indexOf('s_') == 0 && CB != '' && CB != null) {						// if we have processed a successful return -AND- we were passed a callback, then process it
		if (typeof CB === "function") { CB(); }
		else { eval(CB); }
	}
}




function Keys(sAction,P2) {
// performs the ssh key exchange between the specified device (source or target)
// sAction	exchange
// Param2	which device to exchange with: Source, Target (case sensative)
// Param3	[Callback] (string or function)
	if (sAction.indexOf('|') == -1) {			// if a redirect hasn't been passed (e.g. 'busy|load'), then we were only passed an action so assign blank value to sRedirect
		var sRedirect = '';
	} else {						// otherwise we were given a redirect, so assign each value
		var sRedirect = sAction.split('|')[1];
		sAction = sAction.split('|')[0];
	}
	var CB = arguments[2] ? arguments[2] : null;		// store any passed callback

	switch(sAction) {
		case "exchange":
// LEFT OFF - test this
			if (P2 != 'Source' && P2 != 'Target') { alert("The parameter passed to the Keys API is invalid."); return false; }

			var device = document.getElementById('txt'+P2).value;
			var params = document.getElementById('txt'+P2+'Parm').value;
			var user = document.getElementById('txt'+P2+'User').value;
			var pass = document.getElementById('txt'+P2+'Pass').value;

			if (device.substring(0,1) != '@' && device.substring(0,1) != '%') { alert("You can only exchange keys with an ssh device (which is preceeded by an '@' or '%' symbol)."); return false; }
			if (device == '') { alert("You must provide a device IP address or URL before exchanging keys."); return false; }
			if (user == '') { alert("You must provide a username before exchanging keys."); return false; }
			if (pass == '') { alert("You must provide a password before exchanging keys."); return false; }

			ajax(reqJobs,4,'get',gbl_uriProject+"code/jobs.sh",'A=exchange&T=keys&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&device='+escape(device)+'&params='+escape(params)+'&user='+escape(user)+'&pass='+escape(pass),'','','','','',function(){Keys('s_exchange',P2,CB)},function(){Keys('fail|exchange',P2,CB)},function(){Keys('busy|exchange',P2,CB)},function(){Keys('timeout|exchange',P2,CB)},function(){Keys('inactive|exchange',P2,CB)});
			break;
		case "s_exchange":
			alert("The ssh keys have been exchanged successfully!");
			break;


		case "fail":
			alert("There was a problem performing that action with the job, check the logs for details.");
			break;
		case "busy":
		case "inactive":
		case "timeout":
			Keys(sRedirect,P2,CB);
			break;

		default:
			alert("You have passed an invalid action value ("+sAction+") to the API call.");
			return false;
	}

	if (sAction.indexOf('s_') == 0 && CB != '' && CB != null) {						// if we have processed a successful return -AND- we were passed a callback, then process it
		if (typeof CB === "function") { CB(); }
		else { eval(CB); }
	}
}




function Shedule(sAction) {
// performs various tasks related to the job scheduling
// sAction	show		load		save		toggle (cell)
// Param2	-		-		-		oCell
	switch(sAction) {
		case "show":
			document.getElementById('divPopup').className = document.getElementById('divPopup').className = 'PopupMin';
			document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');

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
					"	<tr><td>Sun</td><td>Mon</td><td>Tue</td><td>Wed</td><td>Thr</td><td>Fri</td><td>Sat</td></tr>" +
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
				"	<input type='button' id='btnSave' value='Save' class='button OTButton space' onClick=\"Shedule('save');\" />" +
				"</div>";

			togglePopup('show');
			document.getElementById('divPopup').innerHTML = HTML;
			if (Mobile) {		// if we are on a mobile device, move to the top (so the popup and overlay are shown correctly) along with disabling scrolling on the main document
				document.body.scrollTop = document.documentElement.scrollTop = 0;	// https://stackoverflow.com/questions/4210798/how-to-scroll-to-top-of-page-with-javascript-jquery
				document.body.style.overflow = 'hidden';
			}

			if (document.getElementById('cmbFrequency').value == 'daily') {			// add the function call to each <td>
				for (var i=0; i<7; i++) { document.getElementById('tblDOW').rows[0].cells[i].addEventListener('click', function(){Shedule('toggle',this)}); }
			} else if (document.getElementById('cmbFrequency').value == 'monthly') {
				for (var i=0; i<2; i++ ) {
					Row = document.getElementById('tblDOM').rows[i];
					for (var j=0; j<15; j++) { Row.cells[j].addEventListener('click', function(){Shedule('toggle',this)}); }
				}
			}

			Shedule('load');
			break;

		case "load":
			var Elm = document.getElementById('txtSchedule');
			var Cell;
			var Values;

			if (document.getElementById('cmbFrequency').value == 'daily') {
				Values = Elm.value.substring(6).split(', ');					// NOTE: the 'substring' removes the 'Every ' portion of the string
				for (var i=0; i<Values.length; i++) {
					for (var j=0; j<7; j++) {
						Cell = document.getElementById('tblDOW').rows[0].cells[j];
						if (Values[i] == Cell.innerHTML) { Cell.className = 'selected'; break; }
					}
				}
			} else if (document.getElementById('cmbFrequency').value == 'monthly') {
				Values = Elm.value.substring(6).split(',');
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
				Values = Elm.value.substring(6).split(' ');
				document.getElementById('txtFreq').value = Values[0];
				selListbox('cmbFreq',Values[1]);
			}
			break;

		case "save":
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

			if (Elm.value != '') { Elm.value = 'Every ' + Elm.value; }
			togglePopup('hide');
			break;

		case "toggle":
			var oCell = arguments[1] ? arguments[1] : null;

			if (oCell.className.indexOf('selected') > -1) { oCell.className = ''; }
			else { oCell.className = 'selected'; }
			break;

		default:
			alert("You have passed an invalid action value ("+sAction+") to the API call.");
			return false;
	}
}

