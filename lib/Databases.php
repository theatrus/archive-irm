<?php

require_once 'include/i18n.php';
require_once 'MDB.php';

// A class of static functions to manipulate databases.

class Databases
{
	/** Return the list of defined databases.
	 * @return array A hash of dbid => name pairs
	 */
	function All()
	{
		$data = Config::ReadConfig('database');

		$rv = array();
		foreach ($data as $id => $vals)
		{
			$name = @$vals['name'];
			if (!$name)
			{
				$name = _("Unknown database");
			}
			
			$rv[$id] = $name;
		}

		return $rv;
	}

	/** List of uninitialised databases.
	 * Similar in concept to Databases::List(), but instead does a
	 * check on each configured database to see if it looks like a
	 * configured IRM database, and if so, does not put it in the list.
	 */
	function Uninitialised()
	{
		///* //{{{
		$dflsocket = ini_get('mysql.default_socket');
		$rv = array();

		$data = Config::ReadConfig('database');
		foreach ($data as $id => $vals)
		{
			if (!is_array($vals))
			{
				echo '<p id="warning">'
					._("Section value list is not an array -- I think your config file is malformed")
					."</p>\n";
				break;
			}
						
			$DSN = @$vals['DSN'];
			$socket = @$vals['socket'];
			
			if (!$DSN)
			{
				echo "<p>".sprintf(
						_("No DSN for database %s (%s)"),
						@$vals['name'], $id)
					."</p>\n";
				continue;
			}

			$dbh = new IRMDB($DSN, $socket);
			if ($dbh->error)
			{
				$dberror = sprintf(_('<p id="warning">Could not connect to database %s (%s): %s</p>'),
					@$vals['name'], $id,
					$dbh->error->getMessage()
				);
				unset($dbh);
				continue;
			}


			// Bollocks query just to try and trigger an error if
			// there's no table.  Can't use a query that isn't
			// guaranteed to work on any version of IRM.
			$dbh->pushErrorHandling(PEAR_ERROR_RETURN);
			$err = $dbh->query("SELECT * FROM computers WHERE ID=0");
			
			if (MDB::isError($err))
			{
				$name = @$vals['name'];
				if (!$name)
				{
					$name = _("Unknown database");
				}
			
				$rv[$id] = $name;
			}
			
			$dbh->disconnect();
			unset($dbh);
		}

		ini_set('mysql.default_socket', $dflsocket);
		return $rv;
	}

	/** List of databases that aren't at the given version.
	 * Similar in concept to Databases::List(), but instead does a
	 * check on each configured database to see if it is at the given
	 * version or not.
	 */
	function NotAtVersion($ver)
	{
		$data = Config::ReadConfig('database');
		
		$rv = array();
		foreach ($data as $id => $vals)
		{
			$DSN = @$vals['DSN'];
			$name = @$vals['name'];
			if (!$name)
			{
				$name = _('Unknown database');
			}
			$DB = new IRMDB(@$vals['DSN'], @$vals['socket']);
			if ($DB->error)
			{
				continue;
			}
			$dbver = $DB->GetDatabaseVersion();
	
			PRINT "<table>";
			PRINT "<tr>";
			PRINT "<th>" . _("Installation Name") . "</th>";
			PRINT "<th>" . _("Current Version") . "</th>";
			PRINT "<th>" . _("Upgrade Version Available") . "</th>";
			PRINT "</tr>";
			PRINT "<tr>";
			PRINT "<td>" . $name . "</td>";
			PRINT "<td>" . $dbver . "</td>";
			PRINT "<td>" . $ver. "</td>";
			PRINT "</tr>";
			PRINT "</table>";
			
			// What the fuck is going on here ? Why would this ever evaluate true ? 
			if ($dbver and $ver != $dbver)
			{
				$rv[$id] = $name;
			}
			$DB->disconnect();
		}

		return $rv;
	}
}


class DatabaseDescribe
{
    function DatabaseDescribe($table, $column) {
        $this->table = $table;
        $this->column = $column;
    }

    function getList() {
        $DB = Config::Database();
        $software = $DB->getAll('DESCRIBE ' . $this->table);

        foreach ($software as $key => $value) {
            if ($value['Field'] == $this->column) {
                $data = substr($value['Type'], 5, strlen($value['Type']));
                $data = substr($data,0,(strlen($data)-1));
                $enums = explode(',',$data);
            } 
        }
        return $enums;
    }
}
