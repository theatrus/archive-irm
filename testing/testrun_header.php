<?php
// I like to know about *anything* that goes wrong
error_reporting(E_ALL);
// A quick hack to backtrace any error that gets reported
//require_once 'error_backtrace.php';

// Make all tests run relative to the root of the installation
chdir('..');

ini_set('include_path', ini_get('include_path') . ':testing');

if (isset($_SERVER['SCRIPT_URI'])) {
	$self = $_SERVER['SCRIPT_URI'];
}
else if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF']))
{
	$self = 'http://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
}
else
{
	$self = 'http://localhost/_irmtest/';
}

define('TESTING_BASEURI', preg_replace('|/testing/.*|', '', $self));

$LDAP_TESTS = false;
foreach (explode(':', $_ENV['PATH']) as $path)
{
	if (file_exists("$path/slapd"))
	{
		$LDAP_TESTS = true;
	}
}
/*
if (!$LDAP_TESTS)
{
	trigger_error("Not running LDAP tests; make sure /usr/bin/slapd exists", E_USER_WARNING);
}
*/
