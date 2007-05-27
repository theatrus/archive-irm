<?php

################################################################################
#    IRM - The Information Resource Manager
#    Copyright (C) 2006 Martin Stevens 
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
require_once 'lib/Databases.php';

class Software
{
	function Software()
	{
		$this->ID = $_REQUEST['ID'];
		switch($_REQUEST['action'])
		{
			case "info":
				$this->softwareInfo();
				break;
			case "add":
				$this->softwareAdd();
				break;
			case "select-add":
				$this->softwareAddForm();
				break;
			default:
				$this->softwareSearch();
				break;
		}	
	}
	function softwareAddForm()
	{
		AuthCheck("tech");

		commonHeader(_("Software") . " - " . _("Add Form"));
		error_reporting(E_ALL ^ E_NOTICE);
		__("Fill out this form to add a new software package.");

		PRINT "<table>";
		PRINT '<form method=post action="'.Config::AbsLoc('users/software-index.php').'">';
		PRINT "<input type=hidden name=ID value=\"$ID\">";
		PRINT "<input type=hidden name=action value=add>";

		PRINT '<tr class="softwareheader">';
		PRINT "<td colspan=3>" . _("New Software") . "</td>";
		PRINT "</tr>";

		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Name:")."<br>";
		PRINT "<input type=text name=name value=\"$name\" size=24><br>" . _("Location") . ":<br>";
		PRINT "<input type=text name=package value=\"$package\" size=20><br>";
		PRINT "</td>";

		PRINT "<td>"._("Platform:")."<br>";
		PRINT Dropdown_value("dropdown_os", "platform", $platform);
		PRINT "</td>";
		PRINT "<td>" . _("Class:") . "<br>";

		$Description = &new DatabaseDescribe('software', 'class');
		$class = '<select name="class">';
		foreach ($Description->getList() as $key => $value) {
		    $class.= "<option value=$value>$value</option>";
		}
		$class.= '</select>';
		print $class;

		print "</td>";
		PRINT "</tr>";

		PRINT '<tr class="softwaredetail">';
		PRINT "<td colspan=3>" . _("Comments").":<br>";
		fckeditor("comments",$comments);
		PRINT "</td>";
		PRINT "</tr>";

		PRINT '<tr class="softwareupdate">';
		PRINT "<td colspan=3>";
		PRINT "<input type=submit value=\""._("Add")."\">";
		PRINT "<input type=reset value=\""._("Reset")."\">";
		PRINT "</form>";
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";

		commonFooter();
	}

	function softwareSearch()
	{
		AuthCheck("normal");

		commonHeader(_("Software"));
		__("Welcome to the IRM Software section.  This where you keep information about
		all of your software.");
		$deviceType = "software";
		deviceSearch($deviceType,$software_fields);
		commonFooter();
	}

	function softwareInfo()
	{
		AuthCheck("normal");

		commonHeader(_("Software") . " - " . _("Information"));

		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$query = "SELECT ID, class FROM software WHERE (ID = $ID)";
		$result = $DB->getRow($query);
		$class = $result["class"];
		$ID = $result["ID"];
		showSoftware($ID);
		if ( $class == 'Application Bundle' ) showBundled($ID);
		showLicenses($ID);
		ShowInstalled($ID);

		$files = new Files();	
		$files->setDeviceType("software");
		$files->setDeviceID($ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();

		displayDeviceTracking($ID, "software");

		commonFooter();
	}

	function softwareAdd()
	{
		AuthCheck("tech");

		$vals = array(
			'name' => $_REQUEST['name'],
			'platform' => $_REQUEST['platform'],
			'install_package' => $_REQUEST['package'],
			'class' => $_REQUEST['class'],
			'comments' => $_REQUEST['comments']
			);

		$DB = Config::Database();
		$DB->InsertQuery('software', $vals);
		header("Location: ".$_SESSION['_sess_pagehistory']->Previous());
	}
}
?>
