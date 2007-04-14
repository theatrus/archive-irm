<?php
#    IRM - The Information Resource Manager
#
#    Detailed Tracking Search Module
#    Copyright (C) 2004 Big Walnut Local Schools
#    written by David Maxwell, Technician Big Walnut Local Schools
#    Contains altered code from irm.inc and tracking-index.php files included with
#    the 1.4.2 version of IRM.
#
#    Some functions based on tutorials by Jackson Yee. More info in the
#    function comments.
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
#                                  CHANGELOG                                   #
################################################################################
#  02/23/2004       Initial development of Detailed Tracking Search begun      #
#  02/24/2004 v0.01 Detailed Tracking Search                                   #
#  02/25/2004 v0.02 Fixed off-by-one bug in date ranges                        #
#  02/26/2004 v0.03 Added display of search parameters and number of results   #
#                   to search display.  Also added ability to select results   #
#                   with or without followups.                                 #
#                                                                              #
#  05/25/2004 v0.05 Michael Gower <michael.gower@ca.ibm.com> added ability     #
#                   to search through the followups as well when the tracking  #
#                   contents are being searched.  He made contents the default #
#                   search with <space> the default content to search for.     #
#                                                                              #
#  02/15/2005 v0.10 Ported to IRM 1.5.x. This will no longer work with 1.4.x   #
#                   and before.                                                #
################################################################################

require_once '../../include/irm.inc';
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';

AuthCheck("post-only");

commonHeader(_("Tracking"). " - ". _("Detailed Search"));

print '<link href="../../style.css" rel="stylesheet" type="text/css">';

# WriteDateSelect generates a dropdown dialog for entering the date
# into a form.  It was written by Jackson Yee (me@jacksoncomputing.com)

function WriteDateSelect($BeginYear = 0, $EndYear = 0, $IsPosted = true, $Prefix = '')
{
  if (! $BeginYear)
  {
    $BeginYear = date('Y');
  }

  if (! $EndYear)
  {
    $EndYear = $BeginYear;
  }

  $Year = $IsPosted
          ? (int) $_POST[$Prefix . 'Year']
          : (int) $_GET[$Prefix . 'Year'];
  $Month = $IsPosted
          ? (int) $_POST[$Prefix . 'Month']
          : (int) $_GET[$Prefix . 'Month'];
  $Day = $IsPosted
          ? (int) $_POST[$Prefix . 'Day']
          : (int) $_GET[$Prefix . 'Day'];

  echo "<select name=\"${Prefix}Month\">\n";

  for ($i = 1; $i <= 12; $i++)
  {
    echo '<option ';

    if ($i == $Month)
      echo 'selected="yes"';

    echo '>', $i, '</option>
         ';
  }

  echo '</select>-
        <select name="', $Prefix, 'Day">
          ';

  for ($i = 1; $i <= 31; $i++)
  {
    echo '<option ';

    if ($i == $Day)
      echo 'selected="yes"';

    echo '>', $i, '</option>
         ';
  }

  echo '</select>
       ';

  echo '<select name="', $Prefix, 'Year">
         ';

  for ($i = $BeginYear; $i <= $EndYear; $i++)
  {
    echo '<option ';

    if ($i == $Year)
      echo 'selected="yes"';

    echo '>', $i, '</option>
         ';
  }
  echo '</select>';
 return;
}

function table_dropdown($table,$param)
{
	$DB = Config::Database();

	print "<select name=\"$param\">\n";
	$query = "DESCRIBE $table";
	$result = $DB->getAll($query);

	foreach ($result as $des)
	{
		$param="Field";

		// added in logic to make the contents the default dropdown choice, mg ####

		if ($des[$param]=='contents')
		{
			print "<option selected ";
		}
		else
		{
			print "<option ";
		}
		print ">".$des[$param]."</option>\n";
	}
	
	print "</select>\n";
}

function type_search($param)
{
	print "<select name=\"type_$param\">\n";
	print "<option value=contains>" . _("Contains") . "</option>\n";
	print "<option value=equels>". _("Equals") . "</option>\n";
	print "</select>\n";
}

__("This is a detailed tracking search module for IRM.  It allows trackings to
be searched on various parameters and between date ranges.  Choose your
search terms and dates and click the search button.");

?>

<hr noshade>

<form method=get action="<?php echo Config::AbsLoc('users/reports/tracking-detail-search.php'); ?>">

<table>
	<tr class="trackingdetail">
		<td width="20%"><?php __("Search all of tracking where:") ?></td>
		<td width="10%"><?php table_dropdown("tracking","primary") ?></td>
		<td width="8%"><?php type_search("primary") ?></td>
		<td><input type=text name=contains size=20 value=" "></td>
	</tr>
	<tr>
		<td colspan="4">
			&nbsp;
		</td>
	</tr>
	<tr class="trackingdetail">
		<td>
			<input type="checkbox" name="use_secondary" value="yes">
			<?php __("AND:") ?>
		</td>
		<td>
			<?php table_dropdown("tracking","secondary") ?>
		</td>
		<td>
			<?php type_search("secondary") ?>
		</td>
		<td>
			<input type=text name=contains2 size=20>
		</td>
	</tr>
	<tr class="trackingdetail">
		<td>
			<input type="checkbox" name="limit_computers" value="yes">
			<?php __("Only computers where:") ?>
		</td>
		<td>
			<?php table_dropdown("computers","machines") ?>
		</td>
		<td>
			<?php type_search("machines") ?>
		</td>
		<td>
			<input type=text name=machines_limit size=20>
		</td>
	</tr>

	<tr class="trackingdetail">
		<td colspan="4">
			<input type="checkbox" name="date_range" value="yes" checked="yes">
			
			<?php
			__("Constrain results between these (opened) dates:");

			$datestamp = time();
			$_POST['edMonth'] = date("n", $datestamp);
			$_POST['edDay'] = date("j", $datestamp);
			$_POST['edYear'] = date("Y", $datestamp);
			$current_year = date('Y');

			PRINT "<BR>"._("Beginning Date:");
			WriteDateSelect($current_year-3, $current_year+3, "true", bd);
			__("Ending Date:");
			WriteDateSelect($current_year-3, $current_year+3, "true", ed);
			?>
		</td>
	</tr>

	<tr class="trackingdetail">
		<td colspan="4">
			<input type="checkbox" name="include_followups" value="yes" checked="yes">
			<?php __("Include followups in found trackings.") ?>
		</td>
	</tr>

	<tr class="trackingupdate">
		<td colspan="4">
			<input type=submit value="<?php __("Search") ?>">
			</form>
		</td>
	</tr>
</TABLE>

<?php commonFooter() ?>
