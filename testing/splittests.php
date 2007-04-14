<?php
if (!defined('RUNFILE'))
{
	require_once 'testrun_header.php';
	define('RUNFILE', __FILE__);
}

require_once 'simpletest/reporter.php';
require_once 'unittests.php';
require_once 'functests.php';

if (RUNFILE == __FILE__)
{
	$test = &new FunctionalTests();
	if(TextReporter::inCli()){
		exit ($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HTMLReporter);

	$test = &new UnitTests();
	if(TextReporter::inCli()){
		exit ($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HTMLReporter);

}
