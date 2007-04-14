<?php

require_once 'simpletest/unit_tester.php';

class OCSTest extends UnitTestCase
{
	function testOCS()
	{
		Config::Set('ocsuser','root');
		Config::Set('ocspassword','rootpassword');
		Config::Set('ocsserver','localhost');
		Config::Set('ocsdb','ocsweb');

		$OCS = new OCS();
		$this->assertEqual($OCS->ocsdb, 'ocsweb');
		$this->assertEqual($OCS->ocsserver, 'localhost');
		$this->assertEqual($OCS->ocsport, '3306');
		$this->assertEqual($OCS->ocsuser, 'root');
		$this->assertEqual($OCS->ocspassword, 'rootpassword');
		$this->assertEqual($OCS->DSN, 'mysql://root:rootpassword@localhost:3306/ocsweb');
	}
}
									
