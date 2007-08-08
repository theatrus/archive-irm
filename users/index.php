<?php
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
require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("post-only");
$user = new User($IRMName);
$type = $user->getType();

if ($type == "post-only")
{
 	header("Location: ".Config::AbsLoc("users/helper-index.php"));
 	__("Redirecting to users/helper-index.php"); ## does this even need to be here?
 	exit();
}

commonHeader(_("Command Center"));

if($type == "admin" || $type == "tech" || $type == "normal") {
	__("Welcome to IRM, the Information Resource Manager!  This is the
	command center.  The command center is designed to allow a quick
	look at all work requests assigned to you, as well as an overview
	of recent changes IRM.  You can navigate to any of the sub modules
	of IRM by choosing the appropiate entry on the toolbar above.");
}

/*
 * If the user type is admin show work open work request for each user, that has work assigned to them.
 */
if($type == "admin"){
	PRINT "<h3>" . _("Jobs for each Technician") . " - " . "<a href=alert.php>" . _("Display Alerts") . "</a></h3>";
	$DB = Config::Database();
	$users = $DB->getAll("SELECT * from users");

	PRINT "<table class=tracking-count>";
	PRINT "<tr>";
	PRINT "<th>" . _("Technician") . "</th>";
	PRINT "<th>" . _("Work requests open") . "</th>";
	PRINT "<th>" . _("Assigned") . "</th>";
	PRINT "<th>" . _("Active") . "</th>";
	PRINT "<th>" . _("New") . "</th>";
	PRINT "<th>" . _("Wait") . "</th>";
	PRINT "<th>" . _("Very High") . "</th>";
	PRINT "<th>" . _("High") . "</th>";
	PRINT "<th>" . _("Normal") . "</th>";
	PRINT "<th>" . _("Low") . "</th>";
	PRINT "<th>" . _("Very Low") . "</th>";
	PRINT "</tr>\n";
	
	$totalCount = 0;
	
	foreach($users as $user){
		$sql = 'select COUNT(*) from tracking where status="assigned" and assign="' . $user['name'] . '"';
		$assigned= $DB->getOne($sql);
	
		$sql = 'select COUNT(*) from tracking where status="active" and assign="' . $user['name'] . '"';
		$active= $DB->getOne($sql);

		$sql = 'select COUNT(*) from tracking where status="wait" and assign="' . $user['name'] . '"';
		$wait= $DB->getOne($sql);

		$sql = 'select COUNT(*) from tracking where status="new" and assign="' . $user['name'] . '"';
		$new= $DB->getOne($sql);

		$sql = 'select COUNT(*) from tracking where status<>"complete" and status<>"old" and status<>"duplicate" and priority="5" and assign="' . $user['name'] . '"';
		$priority5 = $DB->getOne($sql);

		$sql = 'select COUNT(*) from tracking where status<>"complete" and status<>"old" and status<>"duplicate" and priority="4" and assign="' . $user['name'] . '"';
		$priority4 = $DB->getOne($sql);
		
		$sql = 'select COUNT(*) from tracking where status<>"complete" and status<>"old" and status<>"duplicate" and priority="3" and assign="' . $user['name'] . '"';
		$priority3 = $DB->getOne($sql);

		$sql = 'select COUNT(*) from tracking where status<>"complete" and status<>"old" and status<>"duplicate" and priority="2" and assign="' . $user['name'] . '"';
		$priority2 = $DB->getOne($sql);
	
		$sql = 'select COUNT(*) from tracking where status<>"complete" and status<>"old" and status<>"duplicate" and priority="1" and assign="' . $user['name'] . '"';
		$priority1 = $DB->getOne($sql);
		
		$tracking_order = "date ASC";
		$notClosed = Tracking::getNotClosed("yes", "u:" . $user['name'], $tracking_order);
		$notClosedSize = sizeof($notClosed);
		if($notClosedSize != 0){
			PRINT "<tr class=trackingdetail>";
			PRINT "<td>" . $user['name'] . "</td>";
			PRINT "<td>" . $notClosedSize . "</td>";
			PRINT "<td>" . $assigned. "</td>";
			PRINT "<td>" . $active. "</td>";
			PRINT "<td>" . $new. "</td>";
			PRINT "<td>" . $wait. "</td>";
			PRINT "<td>" . $priority5. "</td>";
			PRINT "<td>" . $priority4. "</td>";
			PRINT "<td>" . $priority3. "</td>";
			PRINT "<td>" . $priority2. "</td>";
			PRINT "<td>" . $priority1. "</td>";
			PRINT "</tr>\n";
		$totalCount = $totalCount + $notClosedSize;
		}
	}	
	PRINT "<tr class=trackingdetail>";
	PRINT "<td>Total Open Work Requests</td>";
	PRINT "<td>" . $totalCount . "</td>";
	PRINT "</tr>\n";

	PRINT "</table>\n";

}

if($type == "admin" || $type == "tech")
{
	print "<br />";

	$DB = Config::Database();
	
	$uname = $DB->getTextValue($IRMName);
	$query = "SELECT advanced_tracking,tracking_order FROM prefs WHERE (user = $uname)";
	$result = $DB->getRow($query);
  	$advanced_tracking = $result["advanced_tracking"];
  	$tracking_order = $result["tracking_order"];
	
	if($tracking_order == "yes")
	{
		$tracking_order = "date ASC";
	} else {
		$tracking_order = "date DESC";
	}

	# Show last 5 events
	$query = "SELECT * FROM event_log ORDER BY date DESC LIMIT 0,5";
	show_events($DB->getAll($query));

	print "<br />";

	# Show how many jobs you have assigned to you currently :)
	$notClosed = Tracking::getNotClosed("yes", "u:$IRMName", $tracking_order);
	$notClosedSize = sizeof($notClosed);

	# Show Tracking
	echo '<table>';
	PRINT "<tr><th>";
	__("Tracking");
	PRINT "</th></tr>\n";
	PRINT "<tr><td>\n";
	
	printf('<a href="%s">%s</a>',
				Config::AbsLoc("users/tracking-index.php?action=display&amp;show=u:$IRMName"),
				sprintf(_("You have %s job(s) assigned to you."), $notClosedSize)
				);
	if (Config::Get('showjobsonlogin'))
	{
		Tracking::displayHeader("open");
		for($i=0;$i<$notClosedSize;$i++)
		{
			$track = new Tracking($notClosed[$i]);
			$track->display(1);
		}
		Tracking::displayFooter();
	}
	
	PRINT "</td></tr></table>\n";
}

if ($type == "normal" || $type == "tech" || $type == "admin")
{
	# Show how many jobs you have entered that have not been closed
	$notClosed = Tracking::getNotClosedBy("ASC");
	$notClosedSize = sizeof($notClosed);

	PRINT '<table class="tracking-open">';
	PRINT "<tr>";
	PRINT "<th>";
	__("Open Work Requests");
	PRINT "</th></tr>\n";
	PRINT "<tr><td>\n";
		
	printf(_("You have entered %s request(s) that have not yet been completed."),
			$notClosedSize);

	if (Config::Get('showjobsonlogin'))
	{
		Tracking::displayHeader("notcomplete");
		for($i=0;$i<$notClosedSize;$i++)
		{
			$track = new Tracking($notClosed[$i]);
			$track->display(1);
		}
		Tracking::displayFooter();
	}
	PRINT "</td></tr></table>\n";
}
commonFooter();
