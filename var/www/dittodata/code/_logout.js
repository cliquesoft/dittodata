// _logout.js	a standard module that provides the relevant page IO for
//		logging users out of the project.
//
// Created	2012-08-15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2020-08-11 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqLogout;					// used to request the "Logout" content via AJAX

function sendLogout(strAction,callback) {
	switch(strAction) {
		case "req":
			ajax(reqLogout,4,'post',gbl_uriProject+"code/_logout.php",'action=logout&target=username&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'','','','','',function(){sendLogout('succ',callback);},function(){sendLogout('fail',callback);},function(){sendLogout('busy',callback);},function(){sendLogout('timeout',callback);},function(){sendLogout('inactive',callback);});
			break;

		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			sendLogout('req',callback);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			sendLogout('req',callback);
			break;
		case "succ":
			// Delete permanent values in hidden objects and/or cookies
			document.getElementById('hidSID').value = '';
			document.getElementById('hidUsername').value = 'guest';
			delCookie('SID','/');
			delCookie('username','/');
			delCookie('first','/');
			delCookie('admin','/');

			// Remove any form objects

			// Make GUI adjustments

			// if these objects are present in the project (e.g. not changing to another screen after logging in), then...
			if (document.getElementById('sStatus_Dashboard')) {
				document.getElementById('sStatus_Dashboard').options[0].text = "Login";		// update the 'Status' listbox values to reflect an accurate login state
				document.getElementById('sStatus_Dashboard').options[1].text = "Logged out";
			}

			if (document.getElementById('liUserAccount')) { document.getElementById('liUserAccount').innerHTML = "Guest"; }

			// if any callbacks have been passed, then execute them!
			if (typeof callback !== 'undefined' && callback !== null) {
				if (typeof callback === "function") { callback(); }
				else { eval(callback); }
			}
			break;
		case "fail":
			// no reason to display anything because the server-side script will handle the message
			if (document.getElementById('sStatus_Dashboard'))
				{ document.getElementById('sStatus_Dashboard').selectedIndex = 0; }
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}

