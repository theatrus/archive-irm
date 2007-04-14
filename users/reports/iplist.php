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

commonHeader(_("IP List Report"));
__("All computers and network devices sorted by primary IP address.");
PRINT "\n<br />\n";

# 1. Get some number data

$DB = Config::Database();

$number_of_computers = $DB->getOne("SELECT COUNT(ID) FROM computers");
$number_of_netdevs = $DB->getOne("SELECT COUNT(ID) FROM networking");
$total_devs =  $number_of_netdevs + $number_of_computers;

# 2. Spew out the data in a table

PRINT '<table class="sortable" id="report">';
PRINT '<tr class="reportheader">';
PRINT "<th>"._("ID")."</th>\n";
PRINT "<th>"._("IP")."</th>\n";
PRINT "<th>"._("Name")."</th>\n";
PRINT "<th>"._("Contact")."</th>\n";
PRINT "<th>"._("Type")."</th>\n";
PRINT "<th>"._("MAC/Network Address")."</th>\n";
PRINT "</tr>\n";

# 3. Get some more number data (list of all devices sorted by IP)

$query = "(SELECT LPAD(ID,5,'0') AS ID,name,contact,ip,type,mac, 1 AS table_name FROM computers)
	UNION ALL
	(SELECT LPAD(ID,5,'0') AS ID,name,contact,ip,type,mac, 2 AS table_name FROM networking)
	ORDER BY
	CAST(SUBSTRING_INDEX(`ip`, '.', 1) AS UNSIGNED),
	CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`ip`, '.', -3), '.', 1) AS UNSIGNED),
	CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`ip`, '.', -2), '.', 1)AS UNSIGNED) ,
	CAST(SUBSTRING_INDEX(`ip`, '.', -1) AS UNSIGNED)";

$netdevslist = $DB->getAll($query);
	
foreach ($netdevslist as $result)
{
	$ID = $result["ID"];
	$name = $result["name"];
	$contact = $result["contact"];
	$type = $result["type"];
	$ip = $result["ip"];
	$mac = $result["mac"];
	$table_name = $result["table_name"];

	PRINT '<tr class="reportdetail">';
	
	if ($table_name == 1)
	{
	        PRINT '<td><a href="'.Config::AbsLoc("users/computers-index.php", 
		       array('ID' => $ID, 'devicetype'=>'computer', 'action'=>'info')   ).'">';
		PRINT "C$ID</a></td>\n";
	}
	else
	{
		PRINT '<td><a href="'.Config::AbsLoc("users/networking-info.php", array('ID' => $ID)).'">';
		PRINT "N$ID</a></td>\n";
	}

	PRINT "<td>$ip</td\n";
	PRINT "<td>$name</td>\n";
	PRINT "<td>$contact</td>\n";
	PRINT "<td>$type</td>\n";
	PRINT "<td>$mac</td>\n";
	PRINT "</tr>\n";
}

PRINT "</table>";

PRINT "<p>"._("Number of Computers:").' '.$number_of_computers."</p>\n";
PRINT "<p>"._("Number of Network Devices:").' '.$number_of_netdevs."</p>\n";
PRINT "<p>"._("Total Number of Devices:").' '.$total_devs."</p>\n";

