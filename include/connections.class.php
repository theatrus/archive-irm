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

class DeviceConnection
{
	var $portNumber;

	var $portName;		

	var $interface;

	var $ipaddress;

	var $macaddress;

	var $deviceID;

	var $deviceType;

	function DeviceConnection(){
		$this->vals = array(
			'device_on' => 		$_REQUEST['device'],
			'device_type' => 	$_REQUEST['device_type'],
			'iface' => 		$_REQUEST['iface'],
			'ifaddr' => 		$_REQUEST['ifaddr'],
			'ifmac' => 		$_REQUEST['ifmac'],
			'logical_number' => 	$_REQUEST['logical_number'],
			'name' => 		$_REQUEST['name']
			);

		$this->device = 	$_REQUEST['device'];
		$this->device_type = 	$_REQUEST['device_type'];
	}

	function backToDevice()
	{
		switch($this->device_type)
		{	
			case '1':
				header("Location: ".Config::AbsLoc("users/computers-index.php?action=info&ID=" . $this->device));
				break;
			case '2':
				header("Location: ".Config::AbsLoc("users/networking-index.php?action=info&ID=" .$this->device));
				break;
			default:
				header("Location: ".Config::AbsLoc("users/device-info.php?devicetype=" . $this->device_type . "&ID=" . $this->device));
				break;
		}

	}
	
	# return the (management) ip for a given computer id
	function ID2IP($ID,$deviceType)
	{
		switch($deviceType){
			case "1":
				$query= "SELECT ip FROM computers WHERE ID = $ID";
				break;
			case "2":	
				$query= "SELECT ip FROM networking WHERE ID = $ID";
				break;
			default:
				$query= "SELECT ip FROM $deviceType WHERE ID = $ID";
				break;
		}

		$DB = Config::Database();
		if ($res=$DB->getOne($query))
		{
			return ($res);
		} else {
	#		trigger_error(sprintf(_("ID2IP: Invalid Computer ID %s"),$ID));
			return ("NOIP");
		}
	}

	function addSinglePort(){
		$DB = Config::Database();
		$DB->InsertQuery('networking_ports', $this->vals);
		logevent($this->device, _("networking"), 4, _("port"), sprintf(_("%s added port"),$IRMName));
	}

	function addAllPorts(){

		$this->IP=$this->ID2IP($this->device,$this->device_type );
		$snmp = new Net_SNMP($this->IP);

		$this->device = 	$_REQUEST['device'];
		$this->device_type = 	$_REQUEST['device_type'];

		
		$data = $snmp->snmpwalk('interfaces.ifTable.ifEntry.ifIndex');

		
		foreach ($data as $ooid => $result)
		{
			$oid='interfaces.ifTable.ifEntry.ifDescr.'.$result['Value'];	
			$name = $snmp->snmpget($oid);
			$snmpdesc = $snmp->snmpget('interfaces.ifTable.ifEntry.ifDescr.'.$result['Value']);
			$logical_number=$result['Value'];
			$name = $snmpdesc['Value'];
			$ifaddr=$snmp->getFirstIpFromIfIndex($result['Value']);
			$snmpmac = $snmp->snmpget('interfaces.ifTable.ifEntry.ifPhysAddress.'.$result['Value']);
			$ifmac= $snmpmac['Value'];
			
			if (!@$iface){
				$iface = '';
			}
			if (!@$ifaddr){
				$ifaddr = '';
			}

			$this->vals = array(
				'device_on' => 		$this->device,
				'device_type' => 	$this->device_type,
				'iface' => 		$iface,
				'ifaddr' => 		$ifaddr,
				'ifmac' => 		$ifmac,
				'logical_number' => 	$logical_number,
				'name' => 		$name
				);
				
			$this->addSinglePort();
		}
		$this->backToDevice();
	}

	function addPort(){
		$this->addSinglePort();
		$this->backToDevice();
	}

	function updatePort(){
		$DB = Config::Database();
		$ID = $DB->getTextValue($_REQUEST['ID']);
		$DB->UpdateQuery('networking_ports', $this->vals, "ID=$ID");
		logevent($this->device, _("networking"), 4, _("port"), sprintf(_("%s updated port %s"),$IRMName,$ID));
		$this->backToDevice();
	}

	// TODO
	function deleteAllPortsOnDevice(){
		$device = $_REQUEST['device_on'];
		$deviceType = $_REQUEST['device_type'];
	}

	function deletePort(){
		$DB = Config::Database();
		$ID = $DB->getTextValue($_REQUEST['ID']);

		// Delete port.
		$query = "DELETE FROM networking_ports WHERE (ID = $ID)";
		$DB->query($query);

		//Delete associated wire.
		$query = "DELETE FROM networking_wire WHERE (end1 = $ID OR end2 = $ID)";
		$DB->query($query);

		logevent($this->device, _("networking"), 4, "port", sprintf(_("%s removed port %s"),$IRMName,$ID));
		$this->backToDevice();
	}

	function addPortFromSNMP(){
		$snmp = new Net_SNMP($this->IP);
		$data = $snmp->snmpwalk('interfaces.ifTable.ifEntry.ifIndex');
		PRINT "<form method=get>";
		PRINT "<select name=\"ifIndex\" size=1>\n";

		foreach ($data as $ooid => $result)
		{
			$oid='interfaces.ifTable.ifEntry.ifDescr.'.$result['Value'];	
			$name = $snmp->snmpget($oid);
			PRINT "<option value=\"". $result['Value']. "\"> ".$name['Value']." </option>\n";
			}
		PRINT "</select>";

		PRINT "<input type=hidden name=device value=".$_REQUEST['device'].">\n";
		PRINT "<input type=hidden name=device_type value=".$_REQUEST['device_type']. ">\n";
		PRINT "<input type=submit value=\""._("Get")."\">\n";
		PRINT "</form>\n";
	}
	
	function addAllPortsFromSNMP(){
		PRINT '<form method="get" action="' . Config::AbsLoc('users/networking-index.php') . '">';
		PRINT "<input type=hidden name=device value=".$_REQUEST['device'].">\n";
		PRINT "<input type=hidden name=action value=all>\n";
		PRINT "<input type=hidden name=device_type value=".$_REQUEST['device_type']. ">\n";
		PRINT "<input type=submit value=\""._("Add all Ports")."\">\n";
		PRINT "</form>\n";
	}

	function addPortForm(){

		commonHeader(_("Networking") . " - " . _("Add Port"));

		PRINT _("Fill out this simple form to add a port to the device.");
		PRINT "<hr noshade>";

		$device = $_REQUEST['device'];
		$device_type = $_REQUEST['device_type'];

		if (Config::Get('snmp')) {
			$this->IP=$this->ID2IP($device,$device_type );

			if ($this->IP == 'NOIP')
			{
				PRINT "This device does not have an IP address";
			}	
			
			switch ($this->IP)
			{
			case 'DHCP':
				break;
			case 'NOIP';
				break;
			default:
				$this->addPortFromSNMP();
				$this->addAllPortsFromSNMP();
				$snmp = new Net_SNMP($this->IP);
			}
		}
		if (isset($_REQUEST['ifIndex']))
		{
			$snmpdesc = $snmp->snmpget('interfaces.ifTable.ifEntry.ifDescr.'.$_REQUEST['ifIndex']);
			$logical_number=$_GET['ifIndex'];
			$name = $snmpdesc['Value'];
			$ifaddr=$snmp->getFirstIpFromIfIndex($_REQUEST['ifIndex']);
			$snmpmac = $snmp->snmpget('interfaces.ifTable.ifEntry.ifPhysAddress.'.$_REQUEST['ifIndex']);
			$ifmac= $snmpmac['Value'];
		}
		
		if (!@$logical_number){
			$logical_number = '';
		}
		if (!@$name){
			$name = '';
		}

		if (!@$iface){
			$iface = '';
		}
		if (!@$ifaddr){
			$ifaddr = '';
		}
		if (!@$ifmac){
			$ifmac = '';
		}

		PRINT '<form method="get" action="' . Config::AbsLoc('users/networking-index.php') . '">';
		PRINT "<input type=hidden name=action value=addPort>";
		PRINT "<table>";
		
		PRINT "<tr>";
		PRINT "<td>"._("Logical Number:")."</td>";
		PRINT "<td><input type=text size=5 name=logical_number value=$logical_number></td>";
		PRINT "</tr>";
		
		PRINT "<tr>";
		PRINT "<td>"._("Name")."</td>";
		PRINT "<td><input type=text size=20 value=\"$name\" name=name></td>";
		PRINT "</tr>";
		
		PRINT "<tr>";
		PRINT "<td>"._("Interface")."</td>";
		PRINT "<td>". Dropdown_value("dropdown_iface","iface", $iface) . "</td>";
		PRINT "</tr>";
		
		PRINT "<tr>";
		PRINT "<td>"._("IP Address")."</td>";
		PRINT "<td><input type=text size=20 name=ifaddr value=\"$ifaddr\"></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("MAC/Network Address")."</td>";
		PRINT "<td><input type=text size=25 name=ifmac value=\"$ifmac\"></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>";
		PRINT "<input type=hidden name=device value=$device>";
		PRINT "<input type=hidden name=device_type value=$device_type>";
		PRINT "<input type=submit value=\""._("Add")."\">";
		PRINT "</td>";
		PRINT "<td><input type=reset value=\""._("Clear")."\"></td>";
		PRINT "</tr>";

		PRINT "</form>";
		PRINT "</table>";

		commonFooter();
	}


	
	function updatePortForm(){
		commonHeader(_("Networking") . " - " . _("Port"));
		__("This is where you change the port properties.")."<hr noshade>\n";

		$ID = $_REQUEST['ID'];

		$DB = Config::Database();

		PRINT '<form method=get action="'.Config::AbsLoc('users/networking-port-update.php').'">';
		PRINT "<table>\n";	
		$qID = $DB->getTextValue($ID);
		$query_port = "SELECT * FROM networking_ports WHERE ID = $qID";
		$result_port = $DB->getRow($query_port);
		$ID = $result_port["ID"];
		$name = $result_port["name"];
		$logical_number = $result_port["logical_number"];
		$iface = $result_port["iface"];
		$device_on = $result_port["device_on"];
		$device_type = $result_port["device_type"];
		$ifaddr = $result_port["ifaddr"];
		$ifmac = $result_port["ifmac"];

		$qID = $DB->getTextValue($ID);
		$wquery = "SELECT * FROM networking_wire WHERE (end1 = $qID OR end2 = $qID)";
		$wresult = $DB->getRow($wquery);
		if (count($wresult) > 0) 
		{
			$wID = $wresult["ID"];
			$wend1 = $wresult["end1"];
			$wend2 = $wresult["end2"];
			if ($wend1 == $ID) 
			{
				$qwID = $DB->getTextValue($wend2);
			}
			else 
			{
				$qwID = $DB->getTextValue($wend1);
			}
			$pquery = "SELECT * FROM networking_ports WHERE (ID = $qwID)";
			$presult = $DB->getRow($pquery);
			$pID = $presult["ID"];
			$pNum = $presult["logical_number"];
			$pOn = $presult["device_on"];
			$pType = $presult["device_type"];
			$qpOn = $DB->getTextValue($pOn);
			if ($pType == 1) 
			{
				$nquery = "SELECT ID,name FROM computers WHERE (ID = $qpOn)";
			}
			else if ($pType == 2)
			{
				$nquery = "SELECT ID,name FROM networking WHERE (ID = $qpOn)";
			}
			$nresult = $DB->getRow($nquery);
			$nname = $nresult["name"];
			$nID = $nresult["ID"];
			$found = 1;
		}

		PRINT "<tr><td>"._("Logical Number:")."</td>";
		PRINT "<td><input type=text size=5 name=logical_number value=$logical_number></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("Name")."</td>";
		PRINT "<td><input type=text size=20 value=\"$name\" name=name></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("Interface")."</td>";
		PRINT "<td>";
		PRINT Dropdown_value("dropdown_iface","iface", $iface);
		PRINT "</td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("IP Address")."</td>";
		PRINT "<td><input type=text size=20 name=ifaddr value=\"$ifaddr\"></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("MAC/Network Address")."</td>";
		PRINT "<td><input type=text size=25 name=ifmac value=\"$ifmac\"></td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>"._("Connection")."</td>";
		PRINT "<td>";
		if ($pType == 1) 
		{
			printf(_('Port %s on computer %s'),
				'<a href="'
					.Config::AbsLoc("users/networking-port.php", array('ID' => $pID))
					."\">$pNum</a>",
				//TODO The following line fails due to file no
				//longer being used.
				'<a href="'
					.Config::AbsLoc("users/computers-info.php", array('ID' => $nID))
					."\">$nname ($nID)</a>"
				);
			PRINT ' | <a href="'.Config::AbsLoc("users/networking-port-discon.php", array('ID' => $ID)).'">'._("Disconnect").'</a>.';
		}
		else if ($pType == 2) 
		{
			printf(_("Port %s on network device %s"),
				'<a href="'.Config::AbsLoc("users/networking-port.php", array('ID' => $pID))
					."\">$pNum</a>",
				'<a href="'.Config::AbsLoc("users/networking-info.php", array('ID' => $nID))
					."\">$nname ($nID)</a>"
				);

			PRINT ' | <a href="'.Config::AbsLoc("users/networking-port-discon.php?ID=$ID").'">'._("Disconnect").'</a>.';
		}
		else if ($found != 1) 
		{
			__("Nothing Connected.").' <a href="'.Config::AbsLoc("users/networking-connecter.php?ID=$ID").'">'._("Connect").'</a>';
		}
		PRINT "</td>";
		PRINT "</tr>";

		PRINT "<tr>";
		PRINT "<td>";
		PRINT "<input type=hidden name=device value=$device_on>";
		PRINT "<input type=hidden name=device_type value=$device_type>";
		PRINT "<input type=hidden name=ID value=$ID>";
		PRINT "<input type=submit value=\""._("Update")."\">";
		PRINT "</td>";
		PRINT "</form>";

		PRINT '<form method=get action="'.Config::AbsLoc('users/networking-index.php').'">';
		PRINT "<input type=hidden name=device value=$device_on>";
		PRINT "<input type=hidden name=action value=deletePort>";
		PRINT "<input type=hidden name=device_type value=$device_type>";
		PRINT "<input type=hidden name=ID value=$ID>";
		PRINT "<td><input type=submit value=\""._("Remove")."\"></td>";
		PRINT "</tr>";
		PRINT "</form>";
		PRINT "</table>";

		commonFooter();
	}
	
	function connectPorts(){
	}

	function disconnectPorts(){
	}
}
