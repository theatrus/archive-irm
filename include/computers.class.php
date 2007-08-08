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

class Computer
{

function Computer($action="",$ID=-1,$expand=1){
	// if the oject is created with values, use it else get from REQUEST
	if($action == "") {
		$action = $_REQUEST['action'];
	}

	if($ID == -1) {
		$this->ID = $_REQUEST['ID'];
	} else {
		$this->ID = $ID;
	}

	switch($action)
	{
		case 'delete':
			AuthCheck("tech");
			$this->ComputerDelete();
			break;
		case 'info':
			$this->ComputerInfo($expand);
			break;
		case 'select-add':
			AuthCheck("tech");
			$this->ComputerAddSelect();
			break;
		case 'add':
			AuthCheck("tech");
			$this->addComputer();
			break;
		case 'new':
			AuthCheck("tech");
			$this->add();
			break;
		case 'update';
			AuthCheck("tech");
			$this->UpdateComputer();
			break;

		default:	
			$this->search();
	}
}

function add(){
	$DB = Config::Database();

	if ($_REQUEST['reqID'])
	{
		if (!preg_match('/^\d+$/', $_REQUEST['reqID']))
		{
			commonHeader(_("Error"));
			__("ERROR: Requested IDs can only contain digits.  Please re-enter your requested IRM ID");
			commonFooter();
			exit();
		}
		
		$qreqID = $DB->getTextValue($_REQUEST['reqID']);
		$query = "SELECT COUNT(ID) FROM computers WHERE (ID = $qreqID)";
		if ($DB->getOne($query) == 0) 
		{
			$ID = $_REQUEST['reqID'];
			$DB->query("INSERT INTO computers__ID (sequence) VALUES ($qreqID)");
			$DB->query("DELETE FROM computers__ID WHERE sequence < $qreqID");
		} else 
		{
			commonHeader(_("Error"));
			printf(_("A computer with ID %s already exists.  Please pick a new ID"), $_REQUEST['reqID']);
			commonFooter();
			exit();
		}
	}

	if ($_REQUEST['reqID'] == 0) 
	{
		$ID = $DB->_dbh->nextId('computers__ID');
	}	

	$this->flags_surplus = $this->Flags($_REQUEST['flags_surplus']);
	$this->flags_server = $this->Flags($_REQUEST['flags_server']);


	$vals = array(
		'ID' => $ID,
		'flags_server' => $this->flags_server,
		'flags_surplus' => $this->flags_surplus,
		'name' => $_REQUEST['name'],
		'type' => $_REQUEST['type'],
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
		'date_mod' => $_REQUEST['date_mod']
		);

	$DB->InsertQuery('computers', $vals);
		
	logevent($ID, _("computers"), 4, _("database"), sprintf(_('%s added record'), $IRMName)); 

	if ($iface_do == "yes") 
	{
		$vals = array(
			'device_on' => $ID,
			'device_type' => 1,
			'iface' => $iface,
			'ifaddr' => $ip,
			'ifmac' => $mac,
			'logical_number' => 1,
			'name' => 'Port 1'
			);
		$DB->InsertQuery('networking_ports', $vals);
	}

	$templID = $_REQUEST['templID'];

	$qtemplID = $DB->getTextValue($templID);
	$query = "SELECT * FROM templ_inst_software WHERE (cID = $qtemplID)";
	$data = $DB->getAll($query);


	foreach ($data as $result)
	{
		$lID = find_license($result['sID'], 1);
		
		if($lID == NULL){
			$lID = 0;
		}

		$vals = array(
			'sID' => $result['sID'],
			'cID' => $ID,
			'lID' => $lID,
			'gID' => NULL,
			'lCnt' => "1", 
			);
		$DB->InsertQuery('inst_software', $vals);
	}

	$newloc = appendURLArguments($_SESSION['_sess_pagehistory']->Previous(), array('add' => 1));
	header("Location: $newloc");

	printf(_("Redirecting to %s\n"), $newloc);
}

function ComputerDelete(){
	header("Location: ".Config::AbsLoc('users/computers-index.php'));
	$track = new Tracking();
	$trackIDs = $track->getByComputerID($this->ID);
	PRINT "trackIDs = $trackIDs\n";
	$trackIDsSize = sizeof($trackIDs);
	for($i=0;$i<$trackIDsSize;$i++)
	{
		$track2 = new Tracking($trackIDs[$i]);
		$track2->delete();
	}
	$DB = Config::Database();
	$qID = $DB->getTextValue($this->ID);
	$query = "DELETE FROM computers WHERE (ID = $qID)";
	$DB->query($query);
	$query = "DELETE FROM inst_software WHERE (cID = $qID)";
	$DB->query($query);
	// Wipe out all wire relating to ports on this PC
	$ports = $DB->getCol("SELECT ID FROM networking_ports WHERE (device_on=$qID AND device_type=1)");
	foreach ($ports as $p)
	{
		$qp = $DB->getTextValue($p);
		$query = "DELETE FROM networking_wire WHERE end1=$qp OR end2=$qp";
		$DB->query($query);
	}
	$query = "DELETE FROM networking_ports WHERE (device_on = $qID AND device_type = 1)";
	$DB->query($query);
	logevent($this->ID, _("computers"), 4, _("database"), sprintf(_("%s deleted record"), $IRMName)); 

}

function ComputerAddSelect(){
	commonHeader(_("Computers") ." - " . _("Select Template"));
	__("Select from one of the templates below to ease in adding a computer. If you wish to create/modify templates, please go to the setup area.");
	templateSelect();
	commonFooter();
}

function ComputerFields(){
	$this->computer_fields = array(
			'name'			 => _("Name"),
			'ID'			 => _("IRM ID"),
			'location'		 => _("Location"),
			'flags_surplus'		 => _("Surplus"),
			'flags_server'		 => _("Server")	,
			'type'			 => _("Type"),
			'os'			 => _("Operating System"),
			'osver'			 => _("Operating System Version"),
			'processor'		 => _("Processor"),
			'processor_speed'	 => _("Processor Speed"),
			'serial'		 => _("Serial Number"),
			'otherserial'		 => _("Other Number"),
			'ramtype'		 => _("RAM Type"),
			'ram'			 => _("RAM Amount (in MB)"),
			'network'		 => _("Network Card Type/Brand"),
			'ip'			 => _("IP Address"),
			'mac'			 => _("MAC/Network Address"),
			'hdspace'		 => _("Hard Drive Capacity"),
			'contact'		 => _("Contact Person"),
			'contact_num'		 => _("Contact Number"),
			'date_mod'		 => _("Date Last Modified"),
			'comments'		 => _("Comments")
			);
}

function Flags($flag)
{
	if ($flag == "yes")
	{
        	$flag = 1;
   	}
	else
	{
        	$flag = 0;
	}

	return $flag;
}

function UpdateComputer(){
	$comments = AddSlashes($comments);

	$this->flags_surplus = $this->Flags($_REQUEST['flags_surplus']);
	$this->flags_server = $this->Flags($_REQUEST['flags_server']);

	$vals = array(
		'name' => $_REQUEST['name'],
		'type' => $_REQUEST['type'],
		'flags_server' => $this->flags_server,
		'flags_surplus' => $this->flags_surplus,
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
		'date_mod' => $_REQUEST['date_mod']
		);

	$DB = Config::Database();
	$ID = $DB->getTextValue($ID);
	$DB->UpdateQuery('computers', $vals, "ID=" . $this->ID);

	logevent($ID, _("computers"), 4, _("database"), sprintf(_("%s updated record"),$IRMName));
	$this->ComputerInfo(1);
}

function showComputer($ID, $expand) 
{
	$DB = Config::Database();
	
	$qID = $DB->getTextValue($ID);
	$query = "SELECT * FROM computers WHERE (ID = $qID)";

	$result = $DB->getRow($query);
    
	$name = $result["name"];
	$date_mod = $result["date_mod"];
	
	$new_date = date("Y-m-d H:i:s");
	$comments = stripslashes($comments);

	$userbase = Config::AbsLoc('users');
	?>
	<table class="computer">
	<form method="POST" action="<?php PRINT Config::AbsLoc('users/computers-index.php') ?>">
	<input type="hidden" name="ID" value="<?php PRINT $ID ?>">
	<input type="hidden" name="action" value="update">
		
	<tr class="computerheader">
	<td colspan=2>
	<?php
	if ($expand == 1){
		PRINT "<strong>$name ($ID)</strong> ";
		PRINT "<a href=\"$userbase/helper-index.php?action=add&ID=$ID&is_group=no&deviceType=computers\">"._("Add Tracking")."</a>$snmp_link";
	} else if ($expand == 0) {
		PRINT "<strong>";
		//TODO The following line fails to due to the files no longer
		//being available
		PRINT "<a href=\"$userbase/computers-info.php?ID=$ID\">$name ($ID)</a>";
		PRINT "</strong>"; 
		PRINT "<a href=\"$userbase/tracking-add-form.php?ID=$ID\">"._("Add Tracking")."</a>$snmp_link";
	}
	PRINT SnmpStatus($result['ip'],$ID, "computers");
	PRINT "</td></tr>\n";

	$this->computerForm($result);

	PRINT '<tr class="computerheader">';
	PRINT "<td colspan=2 align=center>"._("Last Updated:")." $date_mod <input type=hidden name=date_mod value=\"$new_date\"></td>";
	PRINT "</tr>\n";
	
	PRINT '<tr class="computerupdate">';
	PRINT "<td>";
	PRINT "<input type=submit value=\""._("Update")."\">";
	PRINT "</form>";
	PRINT "<form method=GET action=\"$userbase/computers-index.php\">";
	PRINT '<input type="hidden" name="action" value="delete">';
	PRINT '<input type="hidden" name="ID" value="' . $this->ID .'">';
	PRINT "</td>";
	PRINT "<td><input type=submit value=\""._("Delete")."\"></form></td>";
	PRINT "</tr>\n";
	
	PRINT "</table>";
	PRINT "<br />";
}

function computerForm($result)
{
	$Page = new IrmFactory();

	if ($result['flags_server'] == 1) {
		$serverField =  '<input type="checkbox" name="flags_server" value="yes" CHECKED>';
	} else {
		$serverField =  '<input type="checkbox" name="flags_server" VALUE="yes">';
	}
								
	if ($result['flags_surplus'] == 1) {
		$surplusField = '<input type="checkbox" name="flags_surplus" value="yes" CHECKED>';
	} else {
		$surplusField = '<input type="checkbox" name="flags_surplus" value="yes">';
	}

	$computerDetails = array (
	'lableName' => 			_('Name'),
	'lableType' => 			_('Type'),
	'lableLocation' =>		_('Location'),
	'lableOS' =>			_('OS'),
	'lableOSVersion' => 		_('OSVersion'),
	'lableProcessor' => 		_('Processor'),
	'lableProcessorSpeed' => 	_('Processor Speed'),
	'lableSerialNumber' => 		_('Serial Number'),
	'lableOtherSerialNumber' => 	_('Other Serial Number'),
	'lableHardDriveSpace' => 	_('Hard Drive Space in MB'),
	'lableRamType' => 		_('Ram Type'),
	'lableRamAmount' => 		_('Ram Amount'),
	'lableNetworkCard' => 		_('Network Card Type/Brand'),
	'lableIPAddress' => 		_('IP Address'),
	'lableMAC' => 			_('MAC/Network Address'),
	'lableComments' => 		_('Comments'),
	'lableContactPerson' => 	_('Contact Person'),
	'lableContactNumber' => 	_('Contact Number'),
	'lableServer' => 		_("Server (constantly running)"),
	'lableSurplus' => 		_("Surplus"),

	'type' =>  		Dropdown_value("dropdown_type", "type", $result['type']),
	'location' =>  		Dropdown_value("dropdown_locations", "location", $result['location']),
	'os' => 		Dropdown_value("dropdown_os", "os", $result['os']),
	'processor' => 		Dropdown_value("dropdown_processor", "processor", $result['processor']),
	'ramType' => 		Dropdown_value("dropdown_ram", "ramtype", $result['ramtype']),
	'networkCard' =>  	Dropdown_value("dropdown_network", "network", $result['network']),
	
	'name' => 		'<input type="text" name="name"	size="24" value="' . $result['name'] . '">',
	'osver' => 		'<input type="text" name="osver" size="5" value="' . $result['osver'] . '">',
	'processorSpeed' => 	'<input type="text" name="processor_speed" size="4" value="' . $result['processor_speed'] .'">',
	'serialNumber' =>  	'<input type="text" name="serial" size="35" value="' . $result['serial'] .'">',
	'otherSerialNumber' =>  '<input type="text" name="otherserial" size="25" value="' . $result['otherserial'] .'">',
	'HardDriveSpace' =>  	'<input type="text" name="hdspace" size="5" value="'. $result['hdspace'] . '">',
	'ram' =>  		'<input type="text" name="ram" size="5" value="' . $result['ram'] . '">',
	'IPAddress' =>  	'<input type="text" name="ip" size="16" value="'. $result['ip'] . '">',
	'mac' =>  		'<input type="text" name="mac" value="' . $result['mac'] . '">',
	'contactPerson' =>  	'<input type="text" name="contact" size="20" value="' . $result['contact'] .'">',
	'contactNumber' =>  	'<input type="text" name="contact_num" value="' . $result['contact_num'] . '">',
	
	'comments' =>  	'<textarea cols="20" rows="5" name="comments" wrap="soft">' . $result['comments'] . '</textarea>',

	'server' =>	$serverField,
	'surplus' => 	$surplusField,
	);	

	foreach($computerDetails as $key => $value){
		$Page->assign($key,$value);
	}

	$Page->display('computerForm.html.php');
}


function addComputer()
{
	commonHeader(_("Computers") . " - " . _("Add Form"));
	if (@$_REQUEST['add'] == 1) 
	{
		PRINT "<h3>" . _("Computer Added Successfully") . "</h3>";
		PRINT "<hr noshade>";
	}

	$withtemplate = $_REQUEST['withtemplate'];

	$DB = Config::Database();

	if ($withtemplate == 1) 
	{
		$templID = $_REQUEST['ID'];
	
		$qID = $DB->getTextValue($templID);
		
		$query = "SELECT * FROM templates WHERE (ID = $qID)";
	
		$result = $DB->getRow($query);

  		$templname = $result["templname"];
	  	$name = $result["name"];
  		$new_date = date("Y-m-d H:i:s");
	  	$iface = $result["iface"];
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
		$comments = $result["comments"];
		$contact = $result["contact"];
		$contact_num = $result["contact_num"];
	  	$flags_server = $result["flags_server"];
		$flags_surplus = @$result["flags_surplus"];
	}

	printf(_("Use this form to add a computer from template \"%s\":"),$templname);
	PRINT "<br>";
	PRINT '<table>';
	PRINT '<form method=post action="'.Config::AbsLoc('users/computers-index.php').'">';
	PRINT '<input type="hidden" name="action" value="new">';
	PRINT '<tr class="computerdetail">';
	PRINT '<td colspan=2>';
	$new_date = date("Y-m-d H:i:s"); 
	PRINT "<strong>";
	printf(_("New Computer from \"%s\""),$templname);
	PRINT "</strong>";
	PRINT "</td>";
	PRINT "</tr>";
	
	PRINT '<tr class="computerdetail">';
	PRINT "<td>" ;
	PRINT  _("Requested IRM ID (optional):");
	PRINT "<br /><input type=text name=reqID size=3></td>";
	PRINT "<td> " ;
	PRINT "</td>";
	PRINT "</tr>";
	
	$this->computerForm($result);
	
	PRINT '<tr class="computerdetail">';
	PRINT "<td colspan=2> " . _("Network Interface: ");
	PRINT Dropdown_value("dropdown_iface", "iface", $iface);
	PRINT "<br><input type=checkbox name=iface_do value=yes checked> ";
	__("Add a port of this type.");
	PRINT "</td>";
	PRINT "</tr>";
		    
	PRINT '<tr class="computerdetail">';
	PRINT "<td colspan=2 align=center>" . _("Added On: ") . "$new_date <input type=hidden name=date_mod value=\"$new_date\"></td>";
	PRINT "</tr>";
	
	PRINT '<tr class="computerupdate">';
	PRINT "<td>";
	PRINT "<input type=hidden name=templID value=$templID>";
	PRINT "<input type=submit value=". _("Add") . "></td>";
	PRINT "<td><input type=Reset value=" . _("Reset") . "></form></td>";
	PRINT "</tr>";
	PRINT "</table>";
	PRINT "<br>";
	commonFooter();
}


function ComputerInfo($expand){
	if($expand == 1) {
		commonHeader(_("Computers") . " - " . _("Info"));
	}

	$this->showComputer($this->ID, $expand);

	if($expand == 1) {
		showPortsOnDevice($this->ID, 1);
		compsoftShow($this->ID);
		displayDeviceTracking($this->ID, "Computers");
		displayDeviceGroups($this->ID);

		$files = new Files();	
		$files->setDeviceType("computers");
		$files->setDeviceID($this->ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();
		commonFooter();
	}
}

function search(){
	commonHeader(_("Computers"));
	__("Welcome to the IRM Computer Tracking utility!  This is where you store information about the various computers scattered about your organization. Below are tools in which you can view your computers, as well as edit and add entries.");
	$this->deviceType = "computer";
	$this->ComputerFields();
	deviceSearch($this->deviceType, $this->computer_fields);
	commonFooter();
}
}
?>
