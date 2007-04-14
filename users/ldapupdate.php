<?php

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("admin");
commonHeader(_("LDAP") . " - " . _("update info"));

$ldapcfg = Config::LDAP();

$dsCon = ldap_connect($ldapcfg['server']);
if(!(dsCon))
{
	trigger_error(_("Sorry, cannot contact LDAP server"), E_USER_ERROR);
	die(__FILE__.":".__LINE__.": dying with fatal error\n");
	exit;
}

ldap_set_option($dsCon, LDAP_OPT_PROTOCOL_VERSION, $ldapcfg['protocol']);

$bind = ldap_bind($dsCon, $ldapcfg['binddn'], $ldapcfg['bindpw']);
if(!($bind))
{
	trigger_error(_("Sorry, could not bind to your ldap server."), E_USER_ERROR);
	die(__FILE__.":".__LINE__.": dying with fatal error\n");
	exit;
}

$pieces = array($ldapcfg['emailfield'],
		$ldapcfg['fullnamefield'],
		$ldapcfg['phonefield'],
		$ldapcfg['locationfield']);

PRINT "<UL>\n";

$DB = Config::Database();
$users = $DB->getCol("SELECT name FROM users");

foreach ($users as $name)
{
	$user = new User($name);
	$searchResult = ldap_search($dsCon, $ldapcfg['rootdn'], "uid=".$name, $pieces);
	$info = ldap_get_entries($dsCon, $searchResult);
	if($info != false)
	{
		$fullname = $info[0][$ldapcfg['fullnamefield']][0];
		$email = $info[0][$ldapcfg['emailfield']][0];
		$officephone = $info[0][$ldapcfg['phonefield']][0];
		$officelocation = $info[0][$ldapcfg['locationfield']][0];
		$user->setEmail($email);
		$user->setFullname($fullname);
		$user->setLocation($officelocation);
		$user->setPhone($officephone);
		PRINT "<LI>";
		printf(_("Updating user: %s, (%s)"),$user->getName(), $user->getFullname());
		PRINT "</LI>\n";
		$user->commit();
	}
	else
	{
		PRINT "<LI>";
		printf(_("Deleting user: %s, (%s)"),$user->getName(),$user->getFullname());
		PRINT "</LI>\n";
		$user->delete();
	}
}

PRINT "</UL>\n";
commonFooter();
