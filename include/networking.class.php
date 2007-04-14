<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2003 Yann Ramin
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

class networking
{
	function networking()
	{
		$this->ID = $_REQUEST['ID'];

		switch($_REQUEST['action'])
		{
			case 'delete':
				AuthCheck("tech");
				$this->deleteNetworking();
				break;
			case 'info':
				$this->showNetworking();
				break;
			case 'select-add':
				AuthCheck("tech");
				$this->addNetworking();
				break;
			case 'add':
				AuthCheck("tech");
				$this->saveNetworking();
				break;
#			case 'new':
#				AuthCheck("tech");
#				$this->add();
#				break;
			case 'update';
				AuthCheck("tech");
				$this->updateNetworking();
				break;
			case 'all';
				AuthCheck("tech");
				$this->addAllPorts();
				break;	

			case 'addPort';
				AuthCheck("tech");
				$this->addPort();
				break;	

			case 'deletePort';
				AuthCheck("tech");
				$this->deletePort();
				break;	


			default:	
				$this->search();
		}
	}

	function deletePort()
	{
		$port = new DeviceConnection();
		$port->deletePort();
	}

	function addPort()
	{
		$port = new DeviceConnection();
		$port->addPort();
	}

	function addAllPorts()
	{
		$port = new DeviceConnection();
		$port->addAllPorts();
	}

	function deleteNetworking()
	{
		commonHeader(_("Networking") . " - " . _("Device Deleted"));

		$DB = Config::Database();
		$qID = $DB->getTextValue($_REQUEST['ID']);
		$query = "SELECT id FROM networking_ports WHERE (device_on = $qID AND device_type = 2)";
		$ports = $DB->getCol($query);
		foreach ($ports as $portid)
		{
			$qport = $DB->getTextValue($portid);
			$query = "DELETE FROM networking_wire WHERE end1=$qport OR end2=$qport";
			$DB->query($query);
		} 
		 
		$query = "DELETE FROM networking WHERE (ID = $qID)";
		$DB->query($query);
		$query = "DELETE FROM networking_ports WHERE (device_on = $qID AND device_type = 2)";
		$DB->query($query);

		__("I have deleted that networking device and all associated ports.");

		commonFooter();
	}


	function updateNetworking()
	{
		$vals = array(
			'name' => $_REQUEST['name'],
			'type' => $_REQUEST['type'],
			'ram' => $_REQUEST['ram'],
			'ip' => $_REQUEST['ip'],
			'mac' => $_REQUEST['mac'],
			'location' => $_REQUEST['location'],
			'serial' => $_REQUEST['serial'],
			'otherserial' => $_REQUEST['otherserial'],
			'contact' => $_REQUEST['contact'],
			'contact_num' => $_REQUEST['contact_num'],
			'datemod' => date('Y-m-d H:i:s'),
			'comments' => $_REQUEST['comments']
			);

		$DB = Config::Database();
		$ID = $DB->getTextValue($_REQUEST['ID']);
		$DB->UpdateQuery('networking', $vals, "ID=$ID");

		logevent($ID, _("networking"), 4, _("database"), sprintf(_("%s updated record"),$IRMName));
		header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
	}

	function saveNetworking()
	{
		AuthCheck("tech");

		$new_date = date("Y-m-d H:i:s");
		$DB = Config::Database();

		$ID = $DB->nextId('networking__ID');
		$vals = array(
			'ID' => $ID,
			'name' => $_REQUEST['name'],
			'type' => $_REQUEST['type'],
			'ram' => $_REQUEST['ram'],
			'ip' => $_REQUEST['ip'],
			'mac' => $_REQUEST['mac'],
			'location' => $_REQUEST['location'],
			'serial' => $_REQUEST['serial'],
			'otherserial' => $_REQUEST['otherserial'],
			'contact' => $_REQUEST['contact'],
			'contact_num' => $_REQUEST['contact_num'],
			'datemod' => $new_date,
			'comments' => $_REQUEST['comments']
			);
		$DB->InsertQuery('networking', $vals);

		for ($i = 1; $i <= $numports; $i++)
		{
			$vals = array(
				'device_on' => $ID,
				'device_type' => 2,
				'iface' => $ifacetype,
				'ifaddr' => $ip,
				'ifmac' => $mac,
				'logical_number' => $i,
				'name' => "Port $i"
				);
			$DB->InsertQuery('networking_ports', $vals);
		}
		logevent($ID, "networking", 4, "database", sprintf(_("%s added record"), $IRMName));

		header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
	}

	function search()
	{
		commonHeader(_("Networking"));
		__("Welcome to the IRM Networking section.  This where you keep information about all of your networking devices.");
		$deviceType = "networking";
		deviceSearch($deviceType,$networking_fields);
		commonFooter();
	}
	function networkingForm($formtype,$result)
	{
		$this->page = new IrmFactory();

		if ($formtype == "show"){
			$name = $result["name"];
			$type = $result["type"];
			$ram = $result["ram"];
			$serial = $result["serial"];
			$otherserial = $result["otherserial"];
			$location = $result["location"];
			$ip = $result["ip"];
			$mac = $result["mac"];
			$contact = $result["contact"];
			$contact_num = $result["contact_num"];
			$comments = $result["comments"];

			$comments = stripslashes($comments);
		}

		$this->page->assign('name', _("Name"));
		$this->page->assign('nameName', "name");
		$this->page->assign('nameValue', $name);

		$this->page->assign('type', _("Type"));
		$this->page->assign('typeDropdown',Dropdown_value("dropdown_type", "type", $type));
		
		$this->page->assign('location', _("Location"));
		$this->page->assign('locationDropdown', Dropdown_value("dropdown_locations", "location", $location));
		
		$this->page->assign('ram', _("RAM Amount (in MB)"));
		$this->page->assign('nameRam', "ram");
		$this->page->assign('ramValue', $ram);

		$this->page->assign('serial',_("Serial Number"));
		$this->page->assign('nameSerial', "serial" );
		$this->page->assign('serialValue', $serial );

		$this->page->assign('otherSerial',_("Other Serial Number"));
		$this->page->assign('nameOtherSerial', "otherserial" );
		$this->page->assign('otherSerialValue', $otherserial );

		$this->page->assign('ip',_("IP"));
		$this->page->assign('nameIP', "ip" );
		$this->page->assign('ipValue', $ip);

		$this->page->assign('mac',_("MAC/Network Address"));
		$this->page->assign('nameMAC', "mac" );
		$this->page->assign('macValue', $mac );

		$this->page->assign('contact',_("Contact"));
		$this->page->assign('nameContact', "contact" );
		$this->page->assign('contactValue', $contact);

		$this->page->assign('contactNumber',_("Contact Number"));
		$this->page->assign('nameContactNumber', "contact_num" );
		$this->page->assign('contactNumberValue', $contact_num);

		$this->page->assign('comments',_("Comments"));
		$this->page->assign('nameComments', "comments" );
		$this->page->assign('commentsValue', $comments);

		$this->page->display('networkingForm.html.php');
	}

	function showNetworking()
	{
		commonHeader(_("Networking"));

		$ID = $this->ID;

		$DB = Config::Database();

		$qID = $DB->getTextValue($ID);
		$query = "SELECT * FROM networking WHERE (ID = $qID)";

		$result = $DB->getRow($query);

		$name = $result["name"];
		$ip = $result["ip"];
		$datemod = $result["datemod"];
		$new_date = date("Y-m-d H:i:s");
		
		PRINT '<table class="networking ">';
		PRINT '<form method=post action="'.Config::AbsLoc('users/networking-index.php').'">';
		PRINT "<input type=hidden name=ID value=\"$ID\">";
		PRINT "<input type=hidden name=action value=update>";
		PRINT '<tr class="networkingheader">';
		PRINT '<td colspan=2>';
		PRINT "$ip : ";
		PRINT SnmpStatus($ip,$ID, "networking");
		PRINT "<strong>$name ($ID)</strong></td>";
		PRINT "</tr>";

		$this->networkingForm("show", $result);	

		PRINT '<tr class="networkingheader">';
		PRINT "<td colspan=2 align=center>";
		PRINT _("Last Updated:")." $date_mod <input type=hidden name=date_mod value=\"$new_date\">";
		PRINT "</td>";
		PRINT "</tr>\n";
		PRINT "</table>";

		PRINT '<table class="networking ">';
		PRINT '<tr class="networkingupdate">';
		PRINT "<td>";
		PRINT "<input type=submit value=\""._("Update")."\"></form>";
		PRINT "</td>";
		PRINT "<td>";
		PRINT '<form method=post action="'.Config::AbsLoc('users/networking-index.php').'">';
		PRINT "<input type=hidden name=ID value=$ID>";
		PRINT "<input type=hidden name=action value=delete>";
		PRINT "<input type=submit value=\""._("Delete")."\"></form>";
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
		
		showPortsOnDevice($ID, 2);

		$files = new Files();	
		$files->setDeviceType("networking");
		$files->setDeviceID($ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();

		commonFooter();
	}

	function addNetworking()
	{
		commonHeader(_("Networking"));
		__("Fill out this form to add a new networking device.");

		PRINT '<table class="networking">';
		PRINT '<form method=get action="'.Config::AbsLoc('users/networking-index.php').'">';
		PRINT '<tr class="networkingheader">';
		PRINT '<td colspan=2>';
		PRINT "<strong>" . _("New Device") . "</strong>";
		PRINT "</td>";
		PRINT "</tr>";

		$this->networkingForm("add","");	

		PRINT '<tr class="networkingupdate">';
		PRINT "<td><input type=submit value=\""._("Add")."\"></td>";
		PRINT "<td><input type=hidden name=action value=add></td>";
		PRINT "<td>";
		PRINT "<input type=reset value="._("Clear").">";
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
		
		PRINT "<br />";
		
		printf(_("Add %s initial ports of type %s to device."),
			"<input type=text name=numports size=3 value=8>",
			Dropdown("dropdown_iface", "ifacetype"));
		PRINT "</form>";
		commonFooter();
	}
}
?>
