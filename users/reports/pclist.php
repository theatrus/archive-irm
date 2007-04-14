<?php
#    IRM - The Information Resource Manager
#
#    IP List Report Module
#    Copyright (C) 2005 Eran Gilon
#    Based on the Default Report included in IRM version 1.5.3
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
#				CHANGELOG				        #
#################################################################################
#										#
#  2004/02/08 - Eran Gilon first version					#
#		This report prints a list of all devices (both network and -	#
#		Computers sorted by their IP addresses				#
#                                                                               #
#  2005/03/20 - Eran Gilon updated for irm version 1.5 (new DB access)		#
#                                                                               #
#  2005/03/28 - Eran Gilon reformatted the table to look more like the other    #
#		IRM reports.							#
#                                                                               #
#  2006/03/20 - Bruce Luhrs fix call to computers-info.php, migrate to          #
#               call to computers-index.php for release 1.5.8.                  #
#                                                                               #
#################################################################################

#$allow_export = true;

include("../../include/irm.inc");
include("../../include/reports.inc.php");
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("normal");
PRINT "<html><body bgcolor=#ffffff>";
#if ($go == "yes")
if (1) 
{
	global $bgcl, $bgcd;

	commonHeader(_("PC List Report"));
        __("IP List Report: This report will list details of all of your Computers sorted by their IP address.");
        PRINT "\n<BR><BR><BR>\n";

	# 1. Get some number data

	$query = "SELECT ID FROM computers";

	$DB = Config::Database();

	$computers = $DB->getCol($query);
	$number_of_computers = count($computers);

	# 3. Get some more number data (list of all devices sorted by IP)

	$query = "(SELECT LPAD(ID,5,'0') AS ID, name, contact, ip, type, os, osver,
		 processor, processor_speed, ram, hdspace, 
		 serial, otherserial, mac, comments FROM computers)
                ORDER BY
                CAST(SUBSTRING_INDEX(`ip`, '.', 1) AS UNSIGNED),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`ip`, '.', -3), '.', 1) AS UNSIGNED),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`ip`, '.', -2), '.', 1)AS UNSIGNED) ,
                CAST(SUBSTRING_INDEX(`ip`, '.', -1) AS UNSIGNED)";

	$netdevslist = $DB->getAll($query);
	
	#Print table header
	PRINT "<TABLE><tr $bgcd>";

        PRINT "<th>"._("ID")."</th>";
        PRINT "<th>"._("IP")."</th>";
	PRINT "<th>"._("Name")."</th>";
	PRINT "<th>"._("Contact")."</th>";
	PRINT "<th>"._("Type")."</th>";
        PRINT "<th>"._("OS")."</th>";
        PRINT "<th>"._("OS Version")."</th>";
        PRINT "<th>"._("Processor")."</th>";
        PRINT "<th>"._("Processor Speed")."</th>";
        PRINT "<th>"._("RAM")."</th>";
        PRINT "<th>"._("HD")."</th>";
        PRINT "<th>"._("SN")."</th>";
        PRINT "<th>"._("SN2")."</th>";
	PRINT "<th>"._("MAC/Network Address");
        PRINT "<th>"._("Comments")."</th>";
	PRINT "</TR>";

	if ($allow_export)
	{
		#Save the headers in a variable for export
		$csv_output = "ID,IP,Name,Contact,Type,OS,OS Version,Processor,Processor Speed,RAM,HD,SN,SN2,MAC,Comments";
		$csv_output .= "\n";

		$filename = '/tmp/pclist.csv';
		$rootpath = '/var/www/irm';

		if (!$fp = @fopen($rootpath.$filename, "w")) {
		echo "Cannot open file ($rootpath.$filename)";
		exit;
		}

		if (fwrite($fp, $csv_output) === FALSE) {
		echo "Cannot write to file ($rootpath.$filename)";
		exit;
		}
	}

	foreach ($netdevslist as $result)
	{
                $ID = $result["ID"];
                $name = $result["name"];
		$contact = $result["contact"];
                $type = $result["type"];
                $ip = $result["ip"];
                $os = $result["os"];
                $osver = $result["osver"];
                $processor = $result["processor"];
                $processor_speed = $result["processor_speed"];
                $ram = $result["ram"];
                $hdspace = $result["hdspace"];
                $serial = $result["serial"];
                $otherserial = $result["otherserial"];
                $mac = $result["mac"];
		$comments = $result["comments"];

	    	PRINT '<TR BGCOLOR=#DDDDDD><TD>';
#		PRINT '<A HREF="'.Config::AbsLoc("users/computers-info.php?ID=$ID").'">';  # call for 1.5.7
		PRINT '<A HREF="'.Config::AbsLoc("users/computers-index.php",
                       array('ID' => $ID, 'devicetype'=>'computer', 'action'=>'info')   ).'">';
		PRINT "C$ID</A></TD>";
	      	PRINT "<TD>$ip</TD>";
                PRINT "<TD>$name</TD>";
		PRINT "<TD>$contact</TD>";
                PRINT "<TD>$type</TD>";
                PRINT "<TD>$os</TD>";
                PRINT "<TD>$osver</TD>";
                PRINT "<TD>$processor</TD>";
                PRINT "<TD>$processor_speed</TD>";
                PRINT "<TD>$ram</TD>";
                PRINT "<TD>$hdspace</TD>";
                PRINT "<TD>$serial</TD>";
                PRINT "<TD>$otherserial</TD>";
                PRINT "<TD>$mac</TD>";
		PRINT "<TD>$comments</TD>";
    		PRINT "</TR>";

	        if ($allow_export)
		{
			#save the row for export
			$csv_output = "$ID,$ip,$name,$contact,$type,$os,$osver,$processor,$processor_speed";
			$csv_output .= ",$ram,$hdspace,$serial,$otherserial,$mac,$comments";
			$csv_output .= "\n";

        	        if (fwrite($fp, $csv_output) === FALSE) {
			echo "Cannot write to file ($rootpath.$filename)";
			exit;
			}
                }
        }
	PRINT "</table>";

	PRINT "<br><tr><td><b>"._("Number of Computers: ")."</td><td>$number_of_computers</b></td></tr></br>\n";
        if ($allow_export)
        {
                if (!fclose($fp)) {
                echo "Cannot close file ($rootpath.$filename)";
                exit;
                }

                PRINT '<A HREF="'.$filename.'">';
                PRINT "Download this report as a CSV File</A></TD>";
        }

}
else 
{
	commonHeader(_("Reports") . " - " . _("Default Report"));
	__("Welcome to the Default Report!  This report is designed to be a
	    functional model of a real IRM Report.  It provides some simple
	    data, but could really be extended with graphics, percentages,
	    graphs, and user settable options.  But it serves as a good
	    jumping point for making your own report. (NOTE: The IRM header
	    is not necessary, I just put it in.  You must do a 'connectDB();'
	    though.)");
	echo '<p>'._("To generate the report, click on this button:");
?>
 
	<form action="<?php echo Config::AbsLoc('users/reports/default.php'); ?>">
	  <input type=submit value=Go>
	  <input type=hidden name=go value=yes>
	</form>
<?php
}
PRINT "</body></html>";
?>
