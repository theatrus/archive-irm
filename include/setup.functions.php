<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 2005 Martin Stevens 
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

function prefsUpdate()
{
	$DB = Config::Database();
	$user = $DB->getTextValue($_POST['user']);

	$user_obj = new User();
	$user_obj->setName($_POST['user']);
	$user_obj->initPrefs();

	$DB->UpdateQuery('prefs', $_POST, "user=$user");
}

function setupLookup()
{
	AuthCheck("tech");
	$lookupId = $_POST['lookupId'];
	$value = $_POST['value'];
	$action = $_POST['action'];

	$lookup = new Lookup($lookupId);

	switch($action)
	{
		case "add":
			$lookup->addValue($value);
			break;
		case "delete":
			$lookup->deleteValue($value);
			break;
		case "newlookup":
			$lookup->addLookup($_REQUEST['lookupId'],$_REQUEST['lookupName'],$_REQUEST['lookupDescription']);
			break;
	}
	logevent(-1, _("IRM"), 5, _("setup"), sprintf(_("%s %s entry %s from %s."),$IRMName,$action,$value,$lookupId));
}
?>
