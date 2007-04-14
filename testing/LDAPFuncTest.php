<?php

class LDAPFuncTest extends IRM_WebTestCase
{
	function testLDAPLogin()
	{
		$rows[] = "DELETE FROM users";
		BulkQueries($rows);

		$this->_Login('j_ho', 'bootycall', 'LDAP Users');

		$this->assertDBField('j_ho', 'users', 'name');
		$this->assertDBField('j_ho@example.com', 'users', 'email');
		$this->assertDBField('123 4567', 'users', 'phone');
		$this->assertDBField('J. Ho', 'users', 'fullname');
		$this->assertDBField('1X25', 'users', 'location');
	}

	function testLDAPLoginWithAuthBind()
	{
		$rows[] = "DELETE FROM users";
		BulkQueries($rows);
		
		copy('testing/data/ldap_auth.ini', 'config/ldap.ini');

		$this->_Login('Admin', 'nothingmuch', 'LDAP Users');
		
		$this->assertDBField('Admin', 'users', 'name');
		$this->assertDBField('', 'users', 'email');
		$this->assertDBField('777', 'users', 'phone');
		$this->assertDBField('The Almighty', 'users', 'fullname');
		$this->assertDBField('upstairs', 'users', 'location');

		copy('testing/data/ldap.ini', 'config/ldap.ini');
	}

	function testLDAPFailedLogin()
	{
		$this->get(TESTING_BASEURI);
		$this->assertTrue($this->setField('name', 'nosuchuser'));
		$this->assertTrue($this->setField('password', 'foo'));
		$this->assertTrue($this->setField('dbuse', 'LDAP Users'));
		$this->clickSubmit('Login');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTitle('IRM - The Information Resource Manager');
		$this->assertWantedPattern('/Incorrect username or password/');
	}
}
