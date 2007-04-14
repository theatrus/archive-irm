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

class setupFastTrack{

	function setupFastTrack()
	{
		$this->ID = $_REQUEST['ID'];

		switch($_REQUEST['action']){
		case 'add':
			$this->add();
			break;		
		case 'addform':
			$this->addForm();
			break;
		case 'edit':
			$this->edit();
			break;
		case 'update':
			$this->update();
			break;
		case 'delete':
			$this->delete();
			break;
		default:
			$this->main();
			break;
		}
	}

	function update(){
		PRINT_R($_REQUEST);
		AuthCheck("tech");
		$templname = htmlspecialchars($_REQUEST['templname']);
		$contents = htmlspecialchars($_REQUEST['contents']);
		$solution = htmlspecialchars($_REQUEST['solution']);
		$vals = array(
			'name' => $templname,
			'priority' => $_REQUEST['priority'],
			'request' => $contents,
			'response' => $solution
			);
		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$DB->UpdateQuery('fasttracktemplates', $vals, "ID=$ID");
		$this->main();
	}

	function add(){
		AuthCheck("tech");
		$templname = htmlspecialchars($_REQUEST['templname']);
		$contents = htmlspecialchars($_REQUEST['contents']);
		$solution = htmlspecialchars($_REQUEST['solution']);
		$vals = array(
			'name' => $templname,
			'priority' => $_REQUEST['priority'],
			'request' => $contents,
			'response' => $solution
			);
		$DB = Config::Database();
		$DB->InsertQuery('fasttracktemplates', $vals);

		header("Location: ".appendURLArguments($_SESSION['_sess_pagehistory']->Previous(), array('add' => 1)));
	}

	function addForm(){
		AuthCheck("tech");

		commonHeader(_("Setup") . " - " . _("FastTrack Templates Add Form"));

		$new_date = date("Y-m-d H:i:s");

		if ($add == 1) 
		{
		  PRINT "<h3>";
		  __("FastTrack Template Added Successfuly");
		  PRINT "</h3>";
		  PRINT "<hr noshade>";
		}

		__("Use this form to add a FastTrack template.");

		PRINT "<br>";
		PRINT '<a href="'.Config::AbsLoc('users/setup-fasttrack-index.php').'">' . _("Go back to FastTrack templates") . '</a>';
		PRINT '<br>';

		PRINT '<table>';
		PRINT '<form method=post action="'.Config::AbsLoc('users/setup-fasttrack-index.php').'">';
		PRINT '<input type=hidden name=action value=add>';

		PRINT '<tr class="setupheader">';
		PRINT '<th>' . _("Add FastTrack Template ") . "</th>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("FastTrack Template name: ") . "<input type=text name=templname value=\"$templname\" size=40></td>";
		PRINT "</tr>\n";
/*
		// This is commented out as the data is never stored with a fast track template at the moment.

		PRINT "<tr>";
		PRINT "<th>";
		__("Computer");
		if(Config::Get('groups'))
		{
			echo '/'._("Group");
		}
		__(" Information");
		PRINT "</th>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<td>";
		PRINT "<INPUT TYPE=\"RADIO\" NAME=\"IDTYPE\" VALUE=\"IRMID\">" . _("IRM ID: ");
		PRINT "<INPUT TYPE=text NAME=ID SIZE=10>&nbsp;&nbsp;\n";
		PRINT "<INPUT TYPE=hidden NAME=is_group VALUE=\"no\">\n";
		PRINT "<BR>\n";
		if(Config::Get('groups'))
		{
			PRINT "<INPUT TYPE=\"RADIO\" NAME=\"IDTYPE\" VALUE=\"GROUP\"> ";
			__("Select a group:");
			Dropdown_groups("groups", "gID");
			PRINT "<INPUT TYPE=\"HIDDEN\" NAME=\"is_group\" VALUE=\"yes\">\n";
		}
		PRINT "</td>\n";
		PRINT "</tr>\n";
*/
		PRINT '<tr class="setupheader">';
		PRINT "<th>" . _("Work Request Information") . "</th>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<TD>" . _("Priority:");
		PRINT "<SELECT NAME=priority>\n";

		# TODO:Really need to pick this up form the database
		$opts = array(	5 => _('Very High'),
				4 => _('High'),
				3 => _('Normal'),
				2 => _('Low'),
				1 => _('Very Low')
				);
		echo select_options($opts, $priority);
		PRINT "</SELECT>\n";
		PRINT "<BR>\n";
		PRINT _("Describe the problem:") . "<BR>\n";
		PRINT "<textarea cols=50 rows=4 wrap=soft name=contents>$request</textarea><BR>\n";
		PRINT _("Describe the solution (will be added as a followup):") . "<BR>\n";
		PRINT "<textarea cols=50 rows=4 wrap=soft name=solution>$response</textarea>\n"; 
		PRINT "</td>\n";
		PRINT "</tr>\n";
/*
		// This is commented out as the data is never stored with a fast track template at the moment.

		PRINT '<tr class="setupheader">';
		PRINT "<th>";
		__("Additional Information");
		PRINT "</th>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("Assign to:");

		$track = new Tracking($ID);
		$assign = $track->getAssign();
		if($assign != "")
		{
			Tech_list($assign, "user");
		} else
		{
			Tech_list("","user");
		}

		PRINT "<br>\n";
		PRINT _("Set Status to:");

		PRINT "<SELECT NAME=status SIZE=1>";
		$opts = array(	'active' => _("Active"),
				'assigned' => _("Assigned"),
				'complete' =>  _("Complete"),
				'new' => _("New"),
				'old' => _("Old"),
				'wait' => _("Wait")
				);
		echo select_options($opts, 'new');
		PRINT "</SELECT>";

		PRINT '<br>';
		PRINT '<input type="checkbox" name="addtoknowledgebase" value="yes">';
		__("If tracking is marked as complete, should it be used to add something to the knowledgebase?");
		PRINT "</td>";
		PRINT "</tr>\n";
*/
		PRINT '<tr class="setupupdate">';
		PRINT "<td><input type=submit value=Submit></td>";
		PRINT "<tr>";
		PRINT "</table>";

		PRINT "</form>";
		commonFooter();
	}

	function main()
	{
		AuthCheck("admin");

		$query = "SELECT * FROM fasttracktemplates";
		$DB = Config::Database();
		$data = $DB->getAll($query);

		commonHeader(_("Setup") . " - " . _("FastTrack Templates"));

		__("Please select a FastTrack template below to edit or delete.");
		PRINT "<br>";
		printf('<a href="%s">%s</a>', Config::AbsLoc('users/setup-fasttrack-index.php?action=addform'),
				_("Add a new FastTrack template"));

		PRINT "<table>";
		PRINT "<tr>";
		PRINT "<th colspan=2>" . _("FastTrack Templates") . "</th>";
		PRINT "</tr>";

		foreach ($data as $result)
		{
			$ID = $result["ID"];
			$name = $result["name"];
			PRINT '<tr class="setupupdate">';
			PRINT '<td><a href="' . Config::AbsLoc("users/setup-fasttrack-index.php?action=edit&ID=$ID") . '">' . $name . '</a></td>';
			PRINT '<td><a href="' . Config::AbsLoc("users/setup-fasttrack-index.php?action=delete&ID=$ID") . '">['._("Delete").']</a></td>';
			PRINT "</tr><br>";
		}
		PRINT "</table>";
		commonFooter();
	}
	
	function edit()
	{
		AuthCheck("tech");

		commonHeader(_("Setup") . " - " . _("FastTrack Templates Edit"));

		$query = "select * from fasttracktemplates where (ID=" . $this->ID . ")";
		$DB = Config::Database();
		$result = $DB->getRow($query);
		$name = $result["name"];
		$priority = $result["priority"];
		$request = $result["request"];
		$response = $result["response"];

		$new_date = date("Y-m-d H:i:s");

		__("Use this form to edit a FastTrack template.");
		PRINT "<br>";
		PRINT '<a href="'.Config::AbsLoc('users/setup-fasttrack-index.php').'">';
		__("Go back to FastTrack templates");
		PRINT "</a><br>";

		PRINT "<table>";

		PRINT '<form method=post action="'.Config::AbsLoc('users/setup-fasttrack-index.php').'">';
		PRINT '<input type=hidden name=action value=update>';
		PRINT "<input type=hidden name=ID value=$this->ID>";

		PRINT '<tr class="setupheader">';
		PRINT "<td>" . _("FastTrack Template Name") . "</td>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<td><input type=text name=templname value=\"$name\" size=40></td>";
		PRINT "</tr>";

		PRINT '<tr class="setupheader">';
		PRINT "<td>" . _("Priority:") . "</td>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		PRINT "<td>";
		# TODO: this should be picked up from the database
		PRINT "<SELECT NAME=priority>\n";
		$opts = array(	5 => _('Very High'),
				4 => _('High'),
				3 => _('Normal'),
				2 => _('Low'),
				1 => _('Very Low')
				);
		echo select_options($opts, $priority);
		PRINT "</SELECT>\n";
		PRINT "</td>\n";
		PRINT "</tr>\n";

		PRINT '<tr class="setupheader">';
		PRINT "<td>" . _("Describe the problem:") . "</td>\n";
		PRINT "</tr>\n";

		PRINT '<tr class="setupdetail">';
		PRINT "<td><textarea cols=50 rows=4 wrap=soft name=contents>$request</textarea></td>\n";
		PRINT "</tr>\n";

		PRINT '<tr class="setupheader">';
		PRINT "<td>" . _("Describe the solution (will be added as a followup):") . "</td>\n";
		PRINT "</tr>\n";

		PRINT '<tr class="setupdetail">';
		PRINT "<td><textarea cols=50 rows=4 wrap=soft name=solution>$response</textarea></td>\n";
		PRINT "</tr>\n";


		PRINT '<tr class="setupupdate">';
		PRINT "<td>";
		PRINT "<input type=submit value=Update>";
		PRINT "<input type=Reset value=Reset></form>";
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
		commonFooter();
	}

	function delete(){
		AuthCheck("tech");

		header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$query = "DELETE FROM fasttracktemplates WHERE (ID = $ID)";
		$DB->query($query);
	}
}
?>
