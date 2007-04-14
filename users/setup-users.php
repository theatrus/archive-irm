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

AuthCheck("admin");

commonHeader(_("User Setup"));
__("Welcome to the IRM User Setup utility.  Here you can ");
if(Config::UseLDAP())
{
	__("view and update users in the IRM database. \n");
	PRINT "<a href=\"./ldapupdate.php\">"._("Click here to update the database information from LDAP.")."</a>\n";
}
else
{
	__("change, view, delete, and add users to the IRM database. \n");
	PRINT "<a href=\"#add\">"._("Click here to add users.")."</a>\n";
}

$user = new User();
$user->displayAllUsers();


PRINT "<a name=\"add\"></a>";
PRINT '<form method=post action="'.Config::AbsLoc('users/setup-user-add.php').'">';

PRINT '<table>';
PRINT '<tr>';
PRINT '<th>' . _("Add a New User") . '</th>';
PRINT '</tr>';
		
PRINT '<tr class="setupdetail">';
PRINT "<td>"._("Username").": <input type=text width=20 name=username></td>";
PRINT '</tr>';
		
PRINT '<tr class="setupdetail">';
PRINT "<td>"._("Full Name:")."<input type=text width=40 name=fullname></td>";
PRINT "</tr>";

PRINT '<tr class="setupdetail">';
PRINT "<td>"._("Password:")."<input type=password width=20 name=password></td>";
PRINT "</tr>";

PRINT '<tr class="setupdetail">';
PRINT "<td>"._("E-mail:")."<input type=text width=20 name=email></td>";
PRINT '</tr>';
		
PRINT '<tr class="setupdetail">';
PRINT "<td>"._("Phone:")."<input type=text width=20 name=phone></td>";
PRINT "</tr>";

PRINT '<tr class="setupdetail">';
PRINT "<td>"._("Location")."<input type=text width=20 name=location></td>";
PRINT '</tr>';
		
PRINT '<tr class="setupdetail">';
PRINT "<td>"._("User Type:");
PRINT "<select name=type>\n";
PRINT "<option value=admin>"._("Administrator")."</option>\n";
PRINT "<option value=normal>"._("Normal")."</option>\n";
PRINT "<option value=post-only selected>"._("Post Only")."</option>\n";
PRINT "<option value=tech>"._("Technician")."</option>\n";
PRINT "</select>\n";
PRINT "</td>";
PRINT "</tr>";

PRINT '<tr class="setupupdate">';
PRINT "<td><input type=submit value=\""._("Add")."\"></td>";
PRINT "</tr>";
PRINT "</table>";
PRINT "</form>";

commonFooter();
