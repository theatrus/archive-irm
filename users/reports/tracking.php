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

$months = array('01' => _("January"),
		'02' => _("February"),
		'03' => _("March"),
		'04' => _("April"),
		'05' => _("May"),
		'06' => _("June"),
		'07' => _("July"),
		'08' => _("August"),
		'09' => _("September"),
		'10' => _("October"),
		'11' => _("November"),
		'12' => _("December")
		);

function Dropdown_months($months)
{
	foreach($months as $key => $value)
	{
		PRINT "<OPTION VALUE=\"$key\">$value</OPTION>\n";
	}
}

function Dropdown_daysOfMonth()
{
	for($day = 1; $day < 32; $day++)
	{
		PRINT "<OPTION VALUE=\"$day\">$day</OPTION>\n";
	}
}
function Years()
{
	for ($i = date('Y'); $i > 1998; $i--)
	{
		echo "\t<OPTION VALUE=\"$i\">$i</OPTION>\n";
	}
}

function getTracking($startdate, $enddate)
{
	$DB = Config::Database();
	$startdate = $DB->getTextValue($startdate);
	$enddate = $DB->getTextValue($enddate);
	$query = "SELECT ID FROM tracking where (closedate > $startdate)
					AND (closedate < $enddate)";
	return $DB->getCol($query);
}

function getTrackingByUser($startdate, $enddate, $username)
{
	$DB = Config::Database();
	$username = $DB->getTextValue($username);
	$startdate = $DB->getTextValue($startdate);
	$enddate = $DB->getTextValue($enddate);
	$query = "SELECT ID FROM tracking WHERE (assign = $username)
				AND (closedate > $startdate)
				AND (closedate < $enddate)";
	return $DB->getCol($query);
}

if (@$go == "yes") 
{
	commonHeader(_("Tracking") . " - " . _("Report Results"));
	# 1. Get some number data
	$startdate = $startyear . "-" . $startmonth . "-" . $startday;
	$enddate = $endyear . "-" . $endmonth . "-" . $endday;

	$tracking = getTracking($startdate, $enddate);
	$number_of_tracking = count($tracking);
	
	# 2. Spew out the data in a table
	
	PRINT "<table>";
	
	PRINT '<tr class="trackingheader">';
	PRINT "<td>" . _("Number of Closed Tracking:") . "</td>";
	PRINT "<td>$number_of_tracking</td>";
	PRINT "</tr>";	

	PRINT '<tr class="trackingdetail">';
	PRINT "<td colspan=2>" . _("Tracking by user:") .  "</td>";
	PRINT "</tr>";

	# 3. Get some more number data (operating systems per computer)

	$query = "SELECT name FROM users WHERE type='tech' OR type='admin' ORDER BY name";
	$names = $DB->getCol($query);
	
	foreach ($names as $username)
	{
		$usertracking = getTrackingByUser($startdate, $enddate, $username);
		$numRows2 = count($usertracking);
		PRINT '<tr class="trackingdetail">';
		PRINT "<td>$username</td>";
		PRINT "<td>$numRows2</td></tr>";
	}
	PRINT "</table>";
}
else 
{
	commonHeader(_("Tracking") . " - " . _("Report"));

	__("Welcome to the Default Tracking Report! This report is designed
	    to inform you of the tracking requests that have been completed
	    or marked old during the time you specify.");
	echo '<p>'._("To generate the report: select a start date, an end
		      date, and click on the go button.");
?>

	<form action="<?php echo Config::AbsLoc('users/reports/tracking.php'); ?>">

	<?php 
	PRINT "<table>";

	# Start Date Section
	PRINT '<tr "class="trackingheader">';
	PRINT "<td>" . _("Select a start date:") . "</td>";
	PRINT "</tr>";
	
	PRINT '<tr class="trackingdetail">';
	PRINT "<td>";
	PRINT "<select name=startyear>";
	Years();
	PRINT "</select>";
	PRINT "<select name=startmonth>";
	Dropdown_months($months); 
	PRINT "</select>";
	PRINT "<select name=startday>";
	Dropdown_daysOfMonth(); 
	PRINT "</select>";
	PRINT "</td>";
	PRINT "</tr>";

	# End Date Section
	PRINT '<tr "class="trackingheader">';
	PRINT '<td>' . _("Select an end date:") . "</td>";
	PRINT "</tr>";
	
	PRINT '<tr class="trackingdetail">';
	PRINT '<td>';
	PRINT "<select name=endyear>";
	Years(); 
	PRINT "</select>";
	PRINT "<select name=endmonth>";
	Dropdown_months($months);
	PRINT "</select>";
	PRINT "<select name=endday>";
	Dropdown_daysOfMonth(); 
	PRINT "</select>";
	PRINT "</td>";
	PRINT "</tr>";
	
	# Submit
	PRINT '<tr class="trackingupdate">';
	PRINT '<td>';
	PRINT "<input type=submit value=" . _("Go") . "><input type=hidden name=go value=yes>";
	PRINT "</form>";
	PRINT "</td>";
	PRINT "</tr>";

	PRINT "</table>";
}
commonFooter();
?>
