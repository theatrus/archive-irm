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

# NOTE: This is NOT used anymore. Time to develop it was not available for the 1.4.0 release. 

require_once 'lib/Config.php';

class UserDriver 
{
	var $user_values;

	function UserDriver() 
	{
	}

	function authenticate($u, $p) 
	{
		$DB = Config::Database();

		$qu = $DB->getTextValue($u);
		$qp = $DB->getTextValue(md5($p));
		$dbuser = $DB->getOne("SELECT name FROM users WHERE name = $qu AND password = $qp");
		if ($dbuser == $u) 
		{
			return true;
		} else 
		{
			return false; # error!
		}
	}

	function services() 
	{
		$service[add] = 1;
		$service[delete] = 1;
		$service[typemap] = 0; 
		return $service;
	}

	function retrieve($name) 
	{
		$DB = Config::Database();

		$name = $DB->getTextValue($name);
		$result = $DB->getRow("SELECT name,fullname,email,location,phone,type,comments FROM users WHERE (name = $name)");
		$data['Name'] = $result['name'];
		$data['Fullname'] = $result['fullname'];
		$data['Email'] = $result['email'];
		$data['Location'] = $result['location'];
		$data['Phone'] = $result['phone'];
		$data['Type'] = $result['type'];
		$data['Comments'] = $result['comments'];

		return $data; # get returned data	
	}
}
