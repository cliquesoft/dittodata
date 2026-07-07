// _login.js	a standard module that provides the relevant page IO for
//		logging users into the project.
//
// Created	2012-08-15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-03-25 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqLogin;					// used to request the "Login" content via AJAX


function showLogin(strAction,callback) {
// displays the login screen to the user
	switch(strAction) {
		case "req":
			togglePopup('show');

			HTML =	"<div id='divPopupClose' onClick=\"\">&times;</div>" +		// togglePopup('hide'); document.getElementById('sStatus_Dashboard').selectedIndex=1;
				"<h3>&nbsp;Login&nbsp;</h3>" +
				"<div class='divBody divBodyFull'>" +
				"	<p>" +
				"		While designated information found here is open to the public, those that have a working account have additional" +
				"		access to content from other members. Use this form to login with your active account." +
//				"		While all the information found here is open to the public, those that have a working account have additional" +
//				"		abilities like editing and creating issues. Use this form to login with your active account." +
				"		This program contains sensative information so you must login with a valid account before being able to access" +
				"		any of the data. If you have trouble, try the services below or contact your support staff." +
				"	</p>" +
				"	<form action='' id='formLogin'>" +
				"	<ul>" +
				"		<li><label>Username</label><input type='textbox' id='Username' placeholder='Username' maxlength='32' class='textbox' onKeyUp=\"this.value=this.value.toLowerCase();\" onBlur=\"validate(this,null,'[^a-zA-Z0-9@\\\\._\\\\-]','Username',1);\" />" +
				"		<li><label>Password</label><input type='password' id='Password' placeholder='Password' maxlength='32' class='textbox encrypted' onBlur=\"validate(this,null,'![=<>;]','Password',1);\" />";

			if (! CAPTCHAS) {
				HTML +=	"		<li><input type='button' id='btnLogin' value='Login' class='button' />" +
					"	</ul>" +
					"	</form>";
			} else {
				HTML +=	"		<li><label>Captcha</label><input type='textbox' id='txtCaptcha' placeholder='Captcha' class='textbox' autocomplete='off' onBlur=\"validate(this,null,'[^a-zA-Z \\\\-]','Captcha',1);\" /><img src='home/"+gbl_nameUser+"/imgs/webbooks.refresh.png' id='imgCaptcha' onclick=\"reCaptcha('');\" title=\"Refresh the captcha image\" /><input type='button' id='btnLogin' value='Login' class='button' />" +
					"		<li><img src='' id='captcha' />" +
					"	</ul>" +
					"	</form>";
			}

			HTML +=	"	<form action='' id='formServices'>" +
				"	<ul>" +
				"		<li><label>Reset Password</label>&nbsp;" +
				"		<li><select id='lstPasswordKey' size='1' class='listbox'><option value='username'>Username</option><option value='email'>Email Address</option></select><input type='textbox' id='txtPasswordValue' maxlength='128' class='textbox' placeholder='Answer' value='' onBlur=\"validate(this,null,'[^a-zA-Z0-9@\\\\._\\\\-]','Username',1);\" /><input type='button' id='btnSubmit' value='Submit' class='button' onClick=\"resetPassword('req');\" />" +
				"		<li><label>Unlock Account</label>&nbsp;" +
				"		<li><select id='lstUnlockKey' size='1' class='listbox'><option value='username'>Username</option><option value='email'>Email Address</option></select><input type='textbox' id='txtUnlockValue' maxlength='128' class='textbox' placeholder='Answer' value='' onBlur=\"validate(this,null,'[^a-zA-Z0-9@\\\\._\\\\-]','Username',1);\" /><input type='button' id='btnLookup' value='Lookup' class='button' onClick=\"unlockAccount('req');\" />" +
				"	</ul>" +
				"	</form>" +
				"</div>";

			document.getElementById('divPopup').innerHTML = HTML;
			document.getElementById('btnLogin').onclick = function() {sendLogin('req',callback); };

			if (CAPTCHAS) { reCaptcha(''); }
			document.getElementById('Username').focus();
			break;

		//case "busy":
		//	break;
		//case "timeout":
		//	break;
		//case "succ":
		//	break;
		//case "fail":
		//	break;
		//case "inactive":
		//	break;
	}
}


function sendLogin(strAction,callback) {
// sends the login information to the server for the actual login
	switch(strAction) {
		case "req":
			if (document.getElementById('Username').value == '') { alert("You must enter your account username before attempting to login."); return false; }
			if (document.getElementById('Password').value == '') { alert("You must enter your account password before attempting to login."); return false; }
			if (CAPTCHAS) { if(document.getElementById('txtCaptcha').value == ''){alert("You must enter the captcha text before attempting to login."); return false;} }

			ajax(reqLogin,4,'post',gbl_uriProject+"code/_login.php",'action=login&target=username','formLogin','','btnLogin','','',function(){sendLogin('succ',callback);},function(){sendLogin('fail',callback);},function(){sendLogin('busy',callback);},function(){sendLogin('timeout',callback);},function(){sendLogin('inactive',callback);});
			break;
		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			sendLogin('req',callback);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			sendLogin('req',callback);
			break;
		case "succ":
			var aryAccount = PIPED.split("|");

			// Store permanent values in hidden objects and/or cookies
			document.getElementById('hidSID').value = DATA['SID'];						// stores the SID for user validation when interaction with the website
			document.getElementById('hidUsername').value = document.getElementById('Username').value;	// stores the logged in username
			setCookie('SID', DATA['SID'], null, '/');
			setCookie('username', document.getElementById('Username').value, null, '/');
			if (aryAccount.length == 4) { setCookie('first', aryAccount[0], null, '/'); }			// if the project uses broken down names (e.g. tracker), then...
			setCookie('admin', DATA['admin'], null, '/');

			// Fill any form objects
			gbl_nameUser = document.getElementById('Username').value;

			// Hide the popup
			togglePopup('hide');

			// Make GUI adjustments

			// if these objects are present in the project (e.g. not changing to another screen after logging in), then...
			if (document.getElementById('liUserAccount')) { document.getElementById('liUserAccount').innerHTML = aryAccount[0]; }

			if (document.getElementById('sStatus_Dashboard')) {
				document.getElementById('sStatus_Dashboard').options[0].text = "Logged In";		// update the 'Status' listbox values to reflect an accurate login state
				document.getElementById('sStatus_Dashboard').options[1].text = "Logout";
			}

			// if any callbacks have been passed, then execute them!
			if (typeof callback !== 'undefined' && callback !== null) {
				if (typeof callback === "function") { callback(); }
				else { eval(callback); }
			}
			break;
		case "fail":
			// obtain a new captcha if the user failed for any reason (if this was enabled)
			if (CAPTCHAS) { reCaptcha(''); }
			// no reason to display anything because the server-side script will handle the message
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}


function resetPassword(strAction) {
// resets the account password
   switch(strAction) {
	case "req":
		if (document.getElementById('hidUsername').value != '' && document.getElementById('hidUsername').value != 'guest') { alert("No need to reset the password since you are already logged into your account!"); return false; }
		if (document.getElementById('txtPasswordValue').value == '') { alert("You must provide the answer before resetting your account password."); return false; }

		ajax(reqLogin,4,'post',gbl_uriProject+"code/_login.php",'action=reset&target=password&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'formServices','','btnSubmit','','',"resetPassword('succ');","resetPassword('fail');","resetPassword('busy');","resetPassword('timeout');","resetPassword('inactive');");
		break;
	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		resetPassword('req');
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		resetPassword('req');
		break;
	case "succ":
		break;
	case "fail":
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}


function unlockAccount(strAction) {
// resets the account password
   switch(strAction) {
	case "req":
		if (document.getElementById('hidUsername').value != '') { alert("No need to unlock your account since you are already logged in!"); return false; }
		if (document.getElementById('txtUnlockValue').value == '') { alert("You must provide the answer before unlocking your account."); return false; }

		ajax(reqLogin,4,'post',gbl_uriProject+"code/_login.php",'action=unlock&target=Account&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value),'formServices','','btnLookup','','',"unlockAccount('qa');","unlockAccount('fail');","unlockAccount('busy');","unlockAccount('timeout');","unlockAccount('inactive');");
		break;

	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		unlockAccount('req');
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		unlockAccount('req');
		break;

	case "qa":						// now process the question and answer prompt
		var answer = prompt(PIPED);
		if (! answer) { return false; }

		ajax(reqAccount,4,'post',gbl_uriProject+"code/_login.php",'action=unlock&target=Account&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&id='+DATA['id']+'&answer='+escape(answer),'formServices','','btnLookup','','',"unlockAccount('succ');","unlockAccount('fail');","unlockAccount('busy');","unlockAccount('timeout');","unlockAccount('inactive');");
		delete DATA['id'];				// to prevent contamination between failed calls
		break;

	case "succ":
		delete DATA['id'];				// to prevent contamination between failed calls
		break;
	case "fail":
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}

