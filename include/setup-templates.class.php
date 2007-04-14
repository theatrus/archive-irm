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

class setupTemplates{

	function setupTemplates()
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
	
	function setVals(){
		$this->vals = array(
			'templname' => $_REQUEST['templname'],
			'name' => $_REQUEST['name'],
			'type' => $_REQUEST['type'],
			'flags_server' => $_REQUEST['flags_server'],
			'os' => $_REQUEST['os'],
			'osver' => $_REQUEST['osver'],
			'processor' => $_REQUEST['processor'],
			'processor_speed' => $_REQUEST['processor_speed'],
			'location' => $_REQUEST['location'],
			'serial' => $_REQUEST['serial'],
			'otherserial' => $_REQUEST['otherserial'],
			'ramtype' => $_REQUEST['ramtype'],
			'ram' => $_REQUEST['ram'],
			'network' => $_REQUEST['network'],
			'ip' => $_REQUEST['ip'],
			'mac' => $_REQUEST['mac'],
			'hdspace' => $_REQUEST['hdspace'],
			'contact' => $_REQUEST['contact'],
			'contact_num' => $_REQUEST['contact_num'],
			'comments' => $_REQUEST['comments'],
			'iface' => $_REQUEST['iface'],
			'flags_surplus' => $_REQUEST['flags_surplus']
			);
	}

	function update(){
		AuthCheck("tech");

		$flags_server = Computer::Flags(@$flags_server);
		$flags_surplus = Computer::Flags(@$flags_surplus);

		$this->setVals();

		$DB = Config::Database();
		$ID = $DB->getTextValue($ID);
		$DB->UpdateQuery('templates', $this->vals, "ID=$this->ID");
		$this->main();
	}

	function edit(){
		AuthCheck("tech");

		commonHeader(_("Setup") . " - " . _("Templates Editor"));

		$DB = Config::Database();
		$qID = $DB->getTextValue($this->ID);
		$query = "SELECT * FROM templates WHERE (ID = $qID)";
		$DB = Config::Database();
		$result = $DB->getRow($query);
		$ID = $result["ID"];
		$templname = $result["templname"];
		$name = $result["name"];
		$type = $result["type"];
		$os = $result["os"];
		$osver = $result["osver"];
		$processor = $result["processor"];
		$processor_speed = $result["processor_speed"];
		$location = $result["location"];
		$serial = $result["serial"];
		$otherserial = $result["otherserial"];
		$ramtype = $result["ramtype"];
		$ram = $result["ram"];
		$network = $result["network"];
		$ip = $result["ip"];
		$mac = $result["mac"];
		$hdspace = $result["hdspace"];
		$contact = $result["contact"];
		$contact_num = $result["contact_num"];
		$comments = $result["comments"];
		$flags_server = $result["flags_server"];
		$flags_surplus = $result["flags_surplus"];
		$new_date = date("Y-m-d H:i:s");
		$iface = $result["iface"];

		$vals = array(
			'ID' => $ID,
			'name' => $name,
			'type' => $type,
			'flags_server' => $flags_server,
			'flags_surplus' => $flags_surplus,
			'os' => $os,
			'osver' => $osver,
			'processor' => $processor,
			'processor_speed' => $processor_speed,
			'location' => $location,
			'serial' => $serial,
			'otherserial' => $otherserial,
			'ramtype' => $ramtype,
			'ram' => $ram,
			'network' => $network,
			'ip' => $ip,
			'mac' => $mac,
			'hdspace' => $hdspace,
			'contact' => $contact,
			'contact_num' => $contact_num,
			'comments' => $comments,
			'date_mod' => $date_mod
			);

		$new_date = date("Y-m-d H:i:s");

		if (!@$contact)
		{
			$contact = '';
		}
		if (!@$contact_num)
		{
			$contact_num = '';
		}

		printf(_('Use this form to edit template "%s".'), $templname);
		PRINT '<a href="'.Config::AbsLoc('users/setup-templates-index.php').'">'._("Back to Templates").'</a><br>';
		PRINT "<table>";
		PRINT '<form method=post action="'.Config::AbsLoc('users/setup-templates-index.php').'">';
		PRINT '<input type="hidden" name="action" value="update">';
		PRINT '<tr class="setupheader">';
		PRINT '<th colspan=2>';
		PRINT _("Editing Template");
		PRINT "<input type=hidden name=ID value=\"$ID\">";
		PRINT "<input type=text name=templname value=\"$templname\" size=40>";
		PRINT "</th>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		#PRINT "<td>"._("Name:")."<br><input type=text name=name value=\"$name\" size=24></td>";
		#PRINT "<td>"._("Type").":<br>";
		#PRINT Dropdown_value("dropdown_type", "type", $type);
		#PRINT "</td>";
		PRINT "</tr>";

		Computer::computerForm($vals);

		PRINT '<tr class="setupupdate">';
		PRINT "<td><input type=submit value=\""._("Update")."\"></td>";
		PRINT "<td><input type=Reset value=\""._("Reset")."\"></form></td>";
		PRINT "</tr>";
		PRINT "</table>";
		PRINT "<br>";
		templcompsoftShow($ID);
		PRINT "<br>";	
	}
	
	function add(){
		AuthCheck("tech");

		# $flags_server = serverFlags(@$flags_server);
		# $flags_surplus = surplusFlags(@$flags_surplus);
		if ($flags_server = "")
		{
			$flags_server = "0";
		}

		$this->setVals();

		
		$DB = Config::Database();
		$DB->InsertQuery('templates', $this->vals);
		$this->main();
	}

	function delete(){
		AuthCheck("tech");
		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$query = "DELETE FROM templates WHERE (ID = $ID)";
		$DB->query($query);
		$query = "DELETE FROM templ_inst_software WHERE (cID = $ID)";
		$DB->query($query);
		$this->main();
	}
	
	function main(){
		AuthCheck("admin");
		commonHeader(_("Setup - Computer Templates"));
		$this->templatesList();
		commonFooter();
	}

	function addForm(){
		AuthCheck("tech");

		commonHeader(_("Setup") . " - " . _("Templates Add Form"));

		if ($add == 1) 
		{
		  PRINT "<h3>";
		  __("Template Added Successfuly");
		  PRINT "</h3>";
		  PRINT "<hr noshade>";
		}

		$new_date = date("Y-m-d H:i:s");

		__("Use this form to add a template.");
		PRINT '<a href="'.Config::AbsLoc('users/setup-templates-index.php').'">' . _("Back to Templates") . '</a>';
		PRINT '<br>';
		PRINT '<table>';
		PRINT '<form method=post action="'.Config::AbsLoc('users/setup-templates-index.php').'">';
		PRINT '<input type="hidden" name="action" value="add">';
		PRINT '<tr class="computerheader">';
		PRINT '<td colspan=2>';
		PRINT "<strong>";
		__("Add Template");
		PRINT "<input type=text name=templname value=\"$templname\" size=40></strong>";
		PRINT "</td>";
		PRINT "</tr>";

		PRINT '<tr class="setupdetail">';
		#PRINT "<td>" ._("Name:") . "<br><input type=text name=name value=\"$name\" size=24></td>";
		#PRINT "<td>" . _("Type") . ":<br>";
		#PRINT Dropdown_value("dropdown_type", "type", $type);
		#PRINT "</td>";
		PRINT "</tr>";

		Computer::computerForm("");

		PRINT '<tr class="setupdetail">';
		PRINT "<td><input type=submit value=Add></td>";
		PRINT "<td><input type=Reset value=Reset></form></td>";
		PRINT "</tr>";
		PRINT "</table>";
		__("If you wish to add software to this template, you must do so by editing it.");

		commonFooter();
	}
	
	function templatesList()
	{
		printf(_("Please select a template below to edit, delete, or <a href=\"%s\">add one</a>."),Config::AbsLoc('users/setup-templates-index.php?action=addform'));

		$query = "SELECT * FROM templates";
		$DB = Config::Database();
		$data = $DB->getAll($query);

		$data = orderTemplateList($data);

		PRINT "<table>";
		PRINT "<tr>";
		PRINT "<th colspan=2>" . _("Computer Templates") . "</th>";
		PRINT "</tr>\n";

		foreach ($data as $result)
		{
			$ID = $result["ID"];
			$name = $result["templname"];
			PRINT '<tr class="setupdetail">';
			PRINT '<td><a href="' . Config::AbsLoc("users/setup-templates-index.php?action=edit&ID=$ID")	. "\">$name</a></td>";
			PRINT '<td><a href="' . Config::AbsLoc("users/setup-templates-index.php?action=delete&ID=$ID") . '">['. _("Delete"). ']</a></td>';
			PRINT '</tr>';
		}
		PRINT "</table>";
	}



}
?>
