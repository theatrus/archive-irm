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
#  dupiprep.php
#    Check the IP addresses of all systems (computers and network devices) 
#    for any duplciate IP addresses.
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
commonHeader(_("Duplicate IP Address") . " - " . _("Report"));
$DB = Config::Database();
$sort = $DB->getTextValue($sort);             # where "sort by" is passed
$addroct1 = $DB->getTextValue($addroct1);     # where "addroct1" is passed
$addroct2 = $DB->getTextValue($addroct2);     # where "addroct2" is passed
$addroct3 = $DB->getTextValue($addroct3);     # where "addroct3" is passed

if ($sort == "NULL") {$sort = 'IPSort';}
if ($addroct1 == "NULL") {$addroct1 = '192';}
if ($addroct2 == "NULL") {$addroct2 = '*';}
if ($addroct3 == "NULL") {$addroct3 = '*';}

$grpsort = str_replace("'", "", $sort);    # ged rid of the "'s around the rack name
$octet1 =  str_replace("'", "", $addroct1);
$octet2 =  str_replace("'", "", $addroct2);
$octet3 =  str_replace("'", "", $addroct3);

PRINT "This report checks the IP addresses of all computers and network devices.\n";
PRINT "<BR>&nbsp;&nbsp;&nbsp;Any duplicate IP addresses within the range specified are listed.\n";
PRINT "<P>&nbsp;&nbsp;&nbsp;The list below is sorted by: \n";
if  ($grpsort == "NameSort") {
  PRINT "<B><I>Name</I></B>\n";
}
if  ($grpsort == "IPSort") {
  PRINT "<B><I>IP Address</I></B>\n";
}
PRINT ", using <B><I>IP Mask = $octet1.$octet2.$octet3.*</I></B>\n";
PRINT "<P>\n";
#  provide the option to sort list again by Name or IP
?>

<form method="GET" action="
<?php PRINT Config::AbsLoc('users/reports/dupiprep.php');
?>">
   <input type="submit" value="Sort By: ">
   &nbsp;&nbsp; IP Masks, 
   <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Octet1: 
   <select name="addroct1">
      <option value="192">192</option>\n";
      <option value="172">172</option>\n";
      <option value="16">16</option>\n";
      <option value="10">10</option>\n";
      <option value="*">"*"</option>\n";
   </select>
   <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   Octet2:<input type="text" maxlength="5" name="addroct2" value="*">
   <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   Octet3:<input type="text" maxlength="5" name="addroct3" value="*">
   <BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(enter an integer or a "*", e.g. 192.168.100.*)
</form>

<?php
PRINT "&nbsp;&nbsp;&nbsp;(<I>This report will take about 15 seconds to generate each time. </I>)\n";
PRINT "<P>\n";

# get all the "systems" 
# create some additional columns as working values
$query1 = "(SELECT ID, name, comments, 'computer' as type FROM computers)
  UNION 
           (SELECT ID, name, comments, 'netdevice' as type FROM networking)
";

$systems = $DB->getAll($query1);
# systems -  query
# ID, name, type, comments
#       where type = 'computer', 'netdevice' or 'periph'

# ports[nextrow] - constructed array for sorting
# ID, name, sortname, type, portname, ifaddr, ipoct1, ipoct2, ipoct3, ipoct4, mac, logical_number, comment
#       where sortname = strtoupper($name);
#       where ipoct1 ... ipoct4 = derived form ifaddr

# For each of the systems
#  Get the appropriate IP information (creating a new "results matrix"
$nextrow=0;    #  initialize "row counter"
foreach ($systems as $result) {
    $sysid = $result["ID"];
    $name = $result["name"];
    $sortname = strtoupper($result["name"]);
    $systy = $result["type"];
    $comts = $result["comments"];

                # look up the ports on this sytem
                if ($systy == "computer") {      #port (device_)type=1, NIC
                  $query2 = "SELECT ID, name, logical_number, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid AND device_type=1 AND ifaddr !=\"\"
                           ORDER BY name";
                } elseif ($systy == "netdevice") {    #port (device_)type=2, Switch (network device)
                  $query2 = "SELECT ID, name, logical_number, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid AND device_type=2 AND logical_number=0 AND ifaddr !=\"\"
                           ORDER BY name";
                } else {   #port (device_)type= ????
                  $query2 = "SELECT ID, name, logical_number, device_on, device_type, iface, ifaddr, ifmac
                           FROM networking_ports WHERE device_on=$sysid AND ifaddr !=\"\"
                           ORDER BY name";
                };
                $nports = $DB->getAll($query2);

                foreach ($nports as $result1) {
                    $don = $result1["device_on"];
                    $dty = $result1["device_type"];
                    $npnam = $result1["name"];
                    $nplnum = $result1["logical_number"];
                    $npif = $result1["iface"];
                    $npifaddr = $result1["ifaddr"];
                    $npifmac = $result1["ifmac"];

                    # only build new row if IP is not empty (npifaddr !="") AND
                    #   network device port # is 0 (nplum ==0)

                            # create a new array-row for each system's port - if it meets that criteria
                            $ports[$nextrow]["ID"] = $sysid;
                            $ports[$nextrow]["name"] = $name;
                            $ports[$nextrow]["sortname"] = $sortname;
                            $ports[$nextrow]["comments"] = $comts;
                            $ports[$nextrow]["type"] = $systy;
                            $ports[$nextrow]["portname"] = $npnam;
                            $ports[$nextrow]["logical_number"] = $nplnum;
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

#PRINT "<p>Number of systems: <B><I>: $nextrow</I></B><P>\n";
$numports = 0;

# now we want to sort the matrix by "name" OR "IP address"
# we have an array of rows, but "array_multisort" wants an array of columns
#  so we obtain the data as columns, then do the sorting
foreach ($ports as $key => $row) {
    $ar_id[$key]  = $row['ID'];
    $ar_nam[$key]  = $row['name'];
    $ar_sortnam[$key]  = $row['sortname'];
    $ar_type[$key]  = $row['type'];
    $ar_portnam[$key]  = $row['portname'];
    $ar_lnum[$key] = $row["logical_number"];
    $ar_cmt[$key]  = $row['comments'];
    $ar_ip[$key]  = $row['ifaddr'];
    $ar_ip1[$key]  = $row['ipoct1'];
    $ar_ip2[$key]  = $row['ipoct2'];
    $ar_ip3[$key]  = $row['ipoct3'];
    $ar_ip4[$key]  = $row['ipoct4'];
    $ar_mac[$key]  = $row['mac'];
    $numports++;
}
#PRINT "<p>Number of ports: <B><I>: $numports</I></B><P>\n";

# now sort according to "sort type" selected (by name or IP)
if  ($grpsort == "NameSort") {
#PRINT "This Report is Sorted by <B><I>: $grpsort</I></B>\n";
array_multisort($ar_sortnam,SORT_ASC, $ar_nam,SORT_ASC, $ar_portnam,SORT_ASC, $ar_type,SORT_ASC,
  $ar_id,SORT_ASC, $ar_mac,SORT_ASC, $ar_cmt,SORT_ASC, $ar_lnum,SORT_ASC,
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
  $ar_id,SORT_ASC, $ar_type,SORT_ASC, $ar_mac, SORT_ASC, $ar_cmt,SORT_ASC, $ar_lnum,SORT_ASC,
  $ports);
}

# now - display the sorted, filled in matrix
# ports[nextrow] - constructed array for sorting
# ID, name, sortname, type, comments, logical_number, ifaddr, ipoct1, ipoct2, ipoct3, ipoct4, mac
#       where sortname = strtoupper($name);
#       where ipoct1 ... ipoct4 = derived form ifaddr
# now for the each of the ports ....

PRINT "<TABLE BORDER=1>";
PRINT "<TR>\n";
PRINT "<TD BGCOLOR=grey><B>System Name</B></TD>";
PRINT "<TD BGCOLOR=grey><B>Port Name</B></TD>";
PRINT "<TD BGCOLOR=grey><B>IP Address</B></TD>";
PRINT "<TD BGCOLOR=grey><B>MAC Address</B></TD>";
PRINT "<TD BGCOLOR=grey><B>Comments</B></TD>";
PRINT "</TR>\n";

$pindex = 0;
foreach ($ports as $result) {
    $pid = $result["ID"];
    $pnam = $result["name"];
    $typ = $result["type"];
    $prtnam = $result["portname"];
    $plnum =  $result["logical_number"];
    $pip = $result["ifaddr"];
    $pipoct1 = $result["ipoct1"];
    $pipoct2 = $result["ipoct2"];
    $pipoct3 = $result["ipoct3"];
    $pmac = $result["mac"];
    $pcom = $result["comments"];

    if ($pindex >0) {
     if ($p2ip == $pip) {

      if (($octet1 =="*") || ($octet1 == $pipoct1)) {
        if (($octet2 =="*") || ($octet2 == $pipoct2)) {
          if (($octet3 =="*") || ($octet3 == $pipoct3)) {

            # display at "matching-pair header row"
            PRINT "<TR>\n";
            PRINT "<TD BGCOLOR=red COLSPAN=5>\n";
            PRINT "&nbsp;&nbsp;&nbsp;<B>DUPLICATE PAIR below ....</B>\n";
            PRINT "</TD>\n";
            PRINT "</TR>\n";

            # print one of the duplicates  ---------------------------------------------
            PRINT "<TR>\n";
            PRINT "<TD><B>$p2nam</B> (";
            if ($typ2 == "computer") {
#                PRINT '<A HREF="'.Config::AbsLoc("users/computers-info.php?ID=$p2id").'">'; # 1.5.7 call
                PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
		     array('ID' => $p2id, 'devicetype'=>'computer', 'action'=>'info')   ).'">';
                PRINT "$p2id</A>)</TD>\n";
            }
            elseif ($typ2 == "netdevice") {
                PRINT '<A HREF="'.Config::AbsLoc("users/networking-info.php?ID=$p2id").'">';
                PRINT "$p2id</A>)</TD>\n";
            } else {
                PRINT "$pid)</TD>\n";
            }

        # display port name
        PRINT "<TD>$p2rtnam  ($plnum)</TD>";
        # display IP address
        PRINT "<TD>$p2ip</TD>";
        # display MAC address
        if ($pmac != "") {
            PRINT "<TD>$p2mac</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        # display Comments
        if ($pcom != "") {
            PRINT "<TD>$p2com</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        PRINT "</TR>\n";

            # print the other duplicate ---------------------------------------------
            PRINT "<TR>\n";
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

        # display port name
        PRINT "<TD>$prtnam  ($plnum)</TD>";
        # display IP address
        PRINT "<TD>$pip</TD>";
        # display MAC address
        if ($pmac != "") {
            PRINT "<TD>$pmac</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        # display Comments
        if ($pcom != "") {
            PRINT "<TD>$pcom</TD>";
        } else {
            PRINT "<TD>&nbsp;</TD>";
        }
        PRINT "</TR>\n";

      } # end-if octet3 ..
    } # end-if octet2 ..
  } # end-if octet1 ..

  } # end-if p2=p
  } # end-if index1
    $p2id = $pid;
    $typ2 = $typ;
    $p2ip = $pip;
    $p2nam = $pnam;
    $p2rtnam = $prtnam;
    $p2lnum =  $plnum;
    $p2mac = $pmac;
    $p2com = $pcom;
  $pindex++;

}   # end-foreach system in the rack

PRINT "</TABLE><BR>";

commonFooter();
?>

</body>
</html>
