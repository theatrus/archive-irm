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
function groupBatch($deviceType,$field,$newcontains)
{
	$query = deviceQuery($deviceType,$field,$newcontains);

	if ($deviceType == "computer")
	{
		PRINT "<table>";
		PRINT "<tr>";
		if(Config::Get('groups'))
		{
			PRINT "<td align=center><h4>";
			PRINT '<form method=get action="'.Config::AbsLoc('users/computers-groups-batch.php').'">';
			PRINT "<input type=hidden name=aquery value=\"$query\"><input type=submit 
				value=\""._("Setup groups with this query")."\"></form>";
			PRINT "</h4></td>";
		}
		PRINT "</tr>\n";
		PRINT "</table>";
		PRINT "<hr noshade>";
	}
}

function deviceSearch($deviceType, $deviceFields)
{
	global $phrasetype, $show_list_full;

	$pageName = "users/" . $deviceType . "-search.php";

	switch ($deviceType)
	{
		case "computer":
			$infoPage = "users/". $deviceType . "s-index.php";
			$addPage = "users/" . $deviceType . "s-index.php?action=select-add";
			break;
		case "networking":
			$infoPage = "users/". $deviceType . "-index.php";
			$addPage = "users/" . $deviceType . "-index.php?action=select-add";
			break;

		case "software":
			$infoPage = "users/". $deviceType . "-index.php";
			$addPage = "users/" . $deviceType . "-index.php?action=select-add";
			break;

		default:
			$infoPage = "users/device-info.php";
			$addPage = "users/device-add-form.php?device=$deviceType";
			$pageName = "users/device-search.php?device=$deviceType";
			
			$buildArray = array();
			
			foreach($deviceFields as $field)
			{
				$buildArray[$field['Field']] = $field['Field'];
			}
			$deviceFields = $buildArray;
			
	}
	PRINT "<table>";

	PRINT '<tr class="devicedetail">';
	PRINT '<th align=center>';
	PRINTF (_("%s Management") , $deviceType);
	PRINT "</th>";
	PRINT "</tr>\n";

	PRINT '<tr class="devicedetail">';
	PRINT "<td><a href=" . Config::AbsLoc($addPage) . ">";
	PRINTF (_("Add  %s"), $deviceType);
	PRINT "</a></td>";
	PRINT "</tr>\n";
	PRINT "</table>";

	PRINT "<table>";
	PRINT '<tr class="devicedetail">';
	PRINT "<th align=center>";
	PRINTF (_("Select %s"),$deviceType);
	PRINT "</th>";
	PRINT "</tr>\n";

	PRINT '<tr class="devicedetail">';
	PRINT "<td>";
	PRINT '<form method="GET" action="' . Config::AbsLoc($infoPage). '">';
	PRINT '<input type="hidden" name="devicetype" value="' . $deviceType . '">';
	PRINT '<input type="hidden" name="action" value="info">';
	PRINTF (_("Select %s by name:"), $deviceType);
	Dropdown_device($deviceType);
	PRINT '<input type="submit" value="' . _("Show") . '">';
	PRINT "</form>";
	PRINT "</td>\n";
	PRINT "</tr>\n";

	if($deviceFields['location'])
	{

		PRINT '<tr class="devicedetail">';
		PRINT '<th>' . _("View by Location") . "</th>";
		PRINT "</tr>\n";

		PRINT '<tr class="devicedetail">';
		PRINT "<td>";
		PRINT '<form method="get" action="' . Config::AbsLoc($pageName) . '">';
		PRINT '<input type="hidden" name="field" value="location">';
		PRINT '<input type="hidden" name="devicetype" value="' . $deviceType .'">';
		Dropdown( "dropdown_locations",  "contains"); 
		__("and show in");
		PRINT '<select name="style">';
		PRINT select_options($show_list_full);
		PRINT "</select>";
		__("sorted by");
		PRINT '<select name="sort">';
		PRINT select_options($deviceFields);
		PRINT "</select>";
		PRINT '<input type="hidden" name="phrase" value="exact">';
		PRINT '<input type="submit" value="' . _("Show") . '">';
		PRINT "	</form>";
		PRINT "</td>\n";
		PRINT "</tr>\n";
	}

	PRINT "<tr>\n";
	PRINT "<th>" . _("Detailed Search") . "</th>";
	PRINT "</tr>\n";

	PRINT '<tr class="devicedetail">';
	PRINT "<td>";


	PRINT '<form method="get" action="' .  Config::AbsLoc($pageName) . '">';

#	var_dump($softwareFields);
#	var_dump($deviceFields);
	
	PRINT '<select name="field">';
	PRINT select_options($deviceFields);
	PRINT "</select>";
	
	__("where that field");
	
	PRINT '<select name="phrase">';
	PRINT select_options($phrasetype);
	PRINT "</select>";
	
	PRINT '<input type="hidden" name="devicetype" value="' . $deviceType .'">';
	PRINT '<input type=text size=30 name="contains">';
	
	__("and then show in");

	PRINT '<select name="style">';
	PRINT select_options($show_list_full);
	PRINT "</select>";
	
	__("and sort by");
	
	PRINT '<select name="sort">';
	PRINT select_options($deviceFields);
	PRINT "</select>";
	
	PRINT '<input type="hidden" name="action" value="info">';
	PRINT '<input type="submit" value="' . _("Search") . '">';
	PRINT "</form>";
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT "</table>";

}

function softwareSearch($field,$contains,$style)
{
	$DB = Config::Database();

	if (!preg_match('/^[A-Za-z0-9_]+$/', $field))
	{
		trigger_error(sprintf(_("invalid field name: %s"),$field), E_USER_ERROR);
		die(__FILE__.":".__LINE__.": dying with fatal error\n");
		exit;
	}

	$likecontains = $DB->getTextValue("%$contains%");

	if ($style == "full")
	{
		softwareSearchFull($field,$likecontains);
	}
	elseif ($style == "list")
	{
		softwareSearchList($field,$likecontains);
	}
}

function softwareSearchList($field,$likecontains)
{
	$DB = Config::Database();
	$query = "SELECT * FROM software WHERE ($field LIKE $likecontains)";
	$data = $DB->getAll($query);
	PRINT '<table class="sortable" id="software">';
	PRINT "<tr>";
	PRINT "<th>" . _("Name") . "</th>";
	PRINT "<th>" . _("Platform") . "</th>";
	PRINT "<th>" . _("Licenses") . "</th>";
	PRINT "</tr>";
	
	foreach ($data as $result)
	{
		$ID = $result["ID"];
		$name = $result["name"];
		$platform = $result["platform"];
		$license = Count_licenses($ID);
		$installed = Count_installations($ID);
		$remaining = $license - $installed;
		
		PRINT '<tr class="softwaredetail">';
		PRINT '<td><a href="' .Config::AbsLoc("users/software-index.php?ID=$ID&action=info").'">' . "$name ($ID)</a></td>";
		PRINT "<td>$platform</td>";
		PRINT "<td>" . _("Installed: ") . "<b>$installed</b> ";
		__("Remaining: ");
		PRINT "<b>";
		if ($remaining <= 0) {
			PRINT "<font color=red>";
		}
		PRINT "$remaining</font></b> ";
		__("Total: ");
		PRINT "<b>$license</b></td></tr>";
	}
	PRINT "</table>";
}

function softwareSearchFull($field,$likecontains)
{

	$DB = Config::Database();
	$query = "SELECT * FROM software WHERE ($field LIKE $likecontains) ORDER BY name";

	$data = $DB->getAll($query);
	$data = array_slice($data, $goto);
	if (count($data) > 5)
	{
		$nextpage = true;
		$data = array_slice($data, 0, 5);
	}
	else
	{
		$nextpage = false;
	}

	foreach ($data as $result)
	{
		$ID = $result["ID"];
		showSoftware($ID, 0);
	}
	
  	$backgoto = $goto - 5;
  	$forgoto = $goto + 5;
  	PRINT "<table>";
	PRINT "<tr>";
	PRINT "<td>";
  	if ($backgoto > -1)
  	{
    		PRINT "<form>";
		PRINT "<input type=hidden name=field value=\"$field\">";
		PRINT "<input type=hidden name=contains value=\"$contains\">";
		PRINT "<input type=hidden name=style value=\"$style\">";
		PRINT "<input type=hidden name=goto value=$backgoto>";
		PRINT "<input type=submit value=\"Previous 5\">";
		PRINT "</form>";
  	}
  	PRINT "</td>";
	PRINT "<td>";
  	PRINT "<form>";
	PRINT "<input type=hidden name=field value=\"$field\">";
	PRINT "<input type=hidden name=contains value=\"$contains\">";
	PRINT "<input type=hidden name=style value=\"$style\">";
	PRINT "<input type=hidden name=goto value=$forgoto>";
	PRINT "<input type=submit value=\"Next 5\">";
	PRINT "</form>";
  	PRINT "</td>";
	PRINT "</tr>";
	PRINT "</table>";
}

function deviceSearchDisplay($deviceType,$field,$sort,$phrase,$contains,$style)
{
	if (!preg_match('/^[0-9A-Za-z_]+$/', $field))
	{
		trigger_error(sprintf(_("Invalid field name: %s"), $field), E_USER_ERROR);
		die(__FILE__.":".__LINE__.": dying with fatal error\n");
		exit;
	}

	if (!preg_match('/^[0-9A-Za-z_]+$/', $sort))
	{
		trigger_error(sprintf(_("Invalid sort name: %s"), $sort), E_USER_ERROR);
		die(__FILE__.":".__LINE__.": dying with fatal error\n");
		exit;
	}
	$DB = Config::Database();

	# If phrase is only a contains search, add the % characters for the mysql query.
	if ($phrase == "contains")
	{
		$newcontains = $DB->getTextValue("%$contains%");
	} else {
		$newcontains = $DB->getTextValue($contains);
	}

	/* My abject apologies to translators for this abomination. */
	printf(_("Showing results where %s contains %s in %s view 
			sorted by %s"), $field, $newcontains, $style, $sort);

	PRINT "\n<br>\n<br>\n";
	PRINT "<hr noshade>";
	groupBatch($deviceType,$field,$newcontains);
	if ($style == "full")
	{
		deviceSearchFull($deviceType,$field,$newcontains, $sort, $phrase);
	} elseif ($style == "list") {
		deviceSearchList($deviceType,$field,$newcontains, $sort, $phrase);
	}

}

function deviceQuery($deviceType,$field,$newcontains,$sort="name")
{
	//Ugly hack as usage of computer and computers is not consistant
	if ($deviceType == "computer")
	{
		$query = "SELECT * FROM " . $deviceType . "s WHERE ($field LIKE $newcontains) ORDER BY $sort";
	} else {
		$query = "SELECT * FROM $deviceType WHERE ($field LIKE $newcontains)";
	}
	return $query;
}

function deviceSearchFull($deviceType,$field,$newcontains,$sort,$phrase)
{	
	global $IRMName, $goto, $contains, $style, $computerListElements;

	$DB = Config::Database();
	$query = deviceQuery($deviceType,$field,$newcontains,$sort);
	
	$data = $DB->getAll($query);
	$data = array_slice($data, $goto, 5);

	foreach ($data as $result)
  	{
    		$ID = $result["ID"];
		if ($deviceType == "computer")
		{
			$computer = new Computer("info",$ID,0);	// shows computer in brief
		} else {
			showNetworking($ID, 0);
		}
  	}

	$backgoto = $goto - 5;
	$forgoto = $goto + 5;
	PRINT "<table><tr><td>";

	$newcontains = stripslashes($newcontains);

	if ($backgoto > -1)
	{
		PRINT "<form><input type=hidden name=sort value=\"$sort\">
			<input type=hidden name=field value=\"$field\">
			<input type=hidden name=phrase value=\"$phrase\">
			<input type=hidden name=contains value=\"$contains\">
			<input type=hidden name=style value=\"$style\">
			<input type=hidden name=goto value=$backgoto>
			<input type=submit value=\""._("Previous 5")."\"></form>";
	}
	PRINT "</td><td>";
	PRINT "<form><input type=hidden name=sort value=\"$sort\">
		<input type=hidden name=field value=\"$field\">
		<input type=hidden name=phrase value=\"$phrase\">
		<input type=hidden name=contains value=\"$contains\">
		<input type=hidden name=style value=\"$style\">
		<input type=hidden name=goto value=$forgoto>
		<input type=submit value=\""._("Next 5")."\"></form>";
	PRINT "</td></tr></table>";
}

function deviceSearchList($deviceType,$field,$newcontains, $sort, $phrase)
{
	/***
	*
	* This need refactoring for device useage as it picks up fields to
	* display in the list from the computer_fields array, and these are selected from
	* Preferences. Yuk, Ick, Blurgh.
	*
	***/

	global $IRMName, $goto, $contains, $style, $computerListElements;

	$DB = Config::Database();
	$uname = $DB->getTextValue($IRMName);

	// Nasty hack
	switch ($deviceType)
	{
		case "computer":
			//$infoPage = "users/" . $deviceType . "s-info.php?ID=";
			$infoPage = "users/". $deviceType . "s-index.php?devicetype=$deviceType&action=info&ID=";
			break;
		case "software":
			$infoPage = "users/". $deviceType . "-index.php?devicetype=$deviceType&action=info&ID=";
			break;
		case "networking":
			$infoPage = "users/$deviceType-info.php?ID=";
			break;
		default:
			$infoPage = "users/device-info.php?devicetype=$deviceType&ID=";
			break;
	}

	$query = "SELECT * FROM prefs WHERE (user = $uname)";	
	$result = $DB->getRow($query);
	PRINT '<table class="sortable" id="' . $deviceType . '">';
	PRINT "<tr>";
	# The name field is not in the list
	PRINT "<th>". _("Name") ."</th>\n";
	# now print all optional fields	
	foreach ($computerListElements as $name => $string)
	{
		if ( Checked($result[$name]) ) 
		{
			PRINT "<th>". $string ."</th>\n";
		}
	}
		
	PRINT "</tr>\n";
	
	$query = deviceQuery($deviceType,$field,$newcontains,$sort);
	$data = $DB->getAll($query);
	// Chop off any records we want to skip
	$data = array_slice($data, $goto);
	if (count($data) > 25)
	{
		$nextpage = true;
		$data = array_slice($data, 0, 25);
	}
	else
	{
		$nextpage = false;
	}
	
	foreach ($data as $result2)
	{
	    	PRINT "<tr>\n";
		PRINT "<td>";
		# print the table content. name first then loop over the fields

		PRINT '<a href="'.Config::AbsLoc($infoPage . $result2['ID']).'">';
		PRINT $result2['name'] . " (" . $result2['ID'] .")"; 
		PRINT "</a>";
		PRINT "</td>\n";

		foreach ($computerListElements as $name => $string)
		{
			if (Checked($result[$name]))
			{
			      	PRINT "<td>". $result2[$name]. "</td>\n";
			}
		}				
    		PRINT "</td>\n";
	}

	$backgoto = $goto - 25;
	$forgoto = $goto + 25;
	PRINT "</table>";
	PRINT "<table>";
	PRINT "<tr><td>";
	if ($backgoto > -1)
	{
		PRINT "<form><input type=hidden name=sort value=\"$sort\">
			<input type=hidden name=phrase value=\"$phrase\"> 
			<input type=hidden name=field value=\"$field\">
			<input type=hidden name=contains value=\"$contains\">
			<input type=hidden name=style value=\"$style\">
			<input type=hidden name=goto value=$backgoto>
			<input type=submit value=\""._("Previous 25")."\"></form>";
	}
	PRINT "</td><td>";

	if ($nextpage)
	{		
		PRINT "<form><input type=hidden name=sort value=\"$sort\">
			<input type=hidden name=phrase value=\"$phrase\"> 
			<input type=hidden name=field value=\"$field\">
			<input type=hidden name=contains value=\"$contains\">
			<input type=hidden name=style value=\"$style\">
			<input type=hidden name=goto value=$forgoto>
			<input type=submit value=\""._("Next 25")."\"></form>";
	}
	PRINT "</td></tr></table>";
}

?>
