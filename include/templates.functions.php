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
function orderTemplateList($data)
{
	foreach($data as $key => $value)
	{
		$templatename[$key] = strtolower($value['templname']);
	}

	array_multisort($templatename, SORT_STRING, $data);

	return $data;
}

function templateSelect()
{
	PRINT "<br><br>";
	$query = "SELECT * FROM templates";

	$DB = Config::Database();
	$data = $DB->getAll($query);

	$data = orderTemplateList($data);
	
	PRINT "<table>";
	PRINT "<tr>";
	PRINT "<th colspan=2>" . _("Computer Templates") . "</th>";
	PRINT "</tr>\n";

	foreach ($data as $result)
	{
	  	$ID = $result["ID"];
  		$name = $result["templname"];
	  	PRINT '<tr class="setupdetail">';
		PRINT '<td><a href="'.Config::AbsLoc("users/computers-index.php?action=add&withtemplate=1&ID=$ID")."\">$name</a><br>";
		PRINT '</tr>';
	}
	PRINT "</table>";		
}

?>
