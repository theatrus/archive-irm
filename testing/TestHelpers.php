<?php

require_once 'lib/Config.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/unit_tester.php';

// Essential for proper database operation
$_SESSION['_sess_database'] = 'testing';

$DB = Config::Database();
$DB->_EmptyDatabase();
$DB->InitDatabase();

/* Send the array of query strings ($queries) to the global DB connection
 * given in $DB.
 */

function BulkQueries($queries)
{
	$DB = Config::Database();

	$errs = $DB->BulkQueries($tmp = $queries);

	if (count($errs))
	{
		echo "Query failures:\n";
		print_r($errs);
	}
}

class IRM_WebTestCase extends WebTestCase
{
	/**
	 * Initialise the database to allow the tests to run  correctly
	 */
	function setUp()
	{
		$this->get(TESTING_BASEURI);
		$this->_Login('Tech', 'tech');
	}

	function tearDown()
	{
	}
	
	/** Initiate a login to the test system.
	 * Goes to the login page and enters username/password information
	 * as well as selecting the database (if provided).
	 *
	 * Note that the database specification must be the visible name of
	 * the DB, not the actual ID.
	 */
	function _Login($user, $pass, $db = NULL)
	{
		$this->get(TESTING_BASEURI);
		$this->assertTrue($this->setField('name', $user));
		$this->assertTrue($this->setField('password', $pass));
		if ($db !== NULL)
		{
			$this->assertTrue($this->setField('dbuse', $db));
		}
		$this->clickSubmit('Login');
		
		$this->assertTitle('IRM: Command Center');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
	}

	function assertEqual($expected, $actual, $message = '%s')
	{
		return $this->assertExpectation(
			new EqualExpectation($expected),
			$actual,
			$message);
	}

	function assertDBField($val, $tbl, $field, $message = NULL)
	{
		$DB = Config::Database();
		$dbval = $DB->getOne("SELECT $field FROM $tbl");
		
		if ($message === NULL)
		{
			$message = "Field $tbl.$field not correct; expected $val, actual $dbval";
		}
		
		$this->assertEqual($val, $dbval, $message);
	}

	function clearTable($tbl)
	{
		$DB = Config::Database();
		
		$DB->query("DELETE FROM $tbl");
	}
}

class IRM_UnitTestCase extends UnitTestCase
{
	function assertDBField($val, $tbl, $field, $message = NULL)
	{
		$DB = Config::Database();
		
		$this->assertEqual($val, $DB->getOne("SELECT $field FROM $tbl"), $message);
	}

	function assertByDiff($datafile, $actual, $message = NULL)
	{
		if (!file_exists($datafile))
		{
			$fp = fopen("$datafile.new", "w");
			fwrite($fp, $actual);
			fclose($fp);
			
			$this->assertFalse(true, "Data file does not exist; a temporary copy has been placed in $datafile.new");
		}
		else
		{
			$tmpfile = tempnam('/xyzzy', 'aBD');
		
			$fp = fopen($tmpfile, 'w');
			fwrite($fp, $actual);
			fclose($fp);
		
			$output = `diff -uwb "$datafile" "$tmpfile"`;
			
			$this->assertTrue($output == '', "Files differ:\n$output");
		}
	}
}
