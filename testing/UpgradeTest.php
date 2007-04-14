<?php

class UpgradeTest extends UnitTestCase
{
	function testUserUpgradeQueries()
	{
return;
		$rows[] = "DELETE FROM users WHERE name='user' OR name='o\\'user'";
		$rows[] = "INSERT INTO users (name,password)
				VALUES ('user', 'pass')";
		$rows[] = "INSERT INTO users (name,password)
				VALUES ('o\'user', 'notapass')";
		BulkQueries($rows);

		$this->assertNoErrors();
		$DB = Config::Database();
		require 'database/upgrades.php';
		
		$this->assertEqual("INSERT INTO users
				(name, password, fullname, email, location,
				 phone, type, comments)
			    VALUES
				('user', 'pass', 'user',
				 NULL, NULL, NULL, 'post-only',
				 NULL)", $UPGRADES['1.3.3'][7]);
		$this->assertEqual("INSERT INTO users
				(name, password, fullname, email, location,
				 phone, type, comments)
			    VALUES
				('o\\'user', 'notapass', 'o\\'user',
				 NULL, NULL, NULL, 'post-only',
				 NULL)", $UPGRADES['1.3.3'][8]);
	}
}
