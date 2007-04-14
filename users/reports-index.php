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
#                               CHANGELOG                                       #
#################################################################################
#                                                                               #
#  2006/03/20 - Bruce Luhrs   for v1.5.8, add "report list separator            #
#                                                                               #
#################################################################################

require_once '../include/irm.inc';
require_once '../include/reports.inc.php';
require_once 'include/i18n.php';

AuthCheck("normal");
$count = count($report_list);
commonHeader(_("Reports"));
__("Welcome to IRM Reports.  This feature of IRM allows you to gain information
on your organization as a whole.  This is a modular section of IRM, allowing easy integration of third-party report modules.  For information regarding writting your own modules, take a look at docs/REPORTS in your IRM installation.");
?>
<hr noshade>
<?php __("Select a report module below:") ?>
<br>
<?
/** file system listing from users/reports directory. **/
/*TODO This need finishing - basically all files should
  hold some sort of name variable that we can then display.
*/
/*
$fsfiles = dir(dirname(__FILE__) . '/../users/reports');
$exclude = array('.', '..');
while (false !== ($entry = $fsfiles->read())) 
{
	if (in_array($entry, $exclude)) 
	{
		continue; 
	}
        PRINT '<a href="'.Config::AbsLoc("users/reports/$entry")."\">$entry</a>";
	PRINT "<br/>";
}
PRINT "<hr/>";
//*/

foreach ($report_list as $data)
{
	$name = $data['name'];
	$file = $data['file'];
        # add code to insert "reports lists separator" and header based on 'name = Divider'
        if ($name=='Divider') {
          PRINT "<HR>\n";
          PRINT "&nbsp;<B>Additional Reports</B>\n";
          PRINT "<HR>\n";
        } else {
          PRINT '<a href="'.Config::AbsLoc("users/$file")."\">$name</a><br>";
        }
}
commonFooter();
