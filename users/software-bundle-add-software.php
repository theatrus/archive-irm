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
#	This file created by Mica Currie. March 14th, 2001.

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("admin");
#
# First we need to make sure it's ok to Add/Delete software
# AKA we are not allowed to do it if we have any installed licenses.
#
$query = "select COUNT(*) from inst_software WHERE sID=$bID ORDER BY sID";
$DB = Config::Database();
$count = $DB->getOne($query);

if ($count==0) {
	$DB->InsertQuery('software_bundles', array('sID' => $sID,
					     'bID' => $bID)
			);

	header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
} else {
	commonHeader(_("Software") . " - " . _("Bundle: Add Software Error"));
	__("We have copies of this bundle installed. You may not alter the software included with this bundle.");
	PRINT "<BR> <a href=\"".$_SESSION['_sess_pagehistory']->Previous()."\">";
	__("Return to Previous Screen");
	PRINT "</A>";
	commonFooter();
}
