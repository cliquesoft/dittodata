// _share.js	a standard module that provides the relevant page IO for
//		sharing the existence of this project with others.
//
// Created	2012-08-15 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2019-11-05 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqShare;					// used to request the "Share" content via AJAX


function showShare(strAction,strType,strCategory,strName,strID) {
// displays the popup to allow a user to share the existence of this project with others
// strType	the type of the share request, valid values: project, product, referral, website
// strCategory	the optional category that the strType belongs to
// strName	the name of the referenced project (software), product (hardware), person (referral), website (title)
// strID	the ID of the referenced project (software), product (hardware), person (referral), website (url)
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMin/g,'');
   document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');

   switch(strAction) {
	case "req":
		if (document.getElementById('hidUsername').value == '' && strType == 'person')
			{ if (!confirm("If you would like to link the referral to your account\n(for any applicable credit), you must be logged in\nbeforehand. Would you like to continue without\nbeing signed in?")) {return 0;} }

		var HTML = '';

		HTML =	"<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>" +
			"<h3>&nbsp;Share&nbsp;</h3>" +
			"<div class='divBody divBodyFull'>" +
			"	<p>";

		if (strType == 'person')
			{ HTML += "		If you enjoy our website, chances are you probably know someone else that will like it too! To help us connect with "; }
		else if (strType == 'website')
			{ HTML += "		If you enjoy this page or find it helpful, chances are you probably know someone else that will like it too! To help us connect with "; }
		else
			{ HTML += "		If you like one of our "+strType+"s, chances are you probably know someone else that will like one too! To help us connect with "; }

		HTML +=	"		those people, we've included the below form for convenience. Please note, this website does <u>not</u> record this information." +
			"	</p>" +
			"	<form action='' id='formShare'>" +
			"	<input type='hidden' id='hidType' value='"+strType+"' /><input type='hidden' id='hidCategory' value=\""+strCategory+"\" /><input type='hidden' id='hidName' value=\""+strName+"\" /><input type='hidden' id='hidID' value='"+strID+"' />" +
			"	<ul>" +
			"		<li><label>Contact name</label><input type='textbox' id='txtContact' maxlength='64' class='textbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9 _\\\\-]','Contact Name',1);\" />" +
			"		<li><label>Contact email</label><input type='textbox' id='txtContactEmail' maxlength='128' class='textbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9\\\\.@_\\\\-]','Contact Email',1);\" />" +
			"		<li><label>Optional message</label><textarea id='txtContactMsg' class='textbox' maxlength='256' rows='3' onBlur=\"validate(this,null,'![=<>;]','Optional Message',1);\"></textarea>";

		if (! CAPTCHAS) {
			HTML += "		<li><input type='submit' id='btnShare' value='Send' class='button' onClick=\"sendShare('req'); return false;\" />";
		} else {
			HTML += "		<li><label>Captcha</label><input type='textbox' id='txtCaptcha' autocomplete='off' maxlength='16' class='textbox' /><img src='home/"+gbl_nameUser+"/imgs/refresh.png' id='imgCaptcha' onclick=\"reCaptcha('')\" title='Refresh the captcha image' /><input type='submit' id='btnShare' value='Send' class='button' onClick=\"sendShare('req'); return false;\" />" +
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

		document.getElementById('txtContact').focus();		// focus the first form object
		break;
   }
}


function sendShare(strAction) {
// sends the actual submission from the above function
   switch(strAction) {
	case "req":
		if (document.getElementById('txtContact').value == '') { alert("You must fill out the contacts name before sending the referral."); return false; }
		if (document.getElementById('txtContactEmail').value == '') { alert("You must fill out the contacts email address before sending the referral."); return false; }
		if (CAPTCHAS) { if(document.getElementById('txtCaptcha').value == ''){alert("You must provide the captcha text before sending the referral."); return false;} }
		if (! /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(document.getElementById('txtContactEmail').value)) {		// https://stackoverflow.com/questions/4964691/super-simple-email-validation-with-javascript
			alert("The email address entered does not appear to be properly formatted. Please check your spelling and try again.");
			return false;
		}

		ajax(reqShare,4,'post',gbl_uriProject+"code/_share.php",'action=send&target=share&username='+escape(document.getElementById('hidUsername').value),'formShare','','','','',"sendShare('succ');","sendShare('fail');","sendShare('busy');","sendShare('timeout');","sendShare('inactive');");
		break;

	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		sendShare('req');
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		sendShare('req');
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


function sendReferral(strAction,intID) {
// sends the actual referral to the user
// intID	the id of the account to obtain information from
   switch(strAction) {
	case "req":
		ajax(reqShare,4,'post',gbl_uriProject+"code/_share.php",'action=send&target=referral&id='+intID,'','','','','',"sendReferral('succ','"+intID+"');","sendReferral('fail','"+intID+"');","sendReferral('busy','"+intID+"');","sendReferral('timeout','"+intID+"');","sendReferral('inactive','"+intID+"');");
		break;

	case "busy":
		if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
		sendReferral('req',intID);
		break;
	case "timeout":
		if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
		sendReferral('req',intID);
		break;
	case "succ":
		document.getElementById('hidReferral').value = DATA['id'];
		document.getElementById('divReferralName').innerHTML = DATA['name'];
		delete DATA['id'];
		delete DATA['name'];
		break;
	case "fail":
		// the server-side script will handle any messages to the user
		break;
	case "inactive":
		// no reason to display anything because this section isn't applicable to this function
		break;
   }
}

