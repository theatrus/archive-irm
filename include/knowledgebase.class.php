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

class KnowledgeBase
{

	var $question;
	var $answer;

function KnowledgeBase()
{

	$this->question = $_REQUEST['question'];
	$this->answer = $_REQUEST['answer'];
	$this->ID = $_REQUEST['ID'];
	$this->faq = $_REQUEST['faq'];
	$this->categoryID = $_REQUEST['categorylist'];
	$this->searchQuestion = $_REQUEST['search'];
	$this->modify = $_REQUEST['modify'];
	
	$this->vals = array(
		'ID' => $this->ID,
		'categoryID' => $this->categoryID,
		'question' => $this->question,
		'answer' => $this->answer,
		'faq' => $this->faq
		);

	switch($_POST['action'])
	{
		case "addtofaq":
			$this->kbaddtofaq();
			break;

		case "removefromfaq":
			$this->kbremovefromfaq();
			break;

		case "addcategory":
			$this->addKBCategory();
			break;

		case "updatecategory":
			$this->updateKBCategory();
			break;

		case "deletecategory":
			$this->deleteKBCategory();
			break;

		case "preview":
			$this->previewKBArticle();
			break;

		case "modify":
			$this->formModifyKBArticle();
			break;

		case "delete":
			$this->deleteKBArticle();
			break;

		case "search":
			$this->searchKB();
			$this->indexKB();

		case "add":
			AuthCheck("tech");

			if(@$_POST['commit'] != 1)
			{
				$this->formKBArticle();
				break;
			} else {
				if($_POST['modify'] == 1)
				{
					$this->modifyKBArticle();
					break;
				} else {
					$this->addKBArticle();
					break;
				}
			}
			break;

		default:
			if ($_GET['action'] == detail)
			{
				$this->detailKBArticle($this->ID);
				break;
			} elseif ($_GET['action'] == faqdetail) {
				$this->faqdisplayfullarticle($this->ID);
				break;
			} elseif ($_GET['action'] == setup) {
				$this->setupKB();
				break;
			} elseif ($_GET['action'] == from_tracking) {
				$this->trackingID = $_REQUEST['trackingID'];
				$this->getTrackingDetails();
				break;

			} else {
				$this->indexKB();
			}
	}
}
function getTrackingDetails()
{
	$track = new Tracking($this->trackingID);
	$this->question = $track->WorkRequest;
	$followups = new Followup();
	$FollowupIDs = $followups->getByTrackingID($this->trackingID);

	foreach ($FollowupIDs as $id){
		$followups->setID($id);
		$followups->retrieve();
		$this->answer .= $followups->FollowupInfo . "\n\n";
	}

	$this->formKBArticle();
}

function searchKB()
{
	$query = 'SELECT * FROM `kbarticles` WHERE';
	$query .= ' question LIKE(\'%' . $this->searchQuestion . '%\')';
	$query .= ' OR answer LIKE(\'%' . $this->searchQuestion . '%\')';
	$DB = Config::Database();
	$data = $DB->getAll($query);
	
	$this->searchDetails = "<h1>" . _("Search Results") . "</h1>";
	
	foreach($data as $item){
		$this->searchDetails .= "<a href=knowledgebase-index.php?action=detail&amp;ID=" . $item[ID] . ">" . $item[question] . "</a><br />";
	}
	$this->searchDetails .= "<hr />";
} 

function indexKB()
{
	AuthCheck("tech");
	commonHeader(_("Knowledge Base") . $actionDescription);
	__("This is the IRM Knowledge Base system. It allows you to view all of the knowledge base articles that have been entered.");
	PRINT "<hr />";
	PRINT $this->searchDetails;
	PRINT "<table>";
	PRINT "<tr>";
	PRINT "<td align=center>";
	PRINT '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	PRINT '<input type="hidden" name="action"  value="search" />';
	PRINT '<input type="input" name="search"  value="" />';
	PRINT '<input type="submit" value="' . _("Search") . '" />';
	PRINT "</form>\n";

	PRINT '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	PRINT '<input type="hidden" name="action"  value="add" />';
	PRINT '<input type="submit" value="' . _("Add an Article") . '" />';
	PRINT "</form>\n";

	PRINT "</td>";
	PRINT "</tr>";
	PRINT "</table>";
	$this->kbdisplaycategories();
	commonFooter();
}

function kbdisplaycategories($parentID=0)
{
	// display the articles of this category, then explore the childeren
	$this->kbdisplayarticles($parentID);

	$query = "select * from kbcategories where (parentID = $parentID) order by name asc";

	$DB = Config::Database();
	$data = $DB->getAll($query);
	if(count($data) > 0)
	{	
		PRINT "<ol>\n";
		foreach ($data as $result)
		{
			PRINT "<li>";
			PRINT "<b>" . $result["name"] . "</b>";
			$this->kbdisplaycategories($result['ID']);
			PRINT "</li>\n";
		}
		PRINT "</ol>\n";
	} 
}

function kbdisplayarticles($parentID)
{
	$query = "select * from kbarticles where (categoryID = $parentID) order by question asc";

	$DB = Config::Database();
	$data = $DB->getAll($query);
	PRINT "<ol>";
	foreach ($data as $result)
	{
		$this->kbdisplayarticle($result["ID"]);
	}
	PRINT "</ol>\n";
}

function kbdisplayarticle($ID)
{
	$query = "SELECT * FROM kbarticles WHERE (ID=$ID)";

	$DB = Config::Database();
	$result = $DB->getRow($query);
	PRINT '<li><A HREF="'.Config::AbsLoc("users/knowledgebase-index.php?action=detail&amp;ID=$ID").'">';
	PRINT $result["question"];
	print "</a></li>\n";
}

function faqdisplayfullarticle($ID)
{
	commonHeader(_("FAQ - Detailed View"));
	$this->kbdisplayfullarticle($ID);
	$files = new Files();	
	$files->setDeviceType("knowledgebase");
	$files->setDeviceID($ID);
	$files->displayAttachedFiles();
	commonFooter();
}

	
function kbdisplayfullarticle($ID)
{
	$query = "SELECT * FROM kbarticles WHERE (ID=$ID)";
	$DB = Config::Database();
	$result = $DB->getRow($query);
	$categoryID = $result["categoryID"];
	
	PRINT "<h2>".sprintf(_("Question (%s):"), $this->kbcategoryname($categoryID))."</h2>";
	$question = nl2br($result['question']);
	PRINT html_entity_decode($question);
	PRINT "<hr />\n";
	PRINT "<h2>"._("Answer:")."</h2>\n";
	PRINT $result['answer'];
}

function kbaddtofaq()
{
	$DB = Config::Database();
	$ID = $DB->getTextValue($_POST['ID']);
	$DB->query("UPDATE kbarticles SET faq='yes' WHERE ID=$ID");
	$this->detailKBArticle($ID);
	logevent($_REQUEST['ID'], _("faq"), 4, _("faq"), _("Add Knowledge base article to faq")); 
}

function kbremovefromfaq()
{
	$DB = Config::Database();
	$ID = $DB->getTextValue($_POST['ID']);
	$DB->query("UPDATE kbarticles SET faq='no' WHERE ID=$ID");
	$this->detailKBArticle($ID);
	logevent($_REQUEST['ID'], _("faq"), 4, _("faq"), _("Remove Knowledge base article from faq")); 
}

function kbisfaq($ID)
{
	$query = "SELECT * FROM kbarticles WHERE (ID=$ID)";
	$DB = Config::Database();
	$result = $DB->getRow($query);

	$isFAQ = $result["faq"];
	PRINT "<br />";
	PRINT "<hr />";

	if($isFAQ == "yes")
	{
		PRINT _("This Knowledge Base entry is part of the FAQ.");
		$url = '<A HREF="'.Config::AbsLoc("users/knowledgebase-index.php?ID=$ID&removefromfaq=yes").'">'._("Remove Article from the FAQ").'</A>';
		$action = 'removefromfaq';
		$actionstring = _("Remove Article from the FAQ");
	}
	else
	{
		PRINT _("This Knowledge Base entry is not part of the FAQ.");
		$url = '<A HREF="'.Config::AbsLoc("users/knowledgebase-index.php?ID=$ID&addtofaq=yes").'">'._("Add Article to the FAQ").'</A>';
		$action = 'addtofaq';
		$actionstring = _("Add Article to the FAQ");
	}

	PRINT "<br /><br />\n";

	PRINT "<table>\n";
	PRINT "<tr>\n";

	PRINT "<td align=left width=\"33%\">";
	PRINT '<form method="post" action="' . Config::Absloc("users/knowledgebase-index.php") .'">';
	PRINT '<input type="hidden" name="action" value="' . $action . '" />';
	PRINT '<input type="hidden" name="ID" value="' . $ID . '" />';
	PRINT '<input type=submit value="' . $actionstring . '" />';
	PRINT '</form>';
	PRINT '</td>';

	PRINT '<td align="left" width="34%">';
	PRINT '<form method="post" action="' . Config::Absloc("users/knowledgebase-index.php") .'">';
	PRINT '<input type="hidden" name="action" value="modify" />';
	PRINT '<input type="hidden" name="ID" value="' . $ID . '" />';
	PRINT '<input type=submit value="' . _("Modify Article") . '" />';
	PRINT '</form>';
	PRINT '</td>';
	
	PRINT '<td align="left" width="33%">';
	PRINT '<form method="post" action="' . Config::Absloc("users/knowledgebase-index.php") .'">';
	PRINT '<input type="hidden" name="action" value="delete" />';
	PRINT '<input type="hidden" name="ID" value="' . $ID . '" />';
	PRINT '<input type=submit value="' . _("Delete Article") . '" />';
	PRINT "</form>";
	PRINT "</td>";

	PRINT "</tr>\n";
	PRINT "</table>\n";
}

function addKBCategory()
{
	$vals = array(
		'parentID' => $_POST['categorylist'],
		'name' => $_POST['categoryname']
		);

	$DB = Config::Database();
	$DB->InsertQuery('kbcategories', $vals);
	$this->setupKB();
	logevent(_("NEW"), _("kb"), 4, _("kb"), _("Add Knowledge base category")); 
}

function updateKBCategory()
{
	$vals = array(
		'parentID' => $_POST['categorylist'],
		'name' => $_POST['categoryname']
		);

	$DB = Config::Database();
	$qid = $DB->getTextValue($_POST['id']);
	$DB->UpdateQuery('kbcategories', $vals, "ID=$qid");
	$this->setupKB();
	logevent($_REQUEST['id'], _("kb"), 4, _("kb"), _("Update Knowledge base category")); 
}

function deleteKBCategory()
{
	$DB = Config::Database();
	$qid = $DB->getTextValue($_POST['id']);
	$query = "DELETE FROM kbcategories WHERE (ID = $qid)";
	$DB->query($query);
	$query = "DELETE FROM kbarticles WHERE (categoryID = $qid)";
	$DB->query($query);
	$this->setupKB();
	logevent($_REQUEST['id'], _("kb"), 4, _("kb"), _("Delete Knowledge base category")); 
}

function deleteKBArticle()
{
	$DB = Config::Database();
	$ID = $DB->getTextValue($this->ID);
	$query = "DELETE FROM kbarticles WHERE (ID = $ID)";
	$DB->query($query);
	$this->indexKB();
	logevent($ID, _("kb"), 4, _("kb"), _("Delete Knowledge base article")); 
}

function modifyKBArticle()
{
	$DB = Config::Database();
	$qID = $DB->getTextValue($_REQUEST['ID']);
	$DB->UpdateQuery('kbarticles', $this->vals, "ID=$qID");
	$this->indexKB();
	logevent($this->ID, _("kb"), 4, _("kb"), _("Modify Knowledge base article")); 
}

function addKBArticle()
{
	$DB = Config::Database();
	$DB->InsertQuery('kbarticles', $this->vals);
	$this->indexKB();
	logevent($this->ID, _("kb"), 4, _("kb"), _("Add Knowledge base article")); 
}

function formKBArticle()
{
	commonHeader(_("Knowledge Base") . " - " .  _("Add Article"));
	if(isset($from_tracking))
	{
		$track = new Tracking($from_tracking);
		$question = $track->getWorkRequest();
		$answer = $track->getFollowupsInfo();
		if($answer == "")
		{
			$answer = _("No followups were added, please put something here in the answer!");
		} 
	}

	if (!@$this->question){
		$this->question = '';
	}

	if (!@$this->answer){
		$this->answer = '';
	}

	if (@$this->faq){
		$faqchecked = ' checked';
	} else {
		$faqchecked = '';
	}

	__("Here is where you can add an article to the knowledge base.");
	PRINT "<hr />";
	PRINT "<BR>";
	PRINT '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	__("Select the category in which this article should be placed:");
	PRINT $this->kbcategoryList(@$this->categorylist); 
	PRINT "<br>";
	PRINT "<br>";
	__("Enter the question here.  Please be as detailed as possible with the 
		question, but don't repeat information that can be inferred by the category.");
	PRINT "<br>";
	fckeditor("question",$this->question);
	PRINT "<br>";
	__("Enter the answer here.  Please be as detailed as possible with the answer, including a step by step process.");
	PRINT "<br>";
	fckeditor("answer",$this->answer);
	PRINT "<br>\n";
	PRINT "<input type=checkbox name=faq value=\"yes\" $faqchecked /> "._("Place this Knowledge Base Article into the publicly viewable FAQ as well.")."<br />\n";
	PRINT '<input type="hidden" name="action" value="preview" />' . "\n";
	PRINT '<input type="submit" value="' . _("Add Article") . '" />' . "\n";
	PRINT '<input type="reset" value="' . _("Reset") . '" />' . "\n";
	PRINT "</form>" . "\n";
	commonFooter();
}

function kbErrors()
{
	if ($this->question == ""){
		$this->error = 1;
		__("The following error occured with Knowledgebase Article:  You did not enter any question.");
		PRINT "<br>";
	}

	if ($this->answer == ""){
		$this->error = 1;
		__("The following error occured with your Knowledge Base article:  You did not enter any answer.");
		PRINT "<br>";
	}

	if ($this->kbcategoryname($this->categoryID) == ""){
		$this->error = 1;
		__("The following error occured with your Knowledge Base article:  You did not enter any category (You may not post Knowledge Base Articles in Main).");
		PRINT "<br>";
	}

	if ($this->error != 1){
		__("Please check that the article you are about to submit is correct.  If it is not, use the provided links to re-edit it.");
	} else {
		PRINT "<br /><b>";
		__("Errors occured with your Knowledge Base article.  Your only option is to re-edit the article.");
		PRINT "</b><br />";
	}

}

function previewKBArticleDetails()
{
	//Main Display
	printf(_("Category Selected was: %s"), $this->kbcategoryname($this->categoryID));
	PRINT "<br />";
	PRINT "<hr />";
	PRINT "<strong>" . _("Question:") . "</strong><br />";
	PRINT $this->question;
	PRINT "<hr />";
	PRINT "<strong>" . _("Answer:") . "</strong><br />";
	PRINT $this->answer;
}

function previewHiddenFields()
{
	PRINT '<input type="hidden" name="categorylist" value="' . $this->categoryID .'"/>' ."\n";
	PRINT '<input type="hidden" name="question" value="' . $this->question . '"/>' ."\n"; 
	PRINT '<input type="hidden" name="answer" value="' . $this->answer . '"/>' ."\n";
	PRINT '<input type="hidden" name="faq" value="' . $this->faq . '"/>' ."\n";
}

function previewKBArticle()
{
	AuthCheck("tech");
	commonHeader(_("Knowledge Base") . " - " . _("Article Preview"));

	$error = 0;	
	$modify = $this->modify;

	/* Start error checking */
	$this->kbErrors;

	if (!isset($this->faq)){
		$faq = '';
	}

	$this->previewKBArticleDetails();

	// Re-edit Article
	PRINT "<hr />\n";
	PRINT '<form method=post action="'.Config::AbsLoc('users/knowledgebase-index.php').'">'. "\n";

	$this->previewHiddenFields();

	PRINT "<input type=hidden name=action value=add/>\n";
	PRINT '<input type=submit value="' . _("Re-edit Article") . '" />'. "\n";
	PRINT "</form>\n";
	
	if ($this->error != 1) 
	{
		if (!isset($ID)){
			$ID = '';
		}

		if (!isset($modify)){
			$modify = '';
			$submitValue = _("Add Article");
		} else {
			$submitValue = _("Save Article");
		}
		//Main form
		PRINT '<form method=post action="'. $_SERVER['PHP_SELF'].'">' . "\n";
		PRINT '<input type="hidden" name="ID" value="' . $this->ID . '"/>' . "\n";
		PRINT '<input type=hidden name=modify value="' . $modify . '"/>' . "\n";
		PRINT '<input type=hidden name=commit value=1 />' . "\n";
		PRINT '<input type=hidden name=action value=add />' . "\n";

		$this->previewHiddenFields();

		PRINT '<input type=submit value="' . $submitValue . '" />';
		PRINT '</form>';
	}
}

function detailKBArticle($ID)
{
	commonHeader(_("Knowledge Base") . " - " . _("Detailed View"));
	if(@$addtofaq == "yes")
	{
		$this->kbaddtofaq($ID);
	} else if(@$removefromfaq == "yes")
	{
		$this->kbremovefromfaq($ID);
	}
	$this->kbdisplayfullarticle($ID);
	$this->kbisfaq($ID);
	
	$files = new Files();	
	$files->setDeviceType("knowledgebase");
	$files->setDeviceID($ID);
	$files->displayAttachedFiles();
	$files->displayFileUpload();
	commonFooter();
}

function formModifyKBArticle()
{
	$ID = $_POST['ID'];
	AuthCheck("tech");
	commonHeader(_("Knowledge Base") . " - " . _("Modify Article"));
	$query = "select * from kbarticles where (ID = $ID)";
	$DB = Config::Database();
	$result = $DB->getRow($query);

	$answer = $result["answer"];
	$question = $result["question"];
	$faq = $result["faq"];
	$categorylist = $result["categoryID"];

	__("Here is where you can modify an article that is in the knowledge base.");
	PRINT "<hr />";
	PRINT "<br />";
	PRINT '<form method=post action="' . Config::AbsLoc('users/knowledgebase-index.php') . '">';
	__("Select the category in which this article should be placed:");
	PRINT $this->kbcategoryList($categorylist);
	PRINT "<br />";
	PRINT "<br />";

	__("Modify the question here.  Please be as detailed as possible with the question, but don't repeat information that can be inferred by the category."); 
	PRINT "<br />";
	fckeditor("question",$question);
	PRINT "<br/ >";
	__("Modify the answer here.  Please be as detailed as possible with the answer, including a step by step process."); 
	PRINT "<br>";
	fckeditor("answer",$answer);
	PRINT "<br />";
	PRINT '<input type="checkbox" name="faq" value="yes"' . ($faq == 'yes' ? 'checked' : '') . '"/>';
	__("Place this Knowledge Base Article into the publicly viewable FAQ as well."); 
	PRINT "<br />";
	PRINT '<input type="hidden" name="modify" value="1" />';
	PRINT '<input type="hidden" name="action" value="preview" />';
	PRINT '<input type="hidden" name="ID" value="' . $ID . '" />';
	PRINT '<input type="submit" value="' . _("Preview Article") . '" />';
	PRINT '<input type="reset" value="' . _("Reset") . '" />';
	PRINT '</form>';
	commonFooter();
}

function setupKB()
{
	commonHeader(_("Knowledge Base Setup"));
	__("Welcome to the IRM Knowledge Base Setup utility.  Here you can add, modify, or delete a category from the IRM Knowledge Base.");
	PRINT '<div id="warning">';
	__("Warning : If you delete a category ALL the knowledge base articles in that category WILL be deleted.");
	PRINT "</div>";
	$query = "SELECT * FROM kbcategories";
	$DB = Config::Database();
	$data = $DB->getAll($query);
	foreach ($data as $result)
	{
  		$id = $result["ID"];
	  	$categoryname = $result["name"];
  		$parentID = $result["parentID"];
		$fullcategoryname  = $this->kbcategoryname($id);
  		PRINT '<form method=post action="'.Config::AbsLoc('users/knowledgebase-index.php').'">';
		PRINT "<table>";
		PRINT '<tr class="kbheader">';
		PRINT "<th colspan=4>$fullcategoryname</th>";
		PRINT "</tr>";
	
	  	PRINT '<tr class="kbdetail">';
  		PRINT "<td>" . _("Category Name:") . "<br><input type=text size=\"65%\"	name=categoryname value=\"$categoryname\" /></td>";
		PRINT "<td>" . _("As a subcategory of: ");
		PRINT $this->kbcategoryList($parentID);
		PRINT "</td>\n";
			
		PRINT "<td>";
		PRINT "<input type=hidden name=action value=updatecategory />";
		PRINT "<input type=hidden name=id value=\"$id\" />";
		PRINT "<input type=submit value=\"". _("Update") ."\" />";
		PRINT "</form>";
		PRINT "</td>";
	
		PRINT "<td valign=center>";
		PRINT '<form method=post action="'.Config::AbsLoc('users/knowledgebase-index.php').'">';
		PRINT "<input type=hidden name=action value=deletecategory />";
		PRINT "<input type=hidden name=id value=\"$id\" />";
		PRINT "<input type=submit value=\"". _("Delete") ."\" />";
		PRINT "</form>";
		PRINT "</td>";
		
		PRINT "</tr>";
		PRINT "</table>";
	}
	PRINT "<a name=\"add\"></a><hr /><h4>";
	__("Add a Category");
	PRINT '</h4><form method=post action="'.Config::AbsLoc('users/knowledgebase-index.php').'">';
	PRINT "<table>";
	PRINT '<tr class="kbheader">';
	PRINT "<th colspan=2>" . _("New Category") . "</th>";
	PRINT "</tr>";
	
	PRINT '<tr class="kbdetail">';
	PRINT "<td>" . _("Name:") . "<br> <input type=text size=\"65%\" name=categoryname /></td>";
	PRINT "<td>" . _("As a subcategory of: ");
	PRINT $this->kbcategoryList(0);
	PRINT "</td>";
	PRINT "</tr>";
	
	PRINT '<tr class="kbupdate">';
	PRINT "<td colspan=2>";
	PRINT "<input type=hidden name=action value=addcategory />";
	PRINT "<input type=submit value=" . _("Add") . " />";
	PRINT "</td>";
	PRINT "</tr>";
	PRINT "</table>";
	PRINT "</form>";

	commonFooter();
}

function kbcategoryList($current=0)
{
	$select = '<select name="categorylist" size="1">';
	$select .= '<option value="0">Main</option>';
	$select .= $this->kbcategoryListSelect($current, 0, "Main\\");
	$select .= '</select>';

	return $select;
}

function kbcategoryListSelect($current, $parentID=0, $categoryname="")
{
	$query = "SELECT  * FROM kbcategories order by name desc";
	$DB = Config::Database();
	$data = $DB->getAll($query);

	$optionItems = "";


	foreach ($data as $result)
	{
		$ID = $result["ID"];
		$name = $this->kbcategoryname($ID);
		if($current == $ID){
			$selected = " SELECTED";
		} else {
			$selected = "";
		}
		$optionItems .= "<option value=$ID $selected>$name</option>\n";
		$name = $name . "\\";
	}

	return $optionItems;
}

function kbcategoryname($ID, $wholename="")
{
	if($ID == 0) {
		$name = "Main";
		return (@$name);
	}

	$query = "select * from kbcategories where (ID = $ID)";
	$DB = Config::Database();
	$result = $DB->getRow($query);
	if(count($result) > 0)
	{
		$parentID = $result["parentID"];
		if($wholename == "")
		{
			$name = $result["name"];
		} else {
			$name = $result["name"] . "\\";
		}
		$name = $this->kbcategoryname($parentID, $name) . $name;
		if($parentID == 0){
			$name = "Main\\" . $name;
		}
	}
	return (@$name);
}

}
?>
