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

function getTracking($assign)
{
	$DB = Config::Database();
	$startdate = $DB->getTextValue($startdate);
	$enddate = $DB->getTextValue($enddate);
	$query = 'SELECT * FROM tracking WHERE assign = "' . $assign . '"';
	return $DB->getAll($query);
}

function getUsers()
{
	$DB = Config::Database();
	$startdate = $DB->getTextValue($startdate);
	$enddate = $DB->getTextValue($enddate);
	$query = 'SELECT * FROM users';
	return $DB->getAll($query);
}
function trackingtime($tracking,$years,$weeks){
	$dto = new SimpleDateTimeObject();
	$trackcount = 0;
	foreach($tracking as $track){
		$dto->setMySQLDateTime($track['date']);
		$worktime = $dto->diff_MySQL($time);
		if($worktime['years'] == $years && $worktime['weeks'] == $weeks){
			$trackcount++;
		
		}
	}
	return $trackcount;
}

function trackingForUsers($years, $weeks){
	$users = getUsers();
	PRINT "<table class=tracking>";
	foreach($users as $user){
		$tracking = getTracking($user['name']);
		PRINT "<tr class=trackingdetail><td>New work Requests for " . $user['fullname'] . "</td><td>".  trackingtime($tracking,$years,$weeks) . "</td></tr>" ;
	}
	PRINT "</table>";
}
commonHeader(_("New jobs opened and assigned to user."));
PRINT "<h3>Last Seven Days</h3>";
trackingForUsers(0,0);
PRINT "<h3>8-14 Days</h3>";
trackingForUsers(0,1);
PRINT "<h3>15-21 Days</h3>";
trackingForUsers(0,2);
PRINT "<h3>21-28 Days</h3>";
trackingForUsers(0,3);
commonFooter();
?>
