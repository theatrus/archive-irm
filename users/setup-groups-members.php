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
$setupGroupMembers = new setupGroupMembers();
/*
AuthCheck("tech");

commonHeader(_("Computers") . " - " . _("Group Members"));

printf (_("Welcome to the IRM Group Setup utility.  Here you can edit members of a specified group."));
print '<a href="#add">'._('You may also add computers to a group').'</a>';
print "\n<hr noshade>\n";

$DB = Config::Database();
$qid = $DB->getTextValue($id);
$query = "SELECT name FROM groups WHERE ID = $qid";
$name = $DB->getOne($query);
echo _("Group:")." <b>$name</b> ($id)<br>\n";

$query = "SELECT c.name, c.id
		FROM computers AS c, comp_group AS g
		WHERE g.group_id = $qid
		   AND c.id = g.comp_id
		ORDER BY c.name";
$data = $DB->getAll($query);

if (count($data))
{
	PRINT "<table>\n";

	foreach ($data as $result)
	{
	  	$cname = $result['name'];
	  	$comp_id = $result['id'];
	  	PRINT "<tr><td width=\"50%\">$cname ($comp_id)</td>\n<td>"
	  	.'<a href="'.Config::AbsLoc("users/setup-groups-members-del.php?group_id=$id&comp_id=$comp_id").'">['._("Delete").']</a>
		</td></tr>';
	}

	PRINT "\n</table>\n";
}
else
{
	__("No computers in this group.");
}

PRINT "<a name=\""._("add")."\"></a><hr noshade><h4>"._("Add a Computer to Group")."</h4>";
PRINT '<form method=GET action="'.Config::AbsLoc('users/setup-groups-members-add.php').'">'
	."\n<input type=\"hidden\" name=\"group_id\" value=\"$id\">\n";
__("Computer ID: ");

if (isset($_REQUEST['use_select']))
{
	PRINT "<select name=comp_id size=1>";

	$query = "SELECT computers.id,computers.name
			FROM computers,comp_group
			WHERE comp_group.group_id=$id
			    AND computers.ID=comp_group.comp_id";
	$group = $DB->getAll($query);
	$query = "SELECT computers.id,computers.name
			FROM computers ORDER BY name";
	$all = $DB->getAll($query);
	
	$not_in_group = array_diff_for_real($all, $group);

	foreach ($not_in_group as $result)
	{
		PRINT '<OPTION VALUE="' . $result['id'] . '">' . $result['name'] . "</option>\n";
	}
	PRINT "</SELECT>";
	$switchlink = "<A HREF=\""
        	. Config::AbsLoc('users/setup-groups-members.php', array('id' => $_REQUEST['id']))
		. "\">"._("OR Enter the computer ID")."</A>";
}
else
{
	PRINT '<input type="text" name="comp_id" size="4">';
	$switchlink = '<A HREF="'
		. Config::AbsLoc('users/setup-groups-members.php', array('use_select' => 1,
									'id' => $_REQUEST['id']))
		. '">'._("OR Choose from a dropdown list of computers").'</A>';
}
PRINT "<input type=\"submit\" name=\"btn\" value=\""._("Add")."\"></FORM>\n";
PRINT $switchlink;

commonFooter();
*/
