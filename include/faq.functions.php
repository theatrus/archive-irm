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
function getFAQCategories()
{
	$query = "select * from kbarticles where (faq = 'yes')";

	$DB = Config::Database();
	$data = $DB->getAll($query);
	$catNumbers = array();
	foreach ($data as $result)
	{
		getFAQParentCategories($result["categoryID"], $catNumbers);
		#	$catNumbers[] = $result["categoryID"];
	}

	return($catNumbers);
}	

function getFAQParentCategories($ID, &$catNumbers)
{
	$query = "select * from kbcategories where (ID = '$ID')";

	$DB = Config::Database();
	$result = $DB->getRow($query);
	if(count($result) > 0)
	{
		$parentID = $result["parentID"];
		if(!in_array($parentID, $catNumbers))
		{
			getFAQParentCategories($parentID, $catNumbers);
		}
		if(!in_array($ID, $catNumbers))
		{
			$szecatNumbers = sizeof($catNumbers);
			$catNumbers[$szecatNumbers] = $ID;
		}
	}
}

function faqdisplaycategories($parentID=0)
{
	// display the articles of this category, then explore the childeren
	faqdisplayarticles($parentID);

	$catNumbers = getFAQCategories();
	$query = "select * from kbcategories where (parentID = $parentID) order by name asc";

	$DB = Config::Database();
	$data = $DB->getAll($query);
	if(count($data) > 0)
	{
		PRINT "<ul>\n";

		foreach ($data as $result)
		{
			$name = $result["name"];
			$ID = $result["ID"];
			if(in_array($ID, $catNumbers))
			{
				PRINT "<li><B>$name</B>\n";
				faqdisplaycategories($ID);
			}
		}
		PRINT "</ul>\n";
	} 
}

function faqdisplayarticles($parentID)
{
	$query = "select * from kbarticles where (categoryID = $parentID) and (faq = 'yes') order by question asc";

	$DB = Config::Database();
	$data = $DB->getAll($query);
	PRINT "<ul>\n";
	foreach ($data as $result)
	{
		$ID = $result["ID"];
		faqdisplayarticle($ID);
	}
	PRINT "</ul>\n";
}

function faqdisplayarticle($ID)
{
	$query = "select * from kbarticles where (ID=$ID)";

	$DB = Config::Database();
	$result = $DB->getRow($query);
	$question = $result["question"];
	PRINT '<li><A HREF="'.Config::AbsLoc("users/faq-detail.php?ID=$ID&action=faqdetail").'">';
	PRINT htmlspecialchars($question) . "</A>\n";
}
?>
