<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2003 Yann Ramin
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

$err = false;

function health_error($msg)
{
	return sprintf("<p id=warning><b>%s</b> %s</p>\n", _("ERROR:"), $msg);
}

function health_notice($msg)
{
	return sprintf("<p id=notice><b>%s</b> %s</p>\n", _("NOTICE:"), $msg);
}

function health_good($msg)
{
	return sprintf("<p id=healthy><b>%s</b> %s</p>\n", _("HOORAY:"), $msg);
}

if (!function_exists('mysql_connect'))
{
	$RUNMSG .= health_error(_("You do not appear to have the MySQL module installed."));
	$err = true;
}

if (!function_exists('gettext'))
{
	$RUNMSG .= health_error("No gettext support found.  You will not have translations available.");
}

if (@$tricksy_hiddens !== 'Precioussss')
{
	$RUNMSG .= health_error(_("GPC variables not being registered globally."));
	$err = true;
}

if ((basename(@$_SERVER['SCRIPT_FILENAME']) != 'admin.php') && (basename(@$_SERVER['PATH_TRANSLATED']) != 'admin.php'))
{
	$RUNMSG .= health_error(_("Your webserver isn't providing SCRIPT_FILENAME or PATH_TRANSLATED.  Please report a bug giving your OS and Webserver information."));
	$err = true;
}

if (!preg_match('/(\.[:;])|([:;]\.)/', ini_get('include_path')))
{
	$RUNMSG .= health_error(_("The current directory ('.') does not appear to be in your include_path."));
	$err = true;
}

$verbits = explode('.', PHP_VERSION);
if (($verbits[0] < 4) || ($verbits[0] == 4 && $verbits[1] < 1))
{
	$RUNMSG .= health_error(_("IRM requires a minimum PHP version of 4.1.0."));
	$err = true;
}

if ($verbits[0] > 4)
{
	$RUNMSG .= health_notice(_("IRM has not been properly tested with PHP 5.  Please report success and failure to irm-devel@lists.sf.net."));
#	$err = true;
}

if (!file_exists(ini_get('session.save_path')))
{
	$RUNMSG .= health_error(_("Your configured session save path is invalid!"));
	$err = true;
}

if ($err)
{
	$RUNMSG .= health_error(_("There were problems detected."));
} else {
	$RUNMSG .= health_good(_("Your server appears healthy.  Enjoy IRM!"));
}
