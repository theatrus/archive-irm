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
#  portmap-report.php
#    The port connections for the device specified in a 
#    pull-down menu of networking devices in portmap.php
#    will be shown.
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

commonHeader(_("Network: ") . " - " . _("Port-to-Device Mapping"));

$DB = Config::Database();
$ID = $DB->getTextValue($ID);

# Using the Network device "ID", get and display the name and details of the Device
# mysql> select * from networking where ID=$ID;
# +----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+---------------------+
#| ID | name   | type | ram  | ip | mac  | location | serial     | otherserial | contact | contact_num | datemod     | comments |
#+----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+---------------------+
#| 11 | r30sw1 | 5308 |      |    |      | Rack 030 | SG526JZ0G5 |             |         |             | 2005-10-27 09:03:52 |
#+----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+---------------------+

$query = "SELECT * FROM networking WHERE (ID = $ID)";
$result = $DB->getRow($query);
$name = $result["name"];
$type = $result["type"];
$serial = $result["serial"];
$location = $result["location"];
$id = $result["ID"];
PRINT "<TABLE BORDER=1 WIDTH=100%>";
PRINT "<TR>\n";
PRINT "<TD><B>Network Device</B></TD>";
PRINT "<TD><B>Type</B></TD>";
PRINT "<TD><B>Serial No.</B></TD>";
PRINT "<TD><B>Location</B></TD>";
PRINT "</TR>\n";

PRINT "<BR>\n";
PRINT "<TD>$name (ID= ";
PRINT '<A HREF="'.Config::AbsLoc("users/networking-index.php?action=info&devicetype=networking&ID=$id").'">';
PRINT "$id</A>)</TD>\n";
PRINT "<TD>$type</TD>";
if ($serial=="") {
    PRINT "<TD>&nbsp;</TD>\n";
} else {
    PRINT "<TD>$serial</TD>\n";
}
PRINT "<TD>$location</TD>\n";
PRINT "<BR>\n";

PRINT "</TABLE><BR>";
PRINT "<HR>\n";

# now look up the ports on that device in teh networking_ports table
# mysql> select * from networking_ports where device_on="$ID" AND device_type=2;
#+------+-----------+-------------+-------------+---------------------+-------+----------------+-----------------+
#| ID   | device_on | device_type | iface       | ifaddr              | ifmac | logical_number | name            |
#+------+-----------+-------------+-------------+---------------------+-------+----------------+-----------------+
#...($wireID)
#| 1121 |        11 |           2 | Ethernet    |                     |       |              1 | MOD A - Port 1  |
#| 1606 |        11 |           2 | Serial Port | 10.100.100.155 2004 |       |              0 | Console         |
#+------+-----------+-------------+-------------+---------------------+-------+----------------+-----------------+

$query = "SELECT * FROM networking_ports WHERE (device_on=$ID  AND device_type=2) ORDER BY logical_number";
$data = $DB->getAll($query);

# device_type= 1 is computer, 2 networking device
$numRows = count($data);
if ($numRows < 1)  {
   PRINT "Looks like a lonely device to me.  No ports found.<BR>\n";

} else {   # this isn't a device with no ports
    PRINT "<TABLE BORDER=1>";
    PRINT "<TR>\n";
    PRINT "<TD><B>Log-Num</B></TD>";
    PRINT "<TD><B>Port-Name</B></TD>";
    PRINT "<TD><B>Connected To</B></TD>";
    PRINT "<TD><B>On Device</B></TD>";
    PRINT "</TR>\n";

    foreach ($data as $result)   # for-each port on the networking device
    {
        $wireID = $result["ID"];
        $name = $result["name"];
        $logical_number = $result["logical_number"];
        $iface = $result["iface"];
        $ifaddr = $result["ifaddr"];
        $ifmac = $result["ifmac"];
        PRINT "<TR>\n";
        PRINT "<TD>$logical_number</TD>\n"; # logical number and port-name
        PRINT "<TD>$name</TD>\n"; 
  
        # For each of the networking_ports on that networking device, look up the 2 ends of the networking_wire
        # mysql> select * from networking_wire WHERE (end1 = $wireID OR end2 = $wireID);
        #+-----+------+------+
        #| ID  | end1 | end2 |
        #+-----+------+------+
        #| 260 | 1121 | 1251 |
        #+-----+------+------+

        $wquery = "SELECT * FROM networking_wire WHERE (end1=$wireID OR end2=$wireID)";
        $wire = $DB->getRow($wquery);
        if (count($wire) > 0) {
            $wID = $wire["ID"];
            $wend1 = $wire["end1"];
            $wend2 = $wire["end2"];
            if ($wend1 == $wireID) {
                $qend = $DB->getTextValue($wend2);
            } else {
                $qend = $DB->getTextValue($wend1);
            }
            $isconnected=1;
        } else {
            #... WIRE NOT CONNECTED on either end
            $isconnected=0;
        }

        # Use the ID of the device at the "end of the wire"  NOT connected to the Networking Device 
        # to see what is connected to this port on this networking device#
        # mysql> select * from networking_ports where ID=$end;
        #+------+-----------+-------------+----------+---------------+-------+----------------+-------+
        #| ID   | device_on | device_type | iface    | ifaddr        | ifmac | logical_number | name  |
        #+------+-----------+-------------+----------+---------------+-------+----------------+-------+
        #| 1251 |       244 |           1 | Ethernet | 172.16.110.65 |       |              2 | NIC 2 |
        #+------+-----------+-------------+----------+---------------+-------+----------------+-------+

        # IS this Port connected, if so - get details about the other end ...
        if ($isconnected ==1) {
            $pquery = "SELECT * FROM networking_ports WHERE (ID = $qend)";
            $farend = $DB->getRow($pquery);
            $pID = $farend["ID"];
            $pName = $farend["name"];
            $pNum = $farend["logical_number"];
            $pOn = $farend["device_on"];
            $pType = $farend["device_type"];
            $qOn = $DB->getTextValue($pOn);

            # for device_type= 1 is computer (query computers), 2 networking device (query networking)
            # Finally look up computer that the connected networking port is on
            #
            # mysql> select * from computers where name="USRAL1";
            # +-----+--------+-------+--------------+---------------+-------+-------+-------------------+-----------------+----------+
            # | ID  | name   | type  | flags_server | flags_surplus | os    | osver | processor         | processor_speed | location |
            # +-----+--------+-------+--------------+---------------+-----------------+-------+-------------------+-----------------+
            # | 244 | USRAL1 | DL360 |            0 |      0 | Linux (RHE) 3.0 |    | Intel Pentium III |                | Rack 030 | ..
            # +-----+--------+-------+--------------+---------------+-----------------+-------+-------------------+-----------------+
            # 
            #mysql> select * from networking where ID=11;
            #+----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+----------
            #| ID | name   | type | ram  | ip | mac  | location | serial     | otherserial | contact | contact_num | datemod | comments
            #+----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+----------
            #| 11 | r30sw1 | 5308 |      |    |      | Rack 030 | SG526JZ0G5 |             |         |        | 2005-10-27 09:03:52 |
            #+----+--------+------+------+----+------+----------+------------+-------------+---------+-------------+----------
            #
            if ($pType == 1) {
                $nquery = "SELECT ID,name FROM computers WHERE (ID = $qOn)";
            } else {  # it must be a "2":   if ($pType == 2)
                $nquery = "SELECT ID,name FROM networking WHERE (ID = $qOn)";
            }
            $nresult = $DB->getRow($nquery);
            $nname = $nresult["name"];
            $nID = $nresult["ID"];
            PRINT "<TD>$pName</TD>";
            PRINT "<TD><B>$nname</B> (";
            if ($pType==1) {
#                PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php?action=info&devicetype=computers&ID=$nID").'">';  # was 1.5.7 call
                PRINT '<A HREF="'.Config::AbsLoc("computers-index.php",
                       array('ID' => $nID, 'devicetype'=>'computer', 'action'=>'info')   ).'">';
            } else {
                PRINT '<A HREF="'.Config::AbsLoc("users/networking-index.php?action=info&devicetype=networking&ID=$nID").'">';
            }
            PRINT "$nID</A>)</TD>\n";

        # else Port is Not Connected
        } else {  
            PRINT "<TD>&nbsp;</TD>";
            PRINT "<TD>NOT-CONNECTED</TD>";
        }
        PRINT "</TR>\n";

    }   # end of foreach port
    PRINT "</TABLE><BR>";
    PRINT "<HR>\n";
}  # end of not a device with NO ports

commonFooter();
?>
</body>
</html>
