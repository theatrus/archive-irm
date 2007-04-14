<?php

class UserTest extends UnitTestCase
{
	var $o;
	
	function setUp()
	{
		$this->o = new User;
	}

	function testSetPassword()
	{
		$this->o->setPassword('foo');
		$this->assertEqual(md5('foo'), $this->o->udata['Password']);
	}

	function testAuthenticate()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		$rows[] = "INSERT INTO users (name,password) VALUES ('user', md5('pass'))";
		BulkQueries($rows);
		
		$this->assertEqual(false, $this->o->authenticate('user', 'notapass'));
		$this->assertEqual(true, $this->o->authenticate('user', 'pass'));
		$this->assertEqual(false, $this->o->authenticate('notauser', 'notapass'));
	}

	function testRetrieve()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		$rows[] = "INSERT INTO users (name,fullname,email,location,phone,type,comments)
				VALUES ('user', 'Fullname', 'Email', 'Location', 'Phone', 'post-only', 'Some Random Comments')";
		BulkQueries($rows);
		
		$this->o->setName('user');
		$this->o->retrieve();
		$expected = array('Name' => 'user',
				'Fullname' => 'Fullname',
				'Email' => 'Email',
				'Location' => 'Location',
				'Phone' => 'Phone',
				'Type' => 'post-only',
				'Comments' => 'Some Random Comments');
		
		$this->assertEqual($expected, $this->o->udata);
	}

	function testAdd()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		BulkQueries($rows);

		$this->o->setName('user');
		$this->o->setPassword('pass');
		$this->o->setFullname('User Name');
		$this->o->setType('admin');
		
		$this->o->add();
		
		$DB = Config::Database();
		
		$data = $DB->getRow("SELECT * FROM users WHERE name='user'");
		
		$this->assertEqual('user', $data['name']);
		$this->assertEqual(md5('pass'), $data['password']);
		$this->assertEqual('User Name', $data['fullname']);
		$this->assertEqual('admin', $data['type']);
	}

	function testDelete()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		$rows[] = "INSERT INTO users (name) VALUES ('user')";
		
		BulkQueries($rows);
		
		$this->o->setName('user');
		$this->o->delete();
		
		$DB = Config::Database();
		$this->assertEqual(0, $DB->getOne("SELECT count(name) FROM users WHERE name='user'"));
	}

	function testPermissionCheck()
	{
		$rows[] = "DELETE FROM users WHERE name='permchecktech' || name='permcheckadmin'";
		$rows[] = "INSERT INTO users (name, type)
				VALUES ('permchecktech', 'tech')";
		$rows[] = "INSERT INTO users (name, type)
				VALUES ('permcheckadmin', 'admin')";
		BulkQueries($rows);
	
		$o = new User('permchecktech');
		$this->assertEqual(false, $o->permissionCheck('admin'), 'Tech is not an admin');
		$this->assertEqual(true, $o->permissionCheck('tech'), 'Tech should be a tech');
		$this->assertEqual(true, $o->permissionCheck('post-only'), 'Tech should be able to post');

		$o = new User('permcheckadmin');
		$this->assertEqual(true, $o->permissionCheck('admin'), 'Admin should be admin');
		$this->assertEqual(true, $o->permissionCheck('tech'), 'Admin should be a tech');
		$this->assertEqual(true, $o->permissionCheck('post-only'), 'Admin should be able to post');

		$rows[] = "DELETE FROM users WHERE name='permchecktech' || name='permcheckadmin'";
		BulkQueries($rows);
	}
}
									
class UserDriverTest extends UnitTestCase
{
	var $o;
	
	function setUp()
	{
		$this->o = new UserDriver;
	}
	
	function testAuthenticate()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		$rows[] = "INSERT INTO users (name,password) VALUES ('user', md5('pass'))";
		BulkQueries($rows);
		
		$this->assertEqual(false, $this->o->authenticate('user', 'notapass'));
		$this->assertEqual(true, $this->o->authenticate('user', 'pass'));
		$this->assertEqual(false, $this->o->authenticate('notauser', 'notapass'));
	}

	function testRetrieve()
	{
		$rows[] = "DELETE FROM users WHERE name='user'";
		$rows[] = "INSERT INTO users (name,fullname,email,location,phone,type,comments)
				VALUES ('user', 'Fullname', 'Email', 'Location', 'Phone', 'post-only', 'Some Random Comments')";
		BulkQueries($rows);
		
		$data = $this->o->retrieve('user');
		$expected = array('Name' => 'user',
				'Fullname' => 'Fullname',
				'Email' => 'Email',
				'Location' => 'Location',
				'Phone' => 'Phone',
				'Type' => 'post-only',
				'Comments' => 'Some Random Comments');
		
		$this->assertEqual($expected, $data);
	}
}
