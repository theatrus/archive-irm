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
#error_reporting(E_ALL);

class Software
{
	function Software()
	{
		$this->software_fields = array(
			'name'			=> _("Name"),
			'ID'			=> _("IRM ID"),
			'platform'		=> _("Platform"),
			'comments'		=> _("Comments"),
			'class'			=> _("Class")
		);

		$this->ID = $_REQUEST['ID'];
		switch($_REQUEST['action'])
		{
			case "info":
				$this->softwareInfo();
				break;
			case "add":
				$this->softwareAdd();
				break;
			case "delete":
				$this->delete();
				break;
			case "select-add":
				$this->softwareAddForm();
				break;
			case "update":
				$this->update();
				break;
			default:
				$this->softwareSearch();
				break;
		}	
	}

	function delete(){
		AuthCheck("tech");

		$DB = Config::Database();

		$qID = $DB->getTextValue($this->ID);

		#$installations = Count_installations($ID);
		#$licenses = Count_licenses($ID);
		if ($_REQUEST['force']==1)
		{
			$qID = $DB->getTextValue($this->ID);
			$query = "DELETE FROM software WHERE (ID = $qID)";
			$DB->query($query);
			$query = "DELETE FROM inst_software WHERE (sID = $qID)";
			$DB->query($query);
			$query = "DELETE FROM templ_inst_software WHERE (sID = $qID)";
			$DB->query($query);
			$query = "DELETE FROM software_bundles
				  WHERE (sID = $qID)";
			$DB->query($query);
			$query = "DELETE FROM software_licenses
				  WHERE (sID = $qID)";
			$DB->query($query);
			$this->softwareSearch();
		} else {
			$this->deleteConfirm();
		}
	}

	function deleteConfirm(){
		$DB = Config::Database();

		$qID = $DB->getTextValue($this->ID);

		$query = "SELECT * FROM software WHERE ID=$qID";
		$result = $DB->getRow($query);

		$name = $result['name'];
		$class = $result["class"];
		$platform = $result["platform"];

		commonHeader(_("Software") . " - " . _("Deleted"));
		PRINT '<p id="warning">';
		__("Deleting this package will result in the removal of all Associated Records. Are you sure you want to delete this package. Remember you will loose the following Information about this package:");
		PRINT "<ul>";
		PRINT "<li>" . _("Installations") . "</li>";
		PRINT "<li>" . _("Licenses") . "</li>";
		PRINT "<li>" . _("Comments") . "</li>";
		PRINT "<li>" . _("Templates") . "</li>";
		PRINT "<li>" . _("Bundles") . "</li>";
		PRINT "</ul>";
		PRINT "</p>";

		print "<table>";
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Name") . "</td>";
		PRINT "<td>$name</td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Class") . "</td>";
		PRINT "<td>$class</td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Platform") . "</td>";
		PRINT "<td>$platform</td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Installations") . "</td>";
		PRINT "<td>$installations</td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Licenses") . "</td>";
		PRINT "<td>$licenses</td>";
		PRINT "</tr>\n";
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>" . _("Bundles") . "</td>";
		PRINT "<td>$bundles</td>";
		PRINT "</tr>\n";

		PRINT '<tr class="softwareupdate">';
		PRINT "<td>";
		PRINT "<a href=\"".$_SESSION['_sess_pagehistory']->Previous()."\">". _ ("No") . "</a>&nbsp;&nbsp;";
		PRINT "</td>";

		PRINT "<td>";
		// Effectively ignore the current page in the "history"
		$_SESSION['_sess_pagehistory']->Rollback();

		PRINT "<a href=\"$REQUEST_URI?ID=" . $this->ID . "&amp;force=1&amp;action=delete\">". _("Delete") . "</a>";
		PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}

	function update(){
		AuthCheck("tech");

		$vals = array(
			'name' => $_REQUEST['name'],
			'platform' => $_REQUEST['platform'],
			'install_package' => $_REQUEST['package'],
			'class' => $_REQUEST['class'],
			'comments' => $_REQUEST['comments']
			);

		$DB = Config::Database();
		$ID = $DB->getTextValue($this->ID);
		$DB->UpdateQuery('software', $vals, "ID=$ID");
		$this->softwareInfo();
	}

	/* Modifyed March 8th, 2001 to reflect removal of some data items.
	 * (micajc)
	 */
	function showSoftware() 
	{
		$ID = $this->ID;
		$DB = Config::Database();

		$qID = $DB->getTextValue($ID);
		$query = "SELECT * FROM software WHERE (ID = $qID)";

		$result = $DB->getRow($query);
		$name = $result["name"];
		$platform = $result["platform"];
		$package = $result['install_package'];
		$class = $result["class"];
		$comments = $result["comments"];

		$comments = stripslashes($comments);

		$licensed = Count_licenses($ID);
		$installed = Count_installations($ID);
		$remaining = $licensed - $installed;

		if ($remaining <= 0) {
			$remaining =  "<div class=\"licenses\">$remaining</div>";
		} 


		PRINT '<table class="software">';
		
		PRINT '<tr class="computerheader">';

		PRINT '<td colspan=2>';
		PRINT '<form method=post action="'.Config::AbsLoc('users/software-index.php').'">' . "\n";
		PRINT '<input type=hidden name=ID value="'.$ID.'"/>' . "\n";
		PRINT '<input type=hidden name=action value="update"/>' . "\n";
		PRINT '<input type=hidden name=class value="'.$class.'"/>' . "\n";
		PRINT $class.': '.$name.' ('.$ID.')';
		PRINT '</td>';
		PRINT '</tr>';
		
		PRINT '<tr class="computerdetail">';
		PRINT '<td>';

		PRINT _("Name:").'<br /><input type=text name=name value="'.$name.'" size=24 /><br />' . "\n";
		PRINT _("Location:");

		$lookup = new Lookup("locations");
		PRINT $lookup->dropdown("package",$package);
		
		PRINT "</td>\n";
		PRINT '<td>';

		PRINT _("Platform:");
		$lookup = new Lookup("os");
		print $lookup->dropdown("platform",$platform);

		PRINT _("Class:");

		# TODO Doing this weird shit because values are hardcoded in to
		# the database table 'software' as an enum.
		$Description = &new DatabaseDescribe('software', 'class');
		$temp = '<select name="class">' . "\n";
		foreach ($Description->getList() as $key => $value) {
			$cleanValue = substr($value,1,-1);
			$default = "";
			if(trim($cleanValue) == trim($class)){
				$default = "selected";
			}
			$temp.= '<option value="' . $cleanValue . '" '. $default .'>' . $cleanValue . "</option>\n";
		}
		$temp.= '</select>'. "\n";
		print $temp;

		PRINT '</td></tr>';

		PRINT '<tr class="computerdetail">
			<td>Licenses:
			<table>
			<tr><td>'._("Licenses:").'</td><td>'.$licensed.'</td></tr>
			<tr><td>'._("Installed:").'</td><td>'.$installed.'</td></tr>
			<tr><td>'._("Remaining:").'</td><td>'.$remaining.'</td></tr>
			</table>
		</td>

		<td>'._("Comments").':
			<br />
		';
		fckeditor("comments",$comments);
		PRINT '
		</td>
		</tr>
			
		<tr class="computerheader">
		<td><input type=submit value="'._("Update").'"/></form></td>
		<td>
			<form method="post" action="'.Config::AbsLoc('users/software-index.php').'">
			<input type="hidden" name="ID" value="'.$ID.'" />
			<input type="hidden" name="action" value="delete" />
			<input type="submit" value="'._("Delete").'" />
			</form>
		</td>
		</tr>
		</table>
		';
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
		$this->softwareSearch();
	}


	function softwareAddForm()
	{
		AuthCheck("tech");

		commonHeader(_("Software") . " - " . _("Add Form"));
		__("Fill out this form to add a new software package.");

		PRINT '<form method=post action="'.Config::AbsLoc('users/software-index.php').'">';
		PRINT "<input type=hidden name=ID value=\"$ID\" />\n";
		PRINT "<input type=hidden name=action value=add />\n";

		PRINT "<table>";
		PRINT '<tr class="softwareheader">';
		PRINT "<td colspan=3>" . _("New Software") . "</td>";
		PRINT "</tr>\n";

		PRINT '<tr class="softwaredetail">';
		PRINT "<td>\n";

		PRINT _("Name:") . "<input type=text name=name value=\"$name\" size=24/><br />\n";

		PRINT _("Location:");
		$lookup = new Lookup("locations");
		PRINT $lookup->dropdown("package");

		PRINT "</td>\n";
		PRINT "<td>"._("Platform:")."<br/>";
		$lookup = new Lookup("os");
		print $lookup->dropdown("platform",$platform);
		PRINT "</td>\n";

		PRINT "<td>" . _("Class:") . "<br/>";		
		
		# TODO Doing this weird shit because values are hardcoded in to
		# the database table 'software' as an enum.

		$Description = &new DatabaseDescribe('software', 'class');
		$class = '<select name="class">';
		foreach ($Description->getList() as $key => $value) {
			$cleanValue = substr($value,1,-1);
			$class.= '<option value="' . $cleanValue . '">' . $cleanValue . "</option>";
		}
		$class.= '</select>';

		print $class;
		
		print "</td>\n";
		PRINT "</tr>\n";

		PRINT '<tr class="softwaredetail">';
		PRINT "<td colspan=3>" . _("Comments").":<br/>";
		fckeditor("comments",$comments);
		PRINT "</td>";
		PRINT "</tr>\n";

		PRINT '<tr class="softwareupdate">';
		PRINT "<td colspan=3>";
		PRINT "<input type=submit value=\""._("Add")."\" />";
		PRINT "<input type=reset value=\""._("Reset")."\" />";
		PRINT "</td>";
		PRINT "</tr>\n";
		PRINT "</table>";
		PRINT "</form>";
		commonFooter();
	}

	function softwareSearch()
	{
		AuthCheck("normal");

		commonHeader(_("Software"));
		__("Welcome to the IRM Software section.  This where you keep information about
		all of your software.");
		$deviceType = "software";
		deviceSearch($deviceType,$this->software_fields);
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
		$this->showSoftware();
		if ( $class == 'Application Bundle' ) showBundled($ID);
		$this->showLicenses($ID);
		$this->showInstalled($ID);

		$files = new Files();	
		$files->setDeviceType("software");
		$files->setDeviceID($ID);
		$files->displayAttachedFiles();
		$files->displayFileUpload();

		displayDeviceTracking($ID, "software");

		commonFooter();
	}

	function showLicenses($ID) {
		$DB = Config::Database();

		$qID = $DB->getTextValue($ID);
		$query = "SELECT * FROM software_licenses WHERE (sID = $qID) ORDER BY licensekey";

		$data = $DB->getAll($query);

		print '<table class="license">
		<tr class="licenseheader">
			<td>ID</td>
			<td>'._("License Key").'</td>
			<td>'._("Entitlement").'</td>
			<td>'._("Oem Sticker").'</td>
		</tr>
		
		<tr class="licensedetail">
			<td>
			<form method="post" action="license-add.php">
			<input type="hidden" name="sID" value="'.$ID.'"/>

			<input type=submit value="'._("Add").'"/></td>
			<td><input type="text" name="licensekey" size="40"/></td>
			<td><input type="text" name="entitlement" size="4"/></td>
			<td>
				<input type="checkbox" name="oem_sticker"/>
				</form>

				<form method="post" action="license-del.php">
			</td>
			</tr>
		
		';

		foreach ($data as $result)
		{
			$sID = $result['sID'];
			$lID = $result['ID'];
			$licensekey = $result['licensekey'];
			$entitlement = $result['entitlement'];
			$oem_sticker = $result['oem_sticker'];

			PRINT '<tr class="licensedetail">';
			PRINT '<td><input type=radio name=lID value="'.$lID.'"/>'.$lID.'</td>';
			PRINT '<td>'.$licensekey.'</td>';
			PRINT '<td>'.$entitlement.'</td>';
			PRINT '<td>'.$oem_sticker.'</td>';
			PRINT '</tr>';
		}
		PRINT '<tr class="licenseupdate">';
		PRINT '<td colspan="4">';
		PRINT '<input type="submit" value="'._("Del").'"/>';
		PRINT '</form>';
		PRINT '</td></tr>';
		PRINT '</table>';
	}

	function showInstalled($ID){
		$DB = Config::Database();
		$qID = $DB->getTextValue($ID);
		$query = "SELECT * FROM inst_software WHERE (sID = $qID)";
		$data = $DB->getAll($query);
		print '<table class="license">
		<tr class="licenseheader">
			<th>'._("Computers with this software assigned to them").'</th>
		</tr>';

		foreach($data as $result){
			$computerID = $result['cID'];
			$query = "SELECT * FROM computers WHERE(ID = " . $result['cID'] . ")";
			$computerDetails = $DB->getAll($query);
			foreach($computerDetails as $computer){
				PRINT '<tr class="licensedetail">
					<td><a href='.Config::AbsLoc("users/computers-index.php?action=info&amp;ID=" . $computer['ID']) .'>' . $computer['name'].'</a></td>
				</tr>';
			}
		}
		print '<tr class="licenseheader"><th>'._("Add this software to :").'</th></tr>';
		print '<tr class="licensedetail"><td>';

		PRINT '<form method=post action="'.Config::AbsLoc('users/computers-software-add.php').'">';
		PRINT "<input type=hidden name=sID value=$ID/>";
		Dropdown_device("computer");
		PRINT "<input type=hidden name=reqdliccnt value=1/>";
		PRINT '<input type=submit value="' ._("Add"). '"/>';
		PRINT "</form>";


		PRINT '</td></tr>';
		PRINT '</table>';
	}
}
?>
