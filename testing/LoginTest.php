<?php

class LoginTest extends IRM_WebTestCase
{
	function testBasicAdminLogin()
	{
		$this->get(TESTING_BASEURI);
		$this->assertField('name');
		$this->assertField('password');
		$this->assertTrue($this->setField('name', 'Admin'));
		$this->assertTrue($this->setField('password', 'admin'));
		$this->assertTrue($this->setField('dbuse', 'Test Database'));
		$this->clickSubmit('Login');
		$this->assertCookie('IRMSESSID');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertNoUnwantedPattern('/(Session expired)|(Incorrect username or password)/');
		$this->assertTitle("IRM: Command Center");
	}

	function testFailedLogin()
	{
		$this->get(TESTING_BASEURI);
		$this->assertTrue($this->setField('name', 'Tech'));
		$this->assertTrue($this->setField('password', 'tch'));
		$this->assertTrue($this->setField('dbuse', 'Test Database'));
		$this->clickSubmit('Login');
		$this->assertWantedPattern('/Incorrect username or password/');
		$this->assertTitle('IRM - The Information Resource Manager');
	}

	function testRedirectedLogin()
	{
		$this->get(TESTING_BASEURI.'/users/logout.php');
		$this->get(TESTING_BASEURI.'/users/setup-index.php');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM - The Information Resource Manager');
		$this->assertWantedPattern('/Session expired./');
		$this->assertField('redirect', 'users/setup-index.php');
		$this->assertTrue($this->setField('name', 'Admin'));
		$this->assertTrue($this->setField('password', 'admin'));
		$this->assertTrue($this->setField('dbuse', 'Test Database'));
		$this->clickSubmit('Login');
		
		$this->assertCookie('IRMSESSID');
		$this->assertTitle('IRM: Setup');
	}
}

class PasswordManagementTest extends IRM_WebTestCase
{
	function testPasswordChange()
	{
		$this->assertWantedPattern('/Preferences/');
		$this->assertNoUnwantedPattern('/Incorrect username or password/');
		$this->clickLink('Preferences');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->clickLink('Change Your Password');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Change Password');

		$this->assertTrue($this->setField('oldpassword', 'tech'));
		$this->assertTrue($this->setField('newpassword', 'xyz'));
		$this->assertTrue($this->setField('confirm', 'xyz'));

		$this->clickSubmit('Change Password');

		$this->assertWantedPattern('/Password successfully updated/');
		
		$DB = Config::Database();
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(name) FROM users WHERE name='Tech' AND password=md5('xyz')"));
		$DB->query("UPDATE users SET password=md5('tech') WHERE name='Tech'");
	}

	function testPasswordChangeWrongConfirm()
	{
		$this->clickLink('Preferences');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->clickLink('Change Your Password');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Change Password');

		$this->assertTrue($this->setField('oldpassword', 'tech'));
		$this->assertTrue($this->setField('newpassword', 'xyz'));
		$this->assertTrue($this->setField('confirm', '123'));

		$this->clickSubmit('Change Password');

		$this->assertWantedPattern('/Your new password does not match the confirmation password/');
		
		$DB = Config::Database();
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(name) FROM users WHERE name='Tech' AND password=md5('tech')"));
	}

	function testPasswordChangeWrongOldPassword()
	{
		$this->clickLink('Preferences');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->clickLink('Change Your Password');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertTitle('IRM: Change Password');

		$this->assertTrue($this->setField('oldpassword', 'tek'));
		$this->assertTrue($this->setField('newpassword', 'xyz'));
		$this->assertTrue($this->setField('confirm', 'xyz'));

		$this->clickSubmit('Change Password');

		$this->assertWantedPattern('/You have incorrectly entered your old password/');
		
		$DB = Config::Database();
		$this->assertEqual(1, $DB->getOne("SELECT COUNT(name) FROM users WHERE name='Tech' AND password=md5('tech')"));
	}
}
