<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 2000 Yann Ramin
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
require_once 'lib/Config.php';
require_once 'include/i18n.php';
require_once 'lib/Net_SNMP.php';

AuthCheck("normal");

commonHeader(_("SNMP Status"));

$DB = Config::Database();
$qID = $DB->getTextValue($_GET['ID']);
$devicetype = ($_GET['device']);

$query = "SELECT * FROM $devicetype WHERE ID = $qID";
$result = $DB->getRow($query);
$ip = $result["ip"];
$name = $result["name"];
$id=$_GET['ID'];

$snmp = new Net_SNMP($ip);
$hstatus = $snmp->SNMPHTMLping();
$uptime = $snmp->snmpget("system.sysUpTime.0");

$userbase = Config::AbsLoc('users');

PRINT "<table>\n";
PRINT "<tr>";
PRINT "<th>"._("Name")."</th>";
PRINT "<th>"._("Status")."</th>";
PRINT "<th>" . _("Uptime*") . "</th>";
PRINT "<th>" . _("IP") . "</th>";
PRINT "</tr>";

PRINT "<tr>";
PRINT "<td>$name ($id)</td>";
PRINT "<td>$hstatus</td>";
PRINT "<td>".$uptime["Value"]."</td>";
PRINT "<td>$ip</td>";
PRINT "</tr>";

PRINT "</table>";

PRINT "<p><h3>" . _("Browse MIBS") . "</h3>";
PRINT "<a href=\"$userbase/snmp-browse.php?browse=system&ID=$id&device=$device\">";
__("System");
PRINT "</a><br>";
PRINT "<a href=\"$userbase/snmp-browse.php?browse=interfaces&ID=$id&device=$device\">";
__("Network Interfaces");
PRINT "</a><br>";
PRINT "<a href=\"$userbase/snmp-browse.php?browse=ip&ID=$id&device=$device\">";
__("IP Stats");
PRINT "</a><br>";
PRINT "<a href=\"$userbase/snmp-browse.php?browse=HOST-RESOURCES-MIB::hrStorageEntry&ID=$id&device=$device\">";
__("Storage");
PRINT "</a><br>";
PRINT "<a href=\"$userbase/snmp-browse.php?browse=.1&ID=$id&device=$device\">";
__("Browse all common MIBS. ATTENTION this can take very long time.");
PRINT "</a>";

PRINT "<p><i>";
__("* Uptime here reflects SNMP agent uptime, not computer uptime!");
PRINT "</i>";
commonFooter();
