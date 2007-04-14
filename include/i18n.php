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

// Need to fully-qualify this path, since it may be included prior to irm.inc
// being suitable.
require_once dirname(dirname(__FILE__)).'/include/accept-to-gettext.php';

$supported_languages = array('en_US.ISO-8859-1',
				'de_DE.ISO-8859-1',
				'fr_FR.ISO-8859-15',
				'id_ID.ISO-8859-1',
				'pt_BR.ISO-8859-1',
				'hu_HU.ISO-8859-2');

$locale = al2gt($supported_languages, 'text/html');

putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);


if (!function_exists('_'))
{
	function _($s)
	{
		return $s;
	}
}

function __($s)
{
	echo _($s);
}

if (function_exists('bindtextdomain'))
{
	bindtextdomain('irm', dirname(dirname(__FILE__)).'/locale/');
	textdomain('irm');
}
