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
require_once dirname(__FILE__) . '/../lib/adodb-time.inc.php';

class IRMMain
{
	var $ID;
	var $DateEntered;
	
	function getID()
	{
		return($this->ID);
	}

	function getAuthor()
	{
		return($this->Author);
	}

	function getDateEntered()
	{
		return($this->DateEntered);
	}

	function setDateEntered($DE)
	{
		$this->DateEntered = $DE;
	}

	function setID($sID)
	{
		$this->ID = $sID;
	}

	function dateopened()
	{
		list($year,$month,$day,$hour,$minute,$second) = split("([^0-9])", $this->DateEntered);
		$timestamp = adodb_mktime($hour,$minute,$second,$month,$day,$year);
		$this->dateopened = adodb_date("Y-m-d H:i:s", $timestamp);
	}

	function dateclosed()
	{
		list($year,$month,$day,$hour,$minute,$second) = split("([^0-9])", $this->CloseDate);
		$timestamp = adodb_mktime($hour,$minute,$second,$month,$day,$year);
		$this->dateclosed = adodb_date("Y-m-d H:i:s", $timestamp);
	}

	function selectDeviceType()
	{
		$query = "SELECT * FROM devices";
		$DB = Config::Database();
		$data = $DB->getAll($query);
		foreach($data as $device){
			if($this->device == $device){
				$selected = "selected";
			}else{
				$selected = "";
			}
			$device_values .= "<option value=" . $device['name']  . " ". $selected . ">" . $device['name'] . "</option>";
		}

		//TODO This is to be refactored when computers and networking
		// are integrated into devices.
		if($this->device == 1){
			$selected = "selected";
		}else{
			$selected = "";
		}
		$device_values .= "<option value=1 $selected>Computers</option>";
		if($this->device == 2){
			$selected = "selected";
		}else{
			$selected = "";
		}
		$device_values .= "<option value=2 $selected>Networking</option>";

		$deviceDropdown = "<select name=device>" . $device_values . "</select>";
	
		PRINT "<form type=post method=$_SELF>";
		PRINT $deviceDropdown;
		PRINT "<input type=hidden name=pID value=$this->pID>";
		PRINT "<input type=submit value=" . _("Type") . ">";
		PRINT "</form>";
	}

	function selectDevice()
	{
		//TODO This is to be refactored when computers and networking
		// are integrated into devices.
		if($this->device == 1){
			$this->device_type="computers";
		}elseif($this->device == 2){
			$this->device_type="networking";
		}else{
			$this->device_type=$this->device;
		}
		$this->deviceSelect();		
		PRINT "<form type=post method=$this->URL>";
		PRINT $this->deviceDropdown;
		PRINT "<input type=hidden name=device value=$this->device>";
		PRINT "<input type=hidden name=pID value=$this->pID>";

		PRINT '<input type="hidden" name="devicetype" value="'. $this->device_type .'">';
		PRINT '<input type="hidden" name=action value="search">';

		PRINT "<input type=submit value=" . _("Device") . ">";
		PRINT "</form>";
	}

	function deviceSelect()
	{
		$query = "SELECT id,name FROM $this->device_type ORDER BY name ASC";
		$DB = Config::Database();
		$data = $DB->getAll($query);
		foreach($data as $device){
			if($this->device_name == $device['id']){
				$selected = "selected";
			}
			$this->device_values .= "<option value=" . $device['id'] . " " . $selected . ">" . $device['name'] . "</option>";
		}
		$this->deviceDropdown = "<select name=device_name>" . $this->device_values . "</select>";
	}
}
