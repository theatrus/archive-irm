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
if ($go == "yes") 
{
	commonHeader(_("Reports") . " - " . _("Default Report"));
	# 1. Get some number data

	$query = "SELECT ID FROM computers";

	$DB = Config::Database();

	$computers = $DB->getCol($query);
	$number_of_computers = count($computers);

	$query = "SELECT ID FROM software";

	$software = $DB->getCol($query);
  	$number_of_software = count($software);
	
	# 2. Spew out the data in a table
	
	PRINT "<table>";
	PRINT '<tr class="trackingheader">';
	PRINT "<td>" . _("Number of Computers:") . "</td>";
	PRINT "<td>$number_of_computers</td>";
	PRINT "</tr>";	
	
	PRINT '<tr class="trackingheader">';
	PRINT "<td>" . _("Amount of Software:") . "</td>";
	PRINT "<td>$number_of_software</td>";
	PRINT "</tr>";

	PRINT '<tr class="trackingheader">';
	PRINT "<td colspan=2><b>" . _("Operating Systems") . ":</b></td>";
	PRINT "</tr>";

	# 3. Get some more number data (operating systems per computer)

	$query = 'SELECT * FROM `lookup_data` WHERE `lookup`=\'os\' ORDER BY value';
	$oslist = $DB->getAll($query);
	
	foreach ($oslist as $result)
	{
		$os = $DB->getTextValue($result["value"]);
		$query = "SELECT COUNT(ID) FROM computers WHERE (os = $os)";

		$oscount = $DB->getOne($query);
		PRINT '<tr class="trackingdetail">';
		PRINT "<td>$os</td>";
		PRINT "<td>$oscount</td>";
		PRINT "</tr>";
	}
	PRINT "</table>";
}
else 
{
	commonHeader(_("Reports") . " - " . _("Default Report"));
	__("Welcome to the Default Report!  This report is designed to be a
	    functional model of a real IRM Report.  It provides some simple
	    data, but could really be extended with graphics, percentages,
	    graphs, and user settable options.  But it serves as a good
	    jumping point for making your own report. (NOTE: The IRM header
	    is not necessary, I just put it in.  You must do a 'connectDB();'
	    though.)");
	echo '<p>'._("To generate the report, click on this button:");
?>
 
	<form action="<?php echo Config::AbsLoc('users/reports/default.php'); ?>">
<?
PRINT 	"<input type=submit value=" . ("Go") .">";
PRINT	"<input type=hidden name=go value=yes>";
PRINT	"</form>";
}
commonFooter();
?>
