<?php
#    IRM - The Information Resource Manager
#
#    Detailed Tracking Search Module
#    Copyright (C) 2004 Big Walnut Local Schools
#    written by David Maxwell, Technician Big Walnut Local Schools
#    Contains altered code from irm.inc, tracking-index.php, and
#    tracking-search.phpfiles included with the 1.4.2 version of IRM.
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
#                                  CHANGELOG                                   #
################################################################################
#  02/23/2004 Initial development of Detailed Tracking Search begun            #
#  02/24/2004 v0.01 Detailed Tracking Search                                   #
#  02/25/2004 v0.02 Fixed off-by-one bug in date ranges                        #
#  02/26/2004 v0.03 Added display of search parameters and number of results   #
#                   to search display.  Also added ability to select results   #
#                   with or without followups.                                 #
#                                                                              #
#  05/25/2004 v0.05 Michael Gower <michael.gower@ca.ibm.com> added ability     # 
#                   to search through the followups as well when the tracking  #
#                   contents are being search.  He made contents the default   #
#                   search with <space> the default content to search for.     #
################################################################################

require_once '../../include/irm.inc';
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';

AuthCheck("normal");

commonHeader(_("Tracking").' - '._("Search"));

print '<link href="../../style.css" rel="stylesheet" type="text/css">';

# GetDateSelectString produces a string from the WriteDateSelect function
# which passed data here as a POSTed parameters from tracking-detail.php
# It is based on a function by Jackson Yee (me@jacksoncomputing.com)

function GetDateSelectString($Prefix = '')
{
 $date = sprintf("%04d-%02d-%02d", $_GET[$Prefix . 'Year'], $_GET[$Prefix . 'Month'], $_GET[$Prefix . 'Day']);

 # Subtract a day from the beginning date so the date dialogs behave
 # the way the user would expect.

 if ($Prefix == "bd")
 {
  $date=strtotime($date); 
  $date=$date - 86400;
  $date=date("Y-m-d", $date);
 }

 # Add a day to the ending date for the same reason
	
 if ($Prefix == "ed")
 { 
  $date=strtotime($date);
  $date=$date + 86400;
  $date=date("Y-m-d", $date);
 }

 return $date;
}

function t_search($string)
{
	$USERPREFIX = Config::AbsLoc('users');

	$DB = Config::Database();
  
	$query = $string;
               
	return $DB->getCol($query);
}

function buildquery()
{
 extract($_REQUEST);

 $begin_date = GetDateSelectString("bd");
 $end_date = GetDateSelectString("ed");

 #### Added DISTINCT and LEFT JOIN on followups, mg ####
 $query_common = "SELECT DISTINCT tracking.ID FROM tracking LEFT JOIN followups on followups.tracking = tracking.ID LEFT JOIN computers ON tracking.computer = computers.ID WHERE ";

 if ($type_primary == "equals")
 { 
 $pri_clause = "((tracking.$primary = \"$contains\") ";
 #### Added if statement to check if the search is on the contents field, and if so, to also search the followsup, mg ####
 if ($primary == "contents")
  {
  $pri_clause2 = "OR (followups.contents = \"$contains\")";
  }
 } else 
   {
    $pri_clause = "((tracking.$primary LIKE \"%$contains%\") ";
    if ($primary == "contents")
    {
     $pri_clause2 = "OR (followups.contents LIKE \"%$contains%\")";
    }
    $pri_clause2 = $pri_clause2.") ";
   }

if ($use_secondary == "yes")
 {
 if ($type_secondary == "equals")
  {
  $sec_clause = "AND (tracking.$secondary = \"$contains2\") ";
  } else
  {
  $sec_clause = "AND (tracking.$secondary LIKE \"%$contains2%\") ";
  }
 } else 
   {
    $sec_clause = "";
   }

if ($limit_computers == "yes")
 {
 if ($type_machines == "equals")
  {
  $computer_clause = "AND (computers.$machines = \"$machines_limit\") ";
  } else
    {
     $computer_clause = "AND (computers.$machines LIKE \"%$machines_limit%\") ";
    }
 } else
 {
 $computer_clause = "";
 }

if ($date_range == "yes")
 {
 $date_clause = "AND (tracking.date BETWEEN \"$begin_date\" AND \"$end_date\")";
 } else
 {
 $date_clause = "";
 }


$complete_query = $query_common.$pri_clause.$pri_clause2.$sec_clause.$computer_clause.$date_clause;
return($complete_query);
}

?>

<h3><?php __("Search Results") ?></h3>
 
<?php  

$begin_date = GetDateSelectString("bd");
$end_date = GetDateSelectString("ed");

if ($contains == " ")
{
	PRINT "<b>$primary $type_primary "._("SPACE")."</b> \n";
}
else
{
	PRINT "<b>$primary $type_primary $contains</b> \n";
}

if ($use_secondary == "yes")
{
	PRINT _("AND")." <b>$secondary $type_secondary $contains2</b> \n";
}

if ($limit_computers == "yes")
{
	PRINT _("AND")." <b>$machines $type_machines $machines_limit</b> \n";
}

if($date_range == "yes")
{
	printf(_("between the dates %s and %s."), "<b>$begin_date</b>", "<b>$end_date</b>");
}

$query = buildquery();
$trackingIDs = t_search($query);

$numTrackingIDs = sizeof($trackingIDs);
printf(_("Found %s matching tracking entries"), "<B>$numTrackingIDs</B>");
PRINT "\n<br>\n<hr noshade>\n";

Tracking::displayHeader();
 
foreach ($trackingIDs as $tID)
{
	$track = new tracking($tID);

	if ($include_followups == "yes")
	{
		$track->display(true);
	}
	else 
	{
		$track->display(false);
	}
}

Tracking::displayFooter();
commonFooter();
