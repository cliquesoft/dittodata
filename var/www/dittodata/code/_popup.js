// _popup.js	a standard module that provides the relevant page IO for
//		popups used in the project.
//
// Created	2012-10-01 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2019-05-07 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




function togglePopup(strDisplay) {
// toggles whether the popup is shown or hidden
	if (strDisplay == 'show') { strDisplay='block'; } else { strDisplay='none'; }

	document.getElementById('divOverlay').style.display=strDisplay;
	document.getElementById('divPopup').style.display=strDisplay;

	// NOTE: this is so the mobile side work correctly; counters the 'default.js > showHelp()' and 'earn.js > showJob()'
	if (Mobile && strDisplay == 'none') { document.body.style.overflow = 'visible'; }	// NOTE: this MUST be reset to the original value to make this site work correctly
}
