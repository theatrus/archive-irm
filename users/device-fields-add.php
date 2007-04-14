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

require_once '../include/irm.inc';

AuthCheck("admin");
commonHeader(_("Setup - Devices"));
$device = new Device();
$device->devicetype = $devicetype;
$device->datatype = $datatype;
$device->field_name  = $field_name;

if ($_POST['delete'])
{
	$device->deleteField();
}

if ($field_name != "")
{
	$device->addField();
}
$device->editDeviceFields();

commonFooter();
