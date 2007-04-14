<?php
#    IRM - The Information Resource Manager
#    Copyright (C) 1999 Yann Ramin
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

require_once '../include/irm.inc';
require_once 'lib/Config.php';
require_once 'include/i18n.php';

AuthCheck("admin");

commonHeader(_("Users") . " - " . _("User Update"));

$DB = Config::Database();

if($update == "act")
{
	$vals = array(
		'fullname' => $fullname,
		'email' => $email,
		'location' => $location,
		'phone' => $phone,
		'type' => $type,
		'comments' => $comments
		);

	if (@$_REQUEST['password'])
	{
		$vals['password'] = md5($password);
	}

	$uname = $DB->getTextValue($username);
	$DB->UpdateQuery('users', $vals, "name=$uname");

	logevent(-1, _("IRM"), 5, _("setup"), sprintf(_("%s updated user %s"),$IRMName,$username));
	printf(_("Updated %s"),$username);
	PRINT "<a href=\"".Config::AbsLoc('users/setup-users.php').'">';
	__("Go back");
	PRINT '</a>';
}
else if($update == "edit")
{
	$user = new User($username);
	$fullname = $user->getFullname();
	$email = $user->getEmail();
	$location = $user->getLocation();
	$phone = $user->getPhone();
	$type = $user->getType();
?>
	<form method=get action="<?php echo Config::AbsLoc('users/setup-user-update.php'); ?>">
	<input type="hidden" name="username" value="<?php echo $username ?>">
	<input type=hidden name=update value="act">
	<table>
		<tr class="setupheader">
			<td colspan=2><strong><?php echo $fullname ?></strong> (<?php __("Username") ; echo ": " . $username ?>)</td>
		</tr>
		<tr class="setupdetail">
			<td style="font-family: sans-serif">
				<?php __("Full Name: ") ?>
				<input type=text width=40 name=fullname value="<?php echo $fullname ?>">
			</td>
			<td style="font-family: sans-serif">
				<?php __("New password: ") ?>
				<input type="password" size="20" name="password">
			</td>
		</tr>
		<tr class="setupdetail">
			<td style="font-family: sans-serif">
				<?php __("E-mail: ") ?><br>
				<input type=text width=20 name=email value="<?php echo $email ?>">
			</td>
			<td style="font-family: sans-serif">
				<?php __("Phone: ") ?><br>
				<input type=text width=20 name=phone value="<?php echo $phone ?>">
			</td>
		</tr>
		<tr class="setupdetail">
			<td style="font-family: sans-serif">
				<?php __("Location") ?>: <br>
				<input type=text width=20 name=location value="<?php echo $location ?>">
			</td>
			<td style="font-family: sans-serif">
				<?php __("User Type: ") ?><br>
				<select name=type>
				<?php echo select_options(array(
						'admin' => _("Administrator"),
						'normal' => _("Normal"),
						'post-only' => _("Post Only"),
						'tech' => _("Technician")
						),
						$type); ?>

				</select>
			</td>
		</tr>
		<tr class="setupheader">
			<td colspan=2 style="font-family: sans-serif" valign=center>
				<input type=submit value=Update>
			</td>
		</tr>
	</table>
	</form>
	<br>
<?php
}
else if($update == "delete")
{
 	PRINT '<form method=post action="'.Config::AbsLoc('users/setup-user-del.php').'">';
	printf(_("The user %s is about to be deleted from the database, to cancel this action click %s"),$username, '<A HREF="' .Config::AbsLoc('users/setup-users.php').'">' . _("here") . '</A>.'
);
	PRINT "<br>\n";
 	PRINT "<input type=hidden name=username value=\"$username\">";
 	PRINT "<input type=submit value=Delete></form>";
}
else
{
	__("Invalid action request for user update.");
	PRINT '<A HREF="'.Config::AbsLoc('users/setup-users.php').'">';
	__("Go Back");
	PRINT '</A>';
}
commonFooter();
