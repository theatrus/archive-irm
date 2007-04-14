<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 1999 Yann Ramin
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

require_once '../include/irm.inc';
require_once 'include/i18n.php';
	
AuthCheck("normal");


switch($_REQUEST['action'])
{
	case "csv":
		$device = new Device();
		$deviceType = $_REQUEST['device'];
		$device->setDeviceType($deviceType);
		$device->CsvDevice();
		break;
	case "list":
		$device = new Device();
		$deviceType = $_REQUEST['device'];
		$device->setDeviceType($deviceType);
		commonHeader($deviceType);
		$device->ListAllDevices();
		commonFooter();
		break;
	default:
		$device = new Device();
		$deviceType = $_GET['device']; 
		$device->setDeviceType($deviceType);
		$device->getDeviceFields();

		commonHeader($deviceType);
		__("Welcome to the IRM $deviceType Tracking utility!  This is where you store information about the various $deviceType scattered about your organization. Below are tools in which you can view your $deviceType, as well as edit and add entries.");

		deviceSearch($deviceType,$device->deviceFields);
		commonFooter();
}
