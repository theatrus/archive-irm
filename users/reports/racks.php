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
#  racks.php
#    present a pull-down menu of rack names
#    clicking on SHOW takes you to a "racks report" script
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
commonHeader(_("Racks Report"));
__("Welcome to the Racks Report. Select a rack 
from the pull-down menus below. When you click on SHOW, 
the system (computer or network device) located in that
rack (location) will be shown.
");

#$DB = Config::Database();
$query = "(SELECT location as rack FROM computers)
  UNION 
(SELECT location as rack FROM networking)
ORDER BY rack
";

$racks = $DB->getAll($query);

?>

<form method="GET" action="
<?php PRINT Config::AbsLoc('users/reports/racks-report.php');
?>">
<p>
<?php __("Select a <B>Rack</B> by name:") ?>
</p>
     <select name="ID">

<?php
foreach ($racks as $result)
{
  $rackname = $result["rack"];
  PRINT "<option value=\"$rackname\">$rackname</option>\n";
}
?>
</select>
<input type="submit" value="<?php __("Show Rack") ?>">
<input type="hidden" name="sort" value="<?php __("NameSort") ?>">
<input type="hidden" name="empty" value="<?php __("NoShow") ?>">
</form>

<?php
commonFooter();
?>
</body>
</html>
