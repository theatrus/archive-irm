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
    /**
     * Device class for handling crud user defined devices
     * This class uses the Config::Database for database connection.
     * @package Device
     */
class Device
{
        /**
         * This variable seems not to be used. 
         */
	var $table;

        /**
         * This holds the devices result set. 
         */
	var $deviceList;

        /**
         * This holds the devices fields result set. 
         */
	var $deviceFields;

        /**
         * This holds any new created devices. 
         */
	var $newDevice;

        /**
         * This holds a single devices type. 
         */
	var $deviceType;

        /**
         * This holds the irm factory object for view representation/seperation. 
         */
	var $page;

	/**
         * Device class constructor.
         * @package Device
         */
	function Device()
	{
		$lookups = new Lookup();
		$this->page = new IrmFactory();
		$this->dropdownArray = $lookups->lookupList;
		$this->devicetype = $_POST['devicetype'];
		$this->ID = $_POST['deletedevice'];
	}

	function CsvDevice()
	{
		$sql = "SELECT * FROM " . $this->devicetype;
		$DB = Config::Database();
		$results = $DB->getAll($sql);
		foreach($results as $result)
		{
			foreach($result as $key=>$value)
			{
				$csvList .= $value . ',';
			}
			$csvList .= "\n";
		}
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		print $csvList;

	}

	function ListAllDevices()
	{
		$sql = "SELECT * FROM " . $this->devicetype;
		$DB = Config::Database();
		$results = $DB->getAll($sql);
		foreach($results as $result)
		{
			$this->deviceListHeader($result);
			$this->deviceListData($result);
		}
		print "<table border=1 class=sortable id=tracking>" . $this->headerList . $this->deviceList . "</table>";
	}

	function deviceListHeader($result)
	{
		if($this->header != 1)
		{
			$this->headerList.=  "<tr>";
			foreach($result as $key=>$value)
			{
				$this->headerList .= "<th>" . $key . "</th>";
			}
			$this->headerList .= "</tr>";
			$this->header = 1;
		}
	}

	function deviceListData($result)
	{
		$this->deviceList .=  "<tr>";
		foreach($result as $key=>$value){
			if($key == "ID"){
				if($this->devicetype == 'software') {
					$this->deviceList .= "<td><a href=software-index.php?devicetype=software&amp;action=info&amp;ID=$value >" . $value . "</a></td>";
				}elseif ($this->devicetype == 'computers'){
					$this->deviceList .= "<td><a href=computers-index.php?devicetype=computer&amp;action=info&amp;ID=$value >" . $value . "</a></td>";
				}elseif ($this->devicetype == 'networking'){
					$this->deviceList .= "<td><a href=networking-index.php?devicetype=networking&amp;action=info&amp;ID=$value >" . $value . "</a></td>";
				} else {
					$this->deviceList .= "<td><a href=device-info.php?devicetype=" . $this->devicetype . "&action=info&ID=$value >" . $value . "</a></td>";
				}
			} else {
				$this->deviceList .= "<td>" . $value . "</td>";
			}	
		}
		$this->deviceList .= "</tr>";
	}

	function getDevices()
	{
		$sql = "SELECT * FROM devices";
		$DB = Config::Database();
		$results = $DB->getAll($sql);
		$this->deviceList = $results;
	}

	function printDeviceList()
	{
		foreach ($this->deviceList as $device)
		{
			foreach($device as $key => $value)
			{
				$this->printDeviceListDetail($value);
			}
		}
	}
	
	function printDeviceListDetail($value){
		if(stristr($value, " "))
		{
			PRINT '<tr class="devicedetail">';
			PRINT "<td>$value " . _(" - is not a valid device type") . "</td>";
			PRINT "</tr>\n";
		}
		else
		{
			PRINT '<tr class="devicedetail">';
			PRINT "<td><a href=device-index.php?device=$value>$value</a></td>";
			PRINT "<td><a href=device-index.php?action=list&device=$value>List all $value</a></td>";
			PRINT "<td><a href=device-index.php?action=csv&device=$value>Export $value to CSV</a></td>";
			PRINT "</tr>\n";
		}
	}

	function frontPage()
	{
		$this->addDeviceForm();	
		$this->getDevices();
		PRINT "<table>";
		foreach($this->deviceList as $device)
		{
			foreach($device as $key => $value)
			{
				$this->deviceForm($value);
			}
		}
		PRINT "</table>";
	}
	
	function addDeviceForm(){
		$this->page->assign('addNewDevice', _("Add new devices type"));
		$this->page->assign('addDevice', _("Add Device"));
		$this->page->assign('existingDevice', _("Existing device types"));
		$this->page->display('addDeviceForm.html.php');
	}

	function deviceForm($value){
		$this->page->assign('value', $value);
		$this->page->assign('editFields', _("Edit Fields"));
		$this->page->assign('delete', _("Delete Field"));
		$this->page->display('deviceForm.html.php');
	}

	function deviceFieldForm($field)
	{
		$this->page->assign('field', $field['Field']);
		$this->page->assign('fieldType', $field['Type']);
		$this->page->assign('delete', _("Delete"));
		$this->page->assign('devicetype', $this->devicetype);
		$this->page->display('deviceFieldForm.html.php');
	}

	function addDeviceFieldForm()
	{
		$this->page->assign('addNewField', _("Add new field"));
		$this->page->assign('devicetype',$this->devicetype);
		$this->page->assign('string', _("String"));
		$this->page->assign('textarea', _("Text Area"));
		$this->page->assign('boolean', _("Boolean"));
		$this->page->assign('datetime', _("Date Time"));
		$this->page->assign('addField', _("Add Field"));
		$this->page->display('addDeviceFieldForm.html.php');
	}

	function deviceTable()
	{
		if(stristr($this->newDeviceType, " "))
		{
			PRINT _("You didn't listen did you");
		}
		else
		{
			$sql = "CREATE TABLE ". $this->newDeviceType ." (
				ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
				name char(255) DEFAULT '' NOT NULL,
				locations char(255) DEFAULT '' NOT NULL
				)";
			$DB = Config::Database();
			$DB->query($sql);
		}
	}

	function newDevice($newdevice)
	{
		if(stristr($newdevice, " "))
		{
			PRINT _("You didn't listen did you");
		}
		else
		{
			$this->newDeviceType = $newdevice;
			$this->deviceTable();
			$query = "INSERT INTO devices (name) VALUES ('" . $this->newDeviceType . "')";
			$DB = Config::Database();
			$DB->query($query);
		}
		$this->frontPage();
	}

	function deleteDeviceType()
	{
		$devicetype = $_POST['delete'];

		$DB = Config::Database();
		
		$query = "SELECT * FROM $devicetype";
		$result = $DB->getAll($query);

		foreach($result as $device){
			$this->devicetype = $devicetype;
			$this->ID = $device['ID'];
			$this->deleteDevice();
		}

		$query = 'DELETE FROM `devices` WHERE `name` = \'' . $devicetype . '\' LIMIT 1 ;'. ' ';
		$DB->query($query);

		if(!stristr($devicetype," "))
		{
			$sql = "DROP TABLE " . $devicetype;
			$DB->query($sql);
		}

		PRINT _("Deleted device : ") . $devicetype;
	}

	function setDeviceType($deviceType)
	{
		$this->devicetype = $deviceType;
	}


	function getDeviceFields()
	{
		$sql = "SHOW COLUMNS FROM  " . $this->devicetype;
		$DB = Config::Database();
		$this->deviceFields = $DB->getAll($sql);
	}


	function editDeviceFields()
	{
		PRINTF (_("Editing Fields for %s device type"), $this->devicetype);
		PRINT "<hr />";
		PRINT _("Field names must not include spaces");
		PRINT "<table>";
		PRINT '<tr class="setupheader">';
		PRINT "<td>" . _("Field Name") . "</td>";
		PRINT '<td colspan="2">' . _("Type of Data") . "</td>";
		PRINT "</tr>";
			
		$this->getDeviceFields();
		foreach ($this->deviceFields as $field)
		{
			$this->deviceFieldForm($field);
		}
		$this->addDeviceFieldForm();
		
		PRINT "</table>";
		PRINT "<hr />";
		PRINT _("Existing fields that will allow you to use dropdowns are as follow");
		PRINT "<ul>";
		foreach ($this->dropdownArray as $dropdownName)
		{
			echo "<li>$dropdownName</li>";
		}
		PRINT "</ul>";
		$this->deviceTypeFormFooter();
	}

	function deviceTypeFormFooter()
	{
		PRINT _("If you use any of these names as a field name you will get a corresponding dropdown");
		PRINT "<hr />";
		PRINT _("If a device has a field called ip, then it will be possible to view the UP/DOWN status of the Host, if the corresponding setting is set in the main IRM configuration");
		PRINT "<hr />";
		PRINT _("If a device has a field called user, then a dropdown will be presented of all the users on the system, allowing you to choose a user to link to a device.");

	}
	
	function varCharField()
	{
		if (in_array($this->fieldname,$this->dropdownArray) || $this->fieldname == "location")
		{
			if ($this->fieldname == "location")
			{
				$this->fieldname = "locations";
			}
			//Print "Drop down if fields match";
			$lookup = new Lookup($this->fieldname);
			PRINT $lookup->lookupName;
			PRINT ":<br />";
			PRINT $lookup->dropdown($this->fieldname, $this->fieldData);
		} else if ($this->fieldname == "user")
		{
			echo _("User");
			echo ":<br />";
			echo usersDropdown('user', $this->fieldData);
		} else {
			//Print "Char Text Field";
			PRINT $this->fieldname;
			PRINT ":<br />";
			PRINT '<input type="text" size="' . $this->size . '" name="' . $this->fieldname . '" value="' . $this->fieldData . '">';
		}
	}
	
	function charField()
	{
		if (in_array($this->fieldname,$this->dropdownArray))
		{
			//Print "Drop down if fields match";
			$lookup = new Lookup($this->fieldname);
			PRINT $lookup->lookupName;
			PRINT ":<br />";
			PRINT $lookup->dropdown($this->fieldname, $this->fieldData);
		} else if ($this->fieldname == 'user')
		{
			echo _("User");
			echo ":<br />";
			echo usersDropdown('user', $this->fieldData);
		} else {
			//Print "Char Text Field";
			PRINT $this->fieldname;
			PRINT ":<br />";
			PRINT '<input type="text" name="' . $this->fieldname . '" value="' . $this->fieldData . '">';
		}
	}
	
	function idField()
	{
		# This data type will only ever hold an device ID in the device table
		PRINT $this->fieldname . ":" . $this->fieldData;
		PRINT '<input type="hidden" name="' . $this->fieldname . '" value="' . $this->fieldData . '">';
	}

	function textField()
	{
		//Print "HTML Text Area";
		PRINT $this->fieldname . ':<br />';
		fckeditor($this->fieldname,$this->fieldData);
	}

	function dateTimeField()
	{
		//Print "Date Time Field";
		PRINT $this->fieldname . ':<br />';
		PRINT '<input type=text name="' . $this->fieldname . '" value="'. $this->fieldData . '">';
	}

	function checkBoxField()
	{
		//Print "Checkbox";
		if ($this->fieldData)
		{
			$checked = " checked";
		} else {
			$checked = '';
		}
	
		PRINT $this->fieldname . ':<br />';
		// The following hidden forces the name into the  _POST array,
		// the checkbox then takes over the name and posts a value.
		PRINT '<input type="hidden" name="' . $this->fieldname . '">'; 
		PRINT '<input type="checkbox" name="' . $this->fieldname . '" value="1" ' . $checked . '">';

	}

	function datetime()
	{
		//Print "Date Time";
		PRINT $this->fieldname . ':';
		PRINT $this->fieldData. '<br />';
	}

	function columnCount()
	{
		//Nasty hack to give us two columns
		if ( $this->cell == 2) {
			PRINT "</tr>\n";
			PRINT '<tr class="devicedetail">';
			$this->cell=0 ;
		}
		$this->cell++;
	}

	function dbDataType()
	{
		switch ($this->datatype)
		{
			case datetime:
				$this->dbDataType = "datetime";
				break;
			case string:
				$this->dbDataType = "char(255)";
				break;
			case textarea:
				$this->dbDataType = "text";
				break;
			case boolean:
				$this->dbDataType = "tinyint(4)";
				break;
		}
	}

	function inputTypeDisplay()
	{
		switch ( $this->fieldType )
		{
			case "datetime":
				$this->size = "20";
				$this->dateTimeField();
				break;

			case "varchar(255)":
				$this->size = "20";
				$this->varCharField();
				break;

			case "varchar(200)":
				$this->size = "20";
				$this->varCharField();
				break;

			case "varchar(100)":
				$this->size = "20";
				$this->varCharField();
				break;

			case "varchar(90)":
				$this->size = "20";
				$this->varCharField();
				break;

			case "varchar(50)":
				$this->size = "50";
				$this->varCharField();
				break;

			case "varchar(40)":
				$this->size = "40";
				$this->varCharField();
				break;

			case "varchar(30)":
				$this->size = "30";
				$this->varCharField();
				break;

			case "varchar(20)":
				$this->size = "20";
				$this->varCharField();
				break;

			case "varchar(10)":
				$this->size = "10";
				$this->varCharField();
				break;

			case "varchar(6)":
				$this->size = "6";
				$this->varCharField();
				break;

			case "char(255)":
				$this->size = "20";
				$this->charField();
				break;
				
			case "int(11)":
				$this->idField();
				break;
				
			case "text":
				$this->textField();
				break;
				
			case "tinyint(4)":
				$this->checkBoxField();
				break;

			case "bigint(20) unsigned":
				$this->idField();
				break;

			case "datetime":
				$this->datetime();
				break;
		}

	}
	
	function inputSpecialDisplay()
	{
		switch( $this->fieldname ){
			case "ID":
				$this->ID = $this->fieldData;	
				break;

			case "name":
				$this->name = $this->fieldData;	
				break;

			case "ip":
				$this->IP = $this->fieldData;
				PRINT SnmpStatus($this->IP, $this->ID, $this->devicetype);
				break;
		}
	}

	function formDetail()
	{
		$this->cell=0;
		print '<table>';
		PRINT '<tr class="devicedetail">';
		PRINT '<input type="hidden" name="devicetype" value="' . $this->devicetype . '">';
		foreach ($this->deviceFields as $field)
		{
			$this->fieldType = $field['Type'];
			$this->fieldname = $field['Field'];
			$this->fieldData = $this->data[($this->fieldname)];
			$this->columnCount();		

			PRINT "<td>";
			$this->inputSpecialDisplay();
			$this->inputTypeDisplay();
			PRINT "</td>\n";
		}
		if ($this->cell == 1)
		{
			PRINT "<td></td>";
		}
		PRINT "</tr>";
		PRINT '</table>';
	}

	function addForm()
	{
		$this->getDeviceFields();
		PRINT '<form method="POST" action="device-add.php">';
		print $this->deviceFormHeader(_("Adding new ") . $this->devicetype);
		
		$this->formDetail();		
		
		PRINT "<table>";
		PRINT '<tr class="deviceupdate">';
		PRINT '<td><input type="submit" value="' . _("Add") . '"></td>';
		PRINT '<td><input type="reset" value="'. _("Reset") . '"></td>';
		PRINT "</tr>";
		
		PRINT "</table>";
		PRINT "</form>";
	}

	function deviceFormHeader($string) {
		$this->page->assign('string', $string);
		$this->page->display('deviceFormHeader.html.php');
	}

	function editForm()
	{
		$this->getDeviceFields();
		PRINT '<form method="POST" action="device-update.php">';
		$this->deviceFormHeader(_("Editing ") . $this->devicetype);
		$this->formDetail();		
		PRINT "<table>";
		PRINT '<tr class="deviceupdate">';
		PRINT '<td><input type="submit" value="' . _("Update") . '"></td>';
		PRINT "</form>";
		PRINT '<form method="POST" action="device-update.php">';
		PRINT '<input type="hidden" name="deletedevice" value="' . $this->ID . '">';
		PRINT '<input type="hidden" name="devicetype" value="' . $this->devicetype. '">';
		PRINT '<td><input type="submit" value="' . _("Delete") . '"></td>';
		PRINT "</tr>";	
		PRINT "</table>";
		PRINT "</form>";

		showPortsOnDevice($this->ID, $this->devicetype);
	
		compsoftShow($this->ID);
		$files = new Files();	
		$files->setDeviceType($this->devicetype);
		$files->setDeviceID($this->ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();

		displayDeviceTracking($this->ID, $this->devicetype);
	}


	function addField()
	{
		$this->dbDataType();
		$sql = "ALTER TABLE " . $this->devicetype . " ADD " . $this->field_name . " " . $this->dbDataType;
		$DB = Config::Database();
		$DB->query($sql);
	}

	function deleteField()
	{
		$sql = "ALTER TABLE " . $this->devicetype . " DROP " . $_POST['delete'];
		$DB = Config::Database();
		$DB->query($sql);
	}

	function addNew()
	{
		$this->devicetype = $_POST['devicetype'];
		unset($_POST['devicetype']);
		$DB = Config::Database();
		$DB->InsertQuery($this->devicetype,$_POST);
	
		commonheader(_("Added new $this->devicetype"));
		PRINT "<table>";
		foreach ($_POST as $key=>$value)
		{
			PRINT '<tr class="devicedetail">';
			PRINT "<td>" . $key . "</td><td>" . $value . "</td>" ;
			PRINT "</tr>";
		}
		PRINT "</table>";
		commonfooter();
	}

	function editDevice()
	{
		$this->devicetype = $_GET['devicetype'];
		unset($_GET['devicetype']);
		$DB = Config::Database();
		$query = "SELECT * FROM $this->devicetype WHERE (ID = " . $_GET['ID'] . ")";
		$this->data = $DB->getRow($query);	

		commonHeader($this->devicetype . " - " . _("Device Information"));
		$this->editForm();
		commonFooter();
	}

	function updateDevice()
	{	
		$this->devicetype = $_POST['devicetype'];
		unset($_POST['devicetype']);
		$DB = Config::Database();
		$DB->UpdateQuery($this->devicetype, $_POST, "ID=" . $_POST['ID']);
		
		commonHeader($this->devicetype . " - " . _("Updated"));
		PRINTF (_("%s has been updated"), $this->devicetype);
		PRINT "<a href=device-index.php?device=$this->devicetype>Go to $this->devicetype</a>";
		commonFooter();
	}

	function deleteDevice()
	{
		$DB = Config::Database();
		$query = "SELECT * FROM networking_ports WHERE (device_type=\"" . $this->devicetype . "\") AND (device_on=\"" . $this->ID . "\")";
		$result = $DB->getAll($query);
		foreach($result as $port){
			$ID = $port['ID'];
			// Delete port.
			$query = "DELETE FROM networking_ports WHERE (ID = $ID)";
			$DB->query($query);

			//Delete associated wire.
			$query = "DELETE FROM networking_wire WHERE (end1 = $ID OR end2 = $ID)";
			$DB->query($query);

			logevent($this->devicetype, _("networking"), 4, "port", sprintf(_("%s removed port %s"),$IRMName,$ID));
		}

		$query = "DELETE FROM " . $this->devicetype . " WHERE (ID = " . $this->ID . ")";
		$DB->query($query);

		commonHeader($this->devicetype . " - " . _("Deleted"));
		PRINTF (_("%s - ID %s has been deleted"), $this->devicetype, $this->ID);
		commonFooter();
	}
}
?>
