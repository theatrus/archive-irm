<?php

class ConfigTest extends UnitTestCase
{
	function testReadConfig()
	{
		$data = Config::ReadConfig('database');
		$this->assertEqual(array('testing' => array('DSN' => 'mysql://test@localhost/test',
							   'name' => 'Test Database'),
					'example' => array('DSN' => 'mysql://root:rootpassword@localhost:3306/irmdb',
							   'name' => 'An example DB'),
					'ldapusers' => array('DSN' => 'mysql://test@localhost/test',
							   'name' => 'LDAP Users')),
					$data);
	}

	function testConnection()
	{
		$this->assertIsA(Config::Database(), 'irmdb');
	}

	function testVersion()
	{
		$this->assertEqual('==VER==', Config::Version());
	}

	function testAbsLoc()
	{
		$_SERVER['SCRIPT_NAME'] = '/test/irm/users/logout.php';
		$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__FILE__)).'/users/logout.php';
		$this->assertEqual('/test/irm/users/directfile.php', Config::AbsLoc('users/directfile.php'));

		$_SERVER['SCRIPT_NAME'] = '/~irm/users/logout.php';
		$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__FILE__)).'/users/logout.php';
		$this->assertEqual('/~irm/users/userdirfile.php', Config::AbsLoc('users/userdirfile.php'));

		$_SERVER['SCRIPT_NAME'] = '/nothingmuch/users/logout.php';
		$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__FILE__)).'/users/logout.php';
		$this->assertEqual('/nothingmuch/', Config::AbsLoc(''));
	}

	function testAbsLocWithArgs()
	{
		$_SERVER['SCRIPT_NAME'] = '/irm/users/logout.php';
		$_SERVER['SCRIPT_FILENAME'] = dirname(dirname(__FILE__)).'/users/logout.php';
		$this->assertEqual('/irm/users/directfile.php?x=y&p=q', Config::AbsLoc('users/directfile.php', array('x' => 'y', 'p' => 'q')));
		$this->assertEqual('/irm/users/directfile.php?name=joe+bloggs', Config::AbsLoc('users/directfile.php', array('name' => 'joe bloggs')));
		$this->assertEqual('/irm/users/directfile.php?interest=15%25', Config::AbsLoc('users/directfile.php', array('interest' => '15%')));
	}

	function testGetIncludePath()
	{
		$origpath = ini_get('include_path');
		
		ini_set('include_path', 'foo:bar:baz');
		
		$this->assertEqual(array('foo', 'bar', 'baz'), Config::GetIncludePath());
		
		ini_set('include_path', $origpath);
	}

	function testChecked()
	{
		$this->assertEqual('checked', Checked(1));
		$this->assertEqual('checked', Checked('yes'));
		$this->assertEqual('checked', Checked(true));
		$this->assertEqual('', Checked(false));
		$this->assertEqual('', Checked('no'));
		$this->assertEqual('', Checked(0));
	}		
}
