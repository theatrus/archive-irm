<?php

require_once 'simpletest/unit_tester.php';

class InternalTest extends UnitTestCase
{
	function testInternal()
	{
		$this->assertEqual('xyz', 'xyz');
		$this->assertNull(NULL, "Aiee!  It's not null!");
		$this->assertNotNull('notnull', "Aiee! It's null!");
		$this->assertTrue(true, "Feck!  It's false!");
	}
}
									
