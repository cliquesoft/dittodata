// overview.js	functions pertaining to the 'Overview' tab.
//
// created	2007/05/12 by Dave Henderson (support@cliquesoft.org)
// updated	2022/05/04 by Dave Henderson (support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqOverview;						// used to request the "Overview" tab I/O via AJAX

function Logs(sAction) {
// performs various tasks related to the logs of the jobs
// sAction	load					download
// Param2	sLog (name of the log to load)		[Callback] (string or function)
// Param3	sDiv (div name to load the log)		unused
// Param4	[Callback] (string or function)		unused
	if (sAction.indexOf('|') == -1) {			// if a redirect hasn't been passed (e.g. 'busy|load'), then we were only passed an action so assign blank value to sRedirect
		var sRedirect = '';
	} else {						// otherwise we were given a redirect, so assign each value
		var sRedirect = sAction.split('|')[1];
		sAction = sAction.split('|')[0];
	}
	if (sAction == 'load' || sAction == 's_load') {		// store passed values and optional callback
		var P2 = arguments[1] ? arguments[1] : null;
		var P3 = arguments[2] ? arguments[2] : null;
		var P4 = arguments[3] ? arguments[3] : null;
		var CB = P4;
	}
	if (sAction == 'download' || sAction == 's_download') {
		var P2 = arguments[1] ? arguments[1] : null;
		var P3 = null;
		var P4 = null;
		var CB = P2;
	}

	switch(sAction) {
		case "load":
			ajax(reqOverview,4,'get',gbl_uriProject+"code/overview.sh",'A=load&T=log&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&log='+escape(P2),'','','','','',function(){Logs('s_load',P2,P3,P4)},function(){Logs('fail|load',P2,P3,P4)},function(){Logs('busy|load',P2,P3,P4)},function(){Logs('timeout|load',P2,P3,P4)},function(){Logs('inactive|load',P2,P3,P4)});
			break;
		case "s_load":
			var l = XML.getElementsByTagName('log')[0];
			document.getElementById(P3).innerHTML = "<pre>"+l.firstChild.data+"</pre>";
			break;

		case "download":
			ajax(reqOverview,4,'get',gbl_uriProject+"code/overview.sh",'A=compress&T=logs&UN='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'','','','','',function(){Logs('s_download',P2,P3,P4)},function(){Logs('fail|download',P2,P3,P4)},function(){Logs('busy|download',P2,P3,P4)},function(){Logs('timeout|download',P2,P3,P4)},function(){Logs('inactive',P2,P3,P4)});
			break;
		case "s_download":
			ajaxDownload('get',gbl_uriProject+'code/overview.sh','A=download&T=tarball','application/x-gzip',null);

		case "fail":
			break;
		case "busy":
		case "inactive":
		case "timeout":
			Logs(sRedirect,P2,P3,P4);
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

