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
require_once 'include/i18n.php';

AuthCheck("normal");

commonHeader(_("User") . " - ". _("Change Password"));
$user = new User(@$_SESSION['_sess_username']);

if ($_REQUEST['newpassword'] != $_REQUEST['confirm'])
{
	__("Your new password does not match the confirmation password.  They must be the same.");
}
elseif (!$user->authenticate(@$_SESSION['_sess_username'], $_REQUEST['oldpassword']))
{
	__("You have incorrectly entered your old password.");
}
else
{
	$user->setPassword($_REQUEST['newpassword']);
	$user->commit();
	__("Password successfully updated.\n");
}

commonFooter();
