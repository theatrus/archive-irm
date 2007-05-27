<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 1999 Yann Ramin
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License (in file COPYING) for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
################################################################################

/**
* Class for handling help requests
*
* @package Helper
*/

class Helper extends IRMMain
{
function Helper(){
	$this->pID=$_REQUEST['pID'];
	$this->device=$_REQUEST['device'];
	$this->device_name=$_REQUEST['device_name'];

	switch($_REQUEST['action']){
		case 'preview':
			$this->helpPreview();
			break;
		case 'add':
			$this->helpAdd();
			break;
		case 'search':
			AuthCheck("post-only");
			if($_REQUEST['is_group'] == 'yes'){
				$this->helpAdd();
			} else {
				$this->search();
			}
			break;
		default:
			$this->HelperIndex();
	}
}

function helperIndex(){
	AuthCheck("post-only");
	commonHeader(_("Welcome to the IRM Help Desk"));
	$this->helpFastTrack();
	# General Tracking.
	PRINT "<h3>" . _("Tracking - This is where you can request help with a problem.") . "</h3>";
	$this->helpRequest();
	commonFooter();
}

function helpFastTrack(){
	if(Config::Get('fasttrack')) {
		# Fast track tracking
		PRINT "<h3>";
		__("Fast Track - use Fast Track templates for common problems");
		PRINT "</h3>";
		$query = "SELECT * FROM fasttracktemplates";
		$DB = Config::Database();
		$data = $DB->getAll($query);

		PRINT "<table>";
		PRINT '<tr class="trackingheader">';
		PRINT "<th>" . _("Auto Fill Selections") . "</th></tr>";
		foreach ($data as $result)
		{
			PRINT '<tr class="trackingdetail">';

			$urlLocation = Config::AbsLoc("users/tracking-fasttrack.php?AUTOFILL=" . $result['ID']);

			PRINT '<td><a href="' . $urlLocation . '">' . $result['name'] . "</a>";
			PRINT "</td>\n";
			PRINT "</td>\n";
		}
		PRINT "</table>";

		PRINT "<hr>";
	}
}

function helpRequest()
{
	PRINT "<table>";
	$this->helpRequestID();	

	if(Config::Get('usenamesearch'))
	{
	//	$this->helpRequestUser();	
		$this->helpRequestDevice();	
	}

	$this->helpRequestSoftware();	

	if(Config::Get('groups'))
	{
		$this->helpRequestGroup();
	}
	PRINT "</table>";
}

// requesting help for a computer
function helpRequestID(){
	$Page = new IrmFactory();

	$requestID = array(
		'method' => 		'get',
		'action' => 		Config::AbsLoc('users/helper-index.php'),
		
		'requestOptionHeader' => _("Enter the IRM Computer ID:"),
		'requestOption' =>	_("IRM ID:"),
		'submitText' =>		_("Continue with IRM ID"),
		
		'hiddenInputs' => '	<input type=hidden name=is_group value="no">
					<input type=hidden name=action value="search">
					<input type="hidden" name="devicetype" value="computer">',
		'cssheader' => 		'trackingheader',	
		'cssdetail' => 		'trackingdetail',

		'input' => 		'<input type=text name=ID size=10>'
	);

	foreach($requestID as $key => $value){
		$Page->assign($key,$value);
	}

	$Page->fetch('helpRequest.html.php');
	$Page->display('helpRequest.html.php');
}

// requesting help for a device like network, printer, etc.
function helpRequestDevice(){
	PRINT '<tr class="trackingheader">';
	PRINT "<td colspan=2>" . _("Or, select the name of the device:") . "</td>";
	PRINT "</tr>\n";

	PRINT '<tr class="trackingdetail">';
	PRINT "<td>" . _("Select device type and device:") . "</td>";
	PRINT "<td>\n";
	$this->selectDeviceType();
	if($this->device != NULL){
		$this->selectDevice();
	}
	PRINT "</td>\n";
	PRINT "</tr>\n";
}

// requesting help for a device like network, printer, etc.
function helpRequestSoftware(){
	PRINT '<form method=get action="'.Config::AbsLoc('users/helper-index.php').'">';
	PRINT '<tr class="trackingheader">';
	PRINT "<td colspan=2>" . _("Or, select the name of the software:") . "</td>";
	PRINT "</tr>\n";

	PRINT '<tr class="trackingdetail">';
	PRINT "<td>" . _("Select software:") . "</td>";
	PRINT "<td>";
	Dropdown_device("software");

	PRINT '<input type="hidden" name="is_group" value="no">';
	PRINT '<input type="hidden" name="deviceType" value="software">';
	PRINT '<input type="hidden" name="action" value="add">';
	PRINT '<input type="submit" value="' . _("Continue with software selection") . '">';
	PRINT '</td>';
	PRINT "</tr>\n";
	PRINT "</form>\n";
}

// requesting help for a group of computers
function helpRequestGroup(){
	PRINT '<form method=get action="'.Config::AbsLoc('users/helper-index.php').'">';
	PRINT '<tr class="trackingheader">';
	PRINT "<td colspan=2>" . _("Or, you can select a group of computers:") . "</td>";
	PRINT "</tr>";

	PRINT '<tr class="trackingdetail">';
	PRINT "<td>" . _("Name:") . "</td>";
	PRINT "<td>";
	//Dropdown_groups("groups", "groupname");
	Dropdown_group_label("groups", "groupname");
	PRINT "<input type=hidden name=is_group value=\"yes\">\n";
	PRINT '<input type=hidden name=action value="add">';
	PRINT '<input type=submit value="' . _("Continue with group selection") . '">';
	PRINT "</td>\n";
	PRINT "</tr>\n";
	PRINT "</form>";
}

function search(){
	commonHeader(_("Tracking - Add Job") ." - " . _("Is this the computer?"));
	__("Please confirm that you entered the correct IRM ID or name.  If the computer matches, simply click on the computer's name below.");
	$this->helpSearch();
	commonFooter();
	}

function helpAdd(){
	global $IRMName,
		$ID,
		$computername,
		$is_group,
		$uemail,
		$uname,
		$other_emails,
		$contents,
		$deviceType;

	AuthCheck("post-only");
	$user = new User($IRMName);
	$type = $user->getType();

	if($type != "post-only")
	{
		if ($uname == "")
		{
			$uname = $user->getFullname();
			$uemail = $user->getEmail();
		}
	}

	$DB = Config::Database();
	
	if($_REQUEST['is_group'] == "no"){
		$qID = $DB->getTextValue($_REQUEST['ID']);
		$tableName = $_REQUEST['deviceType'];
	} else {
		$ID = $DB->getOne('select ID from groups where (name = "' . $_REQUEST['groupname'] .'")');
		$qID = $DB->getTextValue($ID);
		$tableName = "groups";
	}

	$query = "SELECT COUNT(name) FROM " . $tableName . " WHERE (ID = $qID)";

	if (!$DB->getOne($query)) 
	{
		commonHeader(_("Tracking") . " - " . _("Bad ID Number"));
		__("It appears that you have entered an incorrect IRM computer ID or group ID number.");
		PRINT '<a href="' . Config::AbsLoc('users/helper-index.php') . '">' . _("Please try again.") . "</a><br>";
		commonFooter();
		exit();
	}

	commonHeader(_("Tracking") . " - " . _("Add Job"));

	__("You can use this form to submit a problem report or request help with a computing resource in your organization.  Please fill out the entire form as clearly as possible.");
	PRINT "<hr noshade>";

	$qID = $DB->getTextValue($_REQUEST['ID']);

	if($is_group == "yes")
	{
		$groupQuery = "SELECT ID FROM groups WHERE (name='" . $_REQUEST['groupname'] . "')";
		$groupID = $DB->getOne($groupQuery);

		$query = "SELECT tracking.ID, LEFT(tracking.contents, 120) as contents";
		$query .= " FROM tracking, comp_group";
	//	$query .= " WHERE comp_group.comp_id=tracking.computer";
//		$query .= " AND comp_group.group_id=$groupID";
		$query .= " WHERE comp_group.group_id=$groupID";
		$query .= " AND tracking.is_group='yes'";
		$query .= " AND tracking.status <> 'complete'";
	} else {
		$query = "SELECT ID, LEFT(contents, 120) as contents FROM tracking";
		$query .= " WHERE status <> 'complete'";
		
		if(!$_REQUEST['ID'] == null)
		{
			$query .= " AND computer=$qID";
		}

		if(!$_REQUEST['deviceType'] == null)
		{
			$query .= " AND device='" . $_REQUEST['deviceType'] . "'";
		}
		$query .= " AND is_group='no'";
	}

	$data = $DB->getAll($query);

	if (count($data) > 0)
	{
		$this->trackingOnDevice($data);
	}

	$this->helpForm();
	commonFooter();
}

function helpSearch()
{
	$DB = Config::Database();
	PRINT "<hr>";

	$deviceType = $_GET['devicetype'];
	
	//Nasty hack for name inconsistencies
	if ($deviceType == "computer")
	{
		$deviceType = "computers";
	}

	$ID = $_GET['ID'];
	if ($ID == "")
	{
		$ID = $_GET['device_name'];
	}
	if ($ID <> "") 
	{
		$qID = $DB->getTextValue($ID);
		$query = "SELECT ID,name FROM " . $deviceType . " WHERE (ID = $qID)";
	} else 	{
		$likename = $DB->getTextValue("%$name%");
		$query = "SELECT ID,name FROM " . $deviceType . " WHERE (name LIKE $likename)";
	}	

	$data = $DB->getAll($query);

	if(count($data) < 1) 
	{
  		__("Bad IRM ID or search terms");
	} else if (count($data) > 5) {
	# Security, can't list all computers
		__("Your search terms were too vague, and yielded more than 5 results.  Please try again.");
	} else {
		foreach ($data as $result)
		{
  			$ID = $result["ID"];
	  		$name = $result["name"];
	  		$location = $result["locations"];
	  		PRINT $deviceType . ' <a href="'
  				.Config::AbsLoc("users/helper-index.php?action=add&ID=$ID&is_group=no&deviceType=$deviceType")
  				."\">$name ($ID)</a><br>";
		}	
	}
}

function helpPreview(){

	$uname =	$_REQUEST['uname'];
	$uemail =	$_REQUEST['uemail'];
	$ID =		$_REQUEST['ID'];
	$contents = 	$_REQUEST['contents'];
	$is_group = 	$_REQUEST['is_group'];
	$other_emails= 	$_REQUEST['other_emails'];
	$priority= 	$_REQUEST['priority'];
	$deviceType= 	$_REQUEST['deviceType'];
	$emailupdates= 	$_REQUEST['emailupdates'];
	
	AuthCheck("post-only");
	commonHeader(_("Tracking") . " - ". _("Preview"));

	/* Start error checking */

	if ($uname == "") {
		$error = 1;
		__("The following error occured with your request for help:");
		echo ' ';
		__("You did not enter a name.");
		PRINT "<br>";
		}
	if ($uemail == "") {
		$error = 1;
		__("The following error occured with your request for help:");
		echo ' ';
		__(" You did not enter an e-mail address.");
		PRINT "<br>";
		}
	if ($ID < 0) {
		$error = 1;
		__("A very unusual error occured.  Contact your sysadmin.");
		PRINT "<br>";
		}
	if ($contents == "") {
		$error = 1;
		__("The following error occured with your request for help:");
		echo ' ';
		__("You did not enter any problem description.");
		PRINT "<br>";

		}

	/* End error checking */

	$contents = htmlspecialchars($contents);
	$contents = stripslashes($contents);

	if (@$error != 1)
	{
		__("Please check that the job you are about to submit is correct.  If it is not, use the provided links to edit it.");
	} else {
		PRINT "<br />" . _("Errors occured with your request for help.  Your only option is to edit the job.") . "<br />\n";
	}

	PRINT "<hr noshade>";

	if($emailupdates == "")
	{
		$emailupdates = "no";
	}

	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);

	if($is_group == "no")
	{
		$query = "SELECT name FROM $deviceType WHERE (ID = $qID)";
	} else {
		$query = "SELECT name FROM groups WHERE (ID = $qID)";
	}
	$computername = $DB->getOne($query);

	PRINT '<form method=post action="' . Config::AbsLoc('users/helper-index.php') . '">';
	PRINT '<input type="hidden" name="status" value="new">';
	PRINT '<input type="hidden" name="uname" value="' 		. $uname .	 '">';
	PRINT '<input type="hidden" name="uemail" size=19 value="'	. $uemail .	 '">';
	PRINT '<input type="hidden" name="emailupdates" value="'	. $emailupdates. '">';
	PRINT '<input type="hidden" name="ID" value="'			. $ID .		 '">';
	PRINT '<input type="hidden" name="is_group" value="'		. $is_group . 	 '">';
	PRINT '<input type="hidden" name="other_emails" value="'	. $other_emails .'">';
	PRINT '<input type="hidden" name="device" value="'		. $deviceType	.'">';
	PRINT '<input type="hidden" name="deviceType" value="'		. $deviceType	.'">';

	if ($is_group == 'yes')
	{
		PRINT '<input type="hidden" name="groupname" value="' . $computername . '">';
	}

	$htmlcontents = nl2br($contents);

	PRINT "<table>";
	PRINT '<input type=hidden name=contents value="' . $contents . '">';
	PRINT '<tr class=trackingupdate>';
	PRINT '<td colspan=2><input type=submit value="' . _("Edit Job"). '"></td>';
	PRINT '</tr>';

	PRINT '</form>';

	PRINT "<tr class=trackingdetail><td>" . _("Priority:").		"</td><td>	 $priority		</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Your Name:").	"</td><td>	 $uname			</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Your E-Mail:").	"</td><td>	 $uemail		</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Other E-mails:").	"</td><td>	 $other_emails		</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Device Type:").	"</td><td>	 $deviceType		</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Computer:").		"</td><td>	 $computername ($ID)	</td></tr>";
	PRINT "<tr class=trackingdetail><td>" . _("Problem Report:").	"</td><td>	 $htmlcontents		</td></tr>";

	if (@$error != 1) 
	{
		$contents = sprintf("%s $uname ($uemail)\n$contents", _("By:"));
		PRINT '<form method=post action="' . Config::AbsLoc('users/tracking-index.php') . '">';
		PRINT '<input type="hidden" name="action" 	value="addTracking">';
		PRINT '<input type="hidden" name="priority" 	value="' . $priority 	. '">';
		PRINT '<input type="hidden" name="ID" 		value="' . $ID 		. '">';
		PRINT '<input type="hidden" name="is_group" 	value="' . $is_group 	. '">';
		PRINT '<input type="hidden" name="status" 	value="new">';
		PRINT '<input type="hidden" name="contents" 	value="' . $contents 	. '">';
		PRINT '<input type="hidden" name="uemail" 	value="' . $uemail 	. '">';
		PRINT '<input type="hidden" name="other_emails" value="' . $other_emails . '">';
		PRINT '<input type="hidden" name="deviceType"	value="' . $deviceType 	. '">';
		PRINT '<input type="hidden" name="emailupdates"	value="' . $emailupdates . '">';
		PRINT '<tr class=trackingupdate><td colspan=2><input type=submit value="' . _("Add job") . '"></td></tr>';
		PRINT "</form>";
	}
	 
	PRINT "<table>";
	commonFooter();
}

function helpForm()
{
	global	$ID,
		$computername,
		$is_group,
		$uemail,
		$uname,
		$other_emails,
		$contents,
		$deviceType;
		
		
	$Page = new IrmFactory();

	$tracking = new Tracking();

	$trackingFormDetails = array(
		'cssheader' => 'computerheader',
		'cssdetail' => 'computerdetail',

		'lableName' =>			_("Name:"),
		'lableHeader' =>		_("The Device or Group you are requesting work on: ") . $computername ."(" .$ID .")",
		'lableEmail' =>			_("E-Mail:"),
		'lableOtherEmail' =>		_("Other E-Mails:"),
		'lablePriority' =>		_("Priority:"),
		'lablePriorityDescription' => 	_("How urgent is your request ? If it can wait, pick a low priority.  If you are stuck, pick a high priority.  If you are unsure how important the problem is, leave it at its present value."),
		'lableEmailDescription' => 	_("If you are entering this help request on behalf of another user, please provide their name and e-mail address below.  They will get an initial notification of this job's creation, but will not get any further e-mails."),
		'lableOtherEmailDescription' => _("These are e-mail addresses which will get copies of all e-mails sent regarding this work request.  Separate multiple e-mail addresses with spaces or commas."),

		'formAction' =>	Config::AbsLoc('users/helper-index.php'),
		'formMethod' => 'post',

		'hiddenInputs' => 	'<input type="hidden" name="status" value="new">
					<input type="hidden" name="ID" value="' . $ID . '">
					<input type="hidden" name="action" value="preview">
					<input type="hidden" name=deviceType value="'. $deviceType. '">
					<input type="hidden" name=is_group value="'. $is_group .'">',
		'inputName' => 		$Page->splugin('input', 'text', 'uname', $uname),
		'inputEmail' =>		$Page->splugin('input', 'text', 'uemail', $uemail),
		'inputOtherEmail' =>	$Page->splugin('input', 'text', 'other_emails', $other_emails, array('size'=>'80')),
		'inputPriority' =>	'<select name="priority" size="1">' . select_options($tracking->priorities(), 3) . '</select>'
		);
	



		foreach($trackingFormDetails as $key => $value){
			$Page->assign($key,$value);
		}

		$Page->display('request.html.php');

	if(Config::Get('userupdates'))
	{
		PRINT '<tr class="computerdetail">';
		PRINT "<td>";
		__("Email Updates");
		PRINT "</td>";
		PRINT "<td>";
		PRINT "<input type=checkbox name=emailupdates value=\"yes\" checked>";
		PRINT "</td>";
		PRINT "<td>";
		__("I would like to receive email updates for this help request.");
		PRINT "</td>";
		PRINT "</tr>";
	}

	PRINT '<tr class="computerdetail">';
	PRINT "<td>";
	__("Describe the problem:"); 
	PRINT "</td>";
	PRINT "<td>";
	$contents = stripslashes(@$contents);
	fckeditor("contents",$contents);
	PRINT "</td>";
	PRINT "<td>";
	__("Please explain the problem, be as clear as possible, but also keep it short.");
	PRINT "<br />";
	PRINT "<br />";
	__("A good example : 'When I turn my computer on it makes a really loud grinding noise and nothing else happens.'"); 
	PRINT "<br />";
	PRINT "<br />";
	__("A bad example : 'It doesn't turn on'.");
	PRINT "</td>";
	PRINT "</tr>";

	PRINT '<tr class="computerupdate">';
	PRINT "<td colspan=3>";
	PRINT '<input type=submit value="' . _("Preview Job") . '"> <input type=reset value="' . _("Reset") . '"></form>';
	PRINT "</td>";
	PRINT "</tr>";
	
	PRINT "</table>";
}

function trackingOnDevice($data)
{
	// there are currently open trackings on this IRM...
	__("There are open trackings on this device. Please scan this list to make sure you are not duplicating an open work request. To make additional comments on an open tracking, click the ID link and add a followup.\n");
	PRINT "<table>\n";
	PRINT "<tr class=trackingheader><th>ID</th><th>";
	__("Problem Reported");
	PRINT "</th></tr>\n";

	foreach ($data as $result)
	{
		$id = $result['ID'];
		PRINT '<tr class="trackingdetail">';
		PRINT "<td>";
		PRINT '<a href="'.Config::AbsLoc("users/tracking-index.php?action=detail&ID=$id") . "\">$id</a>";
		PRINT "</td><td>" . $result["contents"];
		if (strlen($result["contents"]) >= 120)
		{
			PRINT "...";
		}
		PRINT "</td>";
		PRINT "</tr>\n";
        }
	PRINT "</table>";
	__("If none of the above trackings matched your current issue, please submit your request below.");
	PRINT "<hr noshade>\n";
}

}
