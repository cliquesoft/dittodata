// _clipboard.js	contains javascript functions used throughout the project
//
// Created	2021-03-27 by Dave Henderson (dhenderson@digital-pipe.com or support@digital-pipe.com)
// Updated	2021-03-27 by Dave Henderson (dhenderson@digital-pipe.com or support@digital-pipe.com)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




function copy2clipboard(Textbox) {
// copies text to the clipboard
	var Elm = (typeof Textbox === "object") ? Textbox : document.getElementById(Textbox);

	// select all the text
	Elm.select();
	Elm.setSelectionRange(0, 99999);

	// copy the selected text to the clipboard
	document.execCommand("copy");

	// alert the use that the text has been copied
	alert("The link has been copied to the clipboard!");
}
