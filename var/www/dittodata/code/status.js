// status.js	functions pertaining to the 'Status' tab.
//
// created	2007/05/12 by Dave Henderson (support@cliquesoft.org)
// updated	2022/05/19 by Dave Henderson (support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqStatus;							// used to request the "Overview" tab I/O via AJAX

function Status(sAction,P2,P3) {
// performs various tasks related to the running backup and restore jobs
// sAction	init (web.us)			add (new job to list)	post (to job)		cleanup (after job)	clear (completed jobs)	shutdown (web.us)			VER2 - populate (existing running jobs?)
// Param2	[Callback] (string or function)	[Callback]		sID (of object)		sID (of object)		[Callback]		-
// Param3	-				-			sText (to display)	[Callback]		-			-
// Param4	-				-			[Callback]		-			-			-
	if (sAction.indexOf('|') == -1) {			// if a redirect hasn't been passed (e.g. 'busy|load'), then we were only passed an action so assign blank value to sRedirect
		var sRedirect = '';
	} else {						// otherwise we were given a redirect, so assign each value
		var sRedirect = sAction.split('|')[1];
		sAction = sAction.split('|')[0];
	}
	var CB = arguments[1] ? arguments[1] : null;		// store any passed callback

	switch(sAction) {
		case "init":
			ajax(reqStatus,4,'get',gbl_uriProject+"code/status.sh",'A=check&T=webus&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'','','','','',function(){Status('s_init',CB)},function(){Status('fail|init',CB)},function(){Status('busy|init',CB)},function(){Status('timeout|init',CB)},function(){Status('inactive|init',CB)});
			break;
		case "s_init":
			// if we need to start web.us, then go ahead and do so, otherwise trigger the callback
			if (DATA['start'] == 'true') { ajaxFIFO(reqStatus,'get','code/status.sh'); }
			break;


		case "add":
			// NOTE: DAT = date and time.
			document.getElementById('scn4').innerHTML += "<ul id='"+Job[P2].RJN+"' class='ulStatus'><li>Job name: <b>"+Job[P2].Name+"</b></li><li>Source: "+Job[P2].Source+"</li><li>Type: "+Job[P2].Type+"</li><li>Started on: "+Job[P2].DAT+"</li><li>Target: "+Job[P2].Target+"</li><li>Status: <label id='status"+Job[P2].RJN+"'>Connecting...</label></li></ul>";
			sAction = 's_add';							// this is so that any passed callback would execute
			break;


		case "post":
			CB = arguments[3] ? arguments[3] : null;				// store any passed callback
			document.getElementById('status'+P2).innerHTML = P3;
			if (P3.indexOf(" job has completed successfully: ") >= 0 || P3.indexOf(" job has stopped due to failure: ") >= 0) { Status('cleanup',P2); }
			sAction = 's_post';							// this is so that any passed callback would execute
			break;


		case "cleanup":
			ajax(reqStatus,4,'get',gbl_uriProject+"code/status.sh",'A=cleanup&T=job&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&rjn='+P2,'','','','','',function(){Status('s_cleanup',P2,P3)},function(){Status('fail|cleanup',P2,P3)},function(){Status('busy|cleanup',P2,P3)},function(){Status('timeout|cleanup',P2,P3)},function(){Status('inactive|cleanup',P2,P3)});
			break;
		case "s_cleanup":
			CB = arguments[2] ? arguments[2] : null;				// store any passed callback
			break;


		case "clear":
			$('#scn4 > ul').each(function () {					// cycles each 'ul' within the 'div' and removes all that are completed
				if ( $(this).text().indexOf(" job has completed successfully: ") >= 0 || $(this).text().indexOf(" job has stopped due to failure: ") >= 0 ) { $(this).remove(); }
			});
			sAction = 's_clear';							// this is so that any passed callback would execute
			break;


		case "shutdown":
			ajax(reqStatus,4,'get',gbl_uriProject+"code/status.sh",'A=shutdown&T=webus&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'','','','','',function(){Status('s_shutdown')},function(){Status('fail|shutdown')},function(){Status('busy|shutdown')},function(){Status('timeout|shutdown')},function(){Status('inactive|shutdown')});
			break;
		case "s_shutdown":
			break;


		case "fail":
			alert("There was a problem performing that action with the job, check the logs for details.");
			break;
		case "busy":
		case "inactive":
		case "timeout":
			Data(sRedirect,P2,P3,CB);
			break;


		default:
			alert("You have passed an invalid action value ("+sAction+") to the API call.");
			return false;
	}

	if (sAction.indexOf('s_') == 0 && CB != '' && CB != null) {				// if we have processed a successful return -AND- we were passed a callback, then process it
		if (typeof CB === "function") { CB(); }
		else { eval(CB); }
	}
}

