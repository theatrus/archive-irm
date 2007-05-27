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

class setupGroupMembers{

	function setupGroupMembers()
	{
		$this->ID = $_REQUEST['id'];
		$this->comp_id = $_REQUEST['comp_id'];
		$this->group_id =$this->ID;

		switch($_REQUEST['action']){
		case 'add':
			$this->add();
			break;
		case 'delete':
			$this->delete();
			break;
		default:
			$this->main();
			break;
		}
	}

	function main(){
		AuthCheck("tech");

		commonHeader(_("Computers") . " - " . _("Group Members"));

		printf (_("Welcome to the IRM Group Setup utility.  Here you can edit members of a specified group."));
		print '<a href="#add">'._('You may also add computers to a group').'</a>';
		print "\n<hr />\n";

		$DB = Config::Database();
		$qid = $DB->getTextValue($this->ID);
		$query = "SELECT name FROM groups WHERE ID = $qid";
		$name = $DB->getOne($query);
		echo _("Group:")." <b>$name</b> (" . $this->ID . ")<br />\n";

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
				PRINT "<tr>";
				PRINT "<td width=\"50%\">$cname ($comp_id)</td>\n";
				PRINT "<td>";
				PRINT '<a href="'.Config::AbsLoc("users/setup-groups-members.php?action=delete&amp;id="  . $this->ID . "&amp;comp_id=$comp_id").'">['._("Delete").']</a>';
				PRINT '</td></tr>' . "\n";
			}

			PRINT "</table>\n";
		}
		else
		{
			__("No computers in this group.");
		}

		PRINT "<a name=\""._("add")."\"></a>";
		PRINT "<hr />";
		PRINT "<h4>"._("Add a Computer to Group")."</h4>\n";
		PRINT '<form method="get" action="'.Config::AbsLoc('users/setup-groups-members.php').'">' . "\n";
		PRINT '<input type="hidden" name="action" value="add" />' . "\n";
		PRINT '<input type="hidden" name="id" value="' . $this->ID . '" />' . "\n";
		__("Computer ID: ");

		if (isset($_REQUEST['use_select']))
		{
			$query = "SELECT computers.id,computers.name
					FROM computers,comp_group
					WHERE comp_group.group_id=" . $this->ID . "
					    AND computers.ID=comp_group.comp_id";
			$group = $DB->getAll($query);

			$query = "SELECT computers.id,computers.name
					FROM computers ORDER BY name";
			$all = $DB->getAll($query);

			$not_in_group = array_diff_for_real($all, $group);

			PRINT "<select name=comp_id size=1>";
			foreach ($not_in_group as $result)
			{
				PRINT '<option value="' . $result['id'] . '">' . $result['name'] . "</option>\n";
			}
			PRINT "</select>";
			$switchlink = "<a href=\""
				. Config::AbsLoc('users/setup-groups-members.php', array('id' => $_REQUEST['id']))
				. "\">"._("OR Enter the computer ID")."</a>";
		}
		else
		{
			PRINT '<input type="text" name="comp_id" size="4" />';
			$switchlink = '<a href="'
				. Config::AbsLoc('users/setup-groups-members.php', array('use_select' => 1,
											'id' => $_REQUEST['id']))
				. '">'._("OR Choose from a dropdown list of computers").'</a>';
		}
		PRINT '<input type="submit" name="btn" value="' ._("Add"). '" />';
		PRINT "</form>\n";
		PRINT $switchlink;

		commonFooter();
	}

	function add(){
		$DB = Config::Database();
		$qc = $DB->getTextValue($this->comp_id);
		$qg = $DB->getTextValue($this->group_id);
		if (0 == $DB->getOne("SELECT COUNT(*) FROM comp_group WHERE comp_id=$qc AND group_id=$qg"))
		{
			$vals = array(
				'comp_id' => $this->comp_id,
				'group_id' => $this->group_id
				);
			$DB->InsertQuery('comp_group', $vals);
		}
		$this->main();
	}

	function delete(){
		$DB = Config::Database();
		$comp_id = $DB->getTextValue($this->comp_id);
		$group_id = $DB->getTextValue($this->group_id);
		$query = "DELETE FROM comp_group WHERE comp_id = $comp_id AND group_id = $group_id";
		$DB->query($query);
		$this->main();
	}
}
?>
