<?php
if (!defined('RUNFILE'))
{
	require_once 'testrun_header.php';
	define('RUNFILE', __FILE__);
}

require_once 'simpletest/reporter.php';
require_once 'unittests.php';
require_once 'functests.php';

class AllTests extends GroupTest {
	function AllTests() {
		$this->GroupTest('All tests for IRM');
		$this->addTestCase(new UnitTests());
		$this->addTestCase(new FunctionalTests());
	}
}

if (RUNFILE == __FILE__)
{
	$test = &new AllTests();
	if(TextReporter::inCli()){
		exit ($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HTMLReporter);
}
