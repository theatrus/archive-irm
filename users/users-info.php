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

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

function ShowJobs($notClosed){
	$notClosedSize = sizeof($notClosed);
	if (Config::Get('showjobsonlogin'))
	{
		Tracking::displayHeader();
		for($i=0;$i<$notClosedSize;$i++)
		{
			$track = new Tracking($notClosed[$i]);
			$track->display();
		}
		Tracking::displayFooter();
	}
}

AuthCheck("normal");
commonHeader(_("User") . " - " . sprintf(_("Info on %s"),$ID));

$user = new User($ID);
$fullname = $user->getFullname();
$user->displayLong();

$uname = $DB->getTextValue($IRMName);
$query = "SELECT tracking_order FROM prefs WHERE (user = $uname)";
$DB = Config::Database();
$tracking_order = $DB->getOne($query);

if($tracking_order == "yes")
{
  $tracking_order = "date ASC";
} else
{
  $tracking_order = "date DESC";
}

PRINT "<a href=\"".$_SESSION['_sess_pagehistory']->Previous()."\">" . _("Go Back") . "</a><hr noshade>\n";
PRINT "<table>\n";
PRINT "<tr><th>";
printf(_("Requests entered by %s"),$fullname);
PRINT "</th></th>\n";
PRINT "<tr><td>\n";
$notClosed = Tracking::getNotClosedBy("ASC", $ID);
$notClosedSize = sizeof($notClosed);

printf(_("%s has entered %s request(s) that have not yet been completed."),$fullname,$notClosedSize);
ShowJobs($notClosed);
$type = $user->getType();
if(($type == "tech") || ($type == "admin"))
{
	PRINT "<tr><th>";
	printf(_("Requests assigned to %s"),$fullname);
	PRINT "</th></tr>\n";
	PRINT "<tr><td>\n";
	#
	# Show how many jobs you have assigned to you currently :)
	#
	$notClosed = Tracking::getNotClosed("yes", "u:$ID", $tracking_order);
	$notClosedSize = sizeof($notClosed);
		
	printf(_("%s has %s job(s) assigned to him/her."),$fullname,$notClosedSize);
	ShowJobs($notClosed);
	
}	

PRINT "</td></tr></table>\n";
commonFooter();
