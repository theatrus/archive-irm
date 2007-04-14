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

AuthCheck("tech");
$DB = Config::Database();
$comp_id = $DB->getTextValue($comp_id);
$group_id = $DB->getTextValue($group_id);
$query = "DELETE FROM comp_group WHERE comp_id = $comp_id AND group_id = $group_id";
$DB->query($query);
header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
