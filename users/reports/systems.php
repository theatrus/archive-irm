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
#  systems.php
#    list number of computers and network devices by type
#    
#  Author:
#  Bruce Luhrs
#  Andy McBride
#################################################################################
#                               CHANGELOG                                       #
#################################################################################
#  20-Mar-2006  BL  Migrate from IRM 1.5.7 to  IRM v1.5.8                       #
#################################################################################

require_once '../../include/irm.inc';
require_once '../../include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");
PRINT "<html><body bgcolor=#ffffff>";

	commonHeader(_("System Breakdown Report (Numbers by Type)"));
        __("This report will list the number of Computers and
           Network Devices by Type.
          ");
PRINT "\n<br /><br /><br />\n";

$DB = Config::Database();

# Show Major (Page) TABLE HEADER
    PRINT "<table border=0 width=100%>";
    PRINT "<tr><td valign=top>";

# 1. Get COMPUTER DATA
	$query = "SELECT ID FROM computers";
	$computers = $DB->getCol($query);
	$number_of_computers = count($computers);

	# 2. Show the TABLE HEADERs
	PRINT "<table border=1>";
	PRINT "<tr><td colspan=2>";
	PRINT "<H3>Computer Systems</H3></td></tr>";
	PRINT "<tr><td>";
	__("&nbsp;&nbsp;<I>Number of Computers</I>:");
	PRINT "</td><td>$number_of_computers</td></tr>";	

	PRINT "<tr><td colspan=2><b>";
	__("Systems by type");
	PRINT ":</b></td></tr>";

        # 3. Get the COUNTS for each Computer by Type
	$query = 'SELECT * FROM `lookup_data` WHERE `lookup`=\'type\' ORDER BY value';

	$typelist = $DB->getAll($query);
	foreach ($typelist as $result)
	{
		$type = $DB->getTextValue($result["name"]);
		$query = "SELECT COUNT(ID) FROM computers WHERE (type = $type)";
		$typecount = $DB->getOne($query);
                if ($typecount !=0 ) {
		    PRINT "<tr><td>$type</td><td>$typecount</td></tr>";
                }
	}
	PRINT "</table>";

# next page table cell
    PRINT "</td><td valign=top>";

# 1. Get NETWORK DATA
	$query = "SELECT ID FROM networking";
	$networks = $DB->getCol($query);
	$number_of_networks = count($networks);

	# 2. Show the TABLE HEADERs
	PRINT "<table border=1>";
	PRINT "<tr><td colspan=2>";
	PRINT "<H3>Network Devices</H3></td></tr>";
	PRINT "<tr><td>";
	__("&nbsp;&nbsp;<I>Number of Network Devices</I>:");
	PRINT "</td><td>$number_of_networks</td></tr>";	

	PRINT "<tr><td colspan=2><b>";
	__("Network Devices by type");
	PRINT ":</b></td></tr>";

        # 3. Get the COUNTS for each NETWORK DEVICES by Type
	$query = 'SELECT * FROM `lookup_data` WHERE `lookup`=\'type\' ORDER BY value';
	$typelist = $DB->getAll($query);
	foreach ($typelist as $result)
	{
		$type = $DB->getTextValue($result["name"]);
		$query = "SELECT COUNT(ID) FROM networking WHERE (type = $type)";
		$typecount = $DB->getOne($query);
                if ($typecount !=0 ) {
  		    PRINT "<tr><td>$type</td><td>$typecount</td></tr>";
                }
	}
	PRINT "</table>";

# end of "Major" (page) table
   PRINT "</td></tr>";
   PRINT "</table>";

# Get some "additional information"
########################################################################
#  Setup query to get some data from "computers" and "networking_ports"
# result             computers                   networking_ports
# --------------    ------------------         ------------------------
# ID =                 ID                           device_on         <--- Comp-ID, OR ID of Comp or Netdev net-port is on
# name =               name                         device_on
# device_type =        999                          device_type       <--- 1= NIC,  2 = Switch, 999 = computer
# table_name =         1                            2                 <--- 1= computer, 2 network-device


#setup some variables to count computers, computer ports and network ports
$numCC = 0;
$numCP = 0;
$numNP = 0;

$query = "(SELECT  ID, name, '999' as device_type, 1 AS table_name FROM computers)
        UNION ALL
     (SELECT device_on AS ID, device_on AS name, device_type,  2 AS table_name FROM networking_ports)";
# get a list of all computer, networking  names & IDs
$netdevslist = $DB->getAll($query);

foreach ($netdevslist as $result)  {
    $ID = $result["ID"];
    $name = $result["name"];
    $table_name = $result["table_name"];
    $device_type = $result["device_type"];

    if ($table_name ==2) {  # it's not a computer
        if ($device_type == 1) {  #it's a port on a computer
            $numCP++;
        } else { # it's a port on a networking device
            $numNP++;
        }
    } else {   # it's a computer (table_name = 1)
        $numCC++;
    }
}

PRINT "<p><U>Additional Details</U><BR>\n";
PRINT "Number of Computer Ports: $numCP<BR>\n";
PRINT "Number of Network Ports: $numNP<BR>\n";

   PRINT "<H3>&nbsp;</H3>";

commonFooter();
PRINT "</body></html>";


?>
