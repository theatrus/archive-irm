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
#    Modifyed to fit more on one page.
#    Mica Currie, Barton Insurance.

require_once '../include/irm.inc';
require_once 'include/i18n.php';
require_once 'lib/Databases.php';
require_once '../include/setup.functions.php';


if(isset($_POST['lookupId']))
{	
	setupLookup();
}

$lookups = new Lookup();

AuthCheck("tech");
commonHeader(_("Setup dropdowns"));
__("Welcome to IRM dropdown setup.  Here we will administer values that are used in the dropdown fields, when using dropdowns with devices you need to know the ID for the dropdown to work with the device.");

PRINT "<table>";
PRINT '<tr class="setupheader">';
PRINT '<th colspan="3">';
PRINT	_("Lookup Configuration");
PRINT "</th>";
PRINT "</tr>";


$lookupIds = $lookups->lookupList;

foreach ($lookupIds as $lookupId)
{
	$lookup = new Lookup($lookupId);
	PRINT '<tr class="setupdetail">';
	PRINT '<td class="setupheader" colspan=3>' . $lookup->lookupDesc . "</td>";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT '<td>';
	PRINT '<form method="post" action="'. $_SERVER['PHP_SELF'] .'">';
	echo $lookup->dropdown("value");
	PRINT '<input type="hidden" name="lookupId" value=' . $lookupId . '>';
	PRINT '<input type="hidden" name="action" value="delete">';
	PRINT '<input type="submit" value="' . _("Delete") . '">';
	PRINT '</form>';
	PRINT "</td>\n";
	PRINT "<td>";
	PRINT '<form method="post" action="'. $_SERVER['PHP_SELF'] .'">';
	PRINT '<input type="text" maxlength="100" name="value">';
	PRINT '<input type="hidden" name="lookupId" value=' . $lookupId . ">";
	PRINT '<input type="hidden" name="action" value="add">';
	PRINT '<input type="submit" value="' . _("Add")  . '">';
	PRINT "</form>";
	PRINT "</td>\n";
	PRINT '<td>' . _("ID : ") . $lookupId. "</td>";
	PRINT "</tr>\n";
	$lookup = NULL;
}
PRINT "</table>";

PRINT "<hr/>";

PRINT "<table>";
PRINT '<tr class="setupheader">';
PRINT '<th colspan="2">';
PRINT	_("Add new lookup - the ID is the name referenced in the database, this must be not have any spaces in it. The name is what is displayed on the form when entering a new device. The description is for information and is only used on this page at the present time.");
PRINT "</th>";
PRINT "</tr>";


PRINT '<tr class="setupdetail">';
PRINT '<td>';
PRINT '<form method="post" action="'. $_SERVER['PHP_SELF'] .'">';
PRINT _("Lookup ID");
PRINT '<input type="text" name="lookupId" value="">';
PRINT _("Lookup Name");
PRINT '<input type="text" name="lookupName" value="">';
PRINT _("Lookup Description");
PRINT '<input type="text" name="lookupDescription" value="">';
PRINT '<input type="hidden" name="action" value="newlookup">';
PRINT '<input type="submit" value="' . _("Add") . '">';
PRINT '</form>';
PRINT "</td>\n";
PRINT "</tr>";

PRINT "</table>";

commonFooter();
