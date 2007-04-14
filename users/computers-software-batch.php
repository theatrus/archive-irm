<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 1999,2000 Yann Ramin
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

AuthCheck("tech");
commonHeader(_("Computers") . " - " . _("Batch Add Software"));
	__("Use this utility to batch add software.  NOTE: Currently you can't add more than 1 software item at a time")."<br><hr noshade>";
	PRINT "<font color=red size=+2>"._("This has been disabled until it
		 can be rewritten to support License tracking")."</font>";
#	PRINT '<form method=post action="'.Config::AbsLoc('users/computers-software-batch-add.php').'">
#	<input type=hidden name=query value="'.$query.'">Add software ';
#	SoftwareDropdown();
#	PRINT " to all computers from the previous search.  <input type=submit value=Add></form>";
	PRINT "<br>"._("NOTE: Sometimes this may take a while, depending on the number of computers you have (in upwards of 10 seconds).");

commonFooter();
