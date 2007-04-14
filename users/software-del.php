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
require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("tech");

$DB = Config::Database();

$qID = $DB->getTextValue($ID);

$query = "SELECT * FROM software WHERE ID=$qID";
$result = $DB->getRow($query);

$name = $result['name'];
$class = $result["class"];
$platform = $result["platform"];

#$installations = Count_installations($ID);
#$licenses = Count_licenses($ID);
if ($force==1)
{
	$qID = $DB->getTextValue($ID);
	$query = "DELETE FROM software WHERE (ID = $qID)";
	$DB->query($query);
	$query = "DELETE FROM inst_software WHERE (sID = $qID)";
	$DB->query($query);
	$query = "DELETE FROM templ_inst_software WHERE (sID = $qID)";
	$DB->query($query);
	$query = "DELETE FROM software_bundles
		  WHERE (sID = $qID)";
	$DB->query($query);
	$query = "DELETE FROM software_licenses
		  WHERE (sID = $qID)";
	$DB->query($query);
	header("Location: ".Config::AbsLoc("users/software-index.php"));
}
else
{
	commonHeader(_("Software") . " - " . _("Deleted"));
	PRINT '<p id="warning">';
	__("Deleting this package will result in the removal of all Associated Records. Are you sure you want to delete this package. Remember you will loose the following Information about this package:<ul><li>Installations<li>Licenses <li>Comments<li>Templates<li>Bundles. </ul>");
	PRINT "</p>";

	print "<table>";
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Name") . "</td>";
	PRINT "<td>$name</td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Class") . "</td>";
	PRINT "<td>$class</td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Platform") . "</td>";
	PRINT "<td>$platform</td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Installations") . "</td>";
	PRINT "<td>$installations</td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Licenses") . "</td>";
	PRINT "<td>$licenses</td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="softwaredetail">';
	PRINT "<td>" . _("Bundles") . "</td>";
	PRINT "<td>$bundles</td>";
	PRINT "</tr>\n";

	PRINT '<tr class="softwareupdate">';
	PRINT "<td>";
	PRINT "<a href=\"".$_SESSION['_sess_pagehistory']->Previous()."\">". _ ("No") . "</a>&nbsp;&nbsp;";
	PRINT "</td>";

	PRINT "<td>";
	// Effectively ignore the current page in the "history"
	$_SESSION['_sess_pagehistory']->Rollback();

	PRINT "<a href=\"$REQUEST_URI?ID=$ID&force=1\">". _("Delete") . "</a>";
	PRINT "</td>";
	PRINT "</tr>";
	PRINT "</table>";
}
commonFooter();
