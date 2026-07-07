// _dialog.js	a standard module that provides the relevant page IO for
//		displaying messages and other interactive prompts to users
//
// Created	2021-03-23 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-03-23 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.



function dialog(sType,sMessage) {
// instead of using the ugly built-in alert(), confirm(), and prompt(), this function does the same with rendered popups
// sType	the type of screen to display: alert, confirm, prompt, custom, close
// sMessage	the message displayed on all of those input popups
// [sValues]	sType=prompt: the value that represents the default value, sType=custom: the button name values (up to three) separated by pipes (e.g. "yes|no|cancel"), anything else: blank or null, sType=close: the numeric return value
// [callback]	the optional value that represents the callback code for each button (e.g. function(){...})
// [callback]	NOTE: there can be up to three of these values since we can have a max of three buttons when sType=custom
// [callback]	NOTE: don't have to worry about closing the dialog prompt since that will be handled no matter what
	var HTML='';
	var btn;
	var val = arguments[2] ? arguments[2] : null;
	var cb1 = arguments[3] ? arguments[3] : null;
	var cb2 = arguments[4] ? arguments[4] : null;
	var cb3 = arguments[5] ? arguments[5] : null;
//alert('cb1: ' + arguments[3]);

// LEFT OFF - if a child window has called this function, perhaps have it display in the main 'parent' window
//if (window.top != window.self) {
//alert('we are in a child window');
//return true;
//}

	if (sType != 'close') {										// if a call to show this dialog modal is made, then...
		aDialog.push(new conDialog(sType, sMessage, val, cb1, cb2, cb3));			//   add it to the queue	NOTE: the first call will always be index 0, which is just initially shown (since iDialog processing starts at 1)
		if (document.getElementById('divDialog').style.display == 'block') { return true; }	//   if the popup is already showing, then exit this function
	} else if (sType == 'close' && aDialog.length > 1 && iDialog < (aDialog.length-1)) {		// otherwise, if the user clicked a button -AND- there is a queue of dialogs to show -AND- the user hasn't displayed all them, then...
		if (cb1 != '' && cb1 != null) {								//   process any callback from the prior modal
			if (typeof cb1 === "function") { cb1(); }
			else { eval(cb1); }
		}

		iDialog++;										//   update the global pointer of the aDialog array
		sType = aDialog[iDialog].type;								//   set the values to be processed correctly below
		sMessage = aDialog[iDialog].message;
		val = aDialog[iDialog].values;
		cb1 = aDialog[iDialog].callback1;
		cb2 = aDialog[iDialog].callback2;
		cb3 = aDialog[iDialog].callback3;
	}

	switch(sType) {
		case "close":
//alert('closing: ' + cb1);
			aDialog = [];									// reset all the global queue values now that the user has seen all the messages
			iDialog = 0;

			document.getElementById('divDialog').style.display = 'none';
			if (document.getElementById('divPopup').style.display != 'block')		// if a popup is NOT currently showing, then...
				{ document.getElementById('divOverlay').style.display = 'none'; }	//   also hide the .overlay
// UPDATED 2017/04/27 - the divPopup needs to have its zIndex changed no matter what!
//			else										// otherwise, one is showing, so...
				{ document.getElementById('divPopup').style.zIndex=1002; }		//   move it infront of the .overlay
			if (cb1 != '' && cb1 != null) {							// NOTE: we only have to deal with cb1 here since the button code below ALWAYS passes it as that value!
//alert('we have a callback: ' + typeof cb1);
				if (typeof cb1 === "function") { cb1(); }
				else { eval(cb1); }
			}
			return parseInt(val);
			break;

		case "alert":
		case "confirm":
			HTML += "<p>" + sMessage.replace(/\r\n|\r|\n/g, '<br />').replace(/\t/g, ' &nbsp; &nbsp; ') + "</p>" +
				"<div>" +
				"	<span id='count'>" + (aDialog.length==1 ? '&nbsp;' : (iDialog+1)+'/'+aDialog.length) + "</span>" +
// UPDATED 2017/01/21
//				"	<input type='button' id='btnOK_dialog' value='OK' class='button OTButton' onClick=\"dialog('close','',1,"+cb1+");\" />";
				"	<input type='button' id='btnOK_dialog' value='OK' class='button OTButton' onClick=\"\" />";
//			if (sType == 'confirm') { HTML += "	<input type='button' id='btnCancel_dialog' value='Cancel' class='button OTButton space' onClick=\"dialog('close','',0,"+cb2+");\" />"; }
			if (sType == 'confirm') { HTML += "	<input type='button' id='btnCancel_dialog' value='Cancel' class='button OTButton space' onClick=\"\" />"; }
			HTML += "</div>";

			document.getElementById('divDialog').innerHTML = HTML;
			document.getElementById('btnOK_dialog').onclick=function(){dialog('close','',1,cb1)};
			if (sType == 'confirm') { document.getElementById('btnCancel_dialog').onclick=function(){dialog('close','',0,cb2)}; }
			break;
		case "prompt":
			if (val == null) { val=''; }							// so the input doesn't default to a value of 'null'

			HTML += "<p>" + sMessage.replace(/\r\n|\r|\n/g, '<br />').replace(/\t/g, ' &nbsp; &nbsp; ') + "</p>" +
				"<div>" +
				"	<span id='count'>" + (aDialog.length==1 ? '&nbsp;' : (iDialog+1)+'/'+aDialog.length) + "</span>" +
				"	<input type='textbox' id='txtValue_dialog' value=\""+val+"\" class='textbox' onFocus=\"this.select()\" onKeyUp=\"if(event.keyCode==13){document.getElementById('btnOK_dialog').click();}\" />" +
				"	<input type='button' id='btnOK_dialog' value='OK' class='button OTButton space' onClick=\"\" />" +
				"	<input type='button' id='btnCancel_dialog' value='Cancel' class='button OTButton space' onClick=\"\" />" +
				"</div>";

			document.getElementById('divDialog').innerHTML = HTML;
			document.getElementById('btnOK_dialog').onclick=function(){dialog('close','',1,cb1)};
			document.getElementById('btnCancel_dialog').onclick=function(){dialog('close','',0,cb2)};
			break;
		case "custom":
			btn = val.split('|');
			if (btn.length == 0) { return false; }						// if no buttons were passed, don't even process

			HTML += "<p>" + sMessage.replace(/\r\n|\r|\n/g, '<br />').replace(/\t/g, ' &nbsp; &nbsp; ') + "</p>" +
				"<div>" +
				"	<span id='count'>" + (aDialog.length==1 ? '&nbsp;' : (iDialog+1)+'/'+aDialog.length) + "</span>" +
				"	<input type='button' id='btn1' value=\""+btn[0]+"\" class='button VARButton' onClick=\"\" />";
			if (btn.length > 1) { HTML += "	<input type='button' id='btn2' value=\""+btn[1]+"\" class='button VARButton space' onClick=\"\" />"; }
			if (btn.length > 2) { HTML += "	<input type='button' id='btn3' value=\""+btn[2]+"\" class='button VARButton space' onClick=\"\" />"; }
			HTML += "</div>";

			document.getElementById('divDialog').innerHTML = HTML;
			document.getElementById('btn1').onclick=function(){dialog('close','',1,cb1)};
			if (btn.length > 1) { document.getElementById('btn2').onclick=function(){dialog('close','',2,cb2)}; }
			if (btn.length > 2) { document.getElementById('btn3').onclick=function(){dialog('close','',3,cb3)}; }
			break;
	}

	// WARNING: this MUST come above the dynamic growing of the divDialog below!!!
	if (document.getElementById('divPopup').style.display == 'block') { document.getElementById('divPopup').style.zIndex=1000; }	// if a popup is currently showing, move it behind the .overlay
	document.getElementById('divOverlay').style.display = 'block';
	document.getElementById('divDialog').style.display = 'block';
	if (sType == 'alert' || sType == 'confirm') { document.getElementById('btnOK_dialog').focus(); }		// WARNING: these MUST come after the element's display=block!!!
	if (sType == 'prompt') { document.getElementById('txtValue_dialog').focus(); }

	if (Mobile) { return true; }

	// reset the values for the next message in the queue (if there is one)
	var elm=document.getElementById('divDialog');
	elm.style.height = '170px';
	elm.style.marginTop = '-85px';

	// dynamically grow the <p> until the vertical scrollbar goes away (up to a ceiling of 500px for the divDialog)
	elm = document.getElementById('divDialog').getElementsByTagName('p')[0];
	if (elm.scrollHeight <= elm.clientHeight && elm.innerHTML.indexOf('<br />') == -1 && elm.innerHTML.indexOf('<br>') == -1) {	// if there is no scrollbar for <p> -AND- its message does NOT contain a <br /> or <br>, then...
		if (elm.innerHTML.length < 40) { elm.style.textAlign = 'center'; } else { elm.style.textAlign = 'justify'; }

		if (elm.innerHTML.length < 50) { elm.style.top = '48px'; }
		else if (elm.innerHTML.length < 95) { elm.style.top = '38px'; }
		else if (elm.innerHTML.length < 145) { elm.style.top = '28px'; }
		else if (elm.innerHTML.length < 190) { elm.style.top = '18px'; }
	} else {
		elm.style.top = '8px';
		while ($('#divDialog').height() < 499 && elm.scrollHeight > elm.clientHeight) {		// http://stackoverflow.com/questions/4880381/check-whether-html-element-has-scrollbars
			document.getElementById('divDialog').style.marginTop=(parseInt($("#divDialog").css("margin-top")) - 5)+'px';	// move the top up by 5px
			document.getElementById('divDialog').style.height=($('#divDialog').height() + 10) + 'px';			// move the bottom down by 5px (10-5)
		}
	}
}
