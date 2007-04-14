<?php

class SetupFuncTest extends IRM_WebTestCase
{
	function testSetupUpdate()
	{
		$this->get(TESTING_BASEURI);
		$this->_Login('Admin', 'admin');
		$this->get(TESTING_BASEURI.'/users/setup-irm.php');
		$this->assertTitle('IRM: System Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertWantedPattern('/Email Options/');

		$this->assertTrue($this->setField('notifynewtrackingbyemail', false));
		$this->assertTrue($this->setField('userupdates', false));
		$this->assertTrue($this->setField('showjobsonlogin', false));
		$this->assertTrue($this->setField('logo', 'irm-jr2.jpg'));
		$this->assertTrue($this->setField('stylesheet', 'green'));
		
		$this->clickSubmit('Update');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertEqual(false, Config::Get('notifynewtrackingbyemail'));
		$this->assertEqual(false, Config::Get('userupdates'));
		$this->assertEqual(false, Config::Get('showjobsonlogin'));
		$this->assertEqual('irm-jr2.jpg', Config::Get('logo'));
		$this->assertEqual('green.css', Config::Get('stylesheet'));
	}

	function testTemplateUpdate()
	{
		$rows[] = "DELETE FROM templates";
		$rows[] = "INSERT INTO templates (ID, templname, os, osver)
				VALUES (12, 'Unit Tester', 'SimpleTest', '0.3')";

		BulkQueries($rows);
		
		$this->_Login('Admin', 'admin');
		$this->clickLink('Setup');
		$this->clickLink('Manage Templates');
		$this->clickLink('Unit Tester');
		$this->assertTitle('IRM: Setup - Templates Editor');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertTrue($this->setField('osver', '0.5'));
		$this->clickSubmit('Update');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertDBField('0.5', 'templates', 'osver');
	}

	function testAddKnowledgeBaseCategory()
	{
		$this->_Login('Admin', 'admin');
		$this->clickLink('Setup');
		$this->clickLink('Setup the Knowledge Base');
		$this->assertTitle('IRM: Knowledge Base Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertTrue($this->setField('categoryname', 'Random Crap'));
		$this->assertTrue($this->setField('categorylist', 'Main'));
		$this->clickSubmit('Add');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		global $DB;
		$this->assertEqual('Random Crap', $DB->getOne("SELECT name FROM kbcategories WHERE name='Random Crap'"));
		$this->assertEqual('0', $DB->getOne("SELECT parentID FROM kbcategories WHERE name='Random Crap'"));
	}

	function testDeleteKnowledgeBaseCategory()
	{
		$this->_Login('Admin', 'admin');
		$this->clickLink('Setup');
		$this->clickLink('Setup the Knowledge Base');
		$this->assertTitle('IRM: Knowledge Base Setup');
		
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
	}
}

class UserSetupFuncTest extends IRM_WebTestCase
{
	function testUpdateUserDetails()
	{
		$rows[] = "DELETE FROM users";
		$rows[] = "INSERT INTO users (name, password, fullname, type)
			VALUES ('user', md5('xyz'), 'Joe Usr', 'admin')";
		BulkQueries($rows);
		
		$this->_Login('user', 'xyz');
		$this->assertTrue($this->clickLink('Setup'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		
		$this->assertTrue($this->clickLink('Setup Users'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTrue($this->clickLink('[edit]'));
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Users - User Update');
		
		$this->assertTrue($this->setField('fullname', 'Joe User'));
		$this->assertTrue($this->setField('location', 'Nowhere'));
		$this->assertTrue($this->setField('password', 'abc'));
		$this->clickSubmit('Update');
		
		$this->assertDBField('Joe User', 'users', 'fullname');
		$this->assertDBField('Nowhere', 'users', 'location');
		$this->assertDBField(md5('abc'), 'users', 'password');

		$adminpassword = "admin";

		$origrows[] = "DELETE FROM users";
		$origrows[] = "INSERT INTO users
			(name, password, fullname, email,
			 location, phone, type)
			VALUES
			('Admin',md5('$adminpassword'),'Administrator Guy','root@localhost',
			 'Admin Place (in front of the computer)','Use e-mail','admin')";
		$origrows[] = "INSERT INTO users
			(name, password, fullname, type)
			VALUES
			('Guest',md5('guest'),'Guest User','post-only')";
		$origrows[] = "INSERT INTO users
			(name, password, fullname, email, type)
			VALUES
			('Normal',md5('normal'),'Normal User','user@localhost','normal')";
		$origrows[] = "INSERT INTO users
			(name, password, fullname, email, type)
			VALUES
			('Tech',md5('tech'),'Technician User','tech@localhost','tech')";
		$origrows[] = "INSERT INTO users
			(name, password, fullname, type)
			VALUES
			('IRMConnect',md5('irmconnect'),'Special account','post-only')";

		BulkQueries($origrows);

	}
}
