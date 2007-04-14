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
#################################################################################
#                               CHANGELOG                                       #
#################################################################################
#                                                                               #
#  2006/03/21 - Bruce Luhrs   for v1.5.8, add report list separator             #
#                                                                               #
#################################################################################

# The IRM Reports System
# See docs/REPORTS for information.
require_once 'include/i18n.php';


// Display Name
$report_list['device-by-ip']['name'] =		_("All Devices by IP Address");
$report_list['location']['name'] =		_("All Devices by Location");
$report_list["default"]["name"] =		_("Default Report");
$report_list['pclist']['name'] =		_("PC List");
$report_list['software']['name'] = 		_("Software Install Report");
$report_list['tracking-detail']['name'] =	_("Search Tracking");
$report_list["tracking"]["name"] =		_("Tracking Report");
$report_list['trackingsummary']['name'] =	_("Tracking Summary");
$report_list['trackingweekly']['name'] =	_("Tracking Weekly");

# the following entry inserts the "divide" - Additional Reports Header
$report_list['divider']['name'] = _("Divider");
$report_list['divider']['file'] = '';

$report_list['portlist']['name'] =		_("All IP Addresses - List with Octet Mask");
$report_list['iplist2']['name'] =		_("Computers IP Report - Sorted by Address");
$report_list['ipreport']['name'] =		_("Computers IP Report - Sorted by Name");
$report_list['dupip']['name'] =                 _("Duplicate IP Addresses Report");
$report_list['namelist']['name'] =		_("Name List ");
$report_list['portmap']['name'] =		_("Port Map Report");
$report_list['racks']['name'] =			_("Racks Report");
$report_list['systems']['name'] =		_("Systems");


// Report File
$report_list['device-by-ip']['file'] =		'reports/iplist.php';
$report_list['location']['file'] =		'reports/locationlist.php';
$report_list["default"]["file"] =		"reports/default.php";
$report_list['pclist']['file'] =		'reports/pclist.php';
$report_list["tracking"]["file"] =		"reports/tracking.php";
$report_list['software']['file'] =		'reports/software.php';
$report_list['tracking-detail']['file'] =	'reports/tracking-detail.php';
$report_list['trackingsummary']['file'] =	'reports/tracking-summary.php';
$report_list['trackingweekly']['file'] =	'reports/tracking-weekly.php';

$report_list['iplist2']['file'] =		'reports/iplist2.php';
$report_list['ipreport']['file'] =		'reports/ipreport2.php';
$report_list['namelist']['file'] =		'reports/namelist.php';
$report_list['portlist']['file'] =		'reports/portlistoct3.php';
$report_list['portmap']['file'] =		'reports/portmap.php';
$report_list['racks']['file'] =			'reports/racks.php';
$report_list['systems']['file'] =		'reports/systems.php';
$report_list['dupip']['file'] =                 'reports/dupiprep.php';
