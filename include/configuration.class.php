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

class Configuration
{
	var $notifyassignedbyemail;
	var $notifynewtrackingbyemail;
	var $newtrackingemail;
	var $groups;
	var $usenamesearch;
	var $userupdates;
	var $sendexpire;
	var $showjobsonlogin;
	var $minloglevel;
	var $logo;
	var $snmp;
	var $snmp_rcommunity;
	var $snmp_ping;
	var $knowledgebase;
	var $fasttrack;
	var $version;
	var $build;
	var $stylesheet;

	function Configuration()
	{
	}
}

?>
