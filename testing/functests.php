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

require_once 'include/i18n.php';

// Special regexp for PHP errors
define('PHP_ERROR_REGEX', '%<b>(notice|warning|error|fatal).*(on line .*</b>)?%i');

class FunctionalTests extends GroupTest {
	function FunctionalTests() {
		global $LDAP_TESTS;

		$this->GroupTest('Functional Tests');

		if ($_SERVER['argc'] > 1)
		{
			array_shift($_SERVER['argv']);
			foreach ($_SERVER['argv'] as $f)
			{
				$this->addTestFile($f);
			}
		}
		else
		{


			$this->addTestFile('AdminTest.php');
			$this->addTestFile('LoginTest.php');
			$this->addTestFile('FrontpageTest.php');
			$this->addTestFile('SetupFuncTest.php');
			$this->addTestFile('ComputersTest.php');
			$this->addTestFile('SoftwareTest.php');
			$this->addTestFile('FastTrackTest.php');
			$this->addTestFile('TrackingFuncTest.php');
			$this->addTestFile('ComputerGroupsFuncTest.php');
			$this->addTestFile('MenuFuncTest.php');
			$this->addTestFile('DeviceFuncTest.php');
	
			global $LDAP_TESTS;
			if ($LDAP_TESTS)
			{
				$this->addTestFile('LDAPFuncTest.php');
			}

		}
	}
}

if (RUNFILE == __FILE__)
{
	$test = &new FunctionalTests();
	if(TextReporter::inCli()){
		exit ($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HTMLReporter);
}
