<?php

require_once dirname(__FILE__) . '/../lib/Config.php';

$adminpassword = "admin";
$INSTALL = array();
$SAMPLEDATA = array();

$INSTALL[] = "CREATE TABLE comp_group (
	comp_id int(11) DEFAULT '0' NOT NULL,
	group_id int(11) DEFAULT '0' NOT NULL,
	KEY lab_id (group_id))";

$SAMPLEDATA[] = "INSERT INTO comp_group VALUES (1,1)";

$INSTALL[] = "CREATE TABLE computers__ID (
	sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (sequence))";
	
$INSTALL[] = "CREATE TABLE computers (
	ID BIGINT UNSIGNED NOT NULL,
	name varchar(200),
	type varchar(100),
	flags_server tinyint(4) DEFAULT '0' NOT NULL,
	flags_surplus tinyint(4) DEFAULT '0' NOT NULL,
	os varchar(100),
	osver varchar(20),
	processor varchar(30),
	processor_speed varchar(30),
	location varchar(200) DEFAULT '' NOT NULL,
	serial varchar(200) DEFAULT '' NOT NULL,
	otherserial varchar(200) DEFAULT '' NOT NULL,
	ramtype varchar(200) DEFAULT '' NOT NULL,
	ram varchar(6) DEFAULT '' NOT NULL,
	network varchar(200) DEFAULT '' NOT NULL,
	ip varchar(20),
	mac varchar(30),
	hdspace varchar(6),
	contact varchar(90),
	contact_num varchar(90),
	comments text NOT NULL,
	date_mod datetime,
	PRIMARY KEY (ID),
	KEY location (location),
	KEY flags (flags_server))";

$SAMPLEDATA[] = "INSERT INTO computers 
		(ID, name, type, os, osver, processor, processor_speed,
		location, ramtype, ram,
		network, ip, mac, hdspace)
		VALUES (1,'Ants','iMac DV','Mac OS','9.0.4','PowerPC G3',
		'400','Library Back Room','SDRAM DIMMs (<10ns)','192',
		'Generic 100Mbps Card','DHCP','00 50 E4','13')";
$SAMPLEDATA[] = "INSERT INTO computers__ID (sequence) VALUES (1)";
	
$INSTALL[] = "CREATE TABLE devices ( name char(200))";

$INSTALL[] = "CREATE TABLE event_log (
	ID int(11) NOT NULL auto_increment,
	item int(11) DEFAULT '0' NOT NULL,
	itemtype varchar(10) DEFAULT '' NOT NULL,
	date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	service varchar(20),
	level tinyint(4) DEFAULT '0' NOT NULL,
	message text NOT NULL,
	PRIMARY KEY (ID),
	KEY comp (item),
	KEY date (date))";
		
$INSTALL[] = "CREATE TABLE followups (
	ID int(11) NOT NULL auto_increment,
	tracking int(11),
	date datetime,
	author varchar(200),
	contents text,
	minspent int(11) NOT NULL default 0,
	public tinyint NOT NULL default 1,
	PRIMARY KEY (ID))";
		
$INSTALL[] = "CREATE TABLE groups (
	ID int(11) NOT NULL auto_increment,
	name varchar(200) DEFAULT '' NOT NULL,
	PRIMARY KEY (ID),
	KEY ID (ID),
	UNIQUE ID_2 (ID))";

$SAMPLEDATA[] = "INSERT INTO groups VALUES (1,'test')";
		
$INSTALL[] = "CREATE TABLE inst_software (
	ID int(11) NOT NULL auto_increment,
	cID int(11) DEFAULT '0' NOT NULL,
	sID int(11) DEFAULT '0' NOT NULL,
	lID int(11) NOT NULL default '0',
	gID int(11),
	lCnt int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY (ID),
	KEY cID (cID),
	KEY sID (sID),
	KEY lID (lID))";

$INSTALL[] = "CREATE TABLE lookups(
		id varchar(50) NOT NULL,
		name varchar(50) NOT NULL,
		description varchar(255),
		PRIMARY KEY(id))";

$INSTALL[] = "INSERT INTO lookups VALUES
		('locations', '"._('Location')."', '"._('Locations : Use this to edit where equipment can be stored')."'),
		('type', '"._('Type')."', '"._('Computer Types :These list the types of computers you can have (i.e. Dell, HP, IBM RS/6000, etc.)')."'),
		('os', '"._('OS')."', '"._('Operating Systems : This is a list of Operating Systems your computers can run.')."'),
		('ram', '"._('RAM Type')."', '"._('RAM Types : This is the types of RAM your systems can have (i.e. Unbuffered DIMMS, SDRAM DIMMS, ECC DIMMS, etc)')."'),
		('processor', '"._('Processor')."', '"._('Processor Types : This is a list of valid processors, i.e. Intel Pentium, Pentium II, DEC Alpha, EverSlow WinChip, etc.')."'),
		('iface', '"._('Network Interface')."', '"._('Network Interfaces')."'),
		('network', '"._('Network Card Type/Brand')."', '"._('Network Card Brands/Types : This is a list of some possible network cards and their speed.')."')";

$INSTALL[] = "CREATE TABLE lookup_data(
		lookup varchar(50) NOT NULL,
		value varchar(255) NOT NULL,
		INDEX(lookup))";

$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','10Mbps Ethernet (UTP)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','100Mbps Ethernet (UTP)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','100Base FL')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','100Mbps FDDI')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','Frame Relay')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','ISDN')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','T1/E1 +')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('iface','Serial Link')";
	
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('locations','Library Back Room')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('locations','Room 34')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('locations','MAOS Lab')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('locations','Office')";
		
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','3Com (100Mbps)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','3Com (10Mbps)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Intel (100Mbps)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Intel (10Mbps)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Generic 100Mbps Card')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Generic 10Mbps Card')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','None')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','AMD 10Mbps')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Realtek 10Mbps')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('network','Realtek 100Mbps')";
		
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Linux (Debian)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Linux (RedHat)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Linux (Caldera)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','DOS')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','FreeBSD')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Linux (Other)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Windows')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Other')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Mac OS')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','IRIX')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Solaris')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('os','Max OS X')";
		
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','Intel Pentium')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','Intel Pentium II')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','AMD K6-1')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','AMD K6-2')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','AMD K6-3')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','PowerPC G3')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','Intel Pentium III')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','AMD Athlon')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','68k (Motorola)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','486 SX')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','486 DX')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','486 DX2/4')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','Intel Itanium')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','PowerPC G4')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','RS3000')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','RS10k')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','Alpha EV6.7')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','PowerPC 603ev')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','PowerPC 603')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','PowerPC 601')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','68040')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('processor','68040')";
		
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','36pin SIMMS')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','72pin SIMMS (Fast Page)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','72pin SIMMS (EDO)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','Unbuffered DIMMs')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','DIMMs w/EEPROM')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','SDRAM DIMMs (<10ns)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','ECC DIMMs')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','Other')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('ram','iMac DIMMS')";
		
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Generic PC')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Macintosh PPC (other)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Macintosh 68K (68030)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Macintosh 68K (68040)')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Other')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','IBM RS/6000')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Indy')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Octane')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','O2')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Onyx 2')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','iMac')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','iMac DV')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Blue and White G3')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','G4')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Homebrew')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Cisco Catalyst 2900 XL')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Cisco 2600')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','3Com AccessBuilder')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','3Com LinkBuilder Hub')";
$SAMPLEDATA[] = "INSERT INTO lookup_data VALUES ('type','Cisco 400-series Fast Hub')";

$INSTALL[] = "CREATE TABLE networking__ID (
	sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (sequence))";

$INSTALL[] = "CREATE TABLE networking (
	ID BIGINT UNSIGNED NOT NULL,
	name varchar(30) DEFAULT '' NOT NULL,
	type varchar(30) DEFAULT '' NOT NULL,
	ram varchar(10),
	ip varchar(20) DEFAULT '' NOT NULL,
	mac varchar(30),
	location varchar(40) DEFAULT '' NOT NULL,
	serial varchar(50),
	otherserial varchar(50),
	contact varchar(30) DEFAULT '' NOT NULL,
	contact_num varchar(30) DEFAULT '' NOT NULL,
	datemod datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	comments text NOT NULL,
	PRIMARY KEY (ID))";
		
$INSTALL[] = "CREATE TABLE networking_ports (
	ID int(11) NOT NULL auto_increment,
	device_on int(11) DEFAULT '0' NOT NULL,
	device_type varchar(200) DEFAULT '' NOT NULL,
	iface char(40) DEFAULT '' NOT NULL,
	ifaddr char(30) DEFAULT '' NOT NULL,
	ifmac char(30) DEFAULT '0' NOT NULL,
	logical_number int(11) DEFAULT '0' NOT NULL,
	name char(30) DEFAULT '' NOT NULL,
	PRIMARY KEY (ID))";
		
$SAMPLEDATA[] = "INSERT INTO networking_ports
	(device_on, device_type, iface, ifaddr, ifmac, logical_number, name)
	VALUES (1,1,'100Mbps Ethernet (UTP)','DHCP','00 50 E4',1,'Port 1')";
		
$INSTALL[] = "CREATE TABLE networking_wire (
	ID int(11) NOT NULL auto_increment,
	end1 int(11) DEFAULT '0' NOT NULL,
	end2 int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ID))";
		
$INSTALL[] = "CREATE TABLE prefs (
	user varchar(80) DEFAULT '' NOT NULL,
	type enum('no','yes') DEFAULT 'no' NOT NULL,
	os enum('no','yes') DEFAULT 'no' NOT NULL,
	osver enum('no','yes') DEFAULT 'no' NOT NULL,
	processor enum('no','yes') DEFAULT 'no' NOT NULL,
	processor_speed enum('no','yes') DEFAULT 'no' NOT NULL,
	location enum('no','yes') DEFAULT 'no' NOT NULL,
	serial enum('no','yes') DEFAULT 'no' NOT NULL,
	otherserial enum('no','yes') DEFAULT 'no' NOT NULL,
	ramtype enum('no','yes') DEFAULT 'no' NOT NULL,
	ram enum('no','yes') DEFAULT 'no' NOT NULL,
	network enum('no','yes') DEFAULT 'no' NOT NULL,
	ip enum('no','yes') DEFAULT 'no' NOT NULL,
	mac enum('no','yes') DEFAULT 'no' NOT NULL,
	hdspace enum('no','yes') DEFAULT 'no' NOT NULL,
	contact enum('no','yes') DEFAULT 'no' NOT NULL,
	contact_num enum('no','yes') DEFAULT 'no' NOT NULL,
	comments enum('no','yes') DEFAULT 'no' NOT NULL,
	date_mod enum('no','yes') DEFAULT 'no' NOT NULL,
	advanced_tracking enum('no','yes') DEFAULT 'no' NOT NULL,
	tracking_order enum('no','yes') DEFAULT 'no' NOT NULL,
	PRIMARY KEY (user))";
		
$SAMPLEDATA[] = "INSERT INTO prefs
		(user, type, advanced_tracking)
		VALUES ('Admin','yes','yes')";
		
$INSTALL[] = "CREATE TABLE software (
	ID int(11) NOT NULL auto_increment,
	name varchar(200),
	platform varchar(200),
	install_package varchar(255),
	class enum('Operating System',
			'Application',
			'CAL',
			'Application Bundle',
			'Server')
		default 'Application',
	comments text,
	PRIMARY KEY (ID)
)";
		
$SAMPLEDATA[] = "INSERT INTO software
	(name, platform, class,
	 comments)
	VALUES
	('Test Software','FreeBSD','Application',
	 'This one is in the Back Room')";
$SAMPLEDATA[] = "INSERT INTO software
	(name, platform, class,
	 comments)
	VALUES
	('Windows 95','Mac OS','Operating System',
	 'This one is in the Back Room')";
$SAMPLEDATA[] = "INSERT INTO software
	(name, platform, class,
	 comments)
	VALUES
	('BlackWidowExplorer','DOS', 'Application',
	 'Comment Here')";
		
$INSTALL[] = "CREATE TABLE templ_inst_software (
	ID int(11) NOT NULL auto_increment,
	cID int(11) DEFAULT '0' NOT NULL,
	sID int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ID),
	KEY cID (cID),
	KEY sID (sID))";
		
$SAMPLEDATA[] = "INSERT INTO templ_inst_software VALUES (1,2,3)";
$SAMPLEDATA[] = "INSERT INTO templ_inst_software VALUES (2,2,2)";
		
$INSTALL[] = "CREATE TABLE templates (
	ID int(11) NOT NULL auto_increment,
	templname varchar(200),
	name varchar(200),
	type varchar(200),
	flags_server tinyint(4) DEFAULT '0' NOT NULL,
	os varchar(200),
	osver varchar(20),
	processor varchar(200),
	processor_speed varchar(100),
	location varchar(200),
	serial varchar(200),
	otherserial varchar(200),
	ramtype varchar(200),
	ram varchar(20),
	network varchar(200),
	ip varchar(20),
	mac varchar(40),
	hdspace varchar(10),
	contact varchar(200),
	contact_num varchar(200),
	comments text,
	iface varchar(100),
	flags_surplus TINYINT(4) default NULL,
	PRIMARY KEY (ID))";
	
$INSTALL[] = "INSERT INTO templates
	(templname)
	VALUES
	('Blank Template')";
$SAMPLEDATA[] = "INSERT INTO templates
	(templname, type, flags_server, os, osver, processor,
	 processor_speed, location, ramtype, ram, network,
	 ip,hdspace,iface)
	VALUES
	('Mac G3 All-in-One','Macintosh PPC (other)',1,'Mac OS','8.1','486 DX',
	'266','MAOS Lab','SDRAM DIMMs (<10ns)','96','Generic 100Mbps Card',
	'205.155.38','3','100Mbps Ethernet (UTP)')";
$SAMPLEDATA[] = "INSERT INTO templates
	(templname, type, os, osver, processor, processor_speed, location,
	 ramtype, ram, network, mac, hdspace,
	 iface)
	VALUES
	('iMac','iMac','Mac OS','8.6','PowerPC G3','333','Library Back Room',
	 'iMac DIMMS','32','Generic 100Mbps Card','00 50 E4','6',
	 '100Mbps Ethernet (UTP)')";
$SAMPLEDATA[] = "INSERT INTO templates
	(templname, type, os, osver, processor, processor_speed,
	 location, ramtype, ram, network,
	 hdspace, iface)
	VALUES
	('iMac DV','iMac DV','Mac OS','9.0.4','PowerPC G3','400',
	 'Library Back Room','SDRAM DIMMs (<10ns)','64','Generic 100Mbps Card',
	 '13','100Mbps Ethernet (UTP)')";

$INSTALL[] = "CREATE TABLE tracking__ID (
	sequence BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (sequence))";
	
$INSTALL[] = "CREATE TABLE tracking_status (
	status		VARCHAR(255) NOT NULL,
	closed		TINYINT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (status)) TYPE=InnoDB";

$INSTALL[] = "INSERT INTO tracking_status (status, closed) VALUES
		('new', 0),
		('old', 1),
		('wait', 0),
		('assigned', 0),
		('active', 0),
		('complete', 1),
		('duplicate', 1)";

$INSTALL[] = "CREATE TABLE tracking (
	ID BIGINT UNSIGNED NOT NULL,
	date datetime,
	closedate datetime,
	status		VARCHAR(255) NOT NULL DEFAULT 'new',
	author varchar(200),
	assign varchar(200),
	computer int(11),
	contents text,
	priority tinyint(4) DEFAULT '1' NOT NULL,
	is_group enum('no','yes') DEFAULT 'no' NOT NULL,
	uemail varchar(100),
	emailupdates varchar(4),
	other_emails TEXT,
	INDEX (status),
	device varchar (200),
	FOREIGN KEY (status) REFERENCES tracking_status (status),
	PRIMARY KEY (ID)) TYPE=InnoDB";

$INSTALL[] = "CREATE TABLE users (
	name char(255) DEFAULT '' NOT NULL,
	password char(255),
	fullname char(200),
	email char(100),
	location char(200),
	phone char(100),
	type enum('post-only',
		  'normal',
		  'tech',
		  'admin') DEFAULT 'post-only' NOT NULL,
	comments text,
	PRIMARY KEY(name),
	KEY (type))";
		
$INSTALL[] = "INSERT INTO users
	(name, password, fullname, email,
	 location, phone, type)
	VALUES
	('Admin',md5('$adminpassword'),'Administrator Guy','root@localhost',
	 'Admin Place (in front of the computer)','Use e-mail','admin')";
$INSTALL[] = "INSERT INTO users
	(name, password, fullname, type)
	VALUES
	('Guest',md5('guest'),'Guest User','post-only')";
$SAMPLEDATA[] = "INSERT INTO users
	(name, password, fullname, email, type)
	VALUES
	('Normal',md5('normal'),'Normal User','user@localhost','normal')";
$INSTALL[] = "INSERT INTO users
	(name, password, fullname, email, type)
	VALUES
	('Tech',md5('tech'),'Technician User','tech@localhost','tech')";
$INSTALL[] = "INSERT INTO users
	(name, password, fullname, type)
	VALUES
	('IRMConnect',md5('irmconnect'),'Special account','post-only')";

$INSTALL[] = "CREATE TABLE config (
	variable	VARCHAR(255) NOT NULL,
	value		TEXT NOT NULL,
	PRIMARY KEY (variable))";

	
$DBVER = '1.5.8';
$INSTALL[] = "INSERT INTO config (variable, value) VALUES ('dbver', '$DBVER')";
unset($DBVER);

$INSTALL[] = "CREATE TABLE software_bundles (
	bID int(11) unsigned DEFAULT '0' NOT NULL,
	sID int(11) unsigned DEFAULT '0' NOT NULL,
	KEY sID_ndx (sID),
	KEY bID_ndx (bID),
	PRIMARY KEY (sID,bID))";

$INSTALL[] = "CREATE TABLE software_licenses (
	sID int(11) NOT NULL,
	licensekey varchar(200),
	entitlement int(11) DEFAULT '0' NOT NULL,
	ID int(11) NOT NULL auto_increment,
	oem_sticker enum ('Yes', 'No') DEFAULT 'No' NOT NULL,
	PRIMARY KEY (ID),
	KEY sID_ndx (sID),
	KEY ID_ndx (ID))";

$INSTALL[] = "CREATE TABLE kbarticles (
	ID int(11) NOT NULL auto_increment,
	categoryID int(11) NOT NULL default '0',
	question text NOT NULL,
	answer text NOT NULL,
	faq enum('yes','no') NOT NULL default 'no',
	PRIMARY KEY (ID),
	KEY ID (ID))";

$INSTALL[] = "CREATE TABLE kbcategories (
	ID int(11) NOT NULL auto_increment,
	parentID int(11) NOT NULL default '0',
	name text NOT NULL,
	PRIMARY KEY (ID),
	KEY ID (ID))";
	
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(1, 0, 'IRM')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(2, 1, 'Computers')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(3, 1, 'Networking')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(4, 1, 'Software')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(5, 1, 'Tracking')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(6, 1, 'Reports')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(7, 1, 'Request Help')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(8, 1, 'Setup')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(9, 1, 'Preferences')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(10, 1, 'Knowledge Base')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(11, 1, 'FAQ')";
$SAMPLEDATA[] = "INSERT INTO kbcategories
	(ID, parentID, name)
	VALUES
	(12, 1, 'Logout')";
	
$INSTALL[] = "CREATE TABLE fasttracktemplates (
	ID INT NOT NULL auto_increment,
	name char(100),
	priority int(11),
	request text,
	response text,
	PRIMARY KEY (ID))";
	
$SAMPLEDATA[] = "INSERT INTO fasttracktemplates
	(name, priority)
	VALUES
	('Default',3)";
$SAMPLEDATA[] = "INSERT INTO fasttracktemplates
	(name, priority, request,
	 response)
	VALUES ('Reset Password',3, 'User forgot password',
	 'Reset password on the system')";
$SAMPLEDATA[] = "INSERT INTO fasttracktemplates
	(name, priority,
	 request,
	 response)
	VALUES
	('Floppy Disk in Drive',3,
	 'Computer will not boot, it says something about NTLDR not found',
	 'There was a floppy disk in the drive, once user removed it and rebooted system it started up just fine.')";

$INSTALL[] = "CREATE TABLE files(
	ID int(11) NOT NULL auto_increment,
	filename varchar(200),
	device varchar(100),
	deviceid varchar(100),
	PRIMARY KEY (ID))";
