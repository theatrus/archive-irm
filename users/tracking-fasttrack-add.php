<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 1999,2000 Yann Ramin
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

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("post-only");

if($IDTYPE != "IRMID" && $IDTYPE != "GROUP")
{
	commonHeader(_("Tracking") . " - " . _("No IRM ID or Group name was selected"));
	__("ERROR: You forgot to select a computer or a group.\n");
	commonFooter();
	exit();
}

if($ufname == "")
{
	commonHeader(_("Tracking") . " - " . _("User's name was not entered"));
	__("ERROR: You did not enter the User's Name.");
	commonFooter();
	exit();
}

if($uemail == "")
{
	commonHeader(_("Tracking") . " - " ._("User's email address was not entered"));
	__("ERROR: You did not enter the User's email address.");
	commonFooter();
	exit();
}

if($IDTYPE == "IRMID")
{
	$query = "select COUNT(*) from computers where (ID=$ID)";
	$DB = Config::Database();
	if ($DB->getOne($query) != 1)
	{
		commonHeader(_("Tracking") . " - " . _("Bad IRM ID Number"));
		__("It appears that you have enetered an invalid IRM computer ID");
		commonFooter();
		exit();
	}
}

commonHeader(_("Tracking") . " - " . _("Added"));

$opendate = date("Y-m-d H:i:s");

if(Config::Get('userupdates'))
{
	$emailupdates = "yes";
} else
{
	$emailupdates = "no";
}

if($DB->getOne("SELECT closed FROM tracking_status WHERE status=".$DB->getTextValue($status)))
{
	$closedate = date("Y-m-d H:i:s");
	$emailupdates = "no";
}

if($IDTYPE == "IRMID")
{
	$is_group = "no";
} else if($IDTYPE == "GROUP")
{
	$is_group = "yes";
	$ID = $gID;
}

$contents = sprintf("%s $ufname ($uemail)\n", _("By:")) . $contents;
$track = new Tracking();
$track->setDateEntered($opendate);
$track->setCloseDate(@$closedate);
$track->setStatus($status);
$track->setAuthor($IRMName);
$auth = $track->getAuthor();
$track->setAssign($user);
$track->setComputerID($ID);
$track->setWorkRequest($contents);
$track->setPriority($priority);
$track->setIsGroup($is_group);
$track->setAuthorEmail($uemail);
$track->setOtherEmails($oemail);
$track->setEmailUpdatesToAuthor($emailupdates);
if($solution != "")
{
	$follow = new Followup();
	$follow->setDateEntered($opendate);
	$follow->setAuthor($IRMName);
	$follow->setFollowupInfo($solution);
	$follow->setMinSpent($minspent);
	$track->addFollowup(&$follow);
}
$track->add();
$trackingID = $track->getID();

logevent($ID, _("computers"), 4, _("tracking"), _("New tracking job opened")); 

__("That tracking job has been placed into the database.");
commonFooter();
