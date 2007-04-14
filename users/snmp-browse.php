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
require_once 'include/i18n.php';
require_once 'lib/Net_SNMP.php';

AuthCheck("normal");


commonHeader(_("Computers") . " - " . _("SNMP Browser"));

$DB = Config::Database();
$qID = $DB->getTextValue($_GET['ID']);
$device = $_GET['device'];
$query = "SELECT ip FROM $device WHERE ID = $qID";
$ip = $DB->getOne($query);
$snmp = new Net_SNMP($ip);
$data = $snmp->snmpwalk($_GET['browse']);

print "<TABLE>\n";
print "<TR><TH>"._("OID")."</TH><TH>"._("Type")."</TH><TH>"._("Value")."</TH></TR>\n";

foreach ($data as $oid => $result) {
      print "<TR>\n";
      print " <TD>".$oid."</TD>\n";
      print " <TD>".$result['Type']."</TD>\n";
      print " <TD>".$result['Value']."</TD>\n";
      print "</TR>\n";
}
print "</TABLE>\n";

commonFooter();
