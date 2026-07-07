// captcha.js	a standard module that provides the relevant page IO.
//
// Created	2019-09-13 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-03-25 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




function reCaptcha(intIndex,boolFocus=true,CallBack) {
// obtains a new captcha for the passed set of objects
// intIndex	the index number of the set of objects to adjust
// boolFocus	if the captcha entry textbox should receive focus or not
	document.getElementById('captcha'+intIndex).src='home/'+gbl_nameUser+'/imgs/busy.gif';
	document.getElementById('captcha'+intIndex).src='code/_captcha.php?'+Math.random();
	document.getElementById('txtCaptcha'+intIndex).value='';

	if (! Mobile && boolFocus) { document.getElementById('txtCaptcha'+intIndex).focus(); }

	if (! CallBack) { return true; }			// if no callback was passed, no need to process anything else
	if (typeof CallBack === "function") { CallBack(); }	// otherwise we do, so...
	else { eval(CallBack); }

	return true;
}

