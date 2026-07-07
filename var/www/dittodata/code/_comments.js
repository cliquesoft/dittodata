// _comments.js	contains javascript functions used with this page of the project
//
// Created	2020-08-19 by Dave Henderson (dhenderson@digital-pipe.com or support@digital-pipe.com)
// Updated	2020-08-20 by Dave Henderson (dhenderson@digital-pipe.com or support@digital-pipe.com)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.


var reqComments;				// used for AJAX calls via interaction with the 'IO' pane itself




function loadComments(strAction,intPostNo) {
// loads all the deal information
	switch(strAction) {
		case "req":
			ajax(reqDeal,4,'post',gbl_uriProject+"code/_comments.php",'action=load&target=comments&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&nAccountNo='+document.getElementById('nAccountNo').value+'&nPostNo='+intPostNo,'','','','','',function(){loadComments('succ',intPostNo);},function(){loadComments('fail',intPostNo);},function(){loadComments('busy',intPostNo);},function(){loadComments('timeout',intPostNo);},function(){loadComments('inactive',intPostNo);});
			break;
		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			loadComments('req',intPostNo);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			loadComments('req',intPostNo);
			break;
		case "succ":
// VER2 - replace the below with a callback that handles the structure
			var C = XML.getElementsByTagName("comment");
			var HTML = '';

			for (var I=0; I<C.length; I++) {
				HTML += "<li class='comment'><ul>\n" +
					"	<li><img src='home/"+gbl_nameUser+"/imgs/comment.png' />\n" +
					"	<li>"+C[I].getAttribute('date').substr(0,10)+"\n" +
					"    </ul><ul>\n" +
					"    	<li>"+C[I].getAttribute('title')+"\n" +
					"    	<li>"+C[I].firstChild.data.replace(/\n/g, '<br />')+"\n" +
					"    </ul>\n";
			}
			document.getElementById('liComments').innerHTML += HTML;

			document.getElementById('liCommentCount').innerHTML = C.length+' Comment(s)';
			break;
		case "fail":
			// no reason to display anything because the server-side script will handle the message
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}


function saveComment(strAction,intPostNo,Title,Comment) {
// loads all the deal information
// intPostNo	the id of the "post" (e.g. listing) that the comment will be associated with
// Title	the name or object that contains the comment title
// Comment	the name or object that contains the comment
	switch(strAction) {
		case "req":
			var objTitle = (typeof Title === "object") ? Title : document.getElementById(Title);
			var objComment = (typeof Comment === "object") ? Comment : document.getElementById(Comment);

			// check that there is a comment to add
			if (objTitle.value == '') { alert("You must type a comment title before saving."); return false; }
			if (objComment.value == '') { alert("You must type a comment before saving."); return false; }

			ajax(reqDeal,4,'post',gbl_uriProject+"code/_comments.php",'action=save&target=comment&username='+escape(document.getElementById('hidUsername').value)+'&SID='+escape(document.getElementById('hidSID').value)+'&nAccountNo='+document.getElementById('nAccountNo').value+'&nPostNo='+intPostNo+'&sTitle='+escape(objTitle.value)+'&sComment='+escape(objComment.value),'','','','','',function(){saveComment('succ',intPostNo,Title,Comment);},function(){saveComment('fail',intPostNo,Title,Comment);},function(){saveComment('busy',intPostNo,Title,Comment);},function(){saveComment('timeout',intPostNo,Title,Comment);},function(){saveComment('inactive',intPostNo,Title,Comment);});
			break;
		case "busy":
			if (!confirm("There was already a request being processed.\nWould you like to retry?")) {return 0;}
			saveComment('req',intPostNo,Title,Comment);
			break;
		case "timeout":
			if (!confirm("The request timed out communicating with the\nserver. Would you like to retry?")) {return 0;}
			saveComment('req',intPostNo,Title,Comment);
			break;
		case "succ":
// VER2 - replace the below with a callback that handles the clear & hide the comment after submission
			document.getElementById('sCommentTitle').value = '';
			document.getElementById('sComment').value = '';

			toggleComment();
			break;
		case "fail":
			// no reason to display anything because the server-side script will handle the message
			break;
		case "inactive":
			// no reason to display anything because this section isn't applicable to this function
			break;
	}
}

