<?php

class LDAPUserUnitTest extends IRM_UnitTestCase
{
	function setUp()
	{
		$_SESSION['_sess_database'] = 'ldapusers';
	}
	
	function tearDown()
	{
		$_SESSION['_sess_database'] = 'testing';
	}
	
	function testLDAPFindUserDN()
	{
		$this->assertEqual('uid=j_ho,ou=people,o=_irmtest', User::LDAPFindUserDN('j_ho'));
		$this->assertEqual(NULL, User::LDAPFindUserDN('nobodyhome'));
	}
	
	function testLDAPAuth()
	{
		$this->assertTrue(User::authenticate('j_ho', 'bootycall'));
	}
}
