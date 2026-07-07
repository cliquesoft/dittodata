// gallery.admin.js	a standard module that provides the relevant page IO.
//
// Created	2019-10-16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2020-07-24 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqGalleryAdmin;					// used to request the "News" content via AJAX
var strGalleryPrefix = 'data/gallery/';			// the fixed path prefix where the gallery images are stored	WARNING: this MUST have a trailing '/' character!
var strGalleryPath = '';				// the variable extension portion of the path										LEFT OFF - rename sGallerySuffix?
var strGalleryThumb = '200x200';			// if the uploaded file should have a thumbnail created for it; to enable this functionality pass the dimensions desired as the value in WxH format (e.g. 800x600)
var intGalleryClick = 0;				// indicator if the plugin was clicked (value=1) or files dragged (value=0)



function adjGallery(strAction,strObject) {
// shows interface to upload pictures
// strObject	the object to write the contents into
// [strPath]	the path (and filename) where the images are located; 128 characters max; used with the 'delete' action
// NOTES:
//	- HTML5 id's can be any characters https://stackoverflow.com/questions/70579/what-are-valid-values-for-the-id-attribute-in-html
//	- We can NOT include underscores or periods in the folder name since php replaces periods with underscores (we wouldn't know which underscores were previously periods in this case)
//	- https://www.php.net/manual/en/language.variables.external.php
	switch(strAction) {
		case "init":
			var HTML = '';
			HTML =	"<div id='dropbox' class='dropbox' onClick=\"intGalleryClick=1; this.getElementsByClassName('fd-file')[0].click();\">" +	// NOTE: since the generated <file> is hidden, the onClick here triggers to upload
				//"	<img id='imgClose' src='home/"+gbl_nameUser+"/imgs/close.png' onClick=\"if(event.stopPropagation){event.stopPropagation();} event.cancelBubble=true; document.getElementById('lblTitle').click();\" />" +
				"	<span class='message'>To upload, click this box or drag-and-drop files here from your file manager.</span>" +
				"	<div class='multiple'><input type='checkbox' id='multiple' title=\"Checking this box will enable multiple file selections when uploading\" onClick=\"if(event.stopPropagation){event.stopPropagation();} event.cancelBubble=true;\" /></div>" +
				"	<span id='spanHashes'></span>" +
				"</div>" +
				"<form action='' method='post' id='formGallery'>" +
				"	<div id='divGalleryControls'>" +
				"		<label>Create a new group:</label><input type='textbox' id='txtGalleryGroup' maxlength='64' class='textbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9 \-]','Group Name',1);\" /><input type='button' id='btnCreate' class='button' value='Create' onClick=\"adjGallery('create','"+strObject+"');\" /><input type='button' id='btnImplement' class='button' value='Implement' onClick=\"adjGallery('implement','"+strObject+"');\" />" +
				"	</div>" +
				"	<div id='divGalleryListing'>" +
				"	</div>" +
				"</form>";

				document.getElementById(strObject).innerHTML = HTML;

				initFileDrop('dropbox','','',strGalleryPrefix,'@strGalleryPath',1,strGalleryThumb,function(){adjGallery('callback',strObject);});
				adjGallery('refresh',strObject);				// now refresh the listing to account for the new folder
			break;

		case "callback":
			adjGallery('refresh',strObject);					// refresh the listings of images
			initFileDrop('dropbox','','',strGalleryPrefix,'@strGalleryPath',1,strGalleryThumb,function(){adjGallery('callback',strObject);});	// NOTE: the callback function enables multiple back-to-back click-to-upload
			break;

		case "create":		// create a new group for pictures
			ajax(reqGalleryAdmin,4,'post',"code/_gallery.admin.php",'action=create&target=group&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strGalleryPrefix+strGalleryPath+document.getElementById('txtGalleryGroup').value),'','','btnCreate','','',"adjGallery('create_s',\""+strObject+"\");","adjGallery('fail',\""+strObject+"\");","adjGallery('busy',\""+strObject+"\");","adjGallery('timeout',\""+strObject+"\");","adjGallery('inactive',\""+strObject+"\");");
			break;

		case "create_s":
			document.getElementById('txtGalleryGroup').value = '';			// reset the value to nothing
			adjGallery('refresh',strObject);					// now refresh the listing to account for the new folder
			break;

		case "delete":		// delete an existing group of pictures
			if (arguments[2] == '') { alert("The deletion can not be executed due to the path being blank."); return false; }
			if (!confirm("This will also delete any pictures contained\nwithin the group, are you sure?")) {return 0;}
			ajax(reqGalleryAdmin,4,'post',"code/_gallery.admin.php",'action=delete&target=group&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strGalleryPrefix+strGalleryPath+'/'+arguments[2]),'','','','','',"adjGallery('delete_s',\""+strObject+"\");","adjGallery('fail',\""+strObject+"\");","adjGallery('busy',\""+strObject+"\");","adjGallery('timeout',\""+strObject+"\");","adjGallery('inactive',\""+strObject+"\");");
			break;

		case "delete_s":
			adjGallery('refresh',strObject);					// now refresh the listing to account for the new folder
			break;

		case "configure":	// configure an existing group of pictures
			document.getElementById('divPopup').className = document.getElementById('divPopup').className.replace(/\s*PopupMax/g,'');
			document.getElementById('divPopup').className = document.getElementById('divPopup').className += ' PopupMin';

			HTML =  "<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>" +
				"<h3>&nbsp;Configure&nbsp;</h3>" +
				"<div class='divBody'>" +
				"	<p>" +
				"		Use this screen to rename the group." +
				"	</p>" +
				"	<input type='textbox' id='txtNewName' maxlength='64' value=\""+arguments[2]+"\" class='textbox OFTextbox' onBlur=\"validate(this,null,'[^a-zA-Z0-9 \-]','Group Name',1);\" />" +
				"</div>" +
				"<div class='divButtons'>" +
				"	<input type='button' id='btnClose' value='Close' class='button OTButton space' onClick=\"togglePopup('hide');\" /><input type='button' id='btnSave' value='Save' class='button OTButton' onClick=\"adjGallery('configure_a',&quot;"+strObject+"&quot;,&quot;"+arguments[2]+"&quot;);\" />" +
				"</div>";

			togglePopup('show');
			document.getElementById('divPopup').innerHTML = HTML;
			break;

		case "configure_a":
			ajax(reqGalleryAdmin,4,'post',"code/_gallery.admin.php",'action=rename&target=group&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strGalleryPrefix+strGalleryPath)+'&old='+escape(arguments[2])+'&new='+escape(document.getElementById('txtNewName').value),'','','btnSave','','',"adjGallery('configure_s',\""+strObject+"\");","adjGallery('fail',\""+strObject+"\");","adjGallery('busy',\""+strObject+"\");","adjGallery('timeout',\""+strObject+"\");","adjGallery('inactive',\""+strObject+"\");");
			break;

		case "configure_s":
			togglePopup('hide');
			adjGallery('refresh',strObject);					// now refresh the listing to account for the new folder
			break;


		case "implement":	// implment moving (into a group), deleting, re-ordering (within a group) of pictures
			ajax(reqGalleryAdmin,4,'post',"code/_gallery.admin.php",'action=implement&target=changes&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strGalleryPrefix+strGalleryPath),'formGallery','','btnImplement','','',"adjGallery('implement_s',\""+strObject+"\");","adjGallery('fail',\""+strObject+"\");","adjGallery('busy',\""+strObject+"\");","adjGallery('timeout',\""+strObject+"\");","adjGallery('inactive',\""+strObject+"\");");
			break;

		case "implement_s":
			adjGallery('refresh',strObject);					// now refresh the listing to account for the new folder
			break;


		case "refresh":		// refresh the listing of just-uploaded pictures
			ajax(reqGalleryAdmin,4,'post',"code/_gallery.admin.php",'action=refresh&target=listing&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strGalleryPrefix+strGalleryPath),'','','btnImplement','','',"adjGallery('refresh_s',\""+strObject+"\");","adjGallery('fail',\""+strObject+"\");","adjGallery('busy',\""+strObject+"\");","adjGallery('timeout',\""+strObject+"\");","adjGallery('inactive',\""+strObject+"\");");
			break;

		case "refresh_s":
			var G = XML.getElementsByTagName('group');
			var f = XML.getElementsByTagName('unsorted')[0].getElementsByTagName('file');
			var HTML = '';
			var OPTS = "<option value=\"Unsorted\">Unsorted</option>\n";
			var PRUL = 0;								// if the 'priority' <ul> code has been added yet
			var STOP = 0;
			var INDX = 1;
			var LAST = 0;

			for (var i=0; i<G.length; i++)						// this 'for' loop stores all the groups that images can be transferred into
				{ OPTS += "<option value=\""+G[i].getAttribute('name')+"\">"+G[i].getAttribute('name')+"</option>\n"; }

			// -- process all the grouped images first --

			for (var i=0; i<G.length; i++) {					// these next 'for' loops construct the actual HTML
				var F = G[i].getElementsByTagName('file');

				HTML +=	"<ul id='ulGallery-"+G[i].getAttribute('name').replace(/ /g,':')+"' class='ulGalleryGroup'>\n" +
					"	<li class='title'><img src='home/"+gbl_nameUser+"/imgs/close.png' class='fright' title='Delete' onClick=\"adjGallery('delete','"+strObject+"','"+G[i].getAttribute('name')+"')\" /><img src='home/"+gbl_nameUser+"/imgs/settings.png' class='fleft' title='Configure' onClick=\"adjGallery('configure','"+strObject+"','"+G[i].getAttribute('name')+"')\" />"+G[i].getAttribute('name')+"\n";

				// first lets get the total number of priority images per iterated group
				for (var j=0; j<F.length; j++)
					{ if (parseInt(F[j].getAttribute('priority')) > LAST) {LAST = parseInt(F[j].getAttribute('priority'));} }

				// second lets display any that are priority
				INDX = 1;
				PRUL = 0;
				while (1 == 1) {
					STOP = 1;
					for (var j=0; j<F.length; j++) {
						if (parseInt(F[j].getAttribute('priority')) == INDX) {
							if (! PRUL) { HTML += "	<li class='priority'><ul class='ulGalleryGroupPriority'>\n"; PRUL=1; }	// if the sub-group for priority images has not yet been added, then do so now!
							HTML += "		<li class='image'><img src='"+strGalleryPrefix+strGalleryPath+"/"+G[i].getAttribute('name')+"/"+F[j].firstChild.data+"' /><select size='1' class='listbox' id='"+G[i].getAttribute('name').replace(/ /g,':')+":-:"+F[j].firstChild.data+"'>\n" +
								"			<option value=''></option>\n" +
								"			<optgroup label='Actions'>\n" +
								"				<option value='_delete_'>Delete</option>\n" +
								"				<option value='_normal_'>Normalize</option>\n" +
								"			</optgroup>\n" +
								"			<optgroup label='Groups'>\n" +
								// remove the current group from the listing (since we don't want to move into the same group)
								OPTS.replace("<option value=\""+G[i].getAttribute('name')+"\">"+G[i].getAttribute('name')+"</option>\n", '') +
								"			</optgroup>\n" +
								"			<optgroup label='Priority'>\n";
							for (var k=1; k<=LAST; k++)
								{ HTML += "			<option value='"+k+"'>"+k+"</option>\n"; }
							HTML +=	"			</optgroup>\n" +
								"		    </select>\n";
							STOP = 0;
							INDX++;
						}
					}
					if (STOP) {
						if (PRUL) { HTML += "	    </ul>\n"; }
						break;					// this stop the infinite group when we have processed all the priority images
					}
				}

				// and lastly lets display all non-priority images
				for (var j=0; j<F.length; j++) {
					if (parseInt(F[j].getAttribute('priority'))) { continue; }

					HTML += "	<li class='image'><img src='"+strGalleryPrefix+strGalleryPath+"/"+G[i].getAttribute('name')+"/"+F[j].firstChild.data+"' /><select size='1' class='listbox' id='"+G[i].getAttribute('name').replace(/ /g,':')+":-:"+F[j].firstChild.data+"'>\n" +
						"		<option value=''></option>\n" +
						"		<optgroup label='Actions'>\n" +
						"			<option value='_delete_'>Delete</option>\n" +
						"			<option value='_priority_'>Prioritize</option>\n" +
						"		</optgroup>\n" +
						"		<optgroup label='Groups'>\n" +
						// remove the current group from the listing (since we don't want to move into the same group)
						OPTS.replace("<option value=\""+G[i].getAttribute('name')+"\">"+G[i].getAttribute('name')+"</option>\n", '') +
						"		</optgroup>\n" +
						"	    </select>\n";
				}
				HTML +=	"</ul>\n";
			}


			// -- now process all 'unsorted' images --

			if (f.length > 0) {							// if we have contents in the 'Unsorted' group/album, then...
				HTML +=	"<ul id='ulGallery' class='ulGalleryGroup'>\n" +
					"	<li class='title'>Unsorted\n";

				// first lets get the total number of priority images
				LAST = 0;							// reset the value
				for (var i=0; i<f.length; i++)
					{ if (parseInt(f[i].getAttribute('priority')) > LAST) {LAST = parseInt(f[i].getAttribute('priority'));} }

				// second lets display any that are priority
				INDX = 1;
				PRUL = 0;
				while (1 == 1) {
					STOP = 1;
					for (var i=0; i<f.length; i++) {
						if (parseInt(f[i].getAttribute('priority')) == INDX) {
							if (! PRUL) { HTML += "	<li class='priority'><ul class='ulGalleryGroupPriority'>\n"; PRUL=1; }	// if the sub-group for priority images has not yet been added, then do so now!
							HTML += "		<li class='image'><img src='"+strGalleryPrefix+strGalleryPath+"/"+f[i].firstChild.data+"' /><select size='1' class='listbox' id='"+f[i].firstChild.data+"'>\n" +
								"			<option value=''></option>\n" +
								"			<optgroup label='Actions'>\n" +
								"				<option value='_delete_'>Delete</option>\n" +
								"				<option value='_normal_'>Normalize</option>\n" +
								"			</optgroup>\n" +
								"			<optgroup label='Groups'>\n" +
								OPTS.replace("<option value=\"Unsorted\">Unsorted</option>\n", '') +
								"			</optgroup>\n" +
								"			<optgroup label='Priority'>\n";
							for (var k=1; k<=LAST; k++)
								{ HTML += "			<option value='"+k+"'>"+k+"</option>\n"; }
							HTML +=	"			</optgroup>\n" +
								"		    </select>\n";
							STOP = 0;
							INDX++;
						}
					}
					if (STOP) {
						if (PRUL) { HTML += "	    </ul>\n"; }
						break;					// this stop the infinite group when we have processed all the priority images
					}
				}

				// and lastly lets display all non-priority images
				for (var i=0; i<f.length; i++) {
					if (parseInt(f[i].getAttribute('priority'))) { continue; }

					HTML += "	<li class='image'><img src='"+strGalleryPrefix+strGalleryPath+"/"+f[i].firstChild.data+"' /><select size='1' class='listbox' id='"+f[i].firstChild.data+"'>\n" +
							"		<option value=''></option>\n" +
							"		<optgroup label='Actions'>\n" +
							"			<option value='_delete_'>Delete</option>\n" +
							"			<option value='_priority_'>Prioritize</option>\n" +
							"		</optgroup>\n" +
							"		<optgroup label='Groups'>\n" +
							OPTS.replace("<option value=\"Unsorted\">Unsorted</option>\n", '') +
							"		</optgroup>\n" +
							"	    </select>\n";
				}
				HTML +=	"</ul>\n";
			}

			document.getElementById('divGalleryListing').innerHTML = HTML;
			break;


		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			adjGallery('req',strObject);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			adjGallery('req',strObject);
			break;
		case "fail":
			// the server-side script will handle any messages to the user
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}

