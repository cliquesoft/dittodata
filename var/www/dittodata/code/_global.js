// security.js	a standard module that provides the relevant page IO
//
// Created	2019-09-13 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2019-10-01 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var aKeys = {8:"backspace", 
9:"tab",
13:"enter",
16:"shift",
17:"ctrl",
18:"alt",
19:"pause/break",
20:"caps lock",
27:"escape",
33:"page up",
34:"page down",
35:"end",
36:"home",
37:"left arrow",
38:"up arrow",
39:"right arrow",
40:"down arrow",
45:"insert",
46:"delete",
91:"left window",
92:"right window",
93:"select key",
96:"numpad 0",
97:"numpad 1",
98:"numpad 2",
99:"numpad 3",
100:"numpad 4",
101:"numpad 5",
102:"numpad 6",
103:"numpad 7",
104:"numpad 8",
105:"numpad 9",
106:"multiply",
107:"add",
109:"subtract",
110:"decimal point",
111:"divide",
112:"F1",
113:"F2",
114:"F3",
115:"F4",
116:"F5",
117:"F6",
118:"F7",
119:"F8",
120:"F9",
121:"F10",
122:"F11",
123:"F12",
144:"num lock",
145:"scroll lock",
186:";",
187:"=",
188:",",
189:"-",
190:".",
191:"/",
192:"`",
219:"[",
220:"\\",
221:"]",
222:"'"
};


function validate(objCheck,evtKCode,strRegEx,strName,intAlert) {
// validation to check if the input is incorrect
// objCheck	the form object to check its value (typically passed as 'this')
// evtKCode	the event that was fired on the object (typically passed as 'event')
// strRegEx	the regular expression to check against (e.g. '^[0-9a-zA-Z]+$')
// strName	the name of the field to reference in the error message
// intAlert	if the user needs to be alerted, or just exit silently: 0=silent, 1=alert
// NOTES
//		https://stackoverflow.com/questions/10940137/regex-test-v-s-string-match-to-know-if-a-string-matches-a-regular-expression

	// WARNING: do NOT include the 'g' flag since it may make the test() call fail (see the link above for details)		var RegEx = new RegExp(strRegEx,"g");
	if (strRegEx.substring(0,1) != '!') { var RegEx = new RegExp(strRegEx); } else { var RegEx = new RegExp(strRegEx.substring(1)); }
	var Value = strRegEx.replace(/^\!\[|^\^\[|^\[\^|^\[|\]\+\$$|\]\*\$$|\]\$$|\$\]$|\]$/g, '');	// remove any RegEx formatting so we can isolate just the characters that are (dis)allowed

	Value = Value.replace('0-9', '0-9, ');			// adjust the values so the display is readable for the user
	Value = Value.replace('A-Z', 'A-Z, ');
	Value = Value.replace('a-z', 'a-z, ');
	if (Value.lastIndexOf(', ') == Value.length-2)		// if one of the above replacements occured, but there are no trailing single characters, then erase the ', ' postfix
		{ Value = Value.substring(0,Value.length-2); }
	else if (Value.indexOf(', ') > -1)			// if one of the above replacements occured, then comma separate all following single characters
		{ Value = Value.substring(0,Value.lastIndexOf(', '))+', '+Value.substring(Value.lastIndexOf(', ')+2).split('').join(', '); }
	else							// otherwise we have no groups of characters, so separate them all as single characters
		{ Value = Value.split('').join(', '); }

	if (strRegEx.substring(0,1) == '[') {			// if we need to process valid, allowed characters, then...
		if (RegEx.test(objCheck.value)) {
			if (evtKCode && intAlert)
				{ alert("The \""+aKeys[evtKCode.keyCode]+"\" character is not allowed in this value."); }	// String.fromCharCode(evtKCode.keyCode)
			else if (! evtKCode && intAlert)
				{ alert("There is an invalid character in the \""+strName+"\" value. The\nallowed characters are: "+Value); }
			setTimeout(function(){objCheck.focus();},100);		// put the focus back to the form element that needs addressing
			return false; 
		}

	} else if (strRegEx.substring(0,1) == '^') {		// if we need to process valid, allowed characters, then...
		if (! RegEx.test(objCheck.value)) {
			if (evtKCode && intAlert)
				{ alert("The \""+aKeys[evtKCode.keyCode]+"\" character is not allowed in this value."); }
			else if (! evtKCode && intAlert)
				{ alert("There is an invalid character in the \""+strName+"\" value. The\nallowed characters are: "+Value); }
			setTimeout(function(){objCheck.focus();},100);
			return false; 
		}

	} else if (strRegEx.substring(0,1) == '!') {		// if we need to process invalid, illegal characters, then...
		if (RegEx.test(objCheck.value)) {
			if (evtKCode && intAlert)
				{ alert("The \""+aKeys[evtKCode.keyCode]+"\" character is not allowed in this value."); }
			else if (! evtKCode && intAlert)
				{ alert("There is an invalid character in the \""+strName+"\" value. The\ndisallowed characters are: "+Value); }
			setTimeout(function(){objCheck.focus();},100);
			return false; 
		}
	}
	return true;
}


function copy2Clipboard(Element) {
// copies text to the clipboard
// https://www.w3schools.com/howto/howto_js_copy_clipboard.asp
// NOTE: the element has to be visible for this function to work!!!
	var Elm = (typeof Element === "object") ? Element : document.getElementById(Element);

	// Select the text field
	Elm.select();
	Elm.setSelectionRange(0, 99999);	// For mobile devices

	// Copy the text inside the text field
	document.execCommand("copy");

	// Alert the copied text
	alert("The text has been copied to the clipboard!");
}

