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
#################################################################################
#  iplist2.php
#    list computer and network ID, name, IP, MAC addresses and Ports
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
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");
PRINT "<html><body bgcolor=#ffffff>";
global $bgcl, $bgcd;

	commonHeader(_("System IP Matrix Report (Sorted by IP Address)"));
        __("This report will list all of your Computers and associated IP addresses.
            The ID field is encoded such that a CC-ID indicates a Computer and
            CP-ID indicates a Computer Port.
          ");

PRINT "\n<br /><br /><br />\n";

$DB = Config::Database();

# Get some number data
$number_of_computers = $DB->getOne("SELECT COUNT(ID) FROM computers");
$number_of_netdevs = $DB->getOne("SELECT COUNT(ID) FROM networking");
$total_devs =  $number_of_netdevs + $number_of_computers;

#setup some variables to count computers, computer ports and network ports
$numCC = 0;
$numCP = 0;
$numNP = 0;

########################################################################
#  Setup query to get some data:
# result             computers                   networking_ports
# --------------    ------------------         ------------------------
# ID =                 ID                           device_on         <--- Comp-ID, OR ID of Comp or Netdev net-port is on
# name =               name                         device_on
# sortname =           name                         device_on         <--- uppercase copy of name for sorting
# device_type =        999                          device_type       <--- 1= NIC,  2 = Switch, 999 = computer
# ip =                 ip                           ifaddr
#   ipoct1             ip                           ifaddr          <--- temporary fields to sort on
#   ipoct2             ip                           ifaddr          <--- temporary fields to sort on
#   ipoct3             ip                           ifaddr          <--- temporary fields to sort on
#   ipoct4             ip                           ifaddr          <--- temporary fields to sort on
# mac                  mac                          ifmac
# portname =           '          '                 name
# comments =           comments                     ID - ID
# table_name =         1                            2                 <--- 1= computer, 2 network-device

$query = "(SELECT  ID, name, name AS sortname, '999' as device_type, 
          ip, ip AS ipoct1,ip AS ipoct2,ip AS ipoct3,ip AS ipoct4, 
          mac, '           ' AS portname, comments, 1 AS table_name FROM computers)
	UNION ALL
     (SELECT device_on AS ID, device_on AS name, device_on AS sortname, device_type,
          ifaddr AS ip, ifaddr AS ipoct1,ifaddr AS ipoct2,ifaddr AS ipoct3,ifaddr AS ipoct4,
          ifmac as mac,  name AS portname, ID AS comments, 2 AS table_name FROM networking_ports)";

# get a list of all computer, networking  names & IDs 
$netdevslist = $DB->getAll($query);

# get a list of all computer, networking  names (indexed by ID)
# so we can print the name when listing a port
# -------------------------------------------------------------
# compIDlist[$ID] = $name  <-- query (ID, name FROM computers)
#  netIDlist[$ID] = $name  <-- query (ID, name FROM networking)
#
$compIDlist = array();
    $query = "SELECT ID, name FROM computers";
    $tmp_compIDlist = $DB->getAll($query);
    foreach ($tmp_compIDlist  as $acomputer) {
	$ID = $acomputer["ID"];
	$compIDlist[$ID] = $acomputer["name"];
    }
$netIDlist = array();
    $query = "SELECT ID, name FROM networking";
    $tmp_netIDlist = $DB->getAll($query);
    foreach ($tmp_netIDlist  as $anetdev) {
        $ID = $anetdev["ID"];
        $netIDlist[$ID] = $anetdev["name"];
    }

# now work with the combined computers and networking_ports matrix	
$i =0;    # temporary index into list
foreach ($netdevslist as $result)
{
    $ID = $result["ID"];
    $name = $result["name"];
    $sortname = $result["sortname"];
    $portname = $result ["portname"];
    $ip = $result["ip"];
      # $ipoct1 ... $ipoct4 also present with "IP in it", temporary creations for sort purposes
      # ASSUME - that "ip/ifaddr" field ONLY contains a "." if it's a REAL IP address
      if (strpos($ip,".")) {     # if "ip" contains a REAL IP address, break it up for sorting
          $dot1 = strpos($ip,".");
          $dot2 = strpos($ip,".", $dot1+1);
          $dot3 = strpos($ip,".", $dot2+1);
          $end = strpos($ip," ", $dot3+1);
          $ipoct1 = substr($ip,0,$dot1);
          $ipoct2 = substr($ip,$dot1+1,($dot2-$dot1));
          $ipoct3 = substr($ip,$dot2+1,($dot3-$dot2));
          $ipoct4 = substr($ip,$dot3+1);
           #temp-debug   print " ip: $ip  ($ipoct1-$dot1, $ipoct2-$dot2, $ipoct3-$dot3, $ipoct4-$end)<BR>\n";
          $netdevslist[$i]["ipoct1"] = $ipoct1;
          $netdevslist[$i]["ipoct2"] = $ipoct2;
          $netdevslist[$i]["ipoct3"] = $ipoct3;
          $netdevslist[$i]["ipoct4"] = $ipoct4;
      } 
    $mac = $result["mac"];
    $comments = $result["comments"];
    $table_name = $result["table_name"];
    $device_type = $result["device_type"];

    if ($table_name ==2) {  # it's not a computer
	if ($device_type == 1) {  #it's a port on a computer - use its computer name
	    $netdevslist[$i]["name"] = $compIDlist[$name];
	    $netdevslist[$i]["sortname"] = strtoupper($compIDlist[$name]);
	} else { # it's a port on a networking device - use its port name
	    $netdevslist[$i]["name"] = $netIDlist[$name];
	    $netdevslist[$i]["sortname"] = strtoupper($netIDlist[$name]);
        }
    } else {   # it's a computer (table_name = 1)
       $cid = $compIDlist[$name];
       $cidid = $compIDlist[$ID]; 
       $netdevslist[$i]["name"] = $compIDlist[$ID];
       $netdevslist[$i]["sortname"] = strtoupper($compIDlist[$ID]);
   }
    $i++;
}
# end for-each

# now we want to sort the matrix by "name"
# we have an array of rows, but "array_multisort" wants an array of columns
#  so we obtain the data as columns, then do the sorting

foreach ($netdevslist as $key => $row) {
   $ar_id[$key]  = $row['ID'];
   $ar_n[$key]  = $row['name'];
   $ar_sn[$key] = $row["sortname"];
   $ar_pn[$key]  = $row['portname'];
   $ar_ip[$key]  = $row['ip'];
     $ar_ip1[$key]  = $row['ipoct1'];
     $ar_ip2[$key]  = $row['ipoct2'];
     $ar_ip3[$key]  = $row['ipoct3'];
     $ar_ip4[$key]  = $row['ipoct4'];
   $ar_mac[$key]  = $row['mac'];
   $ar_cm[$key]  = $row['comments'];
   $ar_tn[$key]  = $row['table_name'];
   $ar_dt[$key]  = $row['device_type'];
}

# SORT by Ascending 'name', name value is sorted as SORT_STRING (items compared as strings)
array_multisort($ar_ip1,SORT_NUMERIC,SORT_ASC,$ar_ip2,SORT_NUMERIC,SORT_ASC,$ar_ip3,SORT_NUMERIC,SORT_ASC,
     $ar_ip4,SORT_NUMERIC,SORT_ASC,   $ar_ip, SORT_ASC, $ar_mac, SORT_ASC, 
     $ar_sn, SORT_ASC, SORT_STRING, $ar_n, SORT_ASC, $ar_id, SORT_ASC, $ar_pn, SORT_ASC, 
     $ar_cm, SORT_ASC, $ar_tn, SORT_ASC, $ar_dt, SORT_ASC, $netdevslist);

# Data is ready in the matrix now - go ahead and print ...
# Print table headers
PRINT "<TABLE BORDER=1 WIDTH=100%><tr $bgcd>";
PRINT "<th>"._("ID")."</th>";
PRINT "<th>"._("System Name")."</th>";
PRINT "<th>"._("IP Address")."</th>";
PRINT "<th>"._("MAC Address")."</th>";
PRINT "<th>"._("Port Name")."</th>";
PRINT "<th>"._("Comments")."</th>";

$numCC = 0;
$numCP = 0;
$numNP = 0;
foreach ($netdevslist as $result)
   {
    $ID = $result["ID"];
    $IDpad = str_pad($ID, 5, "0", STR_PAD_LEFT);
    $name = $result["name"];
    $sortname = $result["sortname"];
    $portname = $result ["portname"];
    $ip = $result["ip"];
    $mac = $result["mac"];
    $comments = $result["comments"];
    $table_name = $result["table_name"];
    $device_type = $result["device_type"];
		if ($table_name == 1)  # it's a computer
		{
                                PRINT '<TR BGCOLOR=#DDDDD0><TD>';
#			PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php?action=info&ID=$ID").'">';  # was 1.5.7 call
			PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
			     array('ID' => $ID, 'devicetype'=>'computer', 'action'=>'info')   ).'">';

			PRINT "CC$IDpad</A></TD>";
                         	PRINT "<TD>$name</TD>";
                                if ($ip != "") {
                         	   PRINT "<TD>$ip</TD>";
                                } else {
                         	   PRINT "<TD>&nbsp;</TD>";
                                }
                                if ($mac != "") {
                         	   PRINT "<TD>$mac</TD>";
                                } else {
                         	   PRINT "<TD>&nbsp;</TD>";
                                }
                                if ($portname != "") {
                         	   PRINT "<TD>$portname</TD>";
                                } else {
                         	   PRINT "<TD>&nbsp;</TD>";
                                }
                         	PRINT "<TD>$comments</TD>";
                         	PRINT "</TR>";
                         $numCC++;
		} 
		else #it's a port
		{
	                if ($device_type == 1)  #it's a port on a computer
        	        {
                                PRINT '<TR BGCOLOR=#DDDDD0><TD>';

			        PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
			             array('ID' => $ID, 'devicetype'=>'computer', 'action'=>'info')   ).'">';

                		PRINT "CP$IDpad</A></TD>";

                         	PRINT "<TD>$name</TD>";
                                if ($ip != "") {
                         	   PRINT "<TD>$ip</TD>";
                                } else {
                         	   PRINT "<TD>&nbsp;</TD>";
                                }
                                if ($mac != "") {
                         	   PRINT "<TD>$mac</TD>";
                                } else {
                         	   PRINT "<TD>&nbsp;</TD>";
                                }
                         	PRINT "<TD>$portname</TD>";
                         	PRINT "<TD>$comments</TD>";
                         	PRINT "</TR>";
                         $numCP++;
			}
			else # it's a port on a networking device - we decide not to show
                        {
#                                PRINT '<A HREF="'.Config::AbsLoc("users/networking-index.php?action=info&devicetype=networking&ID=$ID").'">';
#                                PRINT "NP$ID</A></TD>";
                         $numNP++;
                        }
		}
   }

PRINT "</table>";

PRINT "<p><U>Additional Details</U><BR>\n";
PRINT "Total Number of Devices:".' '.$total_devs."<BR>\n";
PRINT "Number of Computers: $numCC<BR>\n";
PRINT "Number of Network Devices:".' '.$number_of_netdevs."<BR>\n";
PRINT "Number of Computer Ports: $numCP<BR>\n";
PRINT "Number of Network Ports: $numNP<BR>\n";

PRINT "</body></html>";
commonFooter();
?>
