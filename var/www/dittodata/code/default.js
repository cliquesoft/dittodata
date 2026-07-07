// default.js	Various functions used in the default tab of the project
//
// Created	2022/04/21 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
// Updated	2022/05/09 by Dave Henderson (dhenderson@cliquesoft.org or support@cliquesoft.org)
//
// Unless a valid Cliquesoft Private License (CPLv1) has been purchased for your
// device, this software is licensed under the Cliquesoft Public License (CPLv2)
// as found on the Cliquesoft website at www.cliquesoft.org.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the appropriate Cliquesoft License for details.




function adjButtons(intNum) {
// this function changes which buttons are displayed in the "header" portion of the screen to indicate what the user can do per screen.
	switch(intNum) {
	   case 0:	// Overview
		document.getElementById('divBtn').innerHTML = "<input type='button' id='btnDownloadLogs' value='Download Logs' class='btnHeader curved' onClick=\"downloadLogs('req')\" />";
		break;
	   case 1:	// Jobs
		document.getElementById('divBtn').innerHTML = "<input type='reset' id='btnNew' value='New' class='btnHeader curved' onClick=\"Jobs('create');\" />" +
			"<input type='button' id='btnRename' value='Rename' class='btnHeader curved' onClick=\"Jobs('rename');\" />" +
			"<input type='button' id='btnCopy' value='Copy' class='btnHeader curved' onClick=\"Jobs('copy');\" />" +
			"<input type='button' id='btnDel' value='Delete' class='btnHeader curved' onClick=\"Jobs('delete');\" />" +
			"<input type='button' id='btnSave' value='Save' class='btnHeader curved' onClick=\"Jobs('save');\" />";
		break;
	   case 2:	// Data
		document.getElementById('divBtn').innerHTML = "<input type='button' id='btnReset' value='Reset' class='btnHeader curved' onClick=\"Data('reset');\" />" +
			"<input type='button' id='btnDataSave' value='Save' class='btnHeader curved' onClick=\"Data('save',document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].value,'formData');\" />";

		// used to try to stop an error from happening if the user loads the weblication and goes directly to this tab (while the other ajax calls are being made)
		if (document.getElementById('cmbJobList').options.length == -1 || document.getElementById('cmbJobList').selectedIndex == -1) {
			alert("You must load a job from the 'Jobs' tab before interacting with this screen.");
			return false;
		}

		// if no data has been loaded, perhaps there is an issue with the job config, try to load again if the user switches back to this tab (after hopefully making changes)
		if (document.getElementById('divFolders').innerHTML == '\n<ul class="jqueryFileTree" style="">\n</ul>' || document.getElementById('divFolders').innerHTML == '\n<ul class="jqueryFileTree" style="display: none;">\n</ul>')
			{ Data('populate',document.getElementById('cmbJobList').options[document.getElementById('cmbJobList').selectedIndex].text); }
		break;
	   case 3:
		document.getElementById('divBtn').innerHTML = "<input type='button' id='btnDownloadLogs' value='Download Logs' class='btnHeader curved' onClick=\"downloadLogs('req')\" />";
		break;
	   case 4:
// VER 2 - "<input type='button' id='btnStop' value='Stop' class='btnHeader curved' onClick=\"if(intJobPID==0){alert('Please click on a job to stop before clicking this button.'); return false;} if(confirm('Are you sure you want to stop the job?')==true){stopJob(rjReq,'btnStop');}\" />&nbsp;" +
		document.getElementById('divBtn').innerHTML = "<input type='button' id='btnClearCompleted' value='Clear Completed' class='btnHeader curved' onClick=\"Status('clear',null,null);\" />";
		break;
	   case 5:
		document.getElementById('divBtn').innerHTML = "&nbsp;";
		break;
	}
}

