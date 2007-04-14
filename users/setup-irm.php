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

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("admin");

commonHeader(_("System Setup"));

if(@$submit == "update")
{
	foreach (array_keys($DEFAULT_CONFIG) as $key)
	{
		if (array_key_exists($key, $_REQUEST))
		{
			if (is_bool($DEFAULT_CONFIG[$key]))
			{
				Config::Set($key, 1);
			}
			else
			{
				Config::Set($key, $_REQUEST[$key]);
			}
		}
		else
		{
			if (is_bool($DEFAULT_CONFIG[$key]))
			{
				Config::Set($key, 0);
			}
		}
	}

	__('IRM System Setup updated.');
	printf('<a href="%s">%s</a>', Config::AbsLoc('users/setup-irm.php'),
		_('View or modify the new settings.'));
}
else
{
	irmSetup();
}
commonFooter();
