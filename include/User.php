<?php
################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2003 Yann Ramin
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License (in file COPYING) for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
################################################################################

require_once 'include/i18n.php';
require_once "include/user.db.inc.php";
require_once 'lib/Config.php';

class User
{
	var $driver; # the user driver - not used, didn't work, thrown away, see XIRM :)
	var $udata; # user data

	# Preferences Variables
	var $DisplayComputerType;
	var $DisplayOperatingSystem;
	var $DisplayOperatingSystemVersion;
	var $DisplayProcessor;
	var $DisplayProcessorSpeed;
	var $DisplayLocation;
	var $DisplaySerial;
	var $DisplayOtherSerial;
	var $DisplayRamType;
	var $DisplayRam;
	var $DisplayNetwork;
	var $DisplayIP;
	var $DisplayMachineAddress;
	var $DisplayHardDriveSize;
	var $DisplayContact;
	var $DisplayContactNumber;
	var $DisplayComments;
	var $DisplayDateMod;
	var $AdvancedTracking;
	var $TrackingOrder;

# maybe move the above to a hash?
# yeah, I think so!

	/** Constructor.
	 * Sets up the User object by loading from the DB if a username is
	 * given.
	 */
	function User($name = "")
	{
		if($name != "")
		{
			$this->setName($name);
			
			if(Config::UseLDAP()){
				$this->ldapAddUser($name);
			}

			$this->retrieve();
		}
	}
	
	function authenticate($u, $p) 
	{
		if(!($u) || !($p))
		{
			return false;
		}

		if(Config::UseLDAP())
		{

			$cfg = Config::LDAP();
			
			// Generate a DN from a uid
			$dn = User::LDAPFindUserDN($u);
			
			// Connect to ldap server
			$dsCon = ldap_connect($cfg['server']);

			// Make sure we connected
			if (!($dsCon))
			{
				__("Sorry, cannot contact LDAP server\n");
				return false;
			}

			ldap_set_option($dsCon, LDAP_OPT_TIMELIMIT, 1);
			ldap_set_option($dsCon, LDAP_OPT_PROTOCOL_VERSION, $cfg['protocol']);
			
			// Attempt to bind, if it works, the password is acceptable
			$bind = @ldap_bind($dsCon, $dn, $p);

			ldap_close($dsCon);
			if(!($bind))
			{
				return false;
			}
			else
			{
				$user = new User($u);
				return true;
			}
		}
		else
		{
			$DB = Config::Database();
			
			$qu = $DB->getTextValue($u);
			$qp = $DB->getTextValue(md5($p));

			$user = $DB->getOne("SELECT name FROM users WHERE name=$qu AND password=$qp");

			if ($user == $u)
			{
				return true;
			} else 
			{
				return false; # error!
			}
		}
	}

	function ldapAddUser($u)
	{
		if(!($this->exists($u)))
		{
			$ldapcfg = Config::LDAP();
			
			// Generate a DN from a uid
			$dn = User::LDAPFindUserDN($u);
			if (!$dn)
			{
				// User not found
				return false;
			}
			
			// Connect to ldap server
			$dsCon = ldap_connect($ldapcfg['server']);

			// Make sure we connected
			if (!($dsCon))
			{
				trigger_error(_("Sorry, cannot contact LDAP server"), E_USER_ERROR);
				die(__FILE__.":".__LINE__.": dying with fatal error\n");
				exit;
			}

			ldap_set_option($dsCon, LDAP_OPT_PROTOCOL_VERSION, $ldapcfg['protocol']);

			if (@$ldapcfg['binddn'] && $ldapcfg['bindpw'])
			{
				$bind = @ldap_bind($dsCon, $ldapcfg['binddn'], $ldapcfg['bindpw']);
				if (!$bind)
				{
					trigger_error(sprintf(_("LDAP bind failed for %s"),$ldapcfg['binddn']), E_USER_ERROR);
					die(__FILE__.":".__LINE__.": dying with fatal error\n");
				}
			}
			else
			{
				$bind = @ldap_bind($dsCon);
				if (!$bind)
				{
					trigger_error(_("Anonymous bind failed"), E_USER_ERROR);
					die(__FILE__.":".__LINE__.": dying with fatal error\n");
				}
			}

			$pieces[0] = $ldapcfg['emailfield'];
			$pieces[1] = $ldapcfg['fullnamefield'];
			$pieces[2] = $ldapcfg['phonefield'];
			$pieces[3] = $ldapcfg['locationfield'];
			$filter = $ldapcfg['usernamefield'].'='.$u;
			$searchResult = ldap_search($dsCon, $ldapcfg['rootdn'], $filter, $pieces);
			if (!$searchResult)
			{
				trigger_error("LDAP error searching ".$ldapcfg['rootdn']." for ".$filter, E_USER_WARNING);
			}

			$info = ldap_get_entries($dsCon, $searchResult);

			$fullname = @$info[0][$ldapcfg['fullnamefield']][0];
			$email = @$info[0][$ldapcfg['emailfield']][0];
			$officephone = @$info[0][$ldapcfg['phonefield']][0];
			$officelocation = @$info[0][$ldapcfg['locationfield']][0];

			$this->setName($u);
			$this->setEmail($email);
			$this->setFullname($fullname);
			$this->setLocation($officelocation);
			$this->setPhone($officephone);
			$this->setType("normal");
			$this->add(true);
		}
	}

	function LDAPFindUserDN($uid)
	{
		$cfg = Config::LDAP();
	
		$ds = ldap_connect($cfg['server']);
	
		if (!$ds)
		{
			trigger_error(_("Sorry, cannot contact LDAP server"), E_USER_ERROR);
			die(__FILE__.":".__LINE__.": dying with fatal error\n");
			exit;
		}

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $cfg['protocol']);

		if (@$cfg['binddn'] && $cfg['bindpw'])
		{
			$bind = @ldap_bind($ds, $cfg['binddn'], $cfg['bindpw']);
			if (!$bind)
			{
				trigger_error(sprintf(_("LDAP bind failed for %s"),$cfg['binddn']), E_USER_ERROR);
				die(__FILE__.":".__LINE__.": dying with fatal error\n");
			}
		}
		else
		{
			$bind = @ldap_bind($ds);
			if (!$bind)
			{
				trigger_error(_("Anonymous bind failed"), E_USER_ERROR);
		//		die(__FILE__.":".__LINE__.": dying with fatal error\n");
			}
		}

		$pieces = array();

		$filter = $cfg['usernamefield'].'='.$uid;
		$searchResult = ldap_search($ds, $cfg['rootdn'], $filter, $pieces);

		if (!$searchResult)
		{
			trigger_error("LDAP error searching ".$ldapcfg['rootdn']." for ".$filter, E_USER_WARNING);
		}

		$info = ldap_get_entries($ds, $searchResult);

		return @$info[0]['dn'];
	}
	
	function retrieve()
	{
		$DB = Config::Database();

		$name = $DB->getTextValue($this->udata['Name']);
		
		$result = $DB->getRow("SELECT name,fullname,email,location,phone,type,comments FROM users WHERE (name = $name)");

		// If there is no matching user in the database redirect back to the main index/login page
		if (count($result) == 0){
			header("Location: ".Config::AbsLoc("index.php"));
		 	exit();
		}

		$this->udata['Name'] = $result['name'];
		$this->udata['Fullname'] = $result['fullname'];
		$this->udata['Email'] = $result['email'];
		$this->udata['Location'] = $result['location'];
		$this->udata['Phone'] = $result['phone'];
		$this->udata['Type'] = $result['type'];
		$this->udata['Comments'] = $result['comments'];

		$result = $DB->getRow("SELECT * FROM prefs WHERE (user = $name)");
		$this->setDisplayComputerType($result["type"]);
		$this->setDisplayOperatingSystem($result["os"]);
		$this->setDisplayOperatingSystemVersion($result["osver"]);
		$this->setDisplayProcessor($result["processor"]);
		$this->setDisplayProcessorSpeed($result["processor_speed"]);
		$this->setDisplayLocation($result["location"]);
		$this->setDisplaySerial($result["serial"]);
		$this->setDisplayOtherSerial($result["otherserial"]);
		$this->setDisplayRamType($result["ramtype"]);
		$this->setDisplayRam($result["ram"]);
		$this->setDisplayNetwork($result["network"]);
		$this->setDisplayIP($result["ip"]);
		$this->setDisplayMachineAddress($result["mac"]);
		$this->setDisplayHardDriveSize($result["hdspace"]);
		$this->setDisplayContact($result["contact"]);
		$this->setDisplayContactNumber($result["contact_num"]);
		$this->setDisplayComments($result["comments"]);
		$this->setDisplayDateMod($result["date_mod"]);
		$this->setAdvancedTracking($result["advanced_tracking"]);
		$this->setTrackingOrder($result["tracking_order"]);
	}

	function add($isLDAP = false)
	{
		if($this->udata['Name'] == "")
		{
			__("Error adding user: ");
			__("username not set");
			PRINT "<BR>\n";
		}
		if(@$this->udata['Password'] == "" && ($isLDAP == false))
		{
			__("Error adding user: ");
			__("password not set");
			PRINT "<BR>\n";
		}
		if($this->udata['Fullname'] == "")
		{
			__("Error adding user: ");
			__("full name not set");
			PRINT "<BR>\n";
		}
		if($this->udata['Type'] == "")
		{
			__("Error adding user: type not set")."<BR>\n";
		}

		$DB = Config::Database();

		$vals = array(
			'name' => @$this->udata['Name'],
			'password' => @$this->udata['Password'],
			'fullname' => @$this->udata['Fullname'],
			'email' => @$this->udata['Email'],
			'location' => @$this->udata['Location'],
			'phone' => @$this->udata['Phone'],
			'type' => @$this->udata['Type'],
			'comments' => @$this->udata['Comments']
			);

		$DB->InsertQuery('users', $vals);

		$this->initPrefs();
	}

	function initPrefs()
	{
		$DB = Config::Database();

		$name = $DB->getTextValue($this->udata['Name']);
		$prefsexist = $DB->getOne("SELECT COUNT(*) FROM prefs WHERE (user = $name)");

		if (!$prefsexist)
		{
			$query = "INSERT INTO prefs VALUES ($name, 'yes',";
			$query .= " 'no','no', 'no', 'no', 'no', 'no', 'no', 'no', 'no',";
			$query .= " 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no',";
			$query .= " 'yes', 'no')"; 
			$DB->query($query);
		} 
	}

	function delete()
	{
		if($this->udata['Name'] == "")
		{
			__("Error deleting user: name not set")."<BR>\n";
		}

		$DB = Config::Database();
		$name = $DB->getTextValue($this->udata['Name']);
		
		$DB->query("DELETE FROM users WHERE (name = $name)");
		$DB->query("DELETE FROM prefs WHERE (user = $name)");
	}

	function commit()
	{
		if($this->udata['Name'] == "")
		{
			__("Error updating user: name not set")."<BR>\n";
			return (0);
		}

		$DB = Config::Database();

		$name = $DB->getTextValue($this->udata['Name']);
		$password = $DB->getTextValue($this->udata['Password']);
		$fullname = $DB->getTextValue($this->udata['Fullname']);
		$email = $DB->getTextValue(@$this->udata['Email']);
		$location = $DB->getTextValue(@$this->udata['Location']);
		$phone = $DB->getTextValue(@$this->udata['Phone']);
		$type = $DB->getTextValue($this->udata['Type']);
		$comments = $DB->getTextValue(@$this->udata['Comments']);

		$vals = array(
			'password' => @$this->udata['Password'],
			'fullname' => @$this->udata['Fullname'],
			'email' => @$this->udata['Email'],
			'location' => @$this->udata['Location'],
			'phone' => @$this->udata['Phone'],
			'type' => @$this->udata['Type'],
			'comments' => @$this->udata['Comments']
			);
		$qname = $DB->getTextValue($this->udata['Name']);
		$DB->UpdateQuery('users', $vals, "name=$qname");

		$vals = array(
			'type' => $this->DisplayComputerType,
			'os' => $this->DisplayOperatingSystem,
			'osver' => $this->DisplayOperatingSystemVersion,
			'processor' => $this->DisplayProcessor,
			'processor_speed' => $this->DisplayProcessorSpeed,
			'location' => $this->DisplayLocation,
			'serial' => $this->DisplaySerial,
			'otherserial' => $this->DisplayOtherSerial,
			'ramtype' => $this->DisplayRamType,
			'ram' => $this->DisplayRam,
			'network' => $this->DisplayNetwork, 
			'ip' => $this->DisplayIP,
			'mac' => $this->DisplayMachineAddress, 
			'hdspace' => $this->DisplayHardDriveSize,
			'contact' => $this->DisplayContact,
			'contact_num' => $this->DisplayContactNumber,
			'comments' => $this->DisplayComments,
			'date_mod' => $this->DisplayDateMod, 
			'advanced_tracking' => $this->AdvancedTracking,
			'tracking_order' => $this->TrackingOrder
			);
		$name = $DB->getTextValue($name);
		$DB->UpdateQuery('prefs', $vals, "user=$name");
	}

	function getName()
	{
		return($this->udata['Name']);
	}

	function getFullname()
	{
		return($this->udata['Fullname']);
	}

	function getEmail()
	{

		return($this->udata['Email']);
	}

	function getLocation()
	{
		return($this->udata['Location']);
	}

	function getPhone()
	{
		return($this->udata['Phone']);
	}

	function getType()
	{
		return($this->udata['Type']);
	}

	function getComments()
	{
		return($this->udata['Comments']);
	}

	function setName($name)
	{
		$this->udata['Name'] = $name;
	}

	function setPassword($pass)
	{	
		$this->udata['Password'] = md5($pass);
	}

	function setFullname($fname)
	{
		$this->udata['Fullname'] = $fname;
	}

	function setEmail($email)
	{
		$this->udata['Email'] = $email;
	}

	function setLocation($loc)
	{
		$this->udata['Location'] = $loc;
	}

	function setPhone($phone)
	{
		$this->udata['Phone'] = $phone;
	}

	function setType($type)
	{
		$this->udata['Type'] = $type;
	}

	function setComments($comment)
	{
		$this->udata['Comments'] = $comment;
	}

	function displayHeader()
	{
		PRINT "<table>\n";
		PRINT "<tr>";
		PRINT "<th colspan=5>" . _("Users") . "</th>";
		PRINT "</tr>\n";
	}

	function displayFooter()
	{
		PRINT "</table>\n";
	}

	function display()
	{
		$userbase = Config::AbsLoc('users');
		if($this->udata['Name'] == "")
		{
			PRINT '<tr class="setupdetail">';
			PRINT "<td>";
			__("Error displaying user: Name not set");
			PRINT "</td>\n";
			PRINT "</tr>\n";
		} else {
			$username_enc = str_replace(" ","%20", $this->udata['Name']);
			PRINT '<tr class="setupdetail">';
			PRINT "<td>" . $this->udata['Name'] . "</td>";
			PRINT "<td>" . "(".$this->udata['Fullname'].")" . "</td>";
			PRINT "<td>" .$this->udata['Type'] . "</td>";
			PRINT "<td><a href=\"$userbase/setup-user-update.php?username=$username_enc&update=edit\">[" . _("edit") . "]</a></td>";
			PRINT "<td><a href=\"$userbase/setup-user-update.php?update=delete&username=$username_enc\">[" . _("delete") . "]</a></td>\n";
			PRINT "</tr>\n";
		}
	}

	function displayLong()
	{
		$uExists = $this->exists($this->udata['Name']);
		if(!$uExists)
		{
			printf(_('User "%s" is no longer a registered user on this system'), $this->udata['Name']);
			echo "<br />\n";
			return(0);
		}
		PRINT "<table>\n";
		PRINT '<tr class="setupheader">';
		PRINT "<td colspan=2><strong>".$this->udata['Name']."</strong></td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("Name:") . "\n<br>" . $this->udata['Fullname']."</td>";
		PRINT "<td>&nbsp;</td>";
		PRINT "</tr>";
		
		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("E-mail:")."\n<br>" . $this->udata['Email']."</td>";
		PRINT "<td>" . _("Phone:") ."<br>\n" . $this->udata['Phone']."</td>";
		PRINT "</tr>";
		
		PRINT '<tr class="setupdetail">';
		PRINT "<td>" . _("Location:") . "\n" . $this->udata['Location']."</td>";
		PRINT "<td>" . _("User Type:"). "<br>\n" . $this->udata['Type']."</td>";
		PRINT "</tr>";
		PRINT "</table>";
		PRINT "<br>";
	}

	function AllUsers()
	{
		$DB = Config::Database();
		return $DB->getCol("SELECT name FROM users ORDER BY name DESC");
	}
	
	function displayAllUsers()
	{
		User::displayHeader();
		foreach (User::AllUsers() as $name)
		{
			$user = new User($name);
			$user->display();
		}

		User::displayFooter();
	}

	function exists($name)
	{
		$DB = Config::Database();
		$name = $DB->getTextValue($name);
		return $DB->getOne("SELECT COUNT(name) FROM users WHERE name=$name");
	}

	/** Verify if the user has the specified privilege level.
	 * Takes one of the following levels:
	 *  # admin
	 *  # tech
	 *  # normal
	 *  # port-only
	 *
	 * and returns true if the current user is of that level or higher,
	 * and false if the current user only has a level below that specified.
	 */
	function permissionCheck($priv)
	{
		$authlevels = array('post-only', 'normal', 'tech', 'admin');

		// First, get to the requested level in the level stack
		for ($i = 0; $i < count($authlevels); $i++)
		{
			if ($authlevels[$i] == $priv)
			{
				break;
			}
		}
	
		// Whoops, out the top means that the calling function didn't
		// give us a level we know about
		if ($i >= count($authlevels))
		{
			trigger_error(sprintf(_("Auth level %s not found"), $authtype), E_USER_ERROR);
			die(__FILE__.":".__LINE__.": dying with fatal error\n");
			exit;
		}
	
		// Now look for the user's auth level in the levels still
		// remaining in the stack above the requested level.
		for (; $i < count($authlevels); $i++)
		{
			if ($authlevels[$i] == $this->udata['Type'])
			{
				return true;
			}
		}
	
		return false;
	}

	function getDisplayComputerType()
	{
		return($this->DisplayComputerType);
	}

	function setDisplayComputerType($dct)
	{
		$this->DisplayComputerType = $dct;
	}

	function getDisplayOperatingSystem()
	{
		return($this->DisplayOperatingSystem);
	}

	function setDisplayOperatingSystem($dos)
	{
		$this->DisplayOperatingSystem = $dos;
	}

	function getDisplayOperatingSystemVersion()
	{
		return($this->DisplayOperatingSystemVersion);
	}

	function setDisplayOperatingSystemVersion($dosv)
	{
		$this->DisplayOperatingSystemVersion = $dosv;
	}

	function getDisplayProcessor()
	{
		return($this->DisplayProcessor);
	}

	function setDisplayProcessor($dp)
	{
		$this->DisplayProcessor = $dp;
	}

	function getDisplayProcessorSpeed()
	{
		return($this->DisplayProcessorSpeed);
	}

	function setDisplayProcessorSpeed($dps)
	{
		$this->DisplayProcessorSpeed = $dps;
	}

	function getDisplayLocation()
	{
		return($this->DisplayLocation);
	}

	function setDisplayLocation($dl)
	{
		$this->DisplayLocation = $dl;
	}

	function getDisplaySerial()
	{
		return($this->DisplaySerial);
	}

	function setDisplaySerial($ds)
	{
		$this->DisplaySerial = $ds;
	}

	function getDisplayOtherSerial()
	{
		return($this->DisplayOtherSerial);
	}

	function setDisplayOtherSerial($dos)
	{
		$this->DisplayOtherSerial = $dos;
	}

	function getDisplayRamType()
	{
		return($this->DisplayRamType);
	}

	function setDisplayRamType($drt)
	{
		$this->DisplayRamType = $drt;
	}

	function getDisplayRam()
	{
		return($this->DisplayRam);
	}

	function setDisplayRam($dr)
	{
		$this->DisplayRam = $dr;
	}

	function getDisplayNetwork()
	{
		return($this->DisplayNetwork);
	}

	function setDisplayNetwork($dn)
	{
		$this->DisplayNetwork = $dn;
	}

	function getDisplayIP()
	{
		return($this->DisplayIP);
	}

	function setDisplayIP($dip)
	{
		$this->DisplayIP = $dip;
	}

	function getDisplayMachineAddress()
	{
		return($this->DisplayMachineAddress);
	}

	function setDisplayMachineAddress($dma)
	{
		$this->DisplayMachineAddress = $dma;
	}

	function getDisplayHardDriveSize()
	{
		return($this->DisplayHardDriveSize);
	}

	function setDisplayHardDriveSize($hds)
	{
		$this->DisplayHardDriveSize = $hds;
	}

	function getDisplayContact()
	{
		return($this->DisplayContact);
	}

	function setDisplayContact($dc)
	{
		$this->DisplayContact = $dc;
	}

	function getDisplayContactNumber()
	{
		return($this->DisplayContactNumber);
	}

	function setDisplayContactNumber($dcn)
	{
		$this->DisplayContactNumber = $dcn;
	}

	function getDisplayComments()
	{
		return($this->DisplayComments);
	}

	function setDisplayComments($dc)
	{
		$this->DisplayComments = $dc;
	}

	function getDisplayDateMod()
	{
		return($this->DisplayDateMod);
	}

	function setDisplayDateMod($dm)
	{
		$this->DisplayDateMod = $dm;
	}

	function getAdvancedTracking()
	{
		return($this->AdvancedTracking);
	}

	function setAdvancedTracking($at)
	{
		$this->AdvancedTracking = $at;
	}

	function getTrackingOrder()
	{
		return($this->TrackingOrder);
	}

	function setTrackingOrder($to)
	{
		$this->TrackingOrder = $to;
	}

}

