<?php
#    IRM - The Information Resource Manager
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
#################################################################################


require_once '../../include/irm.inc';
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");

commonHeader(_("Location List Report"));
__("All devices sorted by location.");
PRINT "\n<br />\n";

$DB = Config::Database();
$total_locations = $DB->getOne('SELECT COUNT(value) FROM lookup_data WHERE `lookup`=\'locations\'');
$locations = $DB->getAll('SELECT * FROM lookup_data WHERE `lookup`=\'locations\' ORDER BY `value` ASC');
$devicetypes = $DB->getAll("SELECT * FROM devices");

function getDevices($location,$devicetype)
{
	$DB = Config::Database();
	if ($devicetype == "computers" || $devicetype == "networking")
	{
		$query = ("SELECT name, location FROM $devicetype ORDER BY name ASC");
	} else {
		$query = ("SELECT name, locations FROM $devicetype ORDER BY name ASC");
	}
	
	$devicesAtLocation = $DB->getAll($query);
	foreach ($devicesAtLocation as $device)
	{
		if ($device['location'] == $location || $device['locations'] == $location)
		{
			PRINT "$devicetype : " .  $device['name'] . "<br>";
		}
	}

}

PRINT "<p>"._("Total Number of Locations:").' '.$total_locations."</p>\n";

foreach($locations as $location)
{
	PRINT "<h3>" . $location['value'] . "</h3>";
	foreach ($devicetypes as $devicetype)
	{
		getDevices($location['value'],$devicetype['name']);
	}
	getDevices($location['value'],"computers");
	getDevices($location['value'],"networking");
}
