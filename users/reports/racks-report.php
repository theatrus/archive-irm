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
#  racks-report.php
#    List the systems in the "rack" specified in a pull-down menu of
#    racks in racks.php.  The system's (computer or network device)
#    name, IP and MAC addresses will be shown.
#
#  Author:
#  Bruce Luhrs
#  Andy McBride
#################################################################################
#                               CHANGELOG                                       #
#################################################################################
#  20-Mar-2006  BL  Migrate from IRM 1.5.7 to  IRM v1.5.8                       #
##################################################################################

require_once '../../include/irm.inc';
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");
commonHeader(_("Rack") . " - " . _("Contents Report"));
$DB = Config::Database();
$ID = $DB->getTextValue($ID);         # where "Rack name" is passed
$sort = $DB->getTextValue($sort);     # where "sort by" is passed
$empty= $DB->getTextValue($empty);    # where "show empty" is passed

$grpid = str_replace("'", "", $ID);   # ged rid of the "'s around the rack name
$grpsort = str_replace("'", "", $sort); 
$ckempty = str_replace("'", "", $empty); 

PRINT "<H3>Contents of Rack: <I><U>$grpid</U></I></H3>\n";
PRINT "This report lists the contents of a rack, sorted by: \n";

if  ($grpsort == "NameSort") {
  PRINT "<B><I>Name</I></B>\n";
  $grpsortkey = "byname";
}
if  ($grpsort == "IPSort") {
  PRINT "<B><I>IP Address</I></B>\n";
  $grpsortkey = "byip";
}

PRINT "<BR>&nbsp;&nbsp;&nbsp;Ports without assigned IP Addresses are being: \n";
if  ($ckempty == "NoShow") {
  PRINT "<B><I>Not Shown</I></B>\n";
} else {
  PRINT "<B><I>Shown</I></B>\n";
}
PRINT "<P>\n";
#  provide the option to sort list again by Name or IP
?>

<form method="GET" action="
<?php PRINT Config::AbsLoc('users/reports/racks-report.php');
?>">
   <input type="submit" value="Sort By: ">
   <select name="sort">
      <option value="NameSort">"Name"</option>\n";
      <option value="IPSort">"IP Address"</option>\n";
   </select>
   
   &nbsp; &nbsp; Null IP Addresses:
   <input type="radio" name="empty" value="NoShow" checked> Do not Show
   <input type="radio" name="empty" value="Show"> Show
<BR>
<input type="hidden" name="ID" value="<?php __("$grpid") ?>">
</form>

<?php
PRINT "<P>\n";
   PRINT '<A HREF="'.Config::AbsLoc("users/reports/racks.php").'">';
   PRINT "Racks-Groups Report Page</A>\n";
PRINT "<P>\n";

# get all the "systems" in this "rack" 
# create some additional columns as working values
$query1 = "(SELECT ID, name, 'computer' as type, location FROM computers
            WHERE location=$ID)
  UNION 
           (SELECT ID, name, 'netdevice' as type, location FROM networking
            WHERE location=$ID)
";
$systems = $DB->getAll($query1);


# systems - from location=$ID  query
# ID, name, type, location
#       where type = 'computer', 'netdevice' or 'periph'
 
# ports[nextrow] - constructed array for sorting
# ID, name, sortname, type, portname, ifaddr, ipoct1, ipoct2, ipoct3, ipoct4, mac
#       where sortname = strtoupper($name);
#       where ipoct1 ... ipoct4 = derived form ifaddr

# For each of the systems that are in the specified RACK
#  Get the appropriate IP information (creating a new "results matrix"
$nextrow=0;    #  initialize "row counter"
foreach ($systems as $result) {
    $sysid = $result["ID"];
    $name = $result["name"];
    $sortname = strtoupper($result["name"]);
    $systy = $result["type"];
    $grploc = $result["location"];

                # look up the ports on this sytem
                if ($systy == "computer") {      #port (device_)type=1, NIC
                  $query2 = "SELECT ID, name, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid AND device_type=1
                           ORDER BY name";
                } elseif ($systy == "netdevice") {    #port (device_)type=2, Switch
                  $query2 = "SELECT ID, name, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid AND device_type=2
                           ORDER BY name";
                } else {   #port (device_)type= ????
                  $query2 = "SELECT ID, name, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid ORDER BY name";
                };
                $nports = $DB->getAll($query2);

                foreach ($nports as $result1) {
                    $don = $result1["device_on"];
                    $dty = $result1["device_type"];
                    $npnam = $result1["name"];
                    $npif = $result1["iface"];
                    $npifaddr = $result1["ifaddr"];
                    $npifmac = $result1["ifmac"];

                    # create a new array-row for each system's port
                    $ports[$nextrow]["ID"] = $sysid;
                    $ports[$nextrow]["name"] = $name;
                    $ports[$nextrow]["sortname"] = $sortname;
                    $ports[$nextrow]["type"] = $systy;
                    $ports[$nextrow]["portname"] = $npnam;
                    $ports[$nextrow]["ifaddr"] = $npifaddr;
                    # $ipoct1 ... $ipoct4 -  IP Octet creations for sort purposes
                    # ASSUME - that "ip/ifaddr" field ONLY contains a "." if it's a REAL IP address
                    if (strpos($npifaddr,".")) {       # if there's a "." in the ip address ...
		       $dot1 = strpos($npifaddr,".");
		       $dot2 = strpos($npifaddr,".", $dot1+1);
		       $dot3 = strpos($npifaddr,".", $dot2+1);
		       $end = strpos($npifaddr," ", $dot3+1);
		       $ipoct1 = substr($npifaddr,0,$dot1);
		       $ipoct2 = substr($npifaddr,$dot1+1,($dot2-$dot1));
		       $ipoct3 = substr($npifaddr,$dot2+1,($dot3-$dot2));
		       $ipoct4 = substr($npifaddr,$dot3+1);
		       $ports[$nextrow]["ipoct1"] = $ipoct1;
		       $ports[$nextrow]["ipoct2"] = $ipoct2;
		       $ports[$nextrow]["ipoct3"] = $ipoct3;
		       $ports[$nextrow]["ipoct4"] = $ipoct4;
		  } else {
		       $ports[$nextrow]["ipoct1"] = "";
	       	       $ports[$nextrow]["ipoct2"] = "";
		       $ports[$nextrow]["ipoct3"] = "";
		       $ports[$nextrow]["ipoct4"] = "";
		  }
                  $ports[$nextrow]["mac"] = $npifmac;
                  $nextrow++;
               }  # end for-each ports on that system

}   # end of all the systems in a group

# now we want to sort the matrix by "name" OR "IP address"
# we have an array of rows, but "array_multisort" wants an array of columns
#  so we obtain the data as columns, then do the sorting
foreach ($ports as $key => $row) {
    $ar_id[$key]  = $row['ID'];
    $ar_nam[$key]  = $row['name'];
    $ar_sortnam[$key]  = $row['sortname'];
    $ar_type[$key]  = $row['type'];
    $ar_portnam[$key]  = $row['portname'];
    $ar_ip[$key]  = $row['ifaddr'];
    $ar_ip1[$key]  = $row['ipoct1'];
    $ar_ip2[$key]  = $row['ipoct2'];
    $ar_ip3[$key]  = $row['ipoct3'];
    $ar_ip4[$key]  = $row['ipoct4'];
    $ar_mac[$key]  = $row['mac'];
}

# now sort according to "sort type" selected (by name or IP)

if  ($grpsort == "NameSort") {
#PRINT "This Report is Sorted by <B><I>: $grpsort</I></B>\n";
array_multisort($ar_sortnam,SORT_ASC, $ar_nam,SORT_ASC, $ar_portnam,SORT_ASC, $ar_type,SORT_ASC,
  $ar_id,SORT_ASC, $ar_mac,SORT_ASC,
  $ar_ip1,SORT_NUMERIC,SORT_ASC,$ar_ip2,SORT_NUMERIC,SORT_ASC,
  $ar_ip3,SORT_NUMERIC,SORT_ASC,$ar_ip4,SORT_NUMERIC,SORT_ASC,
  $ar_ip,SORT_NUMERIC,SORT_ASC,
  $ports);
} else {
#PRINT "This Report is Sorted by <B><I>: $grpsort</I></B>\n";
array_multisort($ar_ip1,SORT_NUMERIC,SORT_ASC,$ar_ip2,SORT_NUMERIC,SORT_ASC,
  $ar_ip3,SORT_NUMERIC,SORT_ASC,$ar_ip4,SORT_NUMERIC,SORT_ASC,
  $ar_ip,SORT_NUMERIC,SORT_ASC,
  $ar_sortnam,SORT_ASC, $ar_nam,SORT_ASC, $ar_portnam,SORT_ASC, 
  $ar_id,SORT_ASC, $ar_type,SORT_ASC, $ar_mac, SORT_ASC,
  $ports);
}

# now - display the sorted, filled in matrix
PRINT "<TABLE BORDER=1>";
PRINT "<TR>\n";
PRINT "<TD><B>System Name</B></TD>";
PRINT "<TD><B>Port Name</B></TD>";
PRINT "<TD><B>IP Address</B></TD>";
PRINT "<TD><B>MAC Address</B></TD>";
PRINT "</TR>\n";

# ports[nextrow] - constructed array for sorting
# ID, name, sortname, type, ifaddr, ipoct1, ipoct2, ipoct3, ipoct4, mac
#       where sortname = strtoupper($name);
#       where ipoct1 ... ipoct4 = derived form ifaddr
# now for the each of the ports ....
$lastnam="";      # working variable to track change in system names as display the list
foreach ($ports as $result) {
    $pid = $result["ID"];
    $pnam = $result["name"];
    $typ = $result["type"];
    $prtnam = $result["portname"];
    $pip = $result["ifaddr"];
    $pmac = $result["mac"];

    # According to "SHOW-NOSHOW", only show those that have assigned IP addresses
    if (($ckempty == "Show") || ($pip !="")) {
        PRINT "<TR>\n";
        # display the system name - differently depending on "Sorted by ..."
        if  (($grpsort == "NameSort") && ($lastname != $pnam)) {
            PRINT "<TD><B>$pnam</B> (";
            if ($typ == "computer") {
#                PRINT '<A HREF="'.Config::AbsLoc("users/computers-info.php?ID=$pid").'">';
                PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
                     array('ID' => $pid, 'devicetype'=>'computer', 'action'=>'info')   ).'">';

                PRINT "$pid</A>)</TD>\n";
            }
            elseif ($typ == "netdevice") {
                PRINT '<A HREF="'.Config::AbsLoc("users/networking-info.php?ID=$pid").'">';
                PRINT "$pid</A>)</TD>\n";
            } else {
                PRINT "$pid)</TD>\n";
            }
            PRINT "<TD BGCOLOR=\"CCCCCC\">&nbsp;</TD>\n";
            PRINT "<TD BGCOLOR=\"CCCCCC\">&nbsp;</TD>\n";
            PRINT "<TD BGCOLOR=\"CCCCCC\">&nbsp;</TD>\n";
            PRINT "</TR><TR>\n";
            PRINT "<TD>&nbsp;&nbsp;<I>$pnam</I></TD>";
            $lastname = $pnam;
        } elseif ($grpsort == "IPSort") {
            PRINT "<TD><B>$pnam</B> (";
            if ($typ == "computer") {
#                PRINT '<A HREF="'.Config::AbsLoc("users/computers-info.php?ID=$pid").'">'; # 1.5.7 call
                PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
                     array('ID' => $pid, 'devicetype'=>'computer', 'action'=>'info')   ).'">';
                PRINT "$pid</A>)</TD>\n";
            }
            elseif ($typ == "netdevice") {
                PRINT '<A HREF="'.Config::AbsLoc("users/networking-info.php?ID=$pid").'">';
                PRINT "$pid</A>)</TD>\n";
            } else {
                PRINT "$pid)</TD>\n";
            }
        } else {
            PRINT "<TD>&nbsp;&nbsp;<I>$pnam</I></TD>";
        }
    
        # display port name
        PRINT "<TD>$prtnam</TD>";
        # display IP address
        if ($pip != "") {
            PRINT "<TD>$pip</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        # display MAC address
        if ($pmac != "") {
            PRINT "<TD>$pmac</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        PRINT "</TR>\n";
    } # end-if non-IP address    
}   # end-foreach system in the rack

PRINT "</TABLE><BR>";

commonFooter();
?>
</body>
</html>
