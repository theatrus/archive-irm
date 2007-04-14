<?php

# The double dirname is to strip off the /include component.
$root_path = dirname(dirname(__FILE__));

#    IRM - The Information Resource Manager
#    Copyright (C) 1999-2003 Yann Ramin & Keith Schoenefeld
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
###############################################################################

$DEV = true;
error_reporting(E_ALL);
if (@$DEV){
	ini_set('display_errors', 1);
} else {
	ini_set('display_errors', 0);
}

ini_set('magic_quotes_runtime', '0');

// Mask all "no variable here" notice messages.
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../lib/Config.php";
require_once dirname(__FILE__) . "/irm.factory.class.php";

$pathsep = Config::PathSeparator();

ini_set('include_path', "$root_path$pathsep" . ini_get('include_path'));
unset($root_path);

// Hack to make our own register globals like function
// Also has the added bonus of stripping errant slashes out of our input
if (!ini_get("register_globals"))
{
	foreach ($_REQUEST as $k=>$v)
	{
		if (get_magic_quotes_gpc())
		{
			$_REQUEST[$k] = stripslashes($v);
		}
		if (!isset($GLOBALS[$k]))
		{
			${$k} = $_REQUEST[$k];
		}
	}
}

require_once 'lib/PageHistory.php';

// And awaaaaaaaay we go!
session_name('IRMSESSID');
//echo ini_get('session.auto_start');
session_start();

// Remember previous pages so we can go back if necessary
if (!isset($_SESSION['_sess_pagehistory']))
{
	$_SESSION['_sess_pagehistory'] = new PageHistory;
}

$_SESSION['_sess_pagehistory']->Add(@$_SERVER['REQUEST_URI']);

$IRMName = @$_SESSION['_sess_username'];

require_once 'include/i18n.php';
require_once 'include/functions.php';
require_once 'include/User.php';

$networking_fields = array(
		'name'			 => _("Name"),
		'ID'			 => _("IRM ID"),
		'location'		 => _("Location"),
		'type'			 => _("Type"),
		'ram'			 => _("RAM Amount (in MB)"),
		'serial'		 => _("Serial Number"),
		'otherserial'		 => _("Other Number"),
		'ip'			 => _("IP Address"),
		'mac'			 => _("MAC/Network Address"),
		'comments'		 => _("Comments"),
		'contact'		 => _("Contact Person"),
		'contact_num'		 => _("Contact Number"),
		'date_mod'		 => _("Date Last Modified")
		);

$software_fields = array(
		'name'			=> _("Name"),
		'ID'			=> _("IRM ID"),
		'platform'		=> _("Platform"),
		'comments'		=> _("Comments"),
		'class'			=> _("Class")
);

$computer_fields = array(
		'name'			 => _("Name"),
		'ID'			 => _("IRM ID"),
		'location'		 => _("Location"),
		'flags_surplus'		 => _("Surplus"),
		'flags_server'		 => _("Server")	,
		'type'			 => _("Type"),
		'os'			 => _("Operating System"),
		'osver'			 => _("Operating System Version"),
		'processor'		 => _("Processor"),
		'processor_speed'	 => _("Processor Speed"),
		'serial'		 => _("Serial Number"),
		'otherserial'		 => _("Other Number"),
		'ramtype'		 => _("RAM Type"),
		'ram'			 => _("RAM Amount (in MB)"),
		'network'		 => _("Network Card Type/Brand"),
		'ip'			 => _("IP Address"),
		'mac'			 => _("MAC/Network Address"),
		'hdspace'		 => _("Hard Drive Capacity"),
		'contact'		 => _("Contact Person"),
		'contact_num'		 => _("Contact Number"),
		'date_mod'		 => _("Date Last Modified"),
		'comments'		 => _("Comments")
		);

$computerListElements = array(
	      'type'			 => _("Computer Type"),
              'os'			 => _("Operating System"),
              'osver'			 => _("Operating System Version"),
              'processor'		 => _("Processor"),
              'processor_speed'		 => _("Processor Speed"),
              'location'		 => _("Location"),
              'serial'			 => _("Serial Number"),
              'otherserial'		 => _("Other Serial Number"),
              'ramtype'			 => _("Ram Type"),
              'ram'			 => _("Ram Amount (in MB)"),
              'network'			 => _("Network Card"),
              'ip'			 => _("IP Address"),
              'mac'			 => _("MAC/Network Address"),
              'hdspace'			 => _("Hard Drive Space"),
              'contact' 		 => _("Contact Person"),
              'contact_num' 		 => _("Contact Number"),
              'comments' 		 => _("Comments"),
              'date_mod'		 => _("Date Modified")
);

$show_list_full = array(
		'list'			 => _("list view"),
		'full'			 => _("full view")
		);

$phrasetype = array(
		'contains'		 => _("contains"),
		'exact'			 => _("is the exact phrase")
		);
