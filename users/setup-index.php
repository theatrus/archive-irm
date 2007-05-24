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
#    Modifyed to fit more on one page.
#    Mica Currie, Barton Insurance.

require_once '../include/irm.inc';
require_once 'include/i18n.php';
require_once 'lib/Databases.php';
require_once '../include/setup.functions.php';


if(isset($_POST['lookupId']))
{	
	setupLookup();
}

AuthCheck("tech");
commonHeader(_("Setup"));
__("Welcome to IRM Setup.  Here we will administer new users, setup various computer types and operating systems, network card brands, and (almost) everything else relating to IRM.") 
?>

<table class="setup">
<tr class="setupheader">
	<th colspan="2"><?php __("IRM Configuration") ?></th>
</tr>

<tr class="setupdetail">
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-users.php') ?>"><?php __("Setup Users") ?></a></td>
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-irm.php') ?>"><?php __("Configure IRM") ?></a></td>
</tr>

<tr class="setupdetail">
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-devices.php') ?>"><?php __("Setup Devices") ?></a></td>
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-templates-index.php') ?>"><?php __("Manage Templates") ?></a></td>
</tr>

<tr class="setupdetail">
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-groups-index.php') ?>"><?php __("Setup computer groups") ?></a></td>
	<td align=center><a href="<?php echo Config::AbsLoc('users/knowledgebase-index.php?action=setup') ?>"><?php __("Setup the Knowledge Base") ?></a></td>
</tr>

<tr class="setupdetail">
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-fasttrack-index.php') ?>"><?php __("Setup the FastTrack Templates") ?></a></td>
	<td align=center><a href="<?php echo Config::AbsLoc('users/setup-lookup.php') ?>"><?php __("Setup dropdowns") ?></a></td>
</tr>

</table>

<table>
<tr class="setupheader">
	<th colspan="2"><?php __("Individual Preferences") ?></th>
</tr>

<tr class="setupdetail">
	<td align=center colspan=2><a href="<?php echo Config::AbsLoc('users/prefs-index.php') ?>"><?php __("Preferences") ?></a></td>
</tr>
</table>

<?php
commonFooter();
?>
