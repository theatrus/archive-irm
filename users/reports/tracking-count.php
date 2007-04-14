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

require_once '../../include/irm.inc';
require_once '../../include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");

function getTracking()
{
	$DB = Config::Database();
	$query = "SELECT * FROM tracking";
	return $DB->getAll($query);
}

function getOpenByUser($user)
{
	$DB = Config::Database();
	$query = 'SELECT * FROM `tracking` WHERE `assign`="' . $user . '" AND `status`<>"complete"';
	$assigned = $DB->getAll($query);
	return count($assigned);
	
}

function getUsers()
{
	$DB = Config::Database();
	$query = "SELECT name FROM users";
	return $DB->getAll($query);
}

commonHeader(_("Tracking") . " - " . _("Report Results"));

$tracking = getTracking();
$userlist = getUsers();

$openTracking = "";
$closedTracking = "";

PRINT "<table>";
PRINT "<tr class=trackingheader><th width=50%>" . _("User Name") . "</th><th>" . _("Requests not completed or closed") .  "</th></tr>";
foreach($userlist as $user)
{
	$count = getOpenByUser($user[name]);
	PRINT "<tr class=trackingdetail><td align=center>" . $user[name] . "</td><td align=center>" . $count  .  "</td></tr>";
}
PRINT "</table>";

foreach($tracking as $track){
	
	if($track['closedate'] == ""){
		$openTracking = $openTracking + 1;
	} else {
		$closedTracking = $closedTracking + 1;
	} 
}



PRINT _("Total Tracking") . "<b> " . count($tracking) . "</b>";
PRINT "<br />";
PRINT _("Total Open Tracking") . "<b> " . $openTracking . "</b>";
PRINT "<br />";
PRINT _("Total Closed Tracking") . "<b> " . $closedTracking . "</b>";
commonFooter();
?>
