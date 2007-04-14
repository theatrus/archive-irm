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

commonHeader(_("Inventory"));
__("Welcome to the IRM Inventory utility!  This is where you access your inventoried items");

$userbase = Config::AbsLoc('users');
$device = new Device();

$inventoryTypes = array(_("Software")	=>	'software',
			_("Files")	=>	'files',
			_("Computers")	=>	'computers',
			_("Networking")	=>	'networking'
			);
PRINT "<hr />";
PRINT "<table>\n";
foreach ($inventoryTypes as $key => $value)
{
	PRINT '<tr class="devicedetail">';
	PRINT "<td><a href=\"$userbase/$value-index.php\">" .$key . "</a></td>";
	//TODO The following points to a device list, which then links to the
	//device display for each device type. These ID links are incorrect for computers and networking.
	PRINT "<td><a href=device-index.php?action=list&device=$value>List all $value</a></td>";
	PRINT "<td><a href=device-index.php?action=csv&device=$value>Export $value to CSV</a></td>";
	PRINT "</tr>\n";
}
$device->getDevices();
$device->printDeviceList();
PRINT "</table>";

PRINT "<a href=ocs-index.php>OCS Inventory Information</a>";
commonFooter();
