<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 2006 Martin Stevens
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

class setupGroups{

	function setupGroups()
	{
		$this->ID = $_REQUEST['id'];

		switch($_REQUEST['action']){
		case 'add':
			$this->add();
			break;
		case 'delete':
			$this->delete();
			break;
		case 'update':
			$this->update();
			break;
		default:
			$this->main();
			break;
		}
	}

	function add(){
		$DB = Config::Database();
		$DB->InsertQuery('groups', array('name' => $_REQUEST['groupname']));
		$this->main();
	}

	function update(){
		$DB = Config::Database();
		$id = $DB->getTextValue($this->ID);
		$DB->UpdateQuery('groups', array('name' => $_REQUEST['groupname']), "ID=$id");
		$this->main();
	}
	
	function delete(){
		$DB = Config::Database();
		$qid = $DB->getTextValue($this->ID);
		$query = "DELETE FROM groups WHERE (ID = $qid)";
		$DB->query($query);
		$query = "DELETE FROM comp_group WHERE (group_id = $qid)";
		$DB->query($query);
		$this->main();
	}

	function main(){
		AuthCheck("tech");

		commonHeader(_("Group Setup"));

		__("Welcome to the IRM Group Setup utility.  Here you can change, view, delete, and add computer groups to the IRM database.");
		printf('<a href="#add">%s</a>', _("Add Groups"));
		echo "<hr noshade>";

		$query = "SELECT * FROM groups";
		$DB = Config::Database();
		$data = $DB->getAll($query);

		PRINT "<a name=\"add\"></a>";
		PRINT "<h4>"._("Add a Group")."</h4>";
		PRINT '<form method=post action="'.Config::AbsLoc('users/setup-groups-index.php').'">';
		PRINT '<input type=hidden name=action value=add>';

		PRINT '<table>';

		PRINT '<tr class="setupheader">';
		PRINT '<td colspan=2><strong>'._("New Group").'</strong></td>';
		PRINT '</tr>';

		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("Name:") . "<br><input type=text size=\"65%\" name=groupname></td>";
		PRINT "</tr>";

		PRINT '<tr class="setupupdate">';
		PRINT "<td colspan=2><input type=submit value="._("Add")."></td>";
		PRINT "</tr>";
		PRINT "</table>";
		PRINT "</form>";

		PRINT "<hr noshade>";

		foreach ($data as $result)
		{
			$id = $result["ID"];
			$groupname = $result["name"];
			PRINT '<form method=post action="' . Config::AbsLoc('users/setup-groups-index.php').'">';
			PRINT "<input type=hidden name=action value=update>";
			PRINT "<table>";
			
			PRINT '<tr class="setupheader">';
			PRINT "<td colspan=2><strong>($id) $groupname</strong></td>";
			PRINT "</tr>";
			
			PRINT '<tr class="setupdetail">';
			PRINT "<td>" . _("ID:")." $id</td>\n";
			PRINT "<td>" . _("Group Name:") . "<br><input type=text size=\"65%\" name=groupname value=\"$groupname\"></td>\n";
			PRINT "</tr>\n";

			PRINT '<tr class="setupupdate">';
			PRINT "<td valign=center>";
			PRINT "<input type=hidden name=id value=\"$id\">";
			PRINT "<input type=submit value="._("Update").">";
			PRINT "</form>";
			PRINT "</td>";
			PRINT "<td valign=center>";
			PRINT '<form method=get action="'.Config::AbsLoc('users/setup-groups-index.php').'">';
			PRINT "<input type=hidden name=action value=delete>";
			PRINT "<input type=hidden name=id value=\"$id\">";
			PRINT "<input type=hidden name=groupname value=\"$groupname\">";
			PRINT "<input type=submit value="._("Delete").">&nbsp;&nbsp;";
			PRINT '<a href="'.Config::AbsLoc("users/setup-groups-members.php?id=$id").'">' . _("Edit Group Members").'</a>';
			PRINT '</form>';
			PRINT "</td>";
			PRINT "</tr>";
			PRINT "</table>";
			PRINT "<br>";
		}

		commonFooter();
	}
}
?>
