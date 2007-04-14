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

require_once '../include/i18n.php';
require_once '../include/irm.inc';
global $IRMName;
AuthCheck("post-only");

$status 	= $_REQUEST['status'];
$tID		= $_REQUEST['tID'];
$ComputerID	= $_REQUEST['ComputerID'];
$priority	= $_REQUEST['priority'];
$workrequest	= $_REQUEST['workrequest'];
$original	= $_REQUEST['original'];
$newfollowup	= $_REQUEST['newfollowup'];
$public		= $_REQUEST['public'];
$newminspent	= $_REQUEST['newminspent'];

$badperms = false;
$datenow = date("Y-m-d H:i:s");
$user2 = new User($IRMName);
$type = $user2->getType();
$permissions = $user2->permissionCheck("tech");
$tID = $_REQUEST['tID'];
$track = new Tracking($tID);
$isStat = $track->isStatus($status);
$isAssign = $track->isAssign(@$user);

if($permissions)
{
	$track->setComputerID($ComputerID);
	if(!$isStat)
	{
		$track->setStatus($status);
	}
	if(!$isAssign)
	{
		$track->setAssign($user);
	}
	if ($priority)
	{
		$track->setPriority($priority);
	}
	if ($workrequest)
	{
		$track->setWorkRequest($workrequest);
	}
} else {
	if((!$isStat) || (!$isAssign))
	{
		$badperms = true;
	}
}

$trimmedFollowup = trim($newfollowup);

if($trimmedFollowup != "")
{
	$follow = new Followup();
	$follow->setAuthor($IRMName);
	$follow->setFollowupInfo($newfollowup);
	$follow->setDateEntered(date('Y-m-d H:i:s'));
	$follow->setPublic(@$public);
	$follow->setMinSpent(@$newminspent);
	$track->addFollowup($follow);
}

if($workrequest != $original)
{
	$follow = new Followup();
	$follow->setAuthor($IRMName);
	$follow->setFollowupInfo(_("Work Request was changed from : ") .  $original);
	$follow->setDateEntered(date('Y-m-d H:i:s'));
	$follow->setPublic(@$public);
	$follow->setMinSpent(@$newminspent);
	$track->addFollowup($follow);
}


$track->commit();

$DB = Config::Database();

$close = $DB->getOne("SELECT closed FROM tracking_status WHERE status=".$DB->getTextValue($status));

if($close && $addtoknowledgebase == "yes" && $permissions)
{
	header("Location: ".Config::AbsLoc("users/knowledgebase-index.php?action=from_tracking&trackingID=$tID"));
} else {
	commonHeader(_("Tracking") . " - " . _("Update Information"));
	PRINT "<a href=\"".$_SESSION['_sess_pagehistory']->Previous()."\">" . _("Go Back") . "</a><hr noshade><br>";
	if($badperms){
		__("Since you are not a technician or administrator, you can not change the status of this work request, nor who it is assigned to.");
		PRINT "<br />";
		printf(_("You are %s"), $IRMName);
	}
	PRINT "<h4>";
	printf(_("Tracking %s has been updated"),$tID);
	PRINT "</h4>\n";
	commonFooter();
}

logevent($tID, _("computers"), 4, _("tracking"), _("Tracking job modified"));
