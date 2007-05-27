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
require_once dirname(__FILE__) . '/irm.inc';
require_once dirname(__FILE__) . '/i18n.php';

class Followup Extends IRMMain
{
	
	var $TrackingID;
	var $Author;
	var $FollowupInfo;
	var $MinSpent;
	var $Public;

	function Followup($ID=0)
	{
		$this->Public = 0;
		if($ID != 0)
		{
			$this->setID($ID);
			$this->retrieve();
		}
	}

	function retrieve()
	{
		$query = "select * from followups where(ID=$this->ID) ORDER BY date";
		$DB = Config::Database();
		$result = $DB->getRow($query);
		$this->setID($this->ID);
		$this->setTrackingID($result['tracking']);
		$this->setAuthor($result['author']);
		$this->setFollowupInfo($result['contents']);
		$this->setMinSpent($result['minspent']);
		$this->setDateEntered($result['date']);
		$this->setPublic($result['public']);
		$this->dateopened();
		$this->dateclosed();
	}

	function add()
	{
		if($this->ID != 0)
		{
			PRINT _("Error adding Followup, ") . _("ID != 0")."<BR>\n";
		}
		if($this->TrackingID == 0)
		{
			PRINT _("Error adding Followup, ") . _("trackingID is invalid")."<BR>\n";
		}
		if($this->Author == "")
		{
			PRINT _("Error adding Followup, ") . _(" author not specified")."<BR>\n";
		}
		if($this->FollowupInfo == "")
		{
			PRINT _("Error adding Followup, ") . _("followupInfo not added")."<BR>\n";
		}
		$this->resetDateEntered();
		$vals = array(
			'tracking' => $this->TrackingID,
			'date' => $this->DateEntered,
			'author' => $this->Author,
			'contents' => $this->FollowupInfo,
			'minspent' => $this->MinSpent,
			'public' => $this->Public
			);
		$DB = Config::Database();
		$DB->InsertQuery('followups', $vals);
	}

	function displayAddForm()
	{
		global $IRMName;
		PRINT '<tr class="followupdetail">';
		PRINT "<td><font COLOR=\"yellow\">"._("Add Followup")."</font></td>";
		PRINT "<td>$IRMName</td>";
		PRINT "<td>";

		fckeditor("newfollowup","");
		PRINT "<input type=checkbox name=public value=1 checked />" . _("Public followup");
		PRINT "</td>";
		PRINT "<td><input type=text size=3 value=0 name=newminspent /></td>";
		PRINT "</tr>";
	}
	
	function delete()
	{
		$DB = Config::Database();
		$id = $DB->getTextValue($this->ID);
		$query = "DELETE FROM followups WHERE (ID=$id)";
		$DB->query($query);
	}

	function getTrackingID()
	{
		return($this->TrackingID);
	}

	function getFollowupInfo()
	{
		return($this->FollowupInfo);
	}

	function setTrackingID($tID)
	{
		$this->TrackingID = $tID;
	}

	function setAuthor($sAuthor)
	{
		$this->Author = $sAuthor;
	}

	function setFollowupInfo($sFI)
	{
		$this->FollowupInfo = $sFI;
	}

	function setPublic($p)
	{
		$this->Public = $p ? 1 : 0;
	}

	function getMinSpent()
	{
		return($this->MinSpent);
	}

	function setMinSpent($sMinSpent)
	{
		$this->MinSpent = $sMinSpent;
	}


	function resetDateEntered()
	{
		$this->DateEntered = date("Y-m-d H:i:s");
	}

	function displayHeader()
	{
		PRINT '<table class="followup">';
		PRINT '<tr><th colspan="4">' . _("Followups") . '</th></tr>';
		PRINT '<tr class="followupsubheader">';
		PRINT "<td>" . _("Date") . "</td>";
		PRINT "<td>" . _("Author") . "</td>";
		PRINT "<td>" . _("Description") . "</td>";
		PRINT "<td>" . _("Time Spent") . "</td>";
		PRINT "</tr>";
	}

	function displayFooter()
	{
		PRINT "</table>";
	}

	function display()
	{
		global $IRMName;

		$user = new User($IRMName);
		$type = $user->getType();
		
		if ($type == 'tech' || $type == 'admin' || $this->Public)
		{
			$contents = nl2br($this->FollowupInfo);
		
			if (!$this->Public)
			{
				PRINT '<tr class="followupdetail"><td colspan="3"><b>' . _("Private Note") . "</b></td></tr>";
			}

			PRINT '<tr class="followupdetail">';
			PRINT "<td>" . $this->dateopened. "</td>";
			PRINT "<td>$this->Author</td>";
			PRINT "<td>$contents</td>";
			PRINT "<td>$this->MinSpent</td>";
			PRINT "</tr>";
		}
	}

	function commit()
	{
		if($this->ID == 0)
		{
			PRINT _("Error committing Followup:") . ' ' 
				. _("No ID given. Use \"add\" to add new
				Followups and \"commit\" to commit changes to Followups.")."<BR>\n";
			return (0);
		}
		if($this->TrackingID == 0)
		{
			PRINT _("Error committing Followup:") . ' '
				. _("Tracking ID is invalid.")."<BR>\n";
			return (0);
		}
		if($this->Author == "")
		{
			PRINT _("Error committing Followup:") . ' '
				. _("Author not specified.")."<BR>\n";
			return (0);
		}
		if($this->FollowupInfo == "")
		{
			PRINT _("Error committing Followup:") . ' '
				. _("Followup Info not added.")."<BR>\n";
			return (0);
		}
		$vals = array(
			'tracking' => $this->TrackingID,
			'date' => $this->DateEntered,
			'author' => $this->Author,
			'contents' => $this->FollowupInfo,
			'minspent' => $this->MinSpent,
			'public' => $this->Public
			);
		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		
		$DB->UpdateQuery('followups', $vals, "ID=$ID");
	}
	
	function getByTrackingID($tID)
	{
		$DB = Config::Database();

		$parms = array('tracking='.$DB->getTextValue($tID));
		
		global $IRMName;
		$user = new User($IRMName);
		if ($user->getType() != 'tech' && $user->getType() != 'admin')
		{
			$parms[] = "public=1";
		}

		$query = "select ID from followups where " . join(' AND ', $parms) . " ORDER BY date";

		return $DB->getCol($query);
	}

        function mailBody()
        {
                $mail .= _("-----")."\n";
                $mail .= _("Added by")." $this->Author, $this->dateopened\n";
                $mail .= _(" ")."$this->FollowupInfo\n";

		return $mail;
	}
}

?>
