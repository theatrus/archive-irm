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

AuthCheck("post-only");
commonHeader(_("FastTrack"));
__("Welcome to IRM FastTrack.  This is where tracking can be entered, assigned, and given a specific status all on one page.  Simply fill in the form below:"); 

$query = "select * from fasttracktemplates where (ID=$AUTOFILL)";
$DB = Config::Database();
$result = $DB->getRow($query);
$name = $result["name"];
$priority = $result["priority"];
$request = htmlspecialchars($result["request"]);
$response = htmlspecialchars($result["response"]);

$user = new User($IRMName);
$uemail = $user->getEmail();
$ufname = $user->getFullname();

__("Enter the IRM ID");
if(Config::Get('groups'))
{
	__("or group.  Make sure that you have selected the proper button to the left as well to indicate which identifier you are providing.");
	PRINT "\n<br />";
}
PRINT "<hr>\n";

PRINT '<FORM METHOD=get ACTION="'.Config::AbsLoc('users/tracking-fasttrack-add.php').'">';

PRINT "<table>";

# Computer/Group Information
PRINT "<tr>";
PRINT "<th>";
__("Computer");
if(Config::Get('groups'))
{
	PRINT "/";
	__("Group");
}
__(" Information");
PRINT "</th>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<INPUT TYPE=\"RADIO\" NAME=\"IDTYPE\" value=\"IRMID\">";
PRINT "<strong>" . _("IRM ID: ") . "</strong>";
PRINT "<INPUT TYPE=text NAME=ID SIZE=10>&nbsp;&nbsp;\n";
PRINT "<br />\n";
if(Config::Get('groups'))
{
	PRINT "<INPUT TYPE=\"RADIO\" NAME=\"IDTYPE\" value=\"GROUP\"> ";
	PRINT "<strong>";
	__("Select a group:");
	PRINT "</strong>";
	Dropdown_groups("groups", "gID");
}
PRINT "</td>\n";
PRINT "</tr>\n";

# User Information
PRINT "<tr>";
PRINT "<th>" . _("User Information") . "</th>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("User's Name:") . "</strong>\n";
PRINT "<input type=text size=15 name=ufname value=\"$ufname\">";
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("User's E-Mail:") . "</strong>\n";
PRINT "<input type=text name=uemail size=19 value=\"$uemail\">";
PRINT "</td>\n";
PRINT "</tr>\n";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("Other E-Mail:") . "</strong>\n";
PRINT "<input type=text name=oemail size=19 value=\"\">";
PRINT "</td>\n";
PRINT "</tr>\n";

PRINT "<tr>";
PRINT "<th>" . _("Work Request Information") . "</th>";
PRINT "</tr>";

# Tracking Detail
PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("Priority:") . "</strong>";
PRINT "<select NAME=priority>\n";
PRINT "<option value=5";
if($priority == 5)
{
	PRINT " selected";
}
PRINT ">Very High</option>\n";
PRINT "<option value=4";
if($priority == 4)
{
	PRINT " selected";
}
PRINT ">High</option>\n";
PRINT "<option value=3";
if($priority == 3)
{
	PRINT " selected";
}
PRINT ">Normal</option>\n";
PRINT "<option value=2";
if($priority == 2)
{
	PRINT " selected";
}
PRINT ">Low</option>\n";
PRINT "<option value=1";
if($priority == 1)
{
	PRINT " selected";
}
PRINT ">Very Low</option>\n";
PRINT "</SELECT>\n";
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";

PRINT "<strong>" . _("Describe the problem:") . "</strong>\n";
PRINT "<br />\n";
PRINT "<textarea cols=50 rows=4 wrap=soft name=\"contents\">$request</textarea>"; 
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";

PRINT "<strong>" . _("Describe the solution (will be added as a followup):") . "</strong>\n";
PRINT "<br />\n";
PRINT "<textarea cols=50 rows=4 wrap=soft name=\"solution\">$response</textarea>\n"; 
PRINT "</td>\n";
PRINT "</tr>\n";

#Additional Information
PRINT "<tr>";
PRINT "<th>" . _("Additional Information") . "</th>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("Assign to:") . "</strong>\n";
$track = new Tracking(@$ID);
$assign = $track->getAssign();
if($assign != "")
{
	Tech_list($assign, "user");
} else
{
	Tech_list("","user");
}
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("Set Status to:") . "</strong>\n";

PRINT "<select NAME=status SIZE=1>";
PRINT "<option value=\"active\">" . _("Active") . "</option>";
PRINT "<option value=\"assigned\">" . _("Assigned") . "</option>";
PRINT "<option value=\"complete\">" . _("Complete") . "</option>";
PRINT "<option value=\"new\" selected>" . _("New") . "</option>";
PRINT "<option value=\"old\">" . _("Old") . "</option>";
PRINT "<option value=\"wait\">" . _("Wait") . "</option>";
PRINT "</select>";
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<strong>" . _("Time Spent:") . "</strong>\n";
PRINT "<input type=text name=minspent size=19 value=\"0\">";
PRINT "</td>\n";
PRINT "</tr>\n";

PRINT '<tr class="trackingdetail">';
PRINT "<td>";
PRINT "<input type=checkbox name=addtoknowledgebase value=yes >";
__("If tracking is marked as complete, should it be used to add something to the knowledgebase?");
PRINT "</td>";
PRINT "</tr>";
#Submit
PRINT '<tr class="trackingupdate">';
PRINT "<td><input type=submit value=\"". _("Submit") ."\"></td>";
PRINT "<tr>";
PRINT "</table>";
PRINT "</form>";
commonFooter();
