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
require_once dirname(__FILE__) . '/../../include/irm.inc';
require_once dirname(__FILE__) . '/../../lib/Config.php';

commonHeader(_("Event Log").' - '._("Search"));
$DB = Config::Database();

$limit_start=empty($_REQUEST['limit_start']) ? 0 : $_REQUEST['limit_start'];
$limit_end=empty($_REQUEST['limit_start']) ? 50 : $_REQUEST['limit_end'];

print "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"\">";
print "<b>" . _("Limit") . "</b> " . _("Start");
print "<input type=\"text\" name=\"limit_start\" value=\"{$limit_start}\">";
print _("End");
print "<input type=\"text\" name=\"limit_end\" value=\"{$limit_end}\">";
print "<input type=\"submit\" value=\"" . _("Go") . "\">";
print "</form>";

$query = "SELECT * FROM event_log ORDER BY date DESC LIMIT {$limit_start}, {$limit_end}";
show_events($DB->getAll($query));

commonFooter();
?>
