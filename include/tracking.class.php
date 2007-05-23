<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2003 Yann Ramin
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
require_once dirname(__FILE__) . '/irmmain.class.php';
require_once dirname(__FILE__) . '/../lib/feedcreator.class.php';

class Tracking Extends IRMMain
{
	var $CloseDate;
	var $Status;
	var $Assign;
	var $ComputerID;
	var $WorkRequest;
	var $Priority;
	var $IsGroup;
	var $Author;
	var $AuthorEmail;
	var $EmailUpdatesToAuthor;
	var $Followups;
	var $newFollowups;
	var $OtherEmails;
	var $ComputerName;
	
	function Tracking($ID=0)
	{
		if($ID != 0)
		{
			$this->setID($ID);
			$this->retrieve();
		}
		$this->Open();
	}

	function Open(){

		switch($_REQUEST['action'])
		{
			case "addTracking":
				$this->addNew();
				break;
			case "display":
				$this->main();
				break;
			case "rss":
				$this->rss();
				break;
			case "search":
				$this->searchTracking();
				break;
			case "detail";
				$this->setID($_REQUEST['ID']);
				$this->retrieve();
				$this->displayDetail($readonly);
				break;
			default:
				break;
		}
	}

	function rss(){
		$this->getTrackingForUser();

		$rss = new UniversalFeedCreator();
		$rss->title = "IRM";
		$rss->description = "IRM";
		$rss->link = "http://irm.stackworks.net";

		for($i = 0; $i < $this->numTrackingIDs; $i++)
		{
			$this->setID($this->trackingIDs[$i]);
			$this->retrieve();
			$item = new FeedItem();
			$item->title = $this->WorkRequest;
			$item->link = "http://irm.stackworks.net";
			$item->description = $this->WorkRequest;
			$rss->addItem($item);
		}
		$rss->saveFeed("RSS1.0", "../users/files/feed.xml");
	}

	function searchTracking(){
		commonHeader(_("Tracking") ." - " . _("Search"));
		$trackingIDs = $this->search($_REQUEST['searchtype'], $_REQUEST['contains']);
		PRINT "<h3>" . _("Search Results") . "</h3>";
		printf(_("%s tracking item(s) related to %s"),count($trackingIDs),htmlspecialchars($_REQUEST['contains'])); 
		PRINT "<hr noshade>";
		$this->displayHeader();
		foreach ($trackingIDs as $ID)
		{
			$this->setID($ID);
			$this->retrieve();
			$this->display();
		}
		$this->displayFooter();
		commonFooter();
	}

	function UserName()
	{
		global $IRMName;
		$this->UserName = $IRMName;
		return $this->UserName;
	}

	function addNew()
	{

		AuthCheck("post-only");
		commonHeader(_("Tracking") . " - " . _("Added"));
		$date = date("Y-m-d H:i:s");
		if ($is_group == "") 
		{
			$is_group = "no";
		}
				
		$this->setDateEntered($date);
		$this->setStatus($_REQUEST['status']);
		$this->setAuthor($this->UserName());
		$this->setComputerID($_REQUEST['ID']);
		$this->setWorkRequest($_REQUEST['contents']);
		$this->setPriority($_REQUEST['priority']);
		$this->setIsGroup($_REQUEST['is_group']);
		$this->setAuthorEmail($_REQUEST['uemail']);
		$this->setEmailUpdatesToAuthor($_REQUEST['emailupdates']);
		$this->setOtherEmails($_REQUEST['other_emails']);
		$this->setDevice($_REQUEST['deviceType']);
		
		$this->add();

		logevent($_REQUEST['ID'], _("computers"), 4, _("tracking"), _("New tracking job opened")); 

		__("Your tracking job has been placed into the database.");

		commonFooter();
	}
	
	function TrackingListHeader()
	{
		global $IRMName;
		PRINT "<table>";

		if($this->advanced_tracking == "yes")
		{
			$this->AdvancedTrackingHeader();
		}
		PRINT '<tr class="trackingheader">';
		PRINT '<td colspan="3">';
		
		PRINT '<form method=get action="' . Config::AbsLoc('users/tracking-index.php') . '">';
		PRINT _("Search");
		PRINT '<input type="hidden" name="action" value="search" />';
		PRINT '<select name="searchtype">';
		PRINT '<option value="tracking">' . _("Ticket only") . "</option>";
		PRINT '<option value="followups">' . _("Followups only") . "</option>";
		PRINT '<option value="">' . _("Ticket and Followups") . "</option>";
		PRINT "</select>\n";

		PRINT _("for the following term: ");
		PRINT '<input type=text name=contains size=20 />';
		PRINT '<input type=submit value="' . _("Search")  . '"/>';
		PRINT "</form>";

		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}

	function AdvancedTrackingHeader()
	{
		global $IRMName;
		$opts = array(
				'all' => _("Show All Tracking"),
				'allandclosed' => _("Show All Tracking inc Closed"),
				'unassigned' => _("Show only tracking not assigned to anyone")
				);

		foreach (User::AllUsers() as $id => $name)
		{
			$opts["u:$name"] = sprintf(_("Show only tracking assigned to %s"), $name);
		}

		PRINT '<tr class="trackingheader">';
		PRINT "<td>";
		PRINT '<form method="get" action="'.Config::AbsLoc('users/tracking-index.php').'">';
		$me = "u:$IRMName";
		PRINT '<input type="hidden" name="show" value="' . $me . '"/>';
		PRINT '<input type=submit value="' . _("My Requests"). '"/>';
		PRINT '<input type="hidden" name="action" value="display"/>';
		PRINT "</form>";
		PRINT "</td>";
		
		PRINT "<td>";
		PRINT '<form method="get" action="'.Config::AbsLoc('users/tracking-index.php').'">';
		PRINT '<input type="hidden" name="show" value="unassigned"/>';
		PRINT '<input type="hidden" name="action" value="display"/>';
		PRINT '<input type=submit value="' . _("Unassigned") . '"/>';
		PRINT "</form>";
		PRINT "</td>";
		
		PRINT "<td>";
		PRINT '<form method="get" action="'.Config::AbsLoc('users/tracking-index.php').'">';
		PRINT '<input type="hidden" name="action" value="display"/>';
		PRINT '<select name="show" size=1>';
		PRINT select_options($opts, $show);
		PRINT "</select>";
		PRINT '<input type=submit value="' . _("Show") . '"/>';
		PRINT "</form>";
		PRINT "</td>";
		PRINT "</tr>";
	}

	function main()
	{
		global $IRMName;
		
		AuthCheck("tech");
		commonHeader(_("Tracking"));
		__("This is the IRM tracking system it allows you to view the jobs currently in the queue.  In addition, you can click on \"more info\" next to any piece of tracking in order to view more detail or add followup information."); 

		$testmail = new EmailTracking();
		
		$this->getTrackingForUser();
		$this->TrackingListHeader();
		$this->DisplayTrackingCount();	
		$this->DisplayTrackingRows();
		commonFooter();
	}

	function getTrackingForUser(){
		$DB = Config::Database();

		if (@$_REQUEST['sort'] && !preg_match('/^[a-zA-Z0-9_]+$/', $_REQUEST['sort']))
		{
			trigger_error(sprintf(_("Invalid field name to sort by: %s"),@$_REQUEST['sort']), E_USER_ERROR);
			die(__FILE__.':'.__LINE__.": Failing on field name");
		}

		$query = "SELECT advanced_tracking,tracking_order FROM prefs WHERE (user = '" . $this->UserName() . "')";
		$result = $DB->getRow($query);

		$this->advanced_tracking = $result['advanced_tracking'];
		$tracking_order = $result["tracking_order"];

		if($tracking_order == "yes")
		{
			$tracking_order = "ASC";
		} else {
			$tracking_order = "DESC";
		}

		if (@$_REQUEST['sort'])
		{
			$tracking_order = $_REQUEST['sort']." $tracking_order";
		} else {
			$tracking_order = "date $tracking_order";
		}

		if (!isset($show))
		{
			$show = '';
		}
		$this->trackingIDs = $this->getNotClosed($this->advanced_tracking, $_REQUEST['show'], $tracking_order);
		$this->numTrackingIDs = sizeof($this->trackingIDs);

	}
	
	function DisplayTrackingCount()
	{
		if($this->numTrackingIDs != 1)
		{
			PRINT "<h3>";
			printf(_("There are currently %s Jobs"), $this->numTrackingIDs);
			PRINT "</h3>\n";
		} else {
			PRINT "<h3>";
			__("There is currently 1 Job");
			PRINT "</h3>\n";
		}
	}

	function DisplayTrackingRows()
	{
		$this->displayHeader();
		for($i = 0; $i < $this->numTrackingIDs; $i++)
		{
			$this->setID($this->trackingIDs[$i]);
			$this->retrieve();
			$this->display();
		}
		$this->displayFooter();
	}

	function priorities()
	{
		$this->priorities = array(5 => _('Very High'),
					4 => _('High'),
					3 => _('Normal'),
					2 => _('Low'),
					1 => _('Very Low')
					);

		return $this->priorities;
	}

	function status_list()
	{
		$this->status_list = array('active' => _('Active'),
					'assigned' => _('Assigned'),
					'complete' => _('Complete'),
					'new' => _('New'),
					'old' => _('Old'),
					'wait' => _('Wait'),
					'duplicate' => _('Duplicate'));
	}
	
	function retrieve()
	{
		$DB = Config::Database();
		$this->qID = $DB->getTextValue($this->ID);
		
		$query = "SELECT * FROM tracking WHERE (ID=" . $this->qID . ")";
		$result = $DB->getRow($query);
		if (count($result))
		{
			$this->setDateEntered($result['date']);
			$this->setCloseDate($result['closedate']);
			$this->setStatus($result['status']);
			$this->setAssign($result['assign']);

			//This is pretty wacked, and could probably do with some refactoring
			$this->setComputerID($result['computer']);
			$this->setComputerName($result['computer']);
			$this->setDeviceID($result['computer']);
			
			$this->setWorkRequest($result['contents']);
			$this->setPriority($result['priority']);
			$this->setIsGroup($result['is_group']);
			$this->setAuthor($result['author']);
			$this->setAuthorEmail($result['uemail']);
			$this->setEmailUpdatesToAuthor($result['emailupdates']);
			$this->setOtherEmails($result['other_emails']);
			$this->setDevice($result['device']);
			$this->setFollowups();
			$this->priorities();
			$this->status_list();
			$this->dateopened();
			$this->dateclosed();
		}
	}

	function setFollowups()
	{
		unset($this->Followups);
		
		if($this->ID == 0)
		{
			PRINT _("Error setting followup information in Tracking Class:")." ";
			PRINT _("Tracking ID has not been set yet")."\n";
		}
		$fol = new Followup();
		$FollowupIDs = $fol->getByTrackingID($this->ID);
		foreach ($FollowupIDs as $id)
		{
			if ($id != 0)
			{
				$this->Followups[] = new Followup($id);
			}
			else
			{
				trigger_error(_("Got zero ID for followup"), E_USER_WARNING);
			}
		}
	}

	function setCloseDate($CD)
	{
		$this->CloseDate = $CD;
	}

	function setStatus($Stat)
	{
		$DB = Config::Database();

		// This is fscking ugly.
		$wasclosed = $DB->getOne("SELECT closed FROM tracking_status WHERE status=".$DB->getTextValue($this->Status));
		$isclosed = $DB->getOne("SELECT closed FROM tracking_status WHERE status=".$DB->getTextValue($stat));
		
		if (!$wasclosed && $isclosed)
		{
			$this->setCloseDate(date('Y-m-d H:i:s'));
		}

		$this->Status = $Stat;
	}

	function setAssign($As)
	{
		if (!$this->Assign && $As && $this->Status == 'new')
		{
			$this->Status = 'assigned';
		}
		$this->Assign = $As;
	}

	function setDevice($DeviceType)
	{
		$this->DeviceType = $DeviceType;
	}

	function setDeviceID($CompID)
	{
		$this->DeviceID = $CompID;
	}

	function setComputerID($CompID)
	{
		$this->ComputerID = $CompID;
	}

	function setComputerName($CompID)
	{
                if($this->IsGroup == "yes")
                {
                        $query = "select * from groups where (ID=$CompID)";
                } else {
                        $query = "select * from computers where (ID=$CompID)";
                }

                $DB = Config::Database();
                $result = $DB->getRow($query);
                $this->ComputerName = $result["name"];
        }

	function setWorkRequest($WR)
	{
		$this->WorkRequest = $WR;
	}

	function setPriority($Pri)
	{
		$this->Priority = $Pri;
	}

	function setIsGroup($IG)
	{
		$this->IsGroup = $IG;
	}

	function setAuthor($Auth)
	{
		$this->Author = $Auth;
	}

	function setAuthorEmail($AE)
	{
		$this->AuthorEmail = $AE;
	}

	function setEmailUpdatesToAuthor($EUTA)
	{
		$this->EmailUpdatesToAuthor = $EUTA;
	}

	function setOtherEmails($OE)
	{
		$this->OtherEmails = $OE;
	}

	function getCloseDate()
	{
		return($this->CloseDate);
	}

	function getStatus()
	{
		return($this->Status);
	}

	function getAssign()
	{
		return($this->Assign);
	}

	function getComputerID()
	{
		return($this->ComputerID);
	}

	function getWorkRequest()
	{
		return($this->WorkRequest);
	}

	function getPriority()
	{
		return($this->Priority);
	}

	function getIsGroup()
	{
		return($this->IsGroup);
	}


	function getAuthorEmail()
	{
		return($this->AuthorEmail);
	}

	function getEmailUpdatesToAuthor()
	{
		return($this->EmailUpdatesToAuthor);
	}

 	function getOtherEmails()
 	{
 		return $this->OtherEmails;
 	}
 
 	/** Get a list of tracking items that are currently open.
 	 * $Advanced gives additional options
 	 * $Show is only used if $Advanced is 'yes', and can be either
 	 *	'all' for all open tracking, 'unassigned' for those
 	 *	open tracking items which have not been assigned to anyone,
 	 *	or 'u:<username>', for those items assigned to a particular
 	 *	person (as specified by <username>).
 	 */
	function getNotClosed($Advanced, $Show, $Order = 'date ASC')
	{
		// Disallow potentially damaging sort specifications
		if (!preg_match('/^[ _0-9A-Za-z]+$/', $Order))
		{
			trigger_error("Tracking::getNotClosed(): "._("Invalid sort string:")." $Order", E_USER_ERROR);
			exit;
		}

		// Prefix the sort field by it's table
		if (substr($Order, 0, 8) == 'location')
		{
			$Order = "computers.$Order";
		}
		else
		{
			$Order = "tracking.$Order";
		}

		$DB = Config::Database();

		$sort = "ORDER BY $Order";

		if ($Advanced == "yes")
		{
			if ($Show == "allandclosed")
	  		{
	  			$where = "WHERE (tracking_status.status=tracking.status)";
			}


			if ($Show == "all" || $Show == '')
	  		{
	  			$where = "WHERE (tracking_status.closed = 0)
					AND (tracking_status.status=tracking.status)";
			}
			else if ($Show == "unassigned")
	  		{
  				$where = "WHERE (tracking_status.closed = 0)
  					AND (tracking_status.status=tracking.status)
					AND ((tracking.assign is null)
						OR (tracking.assign = ''))";
	  		}
			else if (preg_match('/^u:(.*)$/', $Show, $matches))
	  		{
	  			$quser = $DB->getTextValue($matches[1]);
  				$where = "WHERE (tracking_status.closed = 0)
  					AND (tracking_status.status=tracking.status)
  					AND (tracking.assign = $quser)";
			}
		}
		else
		{
			$where = "WHERE (tracking_status.closed = 0)
				AND (tracking_status.status=tracking.status)
				AND ((tracking.assign='" . $this->UserName() . "')
					OR (tracking.author='" . $this->UserName() . "')
					OR (tracking.assign is null))";

		}

		$query = "SELECT tracking.ID
				FROM tracking LEFT JOIN computers
					ON tracking.computer=computers.ID,
				    tracking_status
				$where
				$sort";

		return $DB->getCol($query);
	}
	
	function getNotClosedBy($Order, $username = NULL)
	{
		global $IRMName;
	
		if($username === NULL)
		{
			$username = $IRMName;
		}

		$DB = Config::Database();
		$username = $DB->getTextValue($username);

		$sort = '';
		if (strtolower($Order) == 'asc')
		{
			$sort = "ORDER BY date";
		}
		else if (strtolower($Order) == 'desc')
		{
			$sort = "ORDER BY date DESC";
		}
		
	  	$query = "SELECT ID FROM tracking,tracking_status
					WHERE tracking_status.closed = 0
	  					AND tracking_status.status=tracking.status
						AND (author = $username)
					$sort";

		return $DB->getCol($query);
	}

	function requestAge()
	{
		if (($this->Status != "old") && ($this->Status != "complete"))
		{
			$dto = new SimpleDateTimeObject();
			$worktime = $dto->diff_MySQL($this->DateEntered);

			PRINT "\n<td>";
			if ($worktime['years'] != 0){
				PRINT $worktime['years'] . _(" Year ");
			}
			if ($worktime['weeks'] != 0){
				PRINT $worktime['weeks'] . _(" weeks ");
			}	
			if ($worktime['days'] != 0){
				PRINT $worktime['days'] . _(" days ");
			}
			if ($worktime['hours'] != 0){
				PRINT $worktime['hours'] . _(" hours ");
			}
			if ($worktime['minutes'] != 0){
				PRINT $worktime['minutes'] . _(" minutes");
			}
			PRINT "</td>\n";
		} else {
			PRINT "\n<td>"._("Opened:"). $this->dateopened  . "<br />"._("Closed:"). $this->dateclosed . "</td>\n";
		}
	}

	function display($withFollowups = false)
	{
		global $IRMName;
		if($this->ID == 0)
		{
			PRINT _("Error displaying Tracking: ") . _("ID is not set.")."<BR>\n";
			return;
		}

		$user = new User();
		$authExists = $user->exists($this->Author);
		if($authExists)
		{
			$user2 = new User($this->Author);
			$authorfullname = $user2->getFullname();
		} else
		{
			$authorfullname = $this->Author;
		}

		$assignExists = $user->exists($this->Assign);
		if($assignExists)
		{
			$user2 = new User($this->Assign);
			$assignfullname = $user2->getFullname();
		} else
		{
			$assignfullname = $this->Assign;
		}

		$DB = Config::Database();
		
		$this->deviceSelection();
		
		$DB = Config::Database();
		
		$result = $DB->getRow($this->query);
		$computername = $result["name"];
		$numFollowups = sizeof($this->Followups);
		$text = nl2br($this->WorkRequest);

		$userbase = Config::AbsLoc('users');
		
		$location = $result['location'];
	
		if ($location == '')
		{
			$location = $result['locations'];
		}
	
		if ($location == '')
		{
			$location = '&nbsp;';
		}
		
		PRINT '<tr class="trackingdetail">';
		PRINT '<td align="center">'."<a href=\"$userbase/tracking-index.php?action=detail&amp;ID=$this->ID\">$this->ID</a></td>";
		PRINT namestatus($this->Status);

		$this->requestAge();

		PRINT namepriority($this->Priority);
		
		PRINT "\n<td>";
		if($authExists)
		{
			PRINT "<a href=\"$userbase/users-info.php?ID=$this->Author\">" . $authorfullname . "</a>";
		} else {
			PRINT $authorfullname;
		}

		if((Config::Get('userupdates')) && ($this->EmailUpdatesToAuthor == "yes"))
		{
			PRINT "(U)";
		}

		PRINT "</td>";

		if($this->Assign == "")
		{
			PRINT "<td>["._("Nobody")."]</td>";
		}
		else
		{
			PRINT "<td>\n";
			if($assignExists)
			{
				PRINT "<a href=\"$userbase/users-info.php?ID=$this->Assign\">" . $assignfullname . "</a>\n";
			} else {
				PRINT $assignfullname;
			}
			PRINT "</td>";
		}

		PRINT "<td>";
		if($this->IsGroup != "yes")
		{
			switch ($this->DeviceType)
			{
			case 'computers':
				PRINT "<a href=\"$userbase/computers-index.php?action=info&amp;ID=$this->ComputerID\">";
				break;
			case null;
				PRINT "<a href=\"$userbase/computers-index.php?action=info&amp;ID=$this->ComputerID\">";
				break;
			default:	
				PRINT "<a href=\"$userbase/device-info.php?ID=$this->ComputerID&amp;evicetype=$this->DeviceType\">";
			}
		}
		PRINT $this->DeviceType . " : ";
		PRINT "$computername";
		if($this->IsGroup != "yes")
		{
			PRINT "</a>\n";
		}
		PRINT "</td>";
		
		PRINT "<td>$location</td>";
		
		PRINT "<td>";

		PRINT '<div class="followupsubheader">' . _("Follow Ups:") . "$numFollowups</div>";
		PRINT "$text";
		
		if(($withFollowups) && ($numFollowups > 0))
		{
			Followup::displayHeader();
			for($i=0; $i < $numFollowups; $i++)
			{
				$this->Followups[$i]->display();
			}
			Followup::displayFooter();
		}

		PRINT "</td>\n";
		PRINT "</tr>";
	}
	
	function legacySelectionQuery($type = "computers")
	{
		// Hack because because the locations dropdown does not map to location in the computers table
		if (!$this->DeviceID == "")
		{
			$this->query = "SELECT name,location FROM $type WHERE (ID = $this->DeviceID)";
		} else {
			$this->query = "SELECT name,location FROM $type";
		}

	}

	function softwareSelectionQuery()
	{
		$this->query = "SELECT name FROM software WHERE (ID = $this->DeviceID)";
	}

	function deviceSelection()
	{
		if($this->IsGroup == "yes")
		{
			$this->query = "SELECT name FROM groups WHERE (ID = $this->DeviceID)";
		} else{
			switch($this->DeviceType)
			{
				case "":
					$this->legacySelectionQuery();
					break;
				case "computers":
					$this->legacySelectionQuery();
					break;
				case "Computers":
					$this->legacySelectionQuery();
					break;
				case "software":
					$this->legacySelectionQuery();
					break;
				case "networking":
					$this->legacySelectionQuery("networking");
					break;

				default:
					//Check that the device type exists, but of a hack.
					$this->query = 'SHOW TABLES LIKE "' . $this->DeviceType . '%"';
					$DB = Config::Database();
					$result = $DB->getRow($this->query);
					if(count($result) >= 1){
						$this->query = "SELECT name,locations FROM $this->DeviceType WHERE (ID = $this->DeviceID)";
					}
			}
		} 
	}

	function displayDetail($readonly = true)
	{
		$this->deviceSelection();
		$DB = Config::Database();
		$result = $DB->getRow($this->query);
		$computername = $result["name"];
		$userbase = Config::AbsLoc('users');
		$user = new User();
		$authExists = $user->exists($this->Author);
		if($authExists) {
			$user2 = new User($this->Author);
			$fullname = $user2->getFullname();
		} else {
			$fullname = $this->Author;
		}
		
		commonHeader(_("Tracking") ." - " . _("More Information"));
		PRINT "<hr noshade />";
		PRINT "<br />";

		PRINT '<form method="post" action="tracking-update.php">';
		if (!$this->ComputerID)	{
			PRINT "<B>"._("This Tracking Entry Does Not Exist, or is missing critical details.")."</B><BR />";
			$this->ComputerID = "99999";
		}
	
		PRINT "<table>";
		PRINT "<tr>";
		PRINT "<th colspan=2>"._("Job Number "). $this->ID . "</th>";
		print "</tr>\n";

		PRINT '<tr class="trackingdetail">';

		PRINT "<td width=50%>"._("Status:")."<br />";
		if ($readonly) {
			PRINT $this->status_list[$this->Status];
		} else {
			PRINT "<SELECT NAME=status SIZE=1>";
			PRINT select_options($this->status_list, $this->Status);
			PRINT "</SELECT>";
		}
		PRINT "</td>";

		PRINT "<td>"._("Date Opened:")."<br />" . $this->dateopened . "<br />";
		if(($this->CloseDate != "0000-00-00 00:00:00") && ($this->CloseDate != "")) {
			PRINT "<BR>" . _("Date Closed:") . "<br />$this->CloseDate";
			$query = "SELECT SEC_TO_TIME(UNIX_TIMESTAMP('$this->CloseDate') - UNIX_TIMESTAMP('$this->DateEntered'))";
			$opentime = $DB->getOne($query);
			PRINT "<br />"._("This job was open for:")." $opentime";
		}
		PRINT "</td>";
		PRINT "</tr>";

		PRINT '<tr class="trackingdetail">';
		PRINT "<td>"._("Priority:")."<br />\n";
		if ($readonly) {
			echo $options[$this->Priority]."\n";
		} else {
			PRINT '<SELECT name="priority" size="1">'."\n";
			PRINT select_options($this->priorities, $this->Priority);
			PRINT '</SELECT>'."\n";
		}
		
		PRINT "</td>";
		PRINT "<td>";
		PRINT _("Author:") . "<a href=\"$userbase/users-info.php?ID=$this->Author\">$fullname</a>";
		PRINT "<br />";
		PRINT _("Other Emails:") . $this->getOtherEmails();
		PRINT "</td>";
		PRINT "</tr>";
		
		PRINT '<tr class="trackingdetail">';

		// Device Name and type cell
		PRINT "<td>";
		$this->DeviceType;
		if($this->IsGroup != "yes") {
			PRINT $this->DeviceType . ":";
		} else {
			PRINT _("Group") . ":";
		}

		PRINT "<br />";
		if($this->IsGroup != "yes") {
			if ($this->DeviceType == "computers") {
				PRINT "<a href=\"$userbase/computers-index.php?action=info&amp;ID=$this->ComputerID\">";
			} else {
				PRINT "<a href=\"$userbase/device-info.php?ID=" . $this->ComputerID . "&amp;devicetype=$this->DeviceType\">";
			}
		}

		PRINT "$computername ($this->ComputerID)";

		if($this->IsGroup != "yes") {
			PRINT "</a>";
		}

		if (!$readonly)	{
			print "&nbsp; <input type=\"text\" name=\"ComputerID\" value=\"$this->ComputerID\" size=\"3\" />";
		}
		PRINT "</td>";
		// End of Name and type cell


		PRINT "<td>"._("Assigned to:")."<br />";

		Tech_list($this->Assign, "user", $readonly);

		PRINT "</td>";
		PRINT "</tr>";
		
		PRINT '<tr class="trackingdetail">';
		PRINT "<td colspan=2>"._("Problem Description:")."<br />";
		if ($readonly) {
			echo nl2br(htmlspecialchars($this->WorkRequest));
		} else {
			$text = htmlspecialchars($this->WorkRequest);
			PRINT '<TEXTAREA NAME="workrequest" ROWS="10" COLS="60">' . $text . '</TEXTAREA>';
			PRINT '<input type="hidden" name="original" value="' . $text . '"/>';
		}
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";


		// Display Followups
		$numFollowups = sizeof($this->Followups);

		Followup::displayHeader();
		
		if($numFollowups > 0) {
			for($i=0; $i < $numFollowups; $i++) {
				$this->Followups[$i]->display();
			}
		} else {
			PRINT "<tr><td colspan=3>"._("No Followups on this request")."</td></tr>\n";
		}

		if (!$readonly) {
			Followup::displayAddForm();
		}

		Followup::displayFooter();
		
		//Display Knowledgebase checkbox
		if (!$readonly)	{
			PRINT "<table>";
			PRINT "<tr>";
			PRINT "<th>" . _("Knowledge Base System") . "</th>";
			PRINT "</tr>";

			PRINT '<tr class="trackingdetail">';
			PRINT "<td><input type=checkbox name=addtoknowledgebase value=yes />";
			__("If tracking is marked as complete, should it be used to add something to the knowledgebase?");
			PRINT "</td>";
			PRINT "</tr>\n";

			PRINT "</table>";
			PRINT "<input type=hidden name=tID value=$this->ID />";
			PRINT '<input type=submit VALUE="' . _("Update Tracking"). '" />';
			PRINT "</form>";
			PRINT "<br />\n";
		}

		$files = new Files();	
		$files->setDeviceType("tracking");
		$files->setDeviceID($this->ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();

		commonFooter();
	}
	
	function displayHeader($styleid = "default")
	{
		PRINT '<table class="sortable" id="tracking-'. $styleid .'">';
		PRINT '<tr class="trackingheader">';
		PRINT '<th>'._("ID").'</th>';
		PRINT '<th>'._("Status").'</th>';
		PRINT '<th>'._("Age").'</th>';
		PRINT '<th>'._("Pri").'</th>';
		PRINT '<th>'._("Author").'</th>';
		PRINT '<th>'._("Assigned").'</th>';
		PRINT '<th>'._("Device").'</th>';
		PRINT '<th>'._("Location").'</th>';
		PRINT '<th>'._("Description").'</th>';
		PRINT "</tr>\n";
	}

	function displayFooter()
	{
		PRINT "</table>";
	}

	function search($type, $information)
	{
		$DB = Config::Database();
		$info = $DB->getTextValue("%$information%");

		switch($type){
			case 'tracking':
				$query = "SELECT ID FROM tracking WHERE (contents LIKE $info) ORDER BY date DESC";
				break;
			case 'followups':
				$query = "SELECT DISTINCT tracking.ID AS ID
				FROM tracking INNER JOIN followups ON tracking.ID=followups.tracking
				WHERE followups.contents LIKE $info
				ORDER BY tracking.date DESC";
				break;
			default:
				$query = "SELECT DISTINCT tracking.ID AS ID
				FROM tracking LEFT JOIN followups ON tracking.ID=followups.tracking
				WHERE followups.contents LIKE $info
				OR tracking.contents LIKE $info
				ORDER BY tracking.date DESC";
				break;
		}
		return $DB->getCol($query);
	}

	
	/* Send e-mail to all people interested in this ticket.
	 * If $mod is 'yes', then this is a notification of a change to an
	 * existing ticket.  Otherwise, we're sending notification of a new
	 * ticket being created.
	 */
	function sendEmail($mod="no")
	{
		global $IRMName;
		if(!Config::Get('notifyassignedbyemail')
		    && !Config::Get('userupdates')
		    && !Config::Get('notifynewtrackingbyemail'))
		{
			return;
		}
		// First, work out who's going to get this missive
		$recipients = array();


		// Assignee
		if(Config::Get('notifyassignedbyemail')
		   && $this->Assign != $IRMName)
		{
			$assignUser = new User($this->Assign);
			if ($assignUser->getEmail())
			{
				$recipients[] = $assignUser->getEmail();
			}
		}

		// Author
		if(Config::Get('userupdates')
		   && $this->EmailUpdatesToAuthor == "yes"
		   && $this->AuthorEmail != ""
		   && $IRMName != $this->Author)
		{
			$recipients[] = $this->AuthorEmail;
		}

		// E-mail addresses that get copies of all tracking e-mails
		if(Config::Get('notifynewtrackingbyemail'))
		{
			$recipients = array_merge($recipients, split('[^a-zA-Z0-9@_\.]+', Config::Get('newtrackingemail')));
		}

		// People who have signed on to get info on this ticket
		$recipients = array_merge($recipients, split('[^a-zA-Z0-9_\.@]', $this->OtherEmails));

		// Who do we send it from?
		$currentUser = new User($IRMName);
		$sender = $currentUser->getEmail();

		// What are we going to tell them?
		$body = $this->mailBody();

		if($mod == "no"){
			$subject = sprintf(_("IRM: New Job %s has been ADDED to the work request system."), $this->ID);
		} else if ($this->Status == "complete") {
                          $subject = sprintf(_("IRM: Job %s has been COMPLETED by %s"), $this->ID, $IRMName);
                } else {
			$subject = sprintf(_("IRM: Job %s has been MODIFIED by %s"), $this->ID, $IRMName);
		}

		$headers = "";
		$headers .= "From: $sender\n";
		$headers .= "X-From-IRM: IRM\n ";

		foreach ($recipients as $r)
		{
			if ($r)
			{
				mail($r, $subject, $body, $headers);
			}
		}
	}
	/* Generate an e-mail body suitable for transmission.
	 */
	function mailBody()
	{
		$priorityname = namepriority($this->Priority, false);
 
 		$body .= _("Tracking ID:")." $this->ID for $this->ComputerName: Status $this->Status, Assigned to $this->Assign, $priorityname priority \n";
 		$body .= _("  Work Request:")." $this->WorkRequest\n\n";

		$numFollowups = sizeof($this->Followups);

		if($numFollowups == 0)
		{
			$body .= _("No Followups have been added.")."\n";
		}
		else
		{
			$body .= _("Followups:")."\n";
			foreach ($this->Followups as $fup)
			{
				$body .= $fup->mailBody();
			}
		}

		$body .= "================================================================\n";
		$body .= _("Modify this tracking item:")."\n";
		$body .= "  http://"
				.$_SERVER['SERVER_NAME']
				.Config::AbsLoc("users/tracking-index.php?action=detail&amp;ID=$this->ID")
				."\n";
		$body .= "================================================================\n\n";
		
		return $body;
	}

	function commit()
	{
		global $IRMName;
		
		$CommitError = _("Error committing Work Request. ");
	
		if($this->ID == 0)
		{
			PRINT _("Error committing Work Request. ") . _("ID has not
				been set. Use \"add()\" to add new Work
				Requests and \"commit\" to commit changes to
				Work Requests")."<BR>\n";
			return (0);
		}

		if($this->DateEntered == "")
		{
			PRINT  $CommitError . _("DateEntered has not been set.")."<BR>\n";
			return (0);
		}
		if($this->Status == "")
		{
			PRINT  $CommitError . _("Status has not been set.")."<BR>\n";
			return (0);
		}
		if($this->ComputerID == 0)
		{
			PRINT $CommitError . _("ComputerID has not been set.")."<BR>\n";
			return (0);
		}
		if($this->WorkRequest == "")
		{
			PRINT  $CommitError . _("WorkRequest has not been set.")."<BR>\n";
			return (0);
		}
		if($this->Priority == 0)
		{
			PRINT $CommitError . _("Priority has not been set.")."<BR>\n";
			return (0);
		}
		if($this->IsGroup == "")
		{
			PRINT $CommitError . _("IsGroup has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->Author))
		{
			PRINT $CommitError . _("Author has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->AuthorEmail))
		{
			PRINT $CommitError . _("AuthorEmail has not been set.")."<BR>\n";
			return (0);
		}
		if($this->EmailUpdatesToAuthor == "") 
		{
			PRINT $CommitError . _("EmailUpdatesToAuthor has not been set.")."<BR>\n";
			return (0);
		}
		if(($this->Status == "complete") || ($this->Status == "old"))
		{
			$tempCloseDate = date("Y-m-d H:i:s");
			$this->setCloseDate($tempCloseDate);
		}

		$vals = array(
			'date' => $this->DateEntered,
			'closedate' => $this->CloseDate,
			'status' => $this->Status,
			'assign' => $this->Assign,
			'computer' => $this->ComputerID,
			'contents' => $this->WorkRequest,
			'priority' => $this->Priority,
			'is_group' => $this->IsGroup,
			'author' => $this->Author,
			'uemail' => $this->AuthorEmail,
			'emailupdates' => $this->EmailUpdatesToAuthor,
			'other_emails' => $this->OtherEmails
			);
		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$DB->UpdateQuery('tracking', $vals, "ID=$ID");
	
		$numFollowups = sizeof($this->Followups);
		for($i=0;$i<$numFollowups;$i++)
		{
			$tempVal = $this->Followups[$i]->getID();
			if($tempVal > 0)
			{
				$this->Followups[$i]->commit();
			} else
			{
				$this->Followups[$i]->setTrackingID($this->ID);
				$this->Followups[$i]->add();
			}
		}	
	
		$this->sendEmail("yes");	
	}

	function add()
	# FIXME duplicate see commit()
	{
		if(isset($this->ID))
		{
			PRINT _("Error committing Work Request. ") . _("ID has not
				been set. Use \"add()\" to add new Followups
				and \"commit\" to commit changes to
				Followups")."<BR>\n";
		}
		if(!isset($this->DateEntered))
		{
			PRINT _("Error committing Work Request. ") . _("DateEntered has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->Status))
		{
			PRINT _("Error committing Work Request. ") . _("Status has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->ComputerID))
		{
			PRINT _("Error committing Work Request. ") . _("  ComputerID has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->WorkRequest))
		{
			PRINT _("Error committing Work Request. ") . _(" WorkRequest has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->Priority))
		{
			PRINT _("Error committing Work Request. ") . _("Priority has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->IsGroup))
		{
			PRINT _("Error committing Work Request. ") . _("IsGroup has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->Author))
		{
			PRINT _("Error committing Work Request. ") .  _("Author has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->AuthorEmail))
		{
			PRINT _("Error committing Work Request. ") . _("AuthorEmail has not been set.")."<BR>\n";
			return (0);
		}
		if(!isset($this->EmailUpdatesToAuthor))
		{
			PRINT _("Error committing Work Request. ") . _(" EmailUpdatesToAuthor has not been set.")."<BR>\n";
			return (0);
		}

		$DB = Config::Database();
		$this->ID = $DB->nextId('tracking__ID');
		$vals = array(
			'ID' => $this->ID,
			'date' => $this->DateEntered,
			'closedate' => $this->CloseDate,
			'status' => $this->Status,
			'author' => $this->Author,
			'assign' => $this->Assign,
			'computer' => $this->ComputerID,
			'contents' => $this->WorkRequest,
			'priority' => $this->Priority,
			'is_group' => $this->IsGroup,
			'uemail' => $this->AuthorEmail,
			'emailupdates' => $this->EmailUpdatesToAuthor,
			'other_emails' => $this->OtherEmails,
			'device' => $this->DeviceType
			);
			
		$DB->InsertQuery('tracking', $vals);
		
/*		$numFollowups = sizeof($this->Followups);
		for($i=0;$i<$numFollowups;$i++)
		{
			$this->Followups[$i]->setTrackingID($this->ID);
			$this->Followups[$i]->add();
		}
*/
		$this->sendEmail();
	}

	function addFollowup($newFollowup)
	{
		$this->newFollowups = 1;
		$numFollowups = sizeof($this->Followups);
		$this->Followups[$numFollowups] = $newFollowup;
	}

	function getFollowupsInfo()
	{
		$numFollowups = sizeof($this->Followups);
		$returnVal = "";
		for($i=0; $i<$numFollowups; $i++)
		{	
			$returnVal = $returnVal . $this->Followups[$i]->getFollowupInfo();
		}
		return($returnVal);
	}

	function delete()
	{
		$numFollowups = sizeof($this->Followups);
		for($i=0;$i<$numFollowups;$i++)
		{
			$this->Followups[$i]->delete();
		}
		$DB = Config::Database();
		$id = $DB->getTextValue($this->ID);
		$query = "DELETE FROM tracking WHERE (ID = $id)";
		$DB->query($query);
	}

	function getByComputerID($cID)
	{
		$DB = Config::Database();
		$cID = $DB->getTextValue($cID);
		$query = "SELECT ID FROM tracking WHERE (computer = $cID) and (is_group != 'yes')";
		return $DB->getCol($query);
	}

	function getByGroupID($gID)
	{
		$DB = Config::Database();
		$gID = $DB->getTextValue($gID);
		$query = "SELECT ID FROM tracking WHERE (computer = $gID) and (is_group = 'yes')";
		return $DB->getCol($query);
	}

	function isStatus($status)
	{
		if($this->Status == $status)
		{
			return(TRUE);
		} else {
			return(FALSE);
		}
	}

	function isAssign($assign)
	{
		if($this->Assign == $assign)
		{
			return(TRUE);
		} else {
			return(FALSE);
		}
	}
}


// Functions below here are not in the tracking class but need to be refactored into it.

function deviceTracking($ID,$devicetype)
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);
	
	if ($_REQUEST['showtracking'] != 'none')
	{
		if ($devicetype == "Computers")
		{
			$q = "SELECT DISTINCT(ID) FROM tracking
				WHERE (tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '' OR tracking.device = 'computers'
				)";
		} else {
			$q = "SELECT DISTINCT(ID) FROM tracking
				WHERE (tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '$devicetype'
				)";
		}

		if ($_REQUEST['showtracking'] == 'open')
		{
			$q .= " AND tracking.status != 'complete'";
		}
		$q .= ' ORDER BY date DESC';

		$data = $DB->getCol($q);

		Tracking::displayHeader();
  		foreach ($data as $tID)
  		{
			$track2 = new Tracking($tID);
			$track2->display(@$_REQUEST['showfollowups']);
  		}
		Tracking::displayFooter();
	}
}

function groupTracking($ID,$devicetype)
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);
	
	if ($_REQUEST['showtracking'] != 'none')
	{
		$q = "SELECT DISTINCT(ID) FROM tracking,comp_group
			WHERE ((tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '$devicetype'
				)
			OR (comp_group.comp_id=$qID
				AND tracking.is_group='yes'
				AND tracking.computer=comp_group.group_id))";
		
		if ($_REQUEST['showtracking'] == 'open')
		{
			$q .= " AND tracking.status != 'complete'";
		}
		$q .= ' ORDER BY date DESC';

		$data = $DB->getCol($q);

		Tracking::displayHeader();
  		foreach ($data as $tID)
  		{
			$track2 = new Tracking($tID);
			$track2->display(@$_REQUEST['showfollowups']);
  		}
		Tracking::displayFooter();
	}
}


function displayDeviceGroups($ID)
{
	if (Config::Get('groups'))
	{
		PRINT "<br /><b>";
		__("Group Memberships");
		PRINT "</b><p>	";

		$DB = Config::Database();
		$qID = $DB->getTextValue($ID);

		$query = "SELECT * FROM comp_group where comp_id = $qID";
		$data = $DB->getAll($query);
		foreach ($data as $result)
		{
			$gID = $result["group_id"];
			$qgID = $DB->getTextValue($gID);
			$q2 = "SELECT name FROM groups WHERE id = $qgID";
			$gname = $DB->getOne($q2);
			PRINT '<a href="'.Config::AbsLoc("users/setup-groups-members.php?id=$gID")."\">$gname</a><br />";
		}
	}
}

function deviceTrackingCount($ID,$countType,$devicetype)
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);

	if($devicetype == "Computers")
	{
		$devicetype = "computers";
		$query = "SELECT COUNT(ID) FROM tracking
			WHERE ((tracking.computer = $qID
				AND tracking.is_group='no'
				)
			OR (tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '$devicetype'))";

	} else {
		$query = "SELECT COUNT(ID) FROM tracking
			WHERE (tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '$devicetype')";
	}

	if ($countType == "Open")
	{
		$query .= " AND tracking.status != 'complete'";
	}
	
	$count = $DB->getOne($query);

	return $count;
}

function groupTrackingCount($ID,$countType,$devicetype)
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);

	if($devicetype == "Computers")
	{
		$devicetype = "computers";
	}
	
	$query = "SELECT COUNT(ID) FROM tracking,comp_group
			WHERE ((tracking.computer = $qID
				AND tracking.is_group='no'
				AND tracking.device = '$devicetype')
			OR (comp_group.comp_id=$qID
				AND tracking.is_group='yes'
				AND tracking.computer=comp_group.group_id)
			)";
	if ($countType == "Open")
	{
		$query .= " AND tracking.status != 'complete'";
	}
	
	$count = $DB->getOne($query);

	return $count;
}


function displayDeviceTracking($ID, $devicetype)
{

	$allcount = deviceTrackingCount($ID,"All",$devicetype);
	$opencount = deviceTrackingCount($ID,"Open",$devicetype); 

	$groupallcount = groupTrackingCount($ID,"All",$devicetype);
	$groupopencount = groupTrackingCount($ID,"Open",$devicetype); 


	// Set default showtracking if none given
	if (!@$_REQUEST['showtracking'])
	{
		$_REQUEST['showtracking'] = 'open';
	}
	
	if ($allcount == 0)
	{
		echo '<i>'._("No Tracking Found").'</i>';
	}
	else
	{
		PRINT "<br />";
		PRINT '<table class="tracking">';
		PRINT '<tr class="trackingheader">';
		PRINT "<th>";
		PRINT '<a name="tracking">' . _("Tracking Summary") . "</a>";
		PRINT "</th>";
		PRINT "</tr>";
		PRINT '<tr class="trackingdetail">';
		PRINT "<td>\n";
		printf(_("Found %s tracking items, %s currently open."), $allcount, $opencount);
		PRINT "</td>\n";
		PRINT "</tr>";
			PRINT '<tr class="trackingupdate">';
		PRINT "<td>";
	
		if ($devicetype == "computers" || $devicetype == "Computers")
		{
			PRINT '<form method=get action="'.Config::AbsLoc('users/computers-index.php').'#tracking">';
		} else {
			PRINT '<form method=get action="'.Config::AbsLoc('users/device-info.php'). '#tracking">';
		}
		PRINT "<input type=hidden name=devicetype value=$devicetype />";
		PRINT "<input type=hidden name=action value=info />";
		PRINT "<input type=hidden name=ID value=$ID />";
		echo '<select name="showtracking" size="1" />';
		$options = array(
				'allandclosed' => _("Show All Tracking inc Closed"),
				'all' => _("Show All Tracking"),
				'open' => _("Show Current Tracking"),
				'none' => _("Hide Tracking")
				);
		echo select_options($options, $_REQUEST['showtracking']);
		PRINT "</select>";
		PRINT '<input type="checkbox" name="showfollowups"' . Checked(@$_REQUEST['showfollowups']) .'/>';
		__("Show Followups");
		PRINT '<input type="submit" value="' . _("Show Tracking") . '" /></form>';
		PRINT "</td>";
		PRINT "</tr>\n";
		PRINT "</table>";
		PRINT "<h3>Device Tracking</h3>";
		deviceTracking($ID, $devicetype);
		PRINT "<h3>Group Tracking</h3>";
		groupTracking($ID, $devicetype);

	}
}
?>
