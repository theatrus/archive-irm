DROP DATABASE test;
CREATE DATABASE test;

use test;

CREATE TABLE comp_group ( comp_id int(11) DEFAULT '0' NOT NULL, group_id int(11) DEFAULT '0' NOT NULL, KEY lab_id (group_id));

INSERT INTO comp_group VALUES (1,1);
	
CREATE TABLE computers ( ID int(11) NOT NULL auto_increment, name varchar(200), type varchar(100), flags_server tinyint(4) DEFAULT '0' NOT NULL, os varchar(100), osver varchar(20), processor varchar(30), processor_speed varchar(30), location varchar(200) DEFAULT '' NOT NULL, serial varchar(200) DEFAULT '' NOT NULL, otherserial varchar(200) DEFAULT '' NOT NULL, ramtype varchar(200) DEFAULT '' NOT NULL, ram varchar(6) DEFAULT '' NOT NULL, network varchar(200) DEFAULT '' NOT NULL, ip varchar(20), mac varchar(30), hdspace varchar(6), contact varchar(90), contact_num varchar(90), comments text NOT NULL, date_mod datetime, PRIMARY KEY (ID), KEY location (location), KEY flags (flags_server));

INSERT INTO computers VALUES (1,'Ants','iMac DV',0,'Mac OS','9.0.4','PowerPC G3','400','Library Back Room','','','SDRAM DIMMs (<10ns)','192','Generic 100Mbps Card','DHCP','00 50 E4','13','','','','2000-05-03 17:50:08');
	
CREATE TABLE dropdown_iface ( name char(30));
	
INSERT INTO dropdown_iface VALUES ('10Mbps Ethernet (UTP)');
INSERT INTO dropdown_iface VALUES ('100Mbps Ethernet (UTP)');
INSERT INTO dropdown_iface VALUES ('100Base FL');
INSERT INTO dropdown_iface VALUES ('100Mbps FDDI');
INSERT INTO dropdown_iface VALUES ('Frame Relay');
INSERT INTO dropdown_iface VALUES ('ISDN');
INSERT INTO dropdown_iface VALUES ('T1/E1 +');
INSERT INTO dropdown_iface VALUES ('Serial Link');
	
CREATE TABLE dropdown_locations (name char(200));
		
INSERT INTO dropdown_locations VALUES ('Library Back Room');
INSERT INTO dropdown_locations VALUES ('Room 34');
INSERT INTO dropdown_locations VALUES ('MAOS Lab');
INSERT INTO dropdown_locations VALUES ('Office');
		
		
CREATE TABLE dropdown_network ( name char(200));
		
INSERT INTO dropdown_network VALUES ('3Com (100Mbps)');
INSERT INTO dropdown_network VALUES ('3Com (10Mbps)');
INSERT INTO dropdown_network VALUES ('Intel (100Mbps)');
INSERT INTO dropdown_network VALUES ('Intel (10Mbps)');
INSERT INTO dropdown_network VALUES ('Generic 100Mbps Card');
INSERT INTO dropdown_network VALUES ('Generic 10Mbps Card');
INSERT INTO dropdown_network VALUES ('None');
INSERT INTO dropdown_network VALUES ('AMD 10Mbps');
INSERT INTO dropdown_network VALUES ('Realtek 10Mbps');
INSERT INTO dropdown_network VALUES ('Realtek 100Mbps');
		
CREATE TABLE dropdown_os (name char(100));
INSERT INTO dropdown_os VALUES ('Linux (Debian)');
INSERT INTO dropdown_os VALUES ('Linux (RedHat)');
INSERT INTO dropdown_os VALUES ('Linux (Caldera)');
INSERT INTO dropdown_os VALUES ('DOS');
INSERT INTO dropdown_os VALUES ('FreeBSD');
INSERT INTO dropdown_os VALUES ('Linux (Other)');
INSERT INTO dropdown_os VALUES ('Windows');
INSERT INTO dropdown_os VALUES ('Other');
INSERT INTO dropdown_os VALUES ('Mac OS');
INSERT INTO dropdown_os VALUES ('IRIX');
INSERT INTO dropdown_os VALUES ('Solaris');
INSERT INTO dropdown_os VALUES ('Max OS X');
		
CREATE TABLE dropdown_processor ( name varchar(30));
INSERT INTO dropdown_processor VALUES ('Intel Pentium');
INSERT INTO dropdown_processor VALUES ('Intel Pentium II');
INSERT INTO dropdown_processor VALUES ('AMD K6-1');
INSERT INTO dropdown_processor VALUES ('AMD K6-2');
INSERT INTO dropdown_processor VALUES ('AMD K6-3');
INSERT INTO dropdown_processor VALUES ('PowerPC G3');
INSERT INTO dropdown_processor VALUES ('Intel Pentium III');
INSERT INTO dropdown_processor VALUES ('AMD Athlon');
INSERT INTO dropdown_processor VALUES ('68k (Motorola)');
INSERT INTO dropdown_processor VALUES ('486 SX');
INSERT INTO dropdown_processor VALUES ('486 DX');
INSERT INTO dropdown_processor VALUES ('486 DX2/4');
INSERT INTO dropdown_processor VALUES ('Intel Itanium');
INSERT INTO dropdown_processor VALUES ('PowerPC G4');
INSERT INTO dropdown_processor VALUES ('RS3000');
INSERT INTO dropdown_processor VALUES ('RS10k');
INSERT INTO dropdown_processor VALUES ('Alpha EV6.7');
INSERT INTO dropdown_processor VALUES ('PowerPC 603ev');
INSERT INTO dropdown_processor VALUES ('PowerPC 603');
INSERT INTO dropdown_processor VALUES ('PowerPC 601');
INSERT INTO dropdown_processor VALUES ('68040');
INSERT INTO dropdown_processor VALUES ('68040');
		
CREATE TABLE dropdown_ram ( name char(200));
INSERT INTO dropdown_ram VALUES ('36pin SIMMS');
INSERT INTO dropdown_ram VALUES ('72pin SIMMS (Fast Page)');
INSERT INTO dropdown_ram VALUES ('72pin SIMMS (EDO)');
INSERT INTO dropdown_ram VALUES ('Unbuffered DIMMs');
INSERT INTO dropdown_ram VALUES ('DIMMs w/EEPROM');
INSERT INTO dropdown_ram VALUES ('SDRAM DIMMs (<10ns)');
INSERT INTO dropdown_ram VALUES ('ECC DIMMs');
INSERT INTO dropdown_ram VALUES ('Other');
INSERT INTO dropdown_ram VALUES ('iMac DIMMS');
		
CREATE TABLE dropdown_type ( name char(200));
INSERT INTO dropdown_type VALUES ('Generic PC');
INSERT INTO dropdown_type VALUES ('Macintosh PPC (other)');
INSERT INTO dropdown_type VALUES ('Macintosh 68K (68030)');
INSERT INTO dropdown_type VALUES ('Macintosh 68K (68040)');
INSERT INTO dropdown_type VALUES ('Other');
INSERT INTO dropdown_type VALUES ('IBM RS/6000');
INSERT INTO dropdown_type VALUES ('Indy');
INSERT INTO dropdown_type VALUES ('Octane');
INSERT INTO dropdown_type VALUES ('O2');
INSERT INTO dropdown_type VALUES ('Onyx 2');
INSERT INTO dropdown_type VALUES ('iMac');
INSERT INTO dropdown_type VALUES ('iMac DV');
INSERT INTO dropdown_type VALUES ('Blue and White G3');
INSERT INTO dropdown_type VALUES ('G4');
INSERT INTO dropdown_type VALUES ('Homebrew');
INSERT INTO dropdown_type VALUES ('Cisco Catalyst 2900 XL');
INSERT INTO dropdown_type VALUES ('Cisco 2600');
INSERT INTO dropdown_type VALUES ('3Com AccessBuilder');
INSERT INTO dropdown_type VALUES ('3Com LinkBuilder Hub');
INSERT INTO dropdown_type VALUES ('Cisco 400-series Fast Hub');
		
CREATE TABLE event_log (ID int(11) NOT NULL auto_increment, item int(11) DEFAULT '0' NOT NULL, itemtype varchar(10) DEFAULT '' NOT NULL, date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, service varchar(20), level tinyint(4) DEFAULT '0' NOT NULL, message text NOT NULL, PRIMARY KEY (ID), KEY comp (item), KEY date (date));
		
CREATE TABLE followups ( ID int(11) NOT NULL auto_increment, tracking int(11), date datetime, author varchar(200), contents text, PRIMARY KEY (ID));
		
CREATE TABLE groups ( ID int(11) NOT NULL auto_increment, name varchar(200) DEFAULT '' NOT NULL, PRIMARY KEY (ID), KEY ID (ID), UNIQUE ID_2 (ID));
INSERT INTO groups VALUES (1,'test');
		
CREATE TABLE inst_software ( ID int(11) NOT NULL auto_increment, cID int(11) DEFAULT '0' NOT NULL, sID int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (ID), KEY cID (cID), KEY sID (sID));
		
CREATE TABLE networking ( ID int(11) NOT NULL auto_increment, name varchar(30) DEFAULT '' NOT NULL, type varchar(30) DEFAULT '' NOT NULL, ram varchar(10), ip varchar(20) DEFAULT '' NOT NULL, mac varchar(30), location varchar(40) DEFAULT '' NOT NULL, serial varchar(50), otherserial varchar(50), contact varchar(30) DEFAULT '' NOT NULL, contact_num varchar(30) DEFAULT '' NOT NULL, datemod datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, comments text NOT NULL, PRIMARY KEY (ID));
		
CREATE TABLE networking_ports ( ID int(11) NOT NULL auto_increment, iface char(40) DEFAULT '' NOT NULL, ifaddr char(30) DEFAULT '' NOT NULL, ifmac char(30) DEFAULT '0' NOT NULL, logical_number int(11) DEFAULT '0' NOT NULL, name char(30) DEFAULT '' NOT NULL, PRIMARY KEY (ID));
		
		
INSERT INTO networking_ports VALUES (1,'100Mbps Ethernet (UTP)','DHCP','00 50 E4',1,'Port 1');
		
		
CREATE TABLE networking_wire ( ID int(11) NOT NULL auto_increment, end1 int(11) DEFAULT '0' NOT NULL, end2 int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (ID));
		
CREATE TABLE prefs ( user varchar(80) DEFAULT '' NOT NULL, type enum('no','yes') DEFAULT 'no' NOT NULL, os enum('no','yes') DEFAULT 'no' NOT NULL, osver enum('no','yes') DEFAULT 'no' NOT NULL, processor enum('no','yes') DEFAULT 'no' NOT NULL, processor_speed enum('no','yes') DEFAULT 'no' NOT NULL, location enum('no','yes') DEFAULT 'no' NOT NULL, serial enum('no','yes') DEFAULT 'no' NOT NULL, otherserial enum('no','yes') DEFAULT 'no' NOT NULL, ramtype enum('no','yes') DEFAULT 'no' NOT NULL, ram enum('no','yes') DEFAULT 'no' NOT NULL, network enum('no','yes') DEFAULT 'no' NOT NULL, ip enum('no','yes') DEFAULT 'no' NOT NULL, mac enum('no','yes') DEFAULT 'no' NOT NULL, hdspace enum('no','yes') DEFAULT 'no' NOT NULL, contact enum('no','yes') DEFAULT 'no' NOT NULL, contact_num enum('no','yes') DEFAULT 'no' NOT NULL, comments enum('no','yes') DEFAULT 'no' NOT NULL, date_mod enum('no','yes') DEFAULT 'no' NOT NULL, advanced_tracking enum('no','yes') DEFAULT 'no' NOT NULL, tracking_order enum('no','yes') DEFAULT 'no' NOT NULL, PRIMARY KEY (user));
		
		
INSERT INTO prefs VALUES ('Admin','yes','','','','','','','','','','','','','','','','','','yes','');
		
CREATE TABLE software ( ID int(11) NOT NULL auto_increment, name varchar(200), platform varchar(200), version varchar(20), serial varchar(200), otherserial varchar(200), location varchar(200), license int(11), comments text, PRIMARY KEY (ID));
		
		
INSERT INTO software VALUES (1,'Test Software','FreeBSD','1','313213213','21321321321','Library Back Room',30,'32132132132');
INSERT INTO software VALUES (2,'Word Processing Software','Mac OS','3.4','','','Library Back Room',50,'');
INSERT INTO software VALUES (3,'BlackWidowExplorer','DOS','33','','','Room 34',10,'');
		
CREATE TABLE templ_inst_software ( ID int(11) NOT NULL auto_increment, cID int(11) DEFAULT '0' NOT NULL, sID int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (ID), KEY cID (cID), KEY sID (sID));
		
		
INSERT INTO templ_inst_software VALUES (1,2,3);
INSERT INTO templ_inst_software VALUES (2,2,2);
		
CREATE TABLE templates ( ID int(11) NOT NULL auto_increment, templname varchar(200), name varchar(200), type varchar(200), flags_server tinyint(4) DEFAULT '0' NOT NULL, os varchar(200), osver varchar(20), processor varchar(200), processor_speed varchar(100), location varchar(200), serial varchar(200), otherserial varchar(200), ramtype varchar(200), ram varchar(20), network varchar(200), ip varchar(20), mac varchar(40), hdspace varchar(10), contact varchar(200), contact_num varchar(200), comments text, iface varchar(100), PRIMARY KEY (ID));
	
		
INSERT INTO templates VALUES (1,'Blank Template','','Generic PC',0,'Windows','','Intel Pentium','','Library Back Room','','','72pin SIMMS (EDO)','','Generic 10Mbps Card','','','','','','',NULL);
INSERT INTO templates VALUES (2,'Mac G3 All-in-One','','Macintosh PPC (other)',1,'Mac OS','8.1','486 DX','266','MAOS Lab','','','SDRAM DIMMs (<10ns)','96','Generic 100Mbps Card','205.155.38','','3','','','','100Mbps Ethernet (UTP)');
INSERT INTO templates VALUES (3,'iMac','','iMac',0,'Mac OS','8.6','PowerPC G3','333','Library Back Room','','','iMac DIMMS','32','Generic 100Mbps Card','','00 50 E4','6','','','','100Mbps Ethernet (UTP)');
INSERT INTO templates VALUES (4,'iMac DV','','iMac DV',0,'Mac OS','9.0.4','PowerPC G3','400','Library Back Room','','','SDRAM DIMMs (<10ns)','64','Generic 100Mbps Card','','','13','','','','100Mbps Ethernet (UTP)');

CREATE TABLE tracking ( ID int(11) NOT NULL auto_increment, date datetime, closedate datetime, status enum('new','old'), author varchar(200), assign varchar(200), computer int(11), contents text, priority tinyint(4) DEFAULT '1' NOT NULL, is_group enum('no','yes') DEFAULT 'no' NOT NULL, uemail varchar(100), emailupdates varchar(4), PRIMARY KEY (ID));

CREATE TABLE users ( name varchar(80) DEFAULT '' NOT NULL, password varchar(80), email varchar(80), location varchar(100), phone varchar(100), type enum('normal','admin','post-only') DEFAULT 'normal' NOT NULL, comments text, PRIMARY KEY (name), KEY type (type));
		
		
INSERT INTO users VALUES ('Admin','admin','root@localhost','Admin Place (in front of the computer)','Use e-mail','admin','');
INSERT INTO users VALUES ('Guest','','','','','post-only','');
INSERT INTO users VALUES ('Normal User','normal','user@localhost','','','normal','');

CREATE TABLE version (number varchar(80) DEFAULT '' NOT NULL, build varchar(80), PRIMARY KEY (number));

INSERT INTO version values('1.3.0', '20001215');
