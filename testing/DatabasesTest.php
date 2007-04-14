<?php

class DatabasesTest extends UnitTestCase
{
	function testListOfAllDatabases()
	{
		$expected = array('testing' => 'Test Database',
				'example' => 'An example DB',
				'ldapusers' => 'LDAP Users');
		
		$this->assertEqual($expected, Databases::All());
	}
}

class IRMDBTest extends UnitTestCase
{
	function testSetErrorReporting()
	{
		$_SESSION['_sess_database'] = 'testing';
		$DB = Config::Database();
		
		$this->assertEqual(PEAR_ERROR_CALLBACK, $DB->_dbh->_default_error_mode);
		$this->assertEqual('DBDie', $DB->_dbh->_default_error_options);
		$DB->setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_NOTICE);
		$this->assertEqual(PEAR_ERROR_TRIGGER, $DB->_dbh->_default_error_mode);
		$this->assertEqual(E_USER_NOTICE, $DB->_dbh->_default_error_options);

		$DB->setErrorHandling(PEAR_ERROR_CALLBACK, 'DBDie');
	}

	function testInsertQuery()
	{
		$DB = Config::Database();
		
		$DB->InsertQuery('followups', array('tracking' => 7,
						'author' => 'Fred',
						'contents' => 'Nothing much'));
		
		$row = $DB->getRow("SELECT * from followups");
		
		$this->assertEqual(7, $row['tracking']);
		$this->assertEqual(NULL, $row['date']);
		$this->assertEqual('Fred', $row['author']);
		$this->assertEqual('Nothing much', $row['contents']);
	}		

	function testUpdateQuery()
	{
		$rows[] = "INSERT INTO followups (ID, tracking, author)
					VALUES (12, 7, 'Joe')";
		
		$DB = Config::Database();
		
		$DB->BulkQueries($rows);

		$DB->UpdateQuery('followups', array('tracking' => 7,
						'author' => 'Joe',
						'contents' => 'Even less'),
						"ID=12");
		
		$row = $DB->getRow("SELECT * from followups WHERE ID=12");
		
		$this->assertEqual(7, $row['tracking']);
		$this->assertEqual(NULL, $row['date']);
		$this->assertEqual('Joe', $row['author']);
		$this->assertEqual('Even less', $row['contents']);
	}		
}
