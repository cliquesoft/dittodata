// contact.js	a standard module that provides the relevant page IO.
//
// Created	2019-02-26 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2019-09-13 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqContact;				// used to send the "Contact Us" content via AJAX


function sendMsg(strAction) {
// sends the email filled out by the user
   switch(strAction) {
	case "req":
		if (document.getElementById('txtSender').value == '') { alert("Please enter your name before sending the email."); return false; }
		if (! /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(document.getElementById('txtEmail').value)) {		// https://stackoverflow.com/questions/4964691/super-simple-email-validation-with-javascript
			alert("The email address entered does not appear to be properly formatted. Please check your spelling and try again.");
			return false;
		}
		if (document.getElementById('txtSubject').value == '') { alert("Please enter a subject for the email before sending."); return false; }
		if (document.getElementById('txtMsg').value == '') { alert("You must enter a message for our staff before sending the email."); return false; }
		if (document.getElementById('txtCaptcha1').value == '') { alert("Please enter the captcha text before sending the email."); return false; }

// ADJUSTED 2019/08/28 - for this project only
//		ajax(reqContact,4,'post',gbl_uriProject+"/modules/default/contact.php",'','formMail',true,'btnSend','','',"sendMsg('succ');","sendMsg('fail');","sendMsg('busy');","sendMsg('timeout');","sendMsg('inactive');");
		ajax(reqContact,4,'post',"code/_contact.php",'','formMail',true,'btnSend','','',"sendMsg('succ');","sendMsg('fail');","sendMsg('busy');","sendMsg('timeout');","sendMsg('inactive');");
		break;

	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		sendMsg('req');
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		sendMsg('req');
		break;
	case "succ":
		// the below resets the form
		document.getElementById('formMail').reset();

		// Fill any form objects
		if (document.getElementById('txtCaptcha1')) {			// obtain a (new) captcha if the user failed for any reason (if this was enabled)
// ADJUSTED 2019/08/28 - for this project only
//			document.getElementById('captcha1').src='libraries/php/_captcha.php?'+Math.random();	// get a new captcha picture
			document.getElementById('captcha1').src='code/_captcha.php?'+Math.random();	// get a new captcha picture
			document.getElementById('txtCaptcha1').value = '';	// remove the prior captcha text
		}
		break;
	case "fail":
		// obtain a new captcha if the user failed for any reason (if this was enabled)
		if (document.getElementById('txtCaptcha1')) {
// ADJUSTED 2019/08/28 - for this project only
//			document.getElementById('captcha1').src='libraries/php/_captcha.php?'+Math.random();	// get a new captcha picture
			document.getElementById('captcha1').src='code/_captcha.php?'+Math.random();	// get a new captcha picture
			document.getElementById('txtCaptcha1').value = '';	// remove the prior captcha text
			document.getElementById('txtCaptcha1').focus();		// go to where the user can enter the new captcha text
		}

		// no reason to display anything because this section isn't applicable to this function
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}
