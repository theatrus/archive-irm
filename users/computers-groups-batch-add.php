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

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

$outquery = stripslashes(stripslashes($outquery));
echo $outquery;
AuthCheck("tech");
commonHeader(_("Computers") . " - " . _("Add to Group Completed"));

$DB = Config::Database();
// Group ID, quoted for DB select
$qg = $DB->getTextValue($sID);

$data = $DB->getAll($outquery);
print "<UL>\n";
foreach ($data as $result)
{
	$qc = $DB->getTextValue($result['ID']);
	if (0 == $DB->getOne("SELECT COUNT(*) FROM comp_group WHERE comp_id=$qc AND group_id=$qg"))
	{
		$vals = array(
			'comp_id' => $result['ID'],
			'group_id' => $sID
			);
			$DB->InsertQuery('comp_group', $vals);
		
		print "<LI>".sprintf(_("Computer ID %s added to group %s"), $result['ID'], $sID)."</LI>\n";
	}
	else
	{
		print "<LI>".sprintf(_("Computer ID %s already in this group."), $result['ID'])."</LI>\n";
	}
}
print "</UL>\n";

__("The add to group operation has been completed.");
commonFooter();
