<?php

/* This is a large, unpleasant chunk of code.  It consists, primarily, of a
 * long array of information on how to upgrade the IRM database from one
 * version to the next.
 *
 * The main array, $UPGRADES, is an associative array where each key is a
 * database version, and the associated data is information on how to
 * upgrade the database from the version in the key to the next version of
 * the database.
 * 
 * Information on how to upgrade comes in two forms:
 *
 *  1) An array of queries to execute (possibly with DB-specific queries in it;
 *	see the IRMDB::BulkQueries() method for details on that);
 *  2) The name of a function to execute, which will do the necessary work
 *	itself, and return an array of failed queries (or an empty array if
 *	there were no problems).
 */

require_once 'lib/IRMDB.php';

error_reporting(E_ALL);

// REMEMBER: The index of the upgrades array shows the version of the
// database which you are upgrading *from*.

$UPGRADES = array();

$UPGRADES['1.3.0'] = array(
			"ALTER TABLE tracking
				MODIFY status ENUM 
				('new', 'old', 'wait', 'assigned', 'active',
					'complete')",
			"UPDATE version SET
				number = '1.3.1',
				build = '20031214'
			    WHERE number = '1.3.0'"
			);

$UPGRADES['1.3.1'] = array(
			"CREATE TABLE config (
				ID int(11) NOT NULL default '0',
				notifyassignedbyemail tinyint(4) NOT NULL default '1',
				notifynewtrackingbyemail tinyint(4) NOT NULL default '0',
				newtrackingemail char(200) NOT NULL default 'user@host.com',
				groups tinyint(4) NOT NULL default '1',
				usenamesearch tinyint(4) NOT NULL default '1',
				userupdates tinyint(4) NOT NULL default '1',
				sendexpire tinyint(4) NOT NULL default '0',
				showjobsonlogin tinyint(4) NOT NULL default '1',
				minloglevel tinyint(4) NOT NULL default '5',
				logo char(50) NOT NULL default 'irm-jr1.jpg',
				snmp tinyint(4) NOT NULL default '0',
				snmp_rcommunity char(50) NOT NULL default 'public',
				snmp_ping tinyint(4) NOT NULL default '0',
				version char(50) NOT NULL default '1.3.2',
				build char(50) NOT NULL default '2001041201',
				PRIMARY KEY (ID),
				UNIQUE KEY ID_2 (ID),
				KEY ID (ID))",
			"ALTER table inst_software
				ADD lID int default '0' not null",
			"ALTER table inst_software
				ADD index(lID)",
			"ALTER table inst_software
				ADD gID int(11)",
			"ALTER table software drop version",
			"ALTER table software drop serial",
			"ALTER table software drop otherserial",
			"ALTER table software drop location",
			"ALTER table software drop license",
			"ALTER table software 
				ADD class ENUM('Operating System',
					'Application',
					'CAL',
					'Application Bundle',
					'Server')
				     DEFAULT 'Application' AFTER platform",
			"CREATE TABLE software_bundles (
				bID int(11) unsigned DEFAULT '0' NOT NULL,
				sID int(11) unsigned DEFAULT '0' NOT NULL,
				KEY sID_ndx (sID),
				KEY bID_ndx (bID),
				PRIMARY KEY (sID,bID))",
			"CREATE TABLE software_licenses (
				sID int(11) NOT NULL,
				licensekey varchar(200),
				entitlement int(11) DEFAULT '0' NOT NULL,
				ID int(11) NOT NULL auto_increment,
				oem_sticker enum ('Yes', 'No') DEFAULT 'No' NOT NULL,
				PRIMARY KEY (ID),
				KEY sID_ndx (sID),
				KEY ID_ndx (ID))",
			"DROP table version",
			"INSERT INTO config
				(ID, notifyassignedbyemail,
				 notifynewtrackingbyemail, newtrackingemail,
				 groups, usenamesearch, userupdates,
				 sendexpire, showjobsonlogin, minloglevel,
				 logo, snmp, snmp_rcommunity, snmp_ping,
				 version, build)
			     VALUES
				('0', '1', '0', 'user@host.com', '1', '1',
				 '1', '0', '1', '5', 'irm-jr1.jpg', '0',
				 'public', '0', '1.3.2', '2001041201')"
			);
			

$UPGRADES['1.3.2'] = array(
			"ALTER TABLE config
				ADD knowledgebase tinyint(4) AFTER build",
			"UPDATE config SET
				ID=0,
				version='1.3.3',
				build='20010516',
				knowledgebase=1",
			"CREATE TABLE kbcategories (
				ID int(11) NOT NULL auto_increment,
				parentID int(11) NOT NULL default '0',
				name text NOT NULL,
				PRIMARY KEY (ID),
				KEY ID (ID))",
			"CREATE TABLE kbarticles (
				ID int(11) NOT NULL auto_increment,
				categoryID int(11) NOT NULL default '0',
				question text NOT NULL,
				answer text NOT NULL,
				faq enum('yes','no') NOT NULL default 'no',
				PRIMARY KEY (ID), KEY ID (ID))",
			"INSERT INTO kbcategories VALUES (1, 0, 'IRM')",
			"INSERT INTO kbcategories VALUES (2, 1, 'Computers')",
			"INSERT INTO kbcategories VALUES (3, 1, 'Networking')",
			"INSERT INTO kbcategories VALUES (4, 1, 'Software')",
			"INSERT INTO kbcategories VALUES (5, 1, 'Tracking')",
			"INSERT INTO kbcategories VALUES (6, 1, 'Reports')",
			"INSERT INTO kbcategories VALUES (7, 1, 'Request Help')",
			"INSERT INTO kbcategories VALUES (8, 1, 'Setup')",
			"INSERT INTO kbcategories VALUES (9, 1, 'Preferences')",
			"INSERT INTO kbcategories VALUES (10, 1, 'Knowledge Base')",
			"INSERT INTO kbcategories VALUES (11, 1, 'FAQ')",
			"INSERT INTO kbcategories VALUES (12, 1, 'Logout')"
			);

$UPGRADES['1.3.3'] = '_upgrade_from_1_3_3';

function _upgrade_from_1_3_3($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);
	$errlist = array();
	
	$DB->query("ALTER TABLE users RENAME usersbak");
	$DB->query("CREATE TABLE users (
			name char(255) DEFAULT '' NOT NULL,
			password char(255),
			fullname char(200),
			email char(100),
			location char(200),
			phone char(100),
			type enum('post-only','normal','tech','admin')
				DEFAULT 'post-only' NOT NULL,
			comments text,
			PRIMARY KEY(name),
			KEY (type))"
		);

	$rows = $DB->getAll("SELECT * FROM usersbak", NULL, array(), NULL,
			MDB_FETCHMODE_ASSOC);
	if (MDB::isError($rows))
	{
		$DB->popErrorHandling();
		return array("SELECT * FROM usersbak; Upgrade terminated prematurely");
	}
	
	foreach ($rows as $row)
	{
		$username = $DB->getTextValue($row["name"]);
		$password = $DB->getTextValue($row["password"]);
		$email = $DB->getTextValue($row["email"]);
		$location = $DB->getTextValue($row["location"]);
		$phone = $DB->getTextValue($row["phone"]);
		$type = $DB->getTextValue($row["type"]);
		$comments = $DB->getTextValue($row["comments"]);
		$qry = "INSERT INTO users
				(name, password, fullname, email, location,
				 phone, type, comments)
			    VALUES
				($username, $password, $username,
				 $email, $location, $phone, $type,
				 $comments)";
		$err = $DB->query($qry);
		if (MDB::isError($DB))
		{
			$errlist[] = $qry;
		}
	}

	$qrylist = array("ALTER TABLE config ADD fasttrack int default '1'",
			"CREATE TABLE fasttracktemplates (
				ID INT NOT NULL auto_increment,
				name char(100),
				priority int(11),
				request text,
				response text,
				PRIMARY KEY (ID))",
			"INSERT INTO fasttracktemplates
				(name, priority, request, response)
				VALUES
				('Default',3, '', '')",
			"INSERT INTO fasttracktemplates
				(name, priority, request, response)
				VALUES
				('Reset Password',3, 'User forgot password',
				 'Reset password on the system')",
			"INSERT INTO fasttracktemplates
				(name, priority, request, response)
				VALUES
				('Floppy Disk in Drive',3, 'Computer will not boot, it says something about NTLDR not found', 'There was a floppy disk in the drive, once user removed it and rebooted system it started up just fine.')",
			"UPDATE config SET
				ID=0,
				version='1.3.4', build='2001071101'"
			);
	$errlist = array_merge($errlist, $DB->BulkQueries($qrylist));

	$DB->popErrorHandling();
	
	return $errlist;
}

$UPGRADES['1.3.4'] = array(
			"ALTER TABLE config
				ADD anonymous tinyint(4) AFTER fasttrack",
			"ALTER TABLE config
				ADD anon_faq tinyint(4) AFTER anonymous",
			"ALTER TABLE config
				ADD anon_tt tinyint(4) AFTER anon_faq"
			);

$UPGRADES['1.4.1'] = array(
			"ALTER TABLE computers
				ADD flags_surplus TINYINT(4) NOT NULL DEFAULT '0' AFTER flags_server",
			"UPDATE config SET
				version = '1.4.2',
				build = '20031214'"
			);

$UPGRADES['1.4.2'] = array(
			"ALTER TABLE templates
				ADD flags_surplus tinyint(4) AFTER iface",
			"UPDATE config SET
				version = '1.4.3',
				build = '20040108'"
			);

$UPGRADES['1.4.3'] = '_upgrade_from_1_4_3';

function _upgrade_from_1_4_3($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);

	$qrylist = array(
			"ALTER TABLE config
				MODIFY knowledgebase tinyint NOT NULL default 1",
			"ALTER TABLE config
				MODIFY fasttrack tinyint(4) NOT NULL default 1",
			"ALTER TABLE config
				MODIFY anonymous tinyint NOT NULL default 0",
			"ALTER TABLE config
				MODIFY anon_faq tinyint NOT NULL default 0",
			"ALTER TABLE config
				MODIFY anon_tt tinyint(4) NOT NULL default 0",
			"ALTER TABLE inst_software
				ADD lCnt int(11) NOT NULL default 1",
			"ALTER TABLE computers
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE event_log
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE followups
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE groups
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE inst_software
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE networking
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE networking_ports
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE networking_wire
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE software
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE templ_inst_software
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE templates
				MODIFY ID int(11) NOT NULL auto_increment",
			"ALTER TABLE tracking
				MODIFY ID int(11) NOT NULL auto_increment",
			"DROP TABLE IF EXISTS usersbak",
			"UPDATE users SET
				password=md5(password)
				WHERE password NOT REGEXP '^[0-9a-f]{32}$'");
	
	$errlist = $DB->BulkQueries($qrylist);

	/* Weird freaky shit in here -- some installations seem to have
	 * missed out on the flags_surplus love, and others haven't.  So we
	 * need to guard against the alter getting done if the field already
	 * exists -- which will be signified by the following query
	 * *succeeding*
	 */
	$err = $DB->query("SELECT flags_surplus FROM computers");
	if (MDB::isError($err))
	{
		$q = "ALTER TABLE computers
			ADD flags_surplus tinyint(4) default 0 NOT NULL AFTER flags_server";
		$err = $DB->query($q);
		if (MDB::isError($err))
		{
			$errlist[] = $q;
		}
	}
	
	$q = "UPDATE config SET
			version = '1.5.0',
			build = '1.5.0'";
	$err = $DB->query($q);
	if (MDB::isError($err))
	{
		$errlist[] = $q;
	}

	$DB->popErrorHandling();
	
	return $errlist;
}

$UPGRADES['1.5.0'] = '_upgrade_from_1_5_0';

function _upgrade_from_1_5_0($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);
	
	$qrylist = array("ALTER TABLE config
				MODIFY version char(50) NOT NULL DEFAULT '0'",
			"ALTER TABLE config
				DROP build",
			"ALTER TABLE software
				ADD install_package varchar(255) AFTER platform",
			"ALTER TABLE followups
				ADD public TINYINT NOT NULL DEFAULT 1 AFTER contents",
			"ALTER TABLE tracking
				ADD other_emails TEXT AFTER emailupdates",
			"ALTER TABLE computers
				MODIFY ID BIGINT UNSIGNED NOT NULL",
			"CREATE TABLE computers__ID (
				sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (sequence)
				)",
			"ALTER TABLE tracking
				MODIFY ID BIGINT UNSIGNED NOT NULL",
			"CREATE TABLE tracking__ID (
				sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (sequence)
				)",
			"ALTER TABLE networking
				MODIFY ID BIGINT UNSIGNED NOT NULL",
			"CREATE TABLE networking__ID (
				sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (sequence)
				)"
			);

	// Initialise the "sequences" (MDB-style emulations thereof, anyway,
	// since MySQL doesn't do real sequences) for relevant tables, and
	// set the nextID value appropriately
	$seqs = array('computers', 'tracking', 'networking');
	foreach ($seqs as $tbl)
	{
		$lastid = $DB->getOne("SELECT ID+1 FROM $tbl
				ORDER BY ID DESC LIMIT 1");
		if ($lastid && !MDB::isError($lastid))
		{
			$qrylist[] = "INSERT INTO ${tbl}__ID (sequence)
					VALUES ($lastid)";
		}
	}

	$qrylist[] = "UPDATE config SET
				version = '1.5.1'";

	$errlist = $DB->BulkQueries($qrylist);
	
	$DB->popErrorHandling();
	
	return $errlist;
}

$UPGRADES['1.5.1'] = '_upgrade_from_1_5_1';

function _upgrade_from_1_5_1($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);

	// Retrieve the current system config before we go deleting it all
	$cfgvars = $DB->getRow("SELECT * FROM config");
	if (MDB::isError($cfgvars))
	{
		$DB->popErrorHandling();
		return array("SELECT * FROM config; Upgrade terminated prematurely");
	}

	$qrylist = array("DROP TABLE IF EXISTS config",
			"CREATE TABLE config (
				variable	VARCHAR(255) NOT NULL,
				value		TEXT NOT NULL,
				PRIMARY KEY (variable)
			)");
	$errlist = $DB->BulkQueries($qrylist);
	
	$qrylist = array();
	foreach ($cfgvars as $var => $value)
	{
		if ($var == 'ID')
		{
			continue;
		}
		if ($var == 'version')
		{
			$var = 'dbver';
		}

		$qval = $DB->getTextValue($value);
		$qvar = $DB->getTextValue($var);
		$query = "INSERT INTO config (variable, value) VALUES ($qvar, $qval)";
		$qrylist[] = $query;
	}

	// Repair any sequences that might have become cruftified since our
	// inability to actually consider the consequences of our actions...
	$seqs = array('computers', 'tracking', 'networking');
	foreach ($seqs as $tbl)
	{
		$lastid = $DB->getOne("SELECT ID+1 FROM $tbl
				ORDER BY ID DESC LIMIT 1");
		if ($lastid && !MDB::isError($lastid))
		{
			$qrylist[] = "DELETE FROM ${tbl}__ID";
			$qrylist[] = "INSERT INTO ${tbl}__ID (sequence)
					VALUES ($lastid)";
		}
	}
	
	$qrylist[] = "DELETE FROM config WHERE variable='dbver'";
	$qrylist[] = "INSERT INTO config (value, variable)
			VALUES ('1.5.2', 'dbver')";

	$errlist = array_merge($errlist, $DB->BulkQueries($qrylist));
	
	$DB->popErrorHandling();
	
	return $errlist;
}	

$UPGRADES['1.5.2'] = '_upgrade_from_1_5_2';

function _upgrade_from_1_5_2($DB)
{
	// Very major fuckup in the 1.5.2->1.5.3 upgrade code.  I forgot to
	// update the dbver config variable, leading to nasty duplicate
	// upgrade errors.  Here's an attempt to get around that.

	$DB->pushErrorHandling(PEAR_ERROR_RETURN);
	if (MDB::isError($DB->getOne("SELECT status FROM tracking_status")))
	{
		$qrylist = array(
		"ALTER TABLE tracking TYPE=InnoDB",
		"CREATE TABLE tracking_status (
			status		VARCHAR(255) NOT NULL,
			closed		TINYINT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (status)) TYPE=InnoDB",
		"INSERT INTO tracking_status (status, closed) VALUES
			('new', 0),
			('old', 1),
			('wait', 0),
			('assigned', 0),
			('active', 0),
			('complete', 1)",
		"ALTER TABLE tracking ADD INDEX (status)",
		"ALTER TABLE tracking MODIFY status VARCHAR(255) NOT NULL DEFAULT 'new'",
		"ALTER TABLE tracking ADD FOREIGN KEY (status) REFERENCES tracking_status (status)",
		);
	}
	$DB->popErrorHandling();

	$qrylist[] = "DELETE FROM config WHERE variable='dbver'";
	$qrylist[] = "INSERT INTO config (variable, value)
				VALUES ('dbver', '1.5.3')";

	return $DB->BulkQueries($qrylist);
}

$UPGRADES['1.5.5'] = '_upgrade_from_1_5_5';

function _upgrade_from_1_5_5($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);
	$DB->popErrorHandling();

	$qrylist[] = "DELETE FROM config WHERE variable='dbver'";
	$qrylist[] = "INSERT INTO config (variable, value)
		VALUES ('dbver', '1.5.7')";
	
	$qrylist[] = "CREATE TABLE devices ( name char(200))";
	$qrylist[] = "ALTER TABLE tracking ADD device VARCHAR(200)";

	return $DB->BulkQueries($qrylist);
}

$UPGRADES['1.5.8'] = '_upgrade_from_1_5_8';

function _upgrade_from_1_5_8($DB)
{
	$DB->pushErrorHandling(PEAR_ERROR_RETURN);
	$DB->popErrorHandling();

	$qrylist[] = "DELETE FROM config WHERE variable='dbver'";
	$qrylist[] = "INSERT INTO config (variable, value) VALUES ('dbver', '1.5.8')";
	$qrylist[] = "INSERT INTO tracking_status (status, closed) VALUES ('duplicate', 1)";
	
	$qrylist[] = "ALTER TABLE networking_ports ADD device_on int(11) DEFAULT '0' NOT NULL AFTER ID";
	$qrylist[] = "ALTER TABLE networking_ports ADD device_type varchar(200) DEFAULT '' NOT NULL AFTER device_on";
	
	$qrylist[] = "ALTER TABLE followups ADD minspent int(11) NOT NULL default 0";
	
	$qrylist[] = "CREATE TABLE files(
		ID int(11) NOT NULL auto_increment,
		filename varchar(200),
		device varchar(100),
		deviceid varchar(100),
		PRIMARY KEY (ID))";

	$qrylist[] = "CREATE TABLE lookups(
		id varchar(50) NOT NULL,
		name varchar(50) NOT NULL,
		description varchar(255),
		PRIMARY KEY(id))";

	$qrylist[] = "CREATE TABLE lookup_data(
		lookup varchar(50) NOT NULL,
		value varchar(255) NOT NULL,
		INDEX(lookup))";

	$qrylist[] = "INSERT INTO lookups VALUES
		('locations', '"._('Location')."', '"._('Locations : Use this to edit where equipment can be stored')."'),
		('type', '"._('Type')."', '"._('Computer Types :These list the types of computers you can have (i.e. Dell, HP, IBM RS/6000, etc.)')."'),
		('os', '"._('OS')."', '"._('Operating Systems : This is a list of Operating Systems your computers can run.')."'),
		('ram', '"._('RAM Type')."', '"._('RAM Types : This is the types of RAM your systems can have (i.e. Unbuffered DIMMS, SDRAM DIMMS, ECC DIMMS, etc)')."'),
		('processor', '"._('Processor')."', '"._('Processor Types : This is a list of valid processors, i.e. Intel Pentium, Pentium II, DEC Alpha, EverSlow WinChip, etc.')."'),
		('iface', '"._('Network Interface')."', '"._('Network Interfaces')."'),
		('network', '"._('Network Card Type/Brand')."', '"._('Network Card Brands/Types : This is a list of some possible network cards and their speed.')."')";

        foreach (array('locations', 'type', 'os', 'ram', 'processor',
'iface', 'network') as $lookup)
        {
                $oldtable = 'dropdown_' . $lookup;
                $result = $DB->getAll("SELECT name FROM $oldtable");
                if (!MDB::isError($result) && count($result))
                {
                        $qrylist[] = "INSERT INTO lookup_data VALUES ";
                        $qrylistPos = count($qrylist) - 1;
                        foreach ($result as $value)
                        {
                                $qrylist[$qrylistPos] .= "('$lookup',
                                '".$value['name']."'),";
                        }
                        $qrylist[$qrylistPos] =
                        substr($qrylist[$qrylistPos], 0, -1);
                }
        }

	$qrylist[] = "DROP TABLE dropdown_locations, dropdown_type, dropdown_os, dropdown_ram, dropdown_processor, dropdown_iface, dropdown_network";
		
	return $DB->BulkQueries($qrylist);
}
