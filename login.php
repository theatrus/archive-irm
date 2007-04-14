<?php

#    IRM - The Information Resource Manager
#    Copyright (C) 2001 Yann Ramin
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

require_once 'include/irm.inc';
require_once 'include/User.php';
require_once 'include/functions.php';

$_SESSION['_sess_database'] = $_REQUEST['dbuse'];
$_SESSION['_sess_username'] = @$_REQUEST['name'];

if (!$_REQUEST['name'])
{
	header('Location: index.php');
	echo _('Redirecting to').' '._('the login page');
}

// The sooper-sekrit anonymous user IRMConnect gets a free pass
if (($_REQUEST['name'] != 'IRMConnect')
	&& (!User::Authenticate($_REQUEST['name'], $_REQUEST['password'])))
{
 	logevent(-1, "IRM", 1, "login",
			_(sprintf(_("Failed login: '%s', database '%s'"),
					$_REQUEST['name'],
					$_SESSION['_sess_database'])));
 	$dest = urlencode(@$_REQUEST['redirect']);
	header('Location: index.php?auth=fail&redirect='.$dest);
	echo _('Redirecting to').' <a href="index.php?auth=fail">'._('the login page').'</a>';
	exit;
}

if ($_REQUEST['redirect'])
{
	if ($_REQUEST['ID']){
		header("Location: ".Config::AbsLoc($_REQUEST['redirect']. $_REQUEST['ID']));
	} else {
		header("Location: ".Config::AbsLoc($_REQUEST['redirect']));
	}
} else {
	header("Location: ".Config::AbsLoc('users/index.php'));
}

echo _('Redirecting to').' <a href="users/">'._('the system index').'</a>';
