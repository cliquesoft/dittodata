// _text.js	a standard module that provides the relevant page IO for
//		texting people.
//
// Created	2021-04-07 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2021-04-09 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqText ='';							// used for AJAX calls via interaction with the 'IO' pane itself


function text(strAction) {
// processes texts created by the user
	switch(strAction) {
		// Loads the "Scheduled Texts" table
		case "load":
			ajax(reqText,4,'post',gbl_uriProject+"code/_text.php",'action=load&target=text','','','','','',"text('s_load');","text('fail');","text('busy','"+strAction+"');","text('timeout','"+strAction+"');","text('inactive');");
			break;
		case "s_load":
			var HTML = '<tr><th>&nbsp;</th><th>Date</th><th>Time</th><th>Receipient</th><th>Message</th></tr>';
			var TIME = '';
			var time = '';
			if (XML.getElementsByTagName("text")) { var t = XML.getElementsByTagName("text"); } else { var t = []; }

			for (var i=0; i<t.length; i++) {
				TIME = t[i].getAttribute('time').split(':');
				if (parseInt(TIME[0]) > 12) { time = (parseInt(TIME[0]) - 12)+':'+TIME[1]+'pm'; } else { time = parseInt(TIME[0])+':'+TIME[1]+'am'; }

				HTML += "<tr><td><img src='home/"+gbl_nameUser+"/imgs/x.png' onClick=\"text('delete',"+t[i].getAttribute('id')+");\" /></td><td>"+t[i].getAttribute('date')+"</td><td>"+time+"</td><td>"+t[i].getAttribute('target')+"</td><td>"+t[i].firstChild.data+"</td></tr>\n";
			}

			document.getElementById('tblTexts').innerHTML = HTML;
			break;

		// Sends a text message immediately
		case "send":
			ajax(reqText,4,'post',gbl_uriProject+"code/_text.php",'action=send&target=text','formText','','btnSend','','',"text('s_send');","text('fail');","text('busy','"+strAction+"');","text('timeout','"+strAction+"');","text('inactive');");
			break;
		case "s_send":
			alert('The text message was sent successfully!');
			break;

		// Creates a new "Scheduled Text"
		case "create":
			ajax(reqText,4,'post',gbl_uriProject+"code/_text.php",'action=create&target=text&name='+document.getElementById('lstTarget').options[document.getElementById('lstTarget').selectedIndex].text,'formText','','btnSend','','',"text('s_create');","text('fail');","text('busy','"+strAction+"');","text('timeout','"+strAction+"');","text('inactive');");
			break;
		case "s_create":
			// re-load the "Scheduled Messages" listing
			text('load');

			// clear the form
			document.getElementById('lstTarget').selectedIndex = 0;
			document.getElementById('txtDate').value = '';
			document.getElementById('txtHour').value = '';
			document.getElementById('txtMin').value = '';
			document.getElementById('lstAMPM').selectedIndex = 0;
			document.getElementById('txtMessage').value = '';

			alert("The text message was added successfully!");
			break;

		// Deletes an existing "Scheduled Text"
		case "delete":
			ajax(reqText,4,'post',gbl_uriProject+"code/_text.php",'action=delete&target=text&id='+arguments[1],'','','','','',"text('s_delete');","text('fail');","text('busy',"+arguments[1]+",'"+strAction+"');","text('timeout',"+arguments[1]+",'"+strAction+"');","text('inactive');");
			break;
		case "s_delete":
			// re-load the "Scheduled Messages" listing
			text('load');

			alert("The text message was deleted successfully!");
			break;


		case "fail":
			// no reason to display anything because the server-side script will handle the message
			break;
		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}

			if (arguments.length-1 == 'delete') { text('delete',arguments[1]); }
			else { text(arguments[1]); }
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}

			if (arguments.length-1 == 'delete') { text('delete',arguments[1]); }
			else { text(arguments[1]); }
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}

