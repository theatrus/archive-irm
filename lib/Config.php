<?php

// Default system configuration.  Also defines the possible configuration
// variables.
$DEFAULT_CONFIG = array(
		'dbver' => NULL,
		'notifyassignedbyemail' => true,
		'notifynewtrackingbyemail' => false,
		'newtrackingemail' => 'user@host.com',
		'groups' => true,
		'usenamesearch' => true,
		'userupdates' => true,
		'sendexpire' => false,
		'showjobsonlogin' => true,
		'minloglevel' => 5,
		'logo' => 'irm-jr3.png',
		'snmp' => false,
		'snmp_rcommunity' => 'public',
		'snmp_ping' => false,
		'knowledgebase' => true,
		'fasttrack' => true,
		'anonymous' => false,
		'anon_faq' => false,
		'anon_tt' => false,
		'tree_menu' => true,
		'stylesheet' => 'default',
		'status' => 'Important Information',
		'pop3server' => 'mail.server.com',
		'pop3user' => 'pop3 username',
		'pop3password' => 'pop3 password',
		'ocsserver' => 'ocs.server.com',
		'ocsdb' => 'ocsdbname',
		'ocsport' => '3306',
		'ocsuser' => 'ocs username',
		'ocspassword' => 'ocs password',
		'mrtg' => true,
		'mrtglocation' => 'http://myserver/mrtg/',
		'snmp_nmap' => true,
		'show_events' => true
		);

class Config
{
	// The current IRM version
	function Version()
	{
		return '==VER==';
	}

	/** Return an "absolute" web location for $file (given relative to
	 * the root of the IRM installation.
	 *
	 * Assumes:
	 *   # That the PHP script file is located within the IRM
	 *	installation tree; and
	 *   # That the file that this method exists in is one level
	 *	down from the root of the IRM installation.
	 *
	 * I swear this code made sense to me when I wrote it.
	 *
	 * You can also give AbsLoc a set of arguments to be passed to the
	 * file specified, and they'll be appended in the usual URL manner. 
	 * Make $args an associative array of name => value pairs.
	 *
	 * Warning: Do *not* pass any arguments as part of the filename if
	 * you want to give an array of arguments -- Bad Things will happen.
	 */
	function AbsLoc($file, $args = NULL)
	{
		$sloc = $_SERVER['SCRIPT_NAME'];
		$sfile = @$_SERVER['SCRIPT_FILENAME'];
		if (!$sfile)
		{
			$sfile = @$_SERVER['PATH_TRANSLATED'];
		}
		$sfile = realpath($sfile);
		if (Config::onWindows())
		{
			$sfile = str_replace('\\', '/', $sfile);
		}
		
		// First, work out the filesystem location of the root of
		// the IRM installation
		$instroot = dirname(dirname(__FILE__));
		if (Config::onWindows())
		{
			$instroot = str_replace('\\', '/', $instroot);
		}
		
		// Next, get the name of the script file relative to the root
		// of the IRM installation
		$relativescript = ereg_replace("^$instroot", '', $sfile);

		// Now, we can get the web location of the root of the IRM
		// installation by stripping out the script-file specific
		// portion from the web location of the script
		$webroot = ereg_replace("$relativescript\$", '', $sloc);

		if ($args !== NULL)
		{
			$arglist = array();
			foreach ($args as $k => $v)
			{
				$arglist[] = urlencode($k) . "=" . urlencode($v);
			}
			
			$file = "$file?" . join('&', $arglist);
		}
		
		return "$webroot/$file";
	}

	function &Database()
	{
		require_once dirname(__FILE__) . '/../lib/IRMDB.php';
		global $DB;
		
		$dbcfg = Config::CurrentSection('database');

		if (!@$dbcfg['DSN'])
		{
			trigger_error(sprintf(_("No DSN found for section [%s]"), $_SESSION['_sess_database']), E_USER_ERROR);
		}

		if (!$DB)
		{
			$DB = new IRMDB($dbcfg['DSN'], @$dbcfg['dbsocket']);
			$DB->DieOnError();
		}
		
		if ($dbcfg['DSN'] !== @$DB->dsn)
		{
			unset($DB);
			$DB = new IRMDB($dbcfg['DSN'], @$dbcfg['dbsocket']);
			$DB->DieOnError();
		}

		return $DB;
	}

	function onWindows()
	{
		return preg_match('/^WIN/i', PHP_OS);
	}

	function PathSeparator()
	{
		return Config::onWindows() ? ';' : ':';
	}

	function GetIncludePath()
	{
		return explode(Config::PathSeparator(), ini_get('include_path'));
	}

	function FileAvailable($file)
	{
		foreach (Config::GetIncludePath() as $path)
		{
			if (file_exists($path.'/'.$file))
			{
				return true;
			}
		}
		
		return false;
	}
		
	function ReadConfig($type)
	{
		switch($type)
		{
			case 'database':
				$file = 'database.ini';
				$errlevel = E_USER_ERROR;
				break;
	
			case 'ldap':
				$file = 'ldap.ini';
				$errlevel = false;
				break;
			
			default:
				printf(_("Unknown type of config file: %s\n"), $type);
				echo "Backtrace:\n";
				print_r(debug_backtrace());
				exit(1);
		}
		
		$basedir = dirname(dirname(__FILE__));
		
		$cfgfile = "$basedir/config/$file";

		if (file_exists($cfgfile))
		{
			return parse_ini_file($cfgfile, true);
		} else {
			if ($errlevel)			
			{
				if (@$DEV == false){
					printf(_("config file %s not found"), "config/${type}.ini");
				} else {
					trigger_error(sprintf(_("config file %s not found"), "config/${type}.ini"), $errlevel);
				}
			}
			return false;
		}
	}

	/** Retrieve the "current" section from the specified config file.
	 * Where current section is defined either by what is passed in via
	 * $current, or (if $current is NULL) from the $_SESSION['_sess_database']
	 * variable.  If neither of these is available, then we fall back to
	 * the section called '_default'.
	 *
	 * \returns An associative array of config variables, or an empty
	 *	array if no section with the given name is available.
	 */
	function CurrentSection($type, $current = NULL)
	{
		$cfg = Config::ReadConfig($type);
		
		if ($current === NULL)
		{
			$current = @$_SESSION['_sess_database'];
		}

		if (!$current)
		{
			$current = '_default';
		}
		return @$cfg[$current] ? $cfg[$current] : array();
	}

	/** Retrieve the current value of the specified system config variable.
	 */
	function Get($var)
	{
		global $DEFAULT_CONFIG;
		
		if (!array_intersect(array_keys($DEFAULT_CONFIG), array($var)))
		{
			return NULL;
		}
		
		$DB = Config::Database();
		$qvar = $DB->getTextValue($var);
		$val = $DB->getOne("SELECT value FROM config WHERE variable=$qvar");
		
		if ($val === NULL)
		{
			return $DEFAULT_CONFIG[$var];
		}
		else
		{
			return $val;
		}
	}

	/** Set the value of the specified system config variable.
	 */
	function Set($variable, $value)
	{
		global $DEFAULT_CONFIG;
		
		if (!array_intersect(array_keys($DEFAULT_CONFIG), array($variable)))
		{
			return NULL;
		}
		
		$DB = Config::Database();
		$qvar = $DB->getTextValue($variable);
		$qval = $DB->getTextValue($value);
		
		// Why all this stuff, you ask?  Because we can't rely on
		// MySQL's REPLACE statement (damned database independence)
		// and we can't use UPDATE because it's not guaranteed that
		// the relevant config variable exists in the database.
		$DB->autoCommit(false);
		$DB->query("DELETE FROM config WHERE variable=$qvar");
		$DB->query("INSERT INTO config (variable, value) VALUES ($qvar, $qval)");
		$DB->commit();
		$DB->autoCommit(true);
	}

	/** Retrieve the complete set of config values.
	 * Get all of the current config values from the database and
	 * return them to the calling function as an associative array.
	 */
	function All()
	{
		$DB = Config::Database();
		
		$cfg = $DB->getAll("SELECT variable,value FROM config");
		
		global $DEFAULT_CONFIG;
		$valid = array_keys($DEFAULT_CONFIG);

		$all = $DEFAULT_CONFIG;
		foreach ($cfg as $c)
		{
			if (array_intersect($valid, array($c['variable'])))
			{
				$var = $c['variable'];
				$value = $c['value'];
				if (is_bool($DEFAULT_CONFIG[$var]))
				{
					// Quick boolean conversion
					if ($value == 0)
					{
						$value = false;
					}
					else
					{
						$value = true;
					}
				}

				$all[$var] = $value;
			}
		}
		
		return $all;
	}

	/** Return whether LDAP is in use or not for the currently selected database.
	 * Returns true if there is an LDAP configuration section defined for the
	 * database in use by the current user, or false otherwise.
	 */
	function UseLDAP()
	{
		return (boolean)Config::LDAP();
	}

	/** Return the LDAP config for the current database, if it exists
	 * Returns an associative array containing all of the LDAP config
	 * parameters for the database that the currently logged-in user
	 * is is using, or false if the database does not do LDAP.
	 */
	function LDAP()
	{
		$defaults = array('server' => 'localhost',
				'protocol' => '3',
				'fullnamefield' => 'cn',
				'emailfield' => 'mail',
				'locationfield' => 'roomNumber',
				'phonefield' => 'telephoneField',
				'usernamefield' => 'uid'
				);

		$db = @$_SESSION['_sess_database'];
		$cfg = Config::ReadConfig('ldap');

		if (!$cfg)
		{
			return false;
		}

		if (array_key_exists($db, $cfg))
		{
			$cfg = $cfg[$db];
			
			foreach ($defaults as $k => $v)
			{
				if (!array_key_exists($k, $cfg))
				{
					$cfg[$k] = $v;
				}
			}

			return $cfg;
		}
		else
		{
			return false;
		}
	}
}
