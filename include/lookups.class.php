<?php

	class Lookup
	{

		// Array containing all available lookups
		var $lookupList;

		// Short name of the lookup (i.e. location, os)
		var $lookupId;
		// Long name of the lookup, used on forms
		var $lookupName;
		// Description of the lookup, used in the setup area
		var $lookupDesc;
		// All possible values for the lookup
		var $lookupValues;

		/*
		** Construction function.  If passed a particular lookup id,
		** it will fetch and load data for that id.  Otherwise, will
		** fetch and load the lookupList array for general purpose work.
		*/
		function Lookup($lookupId = '')
		{
			if ($lookupId)
			{
				$this->getLookup($lookupId);
			} else
			{
				$this->getLookupTypes();
			}
		}

    // Load the data for a particular lookup type
		//  string lookupId is the text name of the lookup type
		function getLookup($lookupId)
		{
			// Initialize our variables incase of reuse
			$this->lookupId = '';
			$this->lookupName = '';
			$this->lookupDesc = '';
			$this->lookupValues = array();

			// Grab a database handle
			$DB = Config::Database();

			// Pull in the description for this particular lookup
			$sql = "SELECT * FROM lookups WHERE id = '$lookupId'";
			$result = $DB->getRow($sql);

			if (!MDB::isError($result))
			{
				$this->lookupId = $result['id'];
				$this->lookupName = $result['name'];
				$this->lookupDesc = $result['description'];

				// Pull in the values for this particular lookup
				$sql = "SELECT value FROM lookup_data WHERE lookup = '$lookupId' ORDER BY value";
				$result = $DB->getAll($sql);
				if (!MDB::isError($result))
				{
					foreach ($result as $row)
					{
						$this->lookupValues[] = $row['value'];
					}
				}
			}
		}

		// Load the list of all lookup types
		function getLookupTypes()
		{
			$this->lookupList = array();

			$sql = "SELECT id FROM lookups ORDER BY id";
			$DB = Config::Database();
			$result = $DB->getAll($sql);
			if (!MDB::isError($result))
			{
				foreach ($result as $row)
				{
					$this->lookupList[] = $row['id'];
				}
			}
		}

		/*
		** Creates the HTML for a dropdown from the currently loaded
		** lookup. Must call getLookup before using.
		** This function replaces both Dropdown() and Dropdown_value()
		**
		**   string dropdownName: name of the select statement
		**   string selectedValue: name of the value to be selected by default
		**
		**   Returns:
		**      string: HTML code generated
		*/
		function dropdown($dropdownName, $selectedValue = '')
		{
			$output = '';

			if ($this->lookupId)
			{
				$output = "<select name=\"$dropdownName\">\n";

				foreach ($this->lookupValues as $value)
				{
					$output .= "<option value=\"$value\"";
					if ($selectedValue == $value) $output .= " selected";
					$output .= ">$value</option>\n";
				}

				$output .= "</select>\n";
			}
			return $output;
		}

		// Deletes a lookup value from the current lookup type
		//  string valueName: value name to delete
		function deleteValue($valueName)
		{
			if ($this->lookupId)
			{
				$DB = Config::Database();
				$sql = "DELETE FROM lookup_data WHERE value = '$valueName' AND lookup = '".$this->lookupId."'";
				$DB->query($sql);
			}
		}

		// Adds a lookup value to the current lookup type
		//  string valueName: name of lookup value to add
		function addValue($valueName)
		{
			if ($this->lookupId)
			{
				$DB = Config::Database();
				if (!in_array($valueName, $this->lookupValues))
				{
					$sql = "INSERT INTO lookup_data (lookup, value) VALUES ('".$this->lookupId."', '$valueName')";
					$DB->query($sql);
				}
			}
		}

		// Deletes a lookup type and its associated values
		// This will most likely have serious ramifications
		// in devices using the lookup. For now, I'm just not
		// dealing with it.
		function deleteLookup($lookupId)
		{
			$DB = Config::Database();

			$DB->query("DELETE FROM lookup_data WHERE lookup = '$lookupId'");
			$DB->query("DELETE FROM lookups WHERE id = '$lookupId'");
		}

		// Adds a lookup type
		function addLookup($lookupId, $lookupName, $lookupDesc)
		{
			$this->getLookupTypes();
			if (!in_array($lookupId, $this->lookupList))
			{
				$DB = Config::Database();
				$DB->query("INSERT INTO lookups VALUES ('$lookupId', '$lookupName', '$lookupDesc')");
			}
		}
	}
?>
