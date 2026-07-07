// _bug.js	a standard module that provides the relevant page IO for
//		submitting a bug for this project.
//
// Created	2021-01-02 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-01-02 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqBug;					// used to request the "Bug" content via AJAX


function showBug(strAction,strType,strCategory,strName,strID) {
// displays the popup to allow a user to submit a bug for this website
// strType	the type of the share request, valid values: project, product, website
// strCategory	the optional category that the strType belongs to
// strName	the name of the referenced project (software), product (hardware), website (title)
// strID	the ID of the referenced project (software), product (hardware), website (url)
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMin/g,'');
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');

   switch(strAction) {
	case "req":
//		if (document.getElementById('hidUsername').value == '' && strType == 'person')
//			{ if (!confirm("If you would like to link the referral to your account\n(for any applicable credit), you must be logged in\nbeforehand. Would you like to continue without\nbeing signed in?")) {return 0;} }

		var HTML = '';

		HTML =	"<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>" +
			"<h3>&nbsp;Submit a Bug&nbsp;</h3>" +
			"<div class='divBody divBodyFull'>" +
			"	<p>" +
			"		Although we thoroughly test our projects, bugs are an unfortunate reality of all software. To help our staff identify and resolve these " +
			"		instances, we've included the below form for convenience. Please note, this website does <u>not</u> record this information." +
			"	</p>" +
			"	<form action='' id='formBug'>" +
			"	<input type='hidden' id='hidType' value='"+strType+"' /><input type='hidden' id='hidCategory' value=\""+strCategory+"\" /><input type='hidden' id='hidName' value=\""+strName+"\" /><input type='hidden' id='hidID' value='"+strID+"' />" +
			"	<ul>" +
			"		<li><label>Your name</label><input type='textbox' id='txtBugName' maxlength='64' class='textbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9 _\\\\-]','Your Name',1);\" />" +
			"		<li><label>Your email</label><input type='textbox' id='txtBugEmail' maxlength='128' class='textbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9\\\\.@_\\\\-]','Your Email',1);\" />" +
			"		<li><label>Bug details</label><textarea id='txtBugDetails' class='textbox' maxlength='256' rows='3' onBlur=\"validate(this,null,'![=<>;]','Bug Details',1);\"></textarea>";

		if (! CAPTCHAS) {
			HTML += "		<li><input type='button' id='btnBugSend' value='Send' class='button' onClick=\"sendBug('req');\" />";
		} else {
			HTML += "		<li><label>Captcha</label><input type='textbox' id='txtCaptcha' autocomplete='off' maxlength='16' class='textbox' /><img src='home/"+gbl_nameUser+"/imgs/refresh.png' id='imgCaptcha' onclick=\"reCaptcha('')\" title='Refresh the captcha image' /><input type='button' id='btnBugSend' value='Send' class='button' onClick=\"sendBug('req');\" />" +
				"		<li><img src='home/"+gbl_nameUser+"/imgs/busy.gif' id='captcha' />";
		}
		HTML += "	</ul>" +
			"	</form>" +
			"</div>";

		togglePopup('show');
		document.getElementById('divPopup').innerHTML = HTML;
		if (Mobile) {						// if we are on a mobile device, move to the top (so the popup and overlay are shown correctly) along with disabling scrolling on the main document
			document.body.scrollTop = document.documentElement.scrollTop = 0;	// https://stackoverflow.com/questions/4210798/how-to-scroll-to-top-of-page-with-javascript-jquery
			document.body.style.overflow = 'hidden';
		}

		// Fill any form objects
		if (CAPTCHAS) { reCaptcha(''); }

		document.getElementById('txtBugName').focus();		// focus the first form object
		break;
   }
}


function sendBug(strAction) {
// sends the actual submission from the above function
   switch(strAction) {
	case "req":
		if (document.getElementById('txtBugName').value == '') { alert("You must fill out your name before sending the bug submission."); return false; }
		if (document.getElementById('txtBugEmail').value == '') { alert("You must fill out your email address before sending the bug submission."); return false; }
		if (CAPTCHAS) { if(document.getElementById('txtCaptcha').value == ''){alert("You must provide the captcha text before sending the referral."); return false;} }
		if (! /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(document.getElementById('txtBugEmail').value)) {		// https://stackoverflow.com/questions/4964691/super-simple-email-validation-with-javascript
			alert("The email address entered does not appear to be properly formatted. Please check your spelling and try again.");
			return false;
		}

		ajax(reqBug,4,'post',gbl_uriProject+"code/_bug.php",'action=send&target=bug&username='+escape(document.getElementById('hidUsername').value),'formBug','','','','',"sendBug('succ');","sendBug('fail');","sendBug('busy');","sendBug('timeout');","sendBug('inactive');");
		break;

	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		sendBug('req');
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		sendBug('req');
		break;
	case "succ":
		togglePopup('hide');
		break;
	case "fail":
		// obtain a new captcha if the user failed for any reason (if this was enabled)
		if (CAPTCHAS) { reCaptcha(''); }
		// the server-side script will handle any messages to the user
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}
