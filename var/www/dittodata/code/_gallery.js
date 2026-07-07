// gallery.js	a standard module that provides the relevant page IO.
//
// Created	2019-08-01 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2020-06-16 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




var reqGallery;					// used to request the "News" content via AJAX
var Gallery = new Array();


function showGallery(strAction,strPath) {
// shows associated pictures
// strPath	the path where the images are located; 128 characters max
// [strPrefix]	the prefix of the filename;  32 characters max
// [intIndex]	the index of an image to start showing (e.g. if the user clicked on image 2 within the set); start @ 0
// [strButtons]	the placement of the previous/next buttons: sides, bottom, top
// [intObjects]	the value indicating which titlebar objects to draw: 0 = none, 1 = title & 'X', 2 = title, 3 = 'X'
// [strElement]	the element name to use when embedding the viewer in a page instead of the default 'divPopup';
//		NOTE: if 'strAction' == 'list', this value can be two separated by a pipe (|) with the first value for the 'list' and the second value for the 'req'
// [strClass]	the class name of all the images that is used to highlight the currently viewed image; NOTE: these three varaibles work together
// [strSelect]	the class name to apply to the "selected" image
// [objClicked] the element that was just clicked (usually passed as 'this') and is used when highlighting the selected image from the listing
//		NOTE: the above can be the an objects id as a string, or the object itself being passed
// NOTES:
// - this function assumes that each <img> is within its own <li> of a <ul>
	if (arguments.length > 2) { var strPrefix = arguments[2]; } else { var strPrefix = ''; }
	if (arguments.length > 3) { var intIndex = arguments[3]; } else { var intIndex = 0; }
	if (arguments.length > 4) { var strButtons = arguments[4]; } else { var strButtons = 'bottom'; }
	if (arguments.length > 5) { var strObjects = arguments[5]; } else { var strObjects = 1; }
	if (arguments.length > 6) { var strElement = arguments[6]; } else { var strElement = 'divPopup'; }
	if (arguments.length > 7) { var strClass = arguments[7]; } else { var strClass = ''; }
	if (arguments.length > 8) { var strSelect = arguments[8]; } else { var strSelect = ''; }
	if (arguments.length > 9) { var objClicked = (typeof arguments[9] === "object") ? arguments[9] : document.getElementById(arguments[9]); } else { var objClicked = null; }

	// if we are NOT embedding the viewer, but need it in the popup (when showing clicked pictures), then...
	if (strAction == 'req' && strElement == 'divPopup') { document.getElementById(strElement).className = 'PopupMax'; }

	switch(strAction) {
		case "req":
			var HTML = '';
			if (strObjects == 1 || strObjects == 3) { HTML =  "<div id='divPopupClose' onClick=\"togglePopup('hide');\">&times;</div>"; }
			if (strObjects == 1 || strObjects == 2) { HTML += "<h3>&nbsp;Pictures&nbsp;</h3>"; }
			HTML +=	"<div class='divBody'>" +
				"	<img src='home/"+gbl_nameUser+"/imgs/busy.gif' class='busy' />" +
				"</div>" +
				"<div class='divButtons'>" +
				"	<input type='button' id='btnClose' value='Close' class='button OTButton space' onClick=\"togglePopup('hide');\" />" +
				"</div>";

			if (strElement == 'divPopup') { togglePopup('show'); }		// if we're using the popup, then show it!
			document.getElementById(strElement).innerHTML = HTML;

			ajax(reqGallery,4,'post',"code/_gallery.php",'action=show&target=pictures&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strPath)+'&prefix='+escape(strPrefix),'','','','','',function(){showGallery('succ',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('fail',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('busy',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('timeout',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('inactive',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);});
			break;

		case "succ":
			var HTML = '';

			Gallery = PIPED.split('|');

			if (strButtons == 'top') { HTML += "<input type='button' id='btnPrev' value='&lt;&lt;' class='button OTButton space' onClick=\"showGallery('prev',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" /><input type='button' id='btnNext' value='&gt;&gt;' class='button OTButton' onClick=\"showGallery('next',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" />"; }
			if (strButtons == 'sides') { HTML += "<input type='button' id='btnPrev' value='&lt;&lt;' class='button OTButton SideButton' onClick=\"showGallery('prev',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" />"; }
			if (Gallery[intIndex] == 'unknown.png')
				{ HTML += "	<img src='home/"+gbl_nameUser+"/imgs/"+Gallery[intIndex]+"' class='gallery' />"; }
			else
				{ HTML += "	<img src='"+strPath+"/"+Gallery[intIndex]+"' class='gallery' "+(Mobile ? "onClick=\"window.open(this.src,'_blank');\"" : '')+" />"; }
			if (strButtons == 'sides') { HTML += "<input type='button' id='btnNext' value='&gt;&gt;' class='button OTButton SideButton' onClick=\"showGallery('next',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" />"; }
			document.getElementById(strElement).getElementsByClassName('divBody')[0].innerHTML = HTML;

			HTML =	"	<span>"+(intIndex+1)+" of "+DATA['count']+"</span>";
			if (strButtons == 'bottom') { HTML += "<input type='button' id='btnPrev' value='&lt;&lt;' class='button OTButton space' onClick=\"showGallery('prev',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" /><input type='button' id='btnNext' value='&gt;&gt;' class='button OTButton' onClick=\"showGallery('next',&quot;"+strPath+"&quot;,'','','','','"+strElement+"','"+strClass+"','"+strSelect+"');\" />"; }
			document.getElementById(strElement).getElementsByClassName('divButtons')[0].innerHTML = HTML;

			delete DATA['count'];
			delete PIPED;

			if (strClass != '') { showGallery('highlight',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked); }

			break;
		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			showGallery('req',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			showGallery('req',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);
			break;
		case "fail":
			// the server-side script will handle any messages to the user
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;


		case "prev":
			var Img = document.getElementById(strElement).getElementsByTagName('img')[0];
			var Elm = document.getElementById(strElement).getElementsByTagName('span')[0];
			var Beg = parseInt(Elm.innerHTML.split(' of ')[0]);
			var End = parseInt(Elm.innerHTML.split(' of ')[1]);
			var Index = 0;

			if (Beg == 1 && Beg == End) { return true; }		// if only 1 image has been returned, disable this functionality

			Img.src = "home/"+gbl_nameUser+"/imgs/busy.gif";	// in between transitions, so a busy indicator

			if (Beg == 1) {
				Index = End;
				Img.src = strPath+'/'+Gallery[End-1];
				Elm.innerHTML = End+' of '+End;
			} else {
				Index = Beg-1;
				Img.src = strPath+'/'+Gallery[Beg-2];
				Elm.innerHTML = (Beg-1)+' of '+End;
			}

			if (strClass != '') {					// if we are highlighting the currently viewed picture, then...
				var Sel = document.getElementsByClassName(strSelect)[0];	// store the object that is currently selected
				var OBJs = Sel.parentNode.children;		// store the child of the parent node	NOTE: we use 'children' instead of 'childNode' since the former is for direct children (<li>'s only), the latter for all children (<li>'s and <img>'s)

				showGallery('highlight',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,OBJs[Index]);
			}
			break;

		case "next":
			var Img = document.getElementById(strElement).getElementsByTagName('img')[0];
			var Elm = document.getElementById(strElement).getElementsByTagName('span')[0];
			var Beg = parseInt(Elm.innerHTML.split(' of ')[0]);
			var End = parseInt(Elm.innerHTML.split(' of ')[1]);
			var Index = 0;

			if (Beg == 1 && Beg == End) { return true; }		// if only 1 image has been returned, disable this functionality

			Img.src = "home/"+gbl_nameUser+"/imgs/busy.gif";	// in between transitions, so a busy indicator

			if (Beg == End) {
				Index = 0;
				Img.src = strPath+'/'+Gallery[Index];
				Elm.innerHTML = '1 of '+End;
			} else {
				Index = Beg;
				Img.src = strPath+'/'+Gallery[Index];
				Elm.innerHTML = (Beg+1)+' of '+End;
			}

			if (strClass != '') {					// if we are highlighting the currently viewed picture, then...
				var Sel = document.getElementsByClassName(strSelect)[0];	// store the object that is currently selected
				var OBJs = Sel.parentNode.children;		// store the child of the parent node	NOTE: we use 'children' instead of 'childNode' since the former is for direct children (<li>'s only), the latter for all children (<li>'s and <img>'s)

				// WARNING: we have to '+1' to 'Index' since the first <li> is the header for the group!!!
				showGallery('highlight',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,OBJs[Index+1]);
			}
			break;

		case "highlight":
			if (! objClicked) { return true; }			// this was added so the initializing of this widget will work (since the selected class will need to be applied via javascript initially)

			var RegEx = new RegExp('\\s*'+strSelect,'g');
			var IMGs = document.getElementsByClassName(strClass);

			for (var i=0; i<IMGs.length; i++) { IMGs[i].className = IMGs[i].className.replace(RegEx,''); }
			objClicked.className += ' '+strSelect;
			break;

		case "list":							// lists the image groups
			ajax(reqGallery,4,'post',"code/_gallery.php",'action=list&target=pictures&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&path='+escape(strPath)+'&prefix='+escape(strPrefix),'','','','','',function(){showGallery('list_s',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('fail',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('busy',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('timeout',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);},function(){showGallery('inactive',strPath,strPrefix,intIndex,strButtons,strObjects,strElement,strClass,strSelect,objClicked);});
			break;

		case "list_s":
			var HTML = '';
			var Elm  = strElement;
			var G = XML.getElementsByTagName('group');

			// check if the 'strElement' is actually two values or not (which needs to be split - one for this action, and one for the 'req' action)
			if (strElement.indexOf('|')) {
				Elm = strElement.substr(0,strElement.indexOf('|'));		// store the first value
				strElement = strElement.substr(strElement.indexOf('|')+1);	// store the second value
			}

			for (var i=0; i<G.length; i++) {			// this 'for' loop processes all the image groups
				var I = G[i].getElementsByTagName('image');
				HTML += "<ul class='ulGroup'>\n" +
					"	<li class='Title'>"+G[i].getAttribute('title')+"\n";

				for (var j=0; j<I.length; j++)			// now process each image within the iterated group
					{ HTML += "	<li class='Image' onClick=\"showGallery('req',&quot;"+G[i].getAttribute('path')+"&quot;,'"+strPrefix+"',"+j+",'"+strButtons+"',"+strObjects+",'"+strElement+"','"+strClass+"','"+strSelect+"',this);\"><img src=\""+I[j].getAttribute('src')+"\" />\n"; }

				HTML += "</ul>\n";
			}

			document.getElementById(Elm).innerHTML = HTML;
			break;
	}
}

