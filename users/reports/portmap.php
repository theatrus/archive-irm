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
#  portmap.php
#    present a pull-down menu of networking devices
#    clicking on SHOW takes you to a "port mapping report" script
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

commonHeader(_("Port Mapping Report"));
__("Welcome to the Port Mapping Report. Select a networking device from the
pull-down menu below. When you click on SHOW, the devices connected to the
ports of that device will be shown.
");
?>

<form method="GET" action="<?php PRINT Config::AbsLoc('users/reports/portmap-report.php'); ?>">
<p><?php __("Select a Network Device by name:") ?></p>
        <select name="ID" size="1">
        <?php
$devices = $DB->getAll("SELECT ID, name FROM networking ORDER BY name");
foreach ($devices as $c)
{
  $id = $c['ID'];
  $name = $c['name'];
  PRINT "<option value=\"$id\">$name</option>\n";
}
?>

</select>
<input type="submit" value="<?php __("Show") ?>">
</form>

<?php
commonFooter();
?>
<hr>
</body>
</html>
