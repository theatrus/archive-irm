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

AuthCheck("tech");

$installs = Count_installations($sID);
$licenses = Count_licenses($sID);
$lID = find_license($sID, $reqdliccnt);

$DB = Config::Database();

if($_REQUEST['ID'] != null){
	$cID = $_REQUEST['ID'];
}

function installSoftware($cID, $sID, $lID, $gID, $reqdliccnt){
	# This block is run if we have found a license for our goal or
	# If we did not find a license for our goal and are installing
	# something else or forcing the install. In the later 2 cases
	# $gID is defined (from the form generated below) while in the 
	# first case $gID is not defined and should be our $sID.
	
	$DB = Config::Database();

	if (! $gID) { $gID = $sID; }; 

	if ($gID && $sID && $lID && $cID)
	{
		
		$vals = array(
			'cID' => $cID,  // Computer ID
			'sID' => $sID,  // Software ID
			'lID' => $lID,  // License ID
			'gID' => $gID,  // ??? ID
			'lCnt' => $reqdliccnt // Required Number of Licenses
			);
		$DB->InsertQuery('inst_software', $vals);
	}
}

if($lID > 0 or @$force==1) 
{
	installSoftware($cID, $sID, $lID, $gID, $reqdliccnt);
	header("Location: ".appendURLArguments($_SESSION['_sess_pagehistory']->Previous(), array('ID' => $cID)));
} else {
	commonHeader(_("Software") . " - " . _("Searching for Licenses"));
	# We couldn't find any direct licenses for the product so
	# lets check for any software bundles that contain the product.
	$qsID = $DB->getTextValue($sID);
	$query = "SELECT software.name FROM software
					WHERE ID=$qsID";
	$sname = $DB->getOne($query);

	$query = "SELECT software_bundles.bID, software.name 
		  FROM software_bundles 
		  LEFT JOIN software ON software.ID=software_bundles.bID
		  WHERE software_bundles.sID=$qsID 
		  ORDER BY software_bundles.bID";

	$data = $DB->getAll($query);
	$numRows = count($data);

	if (!$numRows)
	{
		printf ('<p>%s<a href="%s">%s</a><p><a href="%s">%s</a>',
			_("No licenses for the software package were found, and no bundles containing the software were found either."),
			Config::AbsLoc('users/software-index.php'),
			_("Please add one or more licenses or bundles using the Software Management System."),
			Config::AbsLoc("users/computers-index.php?action=info&ID=$cID"),
			_("Return to the computer info form.")
			);
		commonFooter();
		exit;
	}
	printf (_("I found the following %s Software
		bundles that contain the program you were
		looking for. You can either select the software you were 
		trying to install or you can select a bundle. Please note
		that this will force an installation even if there is no
		available license. This will also use goals to allow going
		back to see what license you wanted as opposed to which one
		you installed."), $numRows);
	$installs = Count_installations($sID);
	$licenses = Count_licenses($sID);
	$available = $licenses - $installs;

	print "\n<form action=computers-software-add.php method=post>";
	print "<table>";
	PRINT '<tr class="computerheader">';
	PRINT "<td>&nbsp</td>";
	PRINT "<td>Software</td>";
	PRINT "<td><B>Licenses</B> Available Installed/Licenses</td>";
	PRINT "</tr>";

	PRINT '<tr class="computerdetail">';
	PRINT "<td><input type=radio name=sID value=$sID></td>";
	PRINT "<td>$sname</td>";
	PRINT "<td>$available ($installs/$licenses)</td>";
	PRINT "<tr>";
	
	foreach ($data as $result)
	{
		$name = $result[name];
		$bID = $result[bID];
		print '<tr class="computerdetail">';
		PRINT "<td><input type=radio name=sID value=$bID></td>";
		PRINT "<td>$name ($bID)</td>";
				
		$installs = Count_installations($bID);
		$licenses = Count_licenses($bID);
		$available = $licenses - $installs;
		print "<td>$available ($installs/$licenses)</td>";
		PRINT "</tr>";
	}
	print "</table>";
	// Ignore the current page in the "history"
	
	$_SESSION['_sess_pagehistory']->Rollback();
	
	PRINT "<input type=submit  value=\""._("ADD")."\">";
	PRINT "<input type=hidden name=cID value=$cID>";
	PRINT "<input type=hidden name=gID value=$sID>";
	PRINT "<input type=hidden name=force value=1></form>\n\n";
}
