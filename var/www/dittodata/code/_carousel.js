// _carousel.js	Functions that allow carousel interaction in your project
//
// Created	2012/08/28 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2012/09/06 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Proprietary License (CPLv1) has been purchased
// for this device, this software is licensed under the Cliquesoft Public
// License (CPLv2) as found on the Cliquesoft website at www.cliquesoft.org
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




function addCarouselItem(strUL,strContent,strCallback) {
// adds a new <li> to the coursel <ul>
// strUL	the <ul> object ID that is to be adjusted
// strContent	the content to be added inside the newly added <li>
// strCallback	code that should execute after adding the new <li>
// *		all optional following parameters are the attributes that need to be applied to the new <li>
//		NOTE: this MUST be in pairs (e.g. ..., id, 'liIDName', class, 'cssClassName', onClick, "alert('Ive been clicked!');", etc)
   var e=document.getElementById(strUL);
   var li=document.createElement("li");			// create a new <li>

   li.innerHTML=strContent;
   for (var i=3; i<arguments.length; i=i+2) { li.setAttribute(arguments[i],arguments[i+1]); }	// apply all the desired attributes to the newly created <li>
   e.appendChild(li);					// append the new <li> to the END of the <li> list

   if (strCallback != '') { eval(strCallback); }	// execute the callback if it was passed
}


function delCarouselItem(strUL,strLID,strCallback) {
// deletes a new <li> to the coursel <ul>
// strUL	the parent <ul> object ID containing the <li> that is to be deleted
// strLID	the <li> object ID that is to be deleted
// strCallback	code that should execute after adding the new <li>
   var e=document.getElementById(strUL);
   var li=document.getElementById(strLID);

   e.removeChild(li);					// remove the <li>

   if (strCallback != '') { eval(strCallback); }	// execute the callback if it was passed
}


function moveCarousel(strDirection,strUL) {
// moves the Twicker/Ad carousels in the desired direction
// strDirection	which direction should the scrolling go - right|left	NOTE: shouldn't be hard to incorporate up|down
// strUL	the <ul> object id that is to be adjusted
// *		all optional following parameters are the attributes that need to be copied as well (e.g. id, class, onClick, onChange, etc)
   var e=document.getElementById(strUL);
   var li=document.createElement("li");			// create a new <li>

   if (strDirection == 'right') {			// WE'RE SCROLLING RIGHT!
	li.innerHTML=e.childNodes[1].innerHTML;		// copy the first <li>'s contents to the newly created <li>
	for (var i=2; i<arguments.length; i++) { li.setAttribute(arguments[i],e.childNodes[1].getAttribute(arguments[i])); }		// copy all the desired attributes to the newly created <li>
	e.appendChild(li);				// append the new <li> to the END of the <li> list
	e.removeChild(e.childNodes[1]);			// remove the first <li> since we're cycling to the left

	// APPLY ANIMATION
	$('#'+strUL+' li').animate({ marginLeft: '-=50' }, { duration: 'fast' });
	$('#'+strUL+' li').animate({ marginLeft: '+=50' }, {
	   duration: 'slow',
	   easing: 'easeOutBack'
	});

   } else {						// WE'RE SCROLLING LEFT!
	var intLiTotal = e.getElementsByTagName('li').length;		// dynamically stores the total number of <li> elements

	li.innerHTML=e.childNodes[intLiTotal].innerHTML;		// copy the first <li>'s contents to the newly created <li>
	for (var i=2; i<arguments.length; i++) { li.setAttribute(arguments[i],e.childNodes[intLiTotal].getAttribute(arguments[i])); } // copy all the desired attributes
	e.insertBefore(li,e.childNodes[1]);				// insert the new <li> at the BEGINNING of the <li> list
	e.removeChild(e.childNodes[intLiTotal+1]);			// remove the last <li> since we're cycling to the right

	// APPLY ANIMATION
	$('#'+strUL+' li').animate({ marginLeft: '+=50' }, { duration: 'fast' });
	$('#'+strUL+' li').animate({ marginLeft: '-=50' }, {
	   duration: 'slow',
	   easing: 'easeOutBack'
	});
   }
}

