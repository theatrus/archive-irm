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
	$startdate = $DB->getTextValue($startdate);
	$enddate = $DB->getTextValue($enddate);
	$query = "SELECT * FROM tracking";
	return $DB->getAll($query);
}

function Graph($dataArray, $colour, $headerString){
	ksort($dataArray);

	
	$graphString = "<tr><th colspan=2>$headerString</th></tr>";

	foreach($dataArray as $key => $value)
	{
		$graphString .= "<tr>";
		$graphString .= "<td>Week Number : " . $key . "</td>";
		$graphString .= "<td><img src=../../images/$colour.bmp height=10 width=$value> $value</td>";
		$graphString .= "</tr>";
	}
	return $graphString;
}
commonHeader(_("Tracking") . " - " . _("Report Results"));

$tracking = getTracking();

$dto = new SimpleDateTimeObject();

$openTracking = "";
$closedTracking = "";

$closed = array();
$opened = array();

foreach($tracking as $track){
	
	if($track['closedate'] == ""){
		$openTracking = $openTracking + 1;
	} else {
		$closedTracking = $closedTracking + 1;
	} 
	
	$dto->setMySQLDateTime($track['date']);
	$worktime = $dto->diff_MySQL($time);

	$totalweeks = "0";
	if ($worktime['years'] != 0){
		$totalweeks = $worktime['years'] * 52;
	}
	if ($worktime['weeks'] != 0){
		$totalweeks = $totalweeks + $worktime['weeks'];
	}	
	$opened[] = $totalweeks;

	$dto->setMySQLDateTime($track['closedate']);
	$worktime = $dto->diff_MySQL($time);

	$totalweeks = "0";
	if ($worktime['years'] != 0){
		$totalweeks = $worktime['years'] * 52;
	}
	if ($worktime['weeks'] != 0){
		$totalweeks = $totalweeks + $worktime['weeks'];
	}	
	$closed[] = $totalweeks;
}




$numberOfWeeks = 0;

$closedArray = array_count_values($closed);
$openedArray = array_count_values($opened);
ksort($closedArray);
ksort($openedArray);

PRINT "Opened = <img src=../../images/blue.bmp height=10 width=10>";
PRINT "<br>";
PRINT "Closed= <img src=../../images/red.bmp height=10 width=10>";

PRINT "<table>";

do {
	$openedCount = $openedArray[$numberOfWeeks];
	$closedCount = $closedArray[$numberOfWeeks];
	
	PRINT "<tr>";
	PRINT "<td>Week Number : " . $numberOfWeeks. "</td>";
	PRINT "<td>";
	if(!$openedCount == 0){
		PRINT "<img src=../../images/blue.bmp height=10 width=" . $openedCount * 2 . ">$openedCount";
	}
	PRINT "<br>";
	if(!$closedCount == 0){
		PRINT "<img src=../../images/red.bmp height=10 width=" . $closedCount * 2 . ">$closedCount";
	}
	PRINT "</td>";
	PRINT "</tr>";
	$numberOfWeeks++;
} while ($numberOfWeeks != 200);

PRINT "</table>";


PRINT "<table>";
/*
PRINT Graph(array_count_values($closed),"red", _("Jobs Closed"));
PRINT Graph(array_count_values($opened),"blue", _("Jobs Opened"));

PRINT "</table>";
if(0){
	$closedArray = array_count_values($closed);
	ksort($closedArray);
	PRINT_R($closedArray);
	PRINT "<hr>";
	$openedArray = array_count_values($opened);
	ksort($openedArray);
	PRINT_R($openedArray);
	PRINT "<hr>";
}
*/
PRINT _("Total Open Tracking") . "<b> " . $openTracking . "</b>";
PRINT "<br />";
PRINT _("Total Closed Tracking") . "<b> " . $closedTracking . "</b>";
commonFooter();
?>
