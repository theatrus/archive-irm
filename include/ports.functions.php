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
class ports extends IRMMain
{
	function ports()
	{
		commonHeader(_("Networking Connector Wizard"));
		if($_REQUEST['connect']){
			$this->updateWires();
		} else {
			$this->pID=$_REQUEST['pID'];
			$this->device=$_REQUEST['device'];
			$this->device_name=$_REQUEST['device_name'];
			$this->selectDeviceType();
			if($this->device != NULL){
				$this->selectDevice();
			}
			if($this->device_name != NULL){
				$this->portList();
			}
		}
		commonFooter();
	}
	
	function portList()
	{
		$query = "SELECT * FROM networking_ports WHERE (device_on = $this->device_name) AND (device_type =\"$this->device\") ORDER BY logical_number";
		$DB = Config::Database();
		$data = $DB->getAll($query);
		PRINT "<table class=networking>";
		PRINT "<tr>";
		PRINT "<th>" .("ID") . "</th>";
		PRINT "<th>" .("Port Name") . "</th>";
		PRINT "<th>" .("Type") . "</th>";
		PRINT "<th>" .("IP Address") . "</th>";
		PRINT "<th>" .("MAC Address") . "</th>";
		PRINT "<th>" .("Port Name") . "</th>";
		PRINT "</tr>";

		foreach($data as $port){
			PRINT "<form type=post method=$_SELF>";
			PRINT "<tr>";

			PRINT "<td class=networkingdetail>";
			PRINT "<input type=hidden name=pID value=" . $port['ID']. ">";
			PRINT "<input type=hidden name=pID1 value=$this->pID>";
			PRINT "<input type=hidden name=connect value=true>";
			PRINT "<input type=submit value=" . _("Connect") . ">";
			PRINT "</td>";

			PRINT "<td class=networkingdetail>" . $port['logical_number'] . "</td>";
			PRINT "<td class=networkingdetail>" . $port['name'] . "</td>";
			PRINT "<td class=networkingdetail>" . $port['iface'] . "</td>";
			PRINT "<td class=networkingdetail>" . $port['ifaddr'] . "</td>";
			PRINT "<td class=networkingdetail>" . $port['ifmac'] . "</td>";

			PRINT "</form>\n";
		}
		PRINT "</table>";
	}
	
	function updateWires()
	{
		$vals = array(
			'end1' => $_REQUEST[pID],
			'end2' => $_REQUEST[pID1]
			);
		$DB = Config::Database();
		$DB->InsertQuery('networking_wire', $vals);
		PRINT _("You have now connected two devices");
	}

}


function portsTableHead()
{
	PRINT '<table class="networking">';
	PRINT '<tr class="networkingheader">';
	PRINT "<th>"._("Port #")."</th>";
	PRINT "<th>"._("Name")."</th>";
	PRINT "<th>"._("Interface")."</th>";
	PRINT "<th>"._("IP Address")."</th>";
	PRINT "<th>"._("MAC/Network Address")."</th>";

	PRINT Config::Get('mrtg');

	if(Config::Get('mrtg')== "1"){
		PRINT "<th>"._("MRTG Graph")."</th>";
	}
	PRINT "<th>"._("Connected to...")."</th>";
	PRINT "</tr>\n";
}


function getPortsOnDevice($device, $device_type)
{
	$DB = Config::Database();
	$qdevice = $DB->getTextValue($device);
	$qdevice_type = $DB->getTextValue($device_type);
	$query = "SELECT * FROM networking_ports WHERE (device_on = $qdevice AND device_type = $qdevice_type) ORDER BY logical_number";

	$data = $DB->getAll($query);
	return $data;
}

function getWires($ID)
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($ID);
	$wquery = "SELECT * FROM networking_wire WHERE (end1 = $qID OR end2 = $qID)";
	$wire = $DB->getRow($wquery);
	return $wire;
}

function showPortRow($data, $ip)
{
	$DB = Config::Database();
	$userbase = Config::AbsLoc('users');

	foreach ($data as $result)
	{
		$found = 0;
		$pType=0;

		$ID = $result["ID"];
		$name = $result["name"];
		$logical_number = $result["logical_number"];
		$iface = $result["iface"];
		$ifaddr = $result["ifaddr"];
		$ifmac = $result["ifmac"];

		$wire = getWires($ID);

		if (count($wire) > 0) 
		{
			$wID = $wire["ID"];
			$wend1 = $wire["end1"];
			$wend2 = $wire["end2"];
			if ($wend1 == $ID) 
			{
				$qend = $DB->getTextValue($wend2);
			} else {
				$qend = $DB->getTextValue($wend1);
			}
			
			$pquery = "SELECT * FROM networking_ports WHERE (ID = $qend)";
			$farend = $DB->getRow($pquery);
				
			$pID = $farend["ID"];
			$pNum = $farend["logical_number"];
			$pOn = $farend["device_on"];
			$pType = $farend["device_type"];
			$qOn = $DB->getTextValue($pOn);

			switch ($pType)
			{
			case "1": 
				$portOnDeviceType = "computers"; 
				break;
			case "2":
				$portOnDeviceType = "networking"; 
				break;
			default:
				$portOnDeviceType = $pType;
			}
	
			$nquery = "SELECT ID,name FROM $portOnDeviceType WHERE (ID = $qOn)";
			
			$nresult = $DB->getRow($nquery);
			$nname = $nresult["name"];
			$nID = $nresult["ID"];
			$found = 1;
		}
		else
		{
			$found = 0;
			$pType = 0;
		}
	
		PRINT '<tr class="networkingdetail">';
		PRINT "<td><a href=\"$userbase/networking-port.php?ID=$ID\">$logical_number</a></td>\n";
		PRINT "<td>$name</td>\n";
		PRINT "<td>$iface</td>\n";
		PRINT "<td>$ifaddr</td>\n";
		PRINT "<td>$ifmac</td>\n";

		//TODO MRTG Graph of port
		
		if(Config::Get('mrtg')== "1"){
			$url = Config::Get('mrtglocation');
			
			if($ifaddr && $ifaddr != "127.0.0.1"){
				PRINT "<td><img src=" . $url . $ifaddr . "_" . $logical_number . "-day.png></td>\n";
			}elseif ($ip != null && $ifaddr != "127.0.0.1"){
				PRINT "<td><img src=http://192.168.1.11/~budgester/mrtg/" . $ip . "_" . $logical_number . "-day.png></td>\n";
			} else {
				PRINT "<td></td>\n";
			}
		}
		PRINT "<td>\n";

		if ($found != 1) 
		{
			PRINT _("Nothing Connected.")." <a href=\"$userbase/ports.php?pID=$ID\">"._("Connect")."</a>\n";
		} else {
			switch ($pType)
			{
			case "1": 
				PRINTF(_("Connected to port %s on computer %s"),
					"<a href=\"$userbase/networking-port.php?ID=$pID\">$pNum</a>\n",
					"<a href=\"$userbase/computers-index.php?action=info&ID=$nID\">$nname ($nID)</a>\n"
					);
				PRINT " | <a href=\"$userbase/networking-port-discon.php?ID=$ID\">"._("Disconnect")."</a>.\n";
				break;
			case "2":	
				PRINTF(_("Connected to port %s on network device %s"),
					"<a href=\"$userbase/networking-port.php?ID=$pID\">$pNum</a>\n",
					"<a href=\"$userbase/networking-info.php?ID=$nID\">$nname ($nID)</a>\n"
					);
				PRINT " | <a href=\"$userbase/networking-port-discon.php?ID=$ID\">"._("Disconnect")."</a>.\n";
				break;
			default:
				PRINTF(_("Connected to port %s on %s device %s"),
					"<a href=\"$userbase/networking-port.php?ID=$pID\">$pNum</a>\n",
					$pType,
					"<a href=\"$userbase/device-info.php?ID=$nID&devicetype=$pType\">$nname ($nID)</a>\n"
					);
				PRINT " | <a href=\"$userbase/networking-port-discon.php?ID=$ID\">"._("Disconnect")."</a>.\n";
			}
		}
		PRINT "</td>\n";
		PRINT "</tr>\n";
	}
}

function showPortsOnDevice($device, $device_type)
{
	# 1 is computer, 2 networking device

	$userbase = Config::AbsLoc('users');
	$data = getPortsOnDevice($device, $device_type);

	portsTableHead();

	if(Config::Get('snmp')== "1"){
		$ip = DeviceConnection::ID2IP($device, $device_type);
	}

	if (count($data) < 1){
		PRINT "<TR><td colspan=6>"._("Looks like a lonely device to me.  No ports found.")."</TD></TR>\n";
	} else {
		showPortRow($data, $ip);
	}

	PRINT '<tr class="networkingupdate">';
	PRINT "<td colspan=6 align=right><A HREF=\"$userbase/networking-port-add-form.php?device=$device&device_type=$device_type\">"._("Add Port")."</a></td>\n";
	PRINT "</tr>\n";
	PRINT "</table>\n";
}
?>
