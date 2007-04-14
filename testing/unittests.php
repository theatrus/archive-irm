<?php
if (!defined('RUNFILE'))
{
	require_once dirname(__FILE__) . '/testrun_header.php';
	define('RUNFILE', __FILE__);
}
// Essential for proper database operation
$_SESSION['_sess_database'] = 'testing';

require_once dirname(__FILE__) .'/../lib/Config.php';
require_once 'simpletest/test_case.php';
require_once 'simpletest/reporter.php';
require_once 'TestHelpers.php';

require_once 'include/functions.php';
require_once 'lib/Databases.php';

class UnitTests extends GroupTest
{
	function UnitTests()
	{
		$this->GroupTest('Unit Tests');
		$this->addTestFile('InternalTest.php');
		$this->addTestFile('DatabasesTest.php');
		$this->addTestFile('ConfigTest.php');
		$this->addTestFile('ScrapTest.php');
		$this->addTestFile('SoftwareUnitTest.php');
		$this->addTestFile('UpgradeTest.php');
		$this->addTestFile('UserTest.php');
		$this->addTestFile('FollowupUnitTest.php');
		$this->addTestFile('TrackingUnitTest.php');

		global $LDAP_TESTS;
		if ($LDAP_TESTS)
		{
			$this->addTestFile('LDAPUnitTest.php');
		}
	}
}


if (RUNFILE == __FILE__)
{
	$test = &new UnitTests();
	if(TextReporter::inCli()){
		exit ($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HTMLReporter);
}
