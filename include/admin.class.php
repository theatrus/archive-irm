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
class admin
{
	function admin()
	{
		$this->RUNMSG = '';

		require_once 'database/upgrades.php';

		$this->UPGRADES = $UPGRADES;

		if (@$_REQUEST['submit'] == _('Health Check'))
		{
			$this->health_check();
		} else if (@$_REQUEST['submit'] && @$_REQUEST['active_db']) {
			$dbcfg = Config::CurrentSection('database', $_REQUEST['active_db']);
			$this->dbname = $dbcfg['name'];
			$DB = new IRMDB($dbcfg['DSN'], @$dbcfg['socket']);
			$DB->DieOnError(true);
		}
			
		if (@$_REQUEST['submit'] == _("Install") && @$DB)
		{
			$this->installTables($DB);
		}

		if(@$_REQUEST['submit'] == _('Upgrade') && @$DB)
		{
			$this->upgradeTables($DB);
		}

		if (@$DB)
		{
			$DB->disconnect();
			unset($DB);
		}

		$this->displayPage();

	}

	function installTables($DB)
	{	
		require_once 'database/install.php';
		$adminpassword = $_POST['adminpassword'];

		if ($adminpassword == ""){
			$this->RUNMSG .= 'You have not entered a password';
		} else {
			printf("<p>%s</p>\n", _("Installing database tables..."));
			$errlist = $DB->BulkQueries($INSTALL);

			if (@$_POST['sample_data'])
			{
				printf("<p>%s</p>\n", _("Installing sample data..."));
				$errlist = array_merge($errlist, $DB->BulkQueries($SAMPLEDATA));
			}

			$this->RUNMSG .= sprintf(_("The database '%s' has been initialised."), $this->dbname);
			if (count($errlist))
			{
				$this->RUNMSG .= "<p>"._("There were query errors:")."</p>\n";
				$this->RUNMSG .= "<ul>\n";
				foreach ($errlist as $err)
				{
					$this->RUNMSG .= "<li>$err</li>\n";
				}
				$this->RUNMSG .= "</ul>";
			}
		}

	}

	function upgradeTables($DB)
	{
		$curver = $DB->GetDatabaseVersion();
	
		$bits = explode('.', $curver);
		if ($bits[0] < 1 || ($bits[0] == 1 && $bits[1] < 3))
		{
			die(_("Sorry, your IRM database is pre-1.3.0.
				 Upgrades from versions of IRM prior to 1.3.0 are not supported,
				 due to massive and catastrophic changes to the database format.")."<br>\n");
		}

		$upgrading = false;
		foreach ($this->UPGRADES as $ver => $queries)
		{
			if ($ver >= $curver)
			{
				$upgrading = true;
			}
			
			if ($upgrading)
			{
				$this->RUNMSG .= sprintf(_("Running upgrade for %s"), $ver)."<br>\n";
				if (is_array($queries))
				{
					$this->RUNMSG .=($queries);
					$errors = $DB->BulkQueries($queries);
				} else {
					$errors = $queries($DB);
				}

				if (count($errors))
				{
					$this->RUNMSG .= "<p>"._("There were query errors:")."</p>\n";
					$this->RUNMSG .= "<ul>\n";
					foreach ($errors as $e)
					{
						$this->RUNMSG .= "<li>$e</li>\n";
					}
					$this->RUNMSG .= "</ul>\n";
				}
			}
		}
		
		$this->RUNMSG .= sprintf(_("The database '%s' has been upgraded to the current system version."), $this->dbname);
		$this->RUNMSG .= _("If query errors were reported above, your database is not completely upgraded.");
		$this->RUNMSG .= _("Please review the errors and deal with them as appropriate.");
	}

	function dbDropDown($dblist)
	{
		PRINT '<select name="active_db">';
		foreach ($dblist as $f => $v)
		{
			PRINT "<option value=\"$f\">$v</option>\n";
		}
		PRINT "</select>";

	}

	function sectionHeader($section)
	{
		PRINT "<hr>";
		PRINT "<h2>" . $section . "</h2>";
	}

	function healthCheck()
	{
		$this->sectionHeader(_("Health Check"));
		PRINT "<p>";
		__("We provide a free \"health check\" for your web server to see if some common
		problems exist.  If there are errors, you may have problems running IRM."); 
		PRINT '<form method="POST" action="admin.php">';
		PRINT '<input type="submit" name="submit" value="' . _("Health Check") . '">';
		PRINT '<input type="hidden" name="tricksy_hiddens" value="Precioussss">';
		PRINT '</form>';
		PRINT "</p>";

	}

	function configCheck()
	{
		$this->sectionHeader(_("Configuration File Check"));
		$dblist = Databases::All();

		foreach($dblist as $key=>$value){
			printf(_("Section : %s"),$key);
			PRINT "<br />";	
			printf(_("Name : %s"),$value);
			PRINT "<br />";	
			PRINT "<br />";
		}
	}


	function osCheck()
	{
		$this->sectionHeader(_("Operating System Check"));
		PRINT "<p>";
		if (Config::onWindows())
		{
			__("I have detected that you are running on some form of Windows system.");
		} else {
			__("I have detected that you are running on a Unix-like system (Linux, Mac OS X, etc).");
		}
		PRINT "</p><p>";
		__("If this autodetection is not correct, we have a serious problem.  Please report a bug.");
		PRINT "</p>";
	}

	function installUnitialised()
	{
		$this->sectionHeader(_("Install"));
		$dblist = Databases::Uninitialised();
		if ($dblist)
		{
			PRINT '<form method="POST" action="admin.php">';
			PRINT "<table class=admin>";
				
			PRINT "<tr>";
			PRINT "<th colspan=2>" .  _("Caution: Any data in the database you initialise will be totally destroyed.") . "</th>";
			PRINT "</tr>";			

			PRINT "<tr class=setupdetail>";
			PRINT "<td>" . _("Select a database to initialise.") . "</td>";
			PRINT "<td>";
			$this->dbDropDown($dblist);
			PRINT "</td>";
			PRINT "</tr>";

			PRINT "<tr class=setupdetail>";
			PRINT "<td>" . _("Insert Sample Data?") .  "</td>";
			PRINT '<td><input type="checkbox" name="sample_data" value="1"><br /></td>';
			PRINT "</tr>";

			PRINT "<tr class=setupdetail>";
			PRINT "<td>" . _("Create Admin Password") .  "</td>";
			PRINT '<td><input type="text" name="adminpassword"><br /></td>';
			PRINT "</tr>";

			PRINT "<tr class=setupupdate>";
			PRINT '<td colspan=2 align=center><input type="submit" name="submit" value="' . _("Install") . '"></td>';
			PRINT "</tr>";

			PRINT "</table>";
			PRINT '</form>';
		} else {
			PRINT "<p>" . _("No uninitialised databases found") . "</p>\n";
		}

	}

	function upgradeNotAtVersion()
	{
		$this->sectionHeader(_("Upgrades"));
		
		$lastversion = array_pop($tmp_var = array_keys($this->UPGRADES));
		
		$dblist = Databases::NotAtVersion($lastversion);

		if ($dblist)
		{
			PRINT '<form method="POST" action="admin.php">';
			 __("Please select a database to upgrade.");
			$this->dbDropDown($dblist);
			?>
			<input type="submit" name="submit" value="<?php __("Upgrade") ?>">
			</form>
		<?php
		}

	}

	function displayPage()
	{
		?>
		<html>
		<head>
		<title> <?php printf(_("Setup IRM version %s"), Config::Version()) ?></title>
		<? SetupStyle('default.css'); ?>
		</head>
		<body>
		<center>
		<img src="images/irm-jr1.jpg" alt="IRM Logo">
		<br />
		<?
		PRINT "<a href=" . Config::Absloc("index.php") . ">" . _("Go to login page") . "</a>";
		if(@$this->RUNMSG)
		{
			print '<p id="warning">' . $this->RUNMSG . "<p>";
		}

		$this->configCheck();
		$this->osCheck();
		$this->healthCheck();
		$this->installUnitialised();
		$this->upgradeNotAtVersion();
		PRINT "</center>";
		commonFooter();
	}
	
	function health_error($msg)
	{
		return sprintf("<p id=warning><b>%s</b> %s</p>\n", _("ERROR:"), $msg);
	}

	function health_notice($msg)
	{
		return sprintf("<p id=notice><b>%s</b> %s</p>\n", _("NOTICE:"), $msg);
	}

	function health_good($msg)
	{
		return sprintf("<p id=healthy><b>%s</b> %s</p>\n", _("HOORAY:"), $msg);
	}

	function health_info($msg)
	{
		return sprintf("<p id=info><b>%s</b> %s</p>\n", _("INFORMATION:"), $msg);
	}


	function health_check()
	{
		$err = false;

		if (!function_exists('mysql_connect'))
		{
			$this->RUNMSG .= $this->health_error(_("You do not appear to have the MySQL module installed."));
			$err = true;
		}

		if (!function_exists('gettext'))
		{
			$this->RUNMSG .= $this->health_error("No gettext support found.  You will not have translations available.");
		}

		if (@$_REQUEST['tricksy_hiddens'] !== 'Precioussss')
		{
			$this->RUNMSG .= $this->health_error(_("GPC variables not being registered globally."));
			$err = true;
		}

		if ((basename(@$_SERVER['SCRIPT_FILENAME']) != 'admin.php') && (basename(@$_SERVER['PATH_TRANSLATED']) != 'admin.php'))
		{
			$this->RUNMSG .= $this->health_error(_("Your webserver isn't providing SCRIPT_FILENAME or PATH_TRANSLATED.  Please report a bug giving your OS and Webserver information."));
			$err = true;
		}

		if (!preg_match('/(\.[:;])|([:;]\.)/', ini_get('include_path')))
		{
			$this->RUNMSG .= $this->health_error(_("The current directory ('.') does not appear to be in your include_path."));
			$err = true;
		}

		$verbits = explode('.', PHP_VERSION);
		if (($verbits[0] < 4) || ($verbits[0] == 4 && $verbits[1] < 1))
		{
			$this->RUNMSG .= $this->health_error(_("IRM requires a minimum PHP version of 4.1.0."));
			$err = true;
		}

		if ($verbits[0] > 4)
		{
			$this->RUNMSG .= $this->health_notice(_("IRM has not been properly tested with PHP 5.  Please report success and failure to ") . "<a href=mailto:irm-devel@lists.sf.net>irm-devel@lists.sf.net</a>.");
		}

		if (isset($_SERVER["SERVER_SOFTWARE"]))
		{
			$this->RUNMSG .= $this->health_info(_("Your server is running.") . $_SERVER["SERVER_SOFTWARE"]);
		}



		if (!file_exists(ini_get('session.save_path')))
		{
			$this->RUNMSG .= $this->health_error(_("Your configured session save path is invalid!"));
			$err = true;
		}

		if ($err)
		{
			$this->RUNMSG .= $this->health_error(_("There were problems detected."));
		} else {
			$this->RUNMSG .= $this->health_good(_("Your server appears healthy.  Enjoy IRM!"));
		}
	}
}

?>
