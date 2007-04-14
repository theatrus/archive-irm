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
require_once 'include/i18n.php';

$outquery = $aquery;
AuthCheck("tech");
commonHeader(_("Computers") . " - " . _("Setup computers in groups"));
__("Use this utility to Setup computers in groups.");
PRINT "<br>";
PRINT "<hr noshade>";

PRINT "<table>\n";

PRINT '<tr class="computerheader">';
PRINT "<td colspan=\"2\">\n";
__("Add all computers from previous search to group");
PRINT '<form method=post action="'.Config::AbsLoc('users/computers-groups-batch-add.php').'">';
Dropdown_groups("groups", "sID");
PRINT "</td>";
PRINT "</tr>\n";

PRINT '<tr class="computerupdate">';
PRINT "<td align=center>";
PRINT '<form method=post action="'.Config::AbsLoc('users/computers-groups-batch-add.php').'">';
PRINT "<input type=hidden name=outquery value=\"$outquery\">";
PRINT "<input type=submit value=\""._("Add")."\">";
PRINT "</form>";
PRINT "</td>\n";
PRINT "<td align=center>";
PRINT '<form method=post action="'.Config::AbsLoc('users/computers-groups-batch-del.php').'">';
PRINT "<input type=hidden name=outquery value=\"$outquery\">";
PRINT "<input type=hidden name=sID value=$sID>";
PRINT "<input type=submit value=\""._("Delete")."\">";
PRINT "</form>";
PRINT "</td>\n";
PRINT "</tr>";

PRINT "</table>";

PRINT "<br>";
__("NOTE: Sometimes this may take a while, depending on the number of 
		computers you have (upwards of 10 seconds).");

commonFooter();
