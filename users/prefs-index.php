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
require_once 'lib/Config.php';
require_once 'include/setup.functions.php';

if(isset($_POST['user']))
{
	prefsUpdate();
}

$DB = Config::Database();
$uname = $DB->getTextValue($IRMName);
$query = "SELECT * FROM prefs WHERE (user = $uname)";
$result = $DB->getRow($query);

$advanced_tracking = Checked($result["advanced_tracking"]);
$tracking_order = Checked($result["tracking_order"]);

AuthCheck("normal");
commonHeader(_("Setup - Your Preferences"));

__("This is the place to make IRM what you, and only you, want it to be.  Here you can change what you see in the computers list view, as well as change your password.");

PRINT "<hr noshade>";
PRINT "<p align=center>";
printf('<a href="%s">%s</a>', Config::AbsLoc('users/passwd.php'), _("Change Your Password"));
PRINT "<hr noshade>";
PRINT '<form method="post" action="'.  $_SERVER['PHP_SELF'] .'">';
PRINT "<input type=hidden name=user value=\"$IRMName\">";

PRINT "<table>";

PRINT "<tr>";
PRINT "<th>" . _("Here you can change what fields you see in the Computers list view.") . "</th>";
PRINT "</tr>";

foreach ($computerListElements as $name => $string) 
{
	PRINT "<tr class=trackingdetail>";
	PRINT "<td>";
	PRINT "<input type=hidden name=$name>";
	PRINT "<input type=checkbox name=$name value=\"yes\" " . Checked($result[$name]) . ">$string";
	PRINT "</td>";
	PRINT "<tr>";
}		

PRINT "<tr>";
PRINT "<th>" . _("Here you can change what you see in the Tracking view.") . "</th>";
PRINT "</tr>";

PRINT "<tr class=trackingdetail>";
PRINT "<td>";
PRINT "<input type=hidden name=advanced_tracking>";
PRINT "<input type=checkbox name=advanced_tracking value=\"yes\" $advanced_tracking>"._("Advanced Tracking View");
PRINT "</td>";
PRINT "<tr>";

PRINT "<tr class=trackingdetail>";
PRINT "<td>";
PRINT "<input type=hidden name=tracking_order>";
PRINT "<input type=checkbox name=tracking_order value=\"yes\" $tracking_order>"._("View Oldest Tracking First");
PRINT "</td>";
PRINT "<tr>";

PRINT "<tr class=trackingupdate>";
PRINT "<td>";
PRINT "<input type=submit value=\""._("Change")."\">";
PRINT "<input type=reset value=\""._("Reset")."\">";
PRINT "</td>";
PRINT "<tr>";

PRINT "</form>";

PRINT "</table>";
commonFooter();
