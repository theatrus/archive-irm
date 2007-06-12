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

require_once dirname(__FILE__) . '/i18n.php';
require_once dirname(__FILE__) . '/irm.inc';
require_once dirname(__FILE__) . '/irmmain.class.php';
require_once dirname(__FILE__) . '/tracking.class.php';
require_once dirname(__FILE__) . '/helper.class.php';
require_once dirname(__FILE__) . '/followup.class.php';
require_once dirname(__FILE__) . '/configuration.class.php';
require_once dirname(__FILE__) . '/files.class.php';
require_once dirname(__FILE__) . '/device.class.php';
require_once dirname(__FILE__) . '/knowledgebase.class.php';
require_once dirname(__FILE__) . '/faq.functions.php';
require_once dirname(__FILE__) . '/networking.class.php';
require_once dirname(__FILE__) . '/computers.class.php';
require_once dirname(__FILE__) . '/searching.functions.php';
require_once dirname(__FILE__) . '/ports.functions.php';
require_once dirname(__FILE__) . '/templates.functions.php';
require_once dirname(__FILE__) . '/../lib/stdo.class.php';
require_once dirname(__FILE__) . '/emailtracking.class.php';
require_once dirname(__FILE__) . '/lookups.class.php';
require_once dirname(__FILE__) . '/connections.class.php';
require_once dirname(__FILE__) . '/setup-fasttrack.class.php';
require_once dirname(__FILE__) . '/setup-templates.class.php';
require_once dirname(__FILE__) . '/setup-groups.class.php';
require_once dirname(__FILE__) . '/setup-groups-members.class.php';
require_once dirname(__FILE__) . '/ocs.class.php';
require_once dirname(__FILE__) . '/../FCKeditor/fckeditor.php';

function fckeditor($editorname, $defaultText){
	$oFCKeditor = new FCKeditor($editorname) ;
	$oFCKeditor->BasePath = '../FCKeditor/';
	$oFCKeditor->Value = $defaultText;
	$oFCKeditor->ToolbarSet = 'Basic';
	$oFCKeditor->Create() ;
}

function formSubmit ($extraFields, $submit){
	if(!$submit == ""){
		return $extraFields . '<br /><input type="submit" value="' . $submit . '" /></form>';
	}
}

function redirectCheck(){
	if (@$_REQUEST['redirect'])
	{
		return '<input type="hidden" name="redirect" value="'.$_REQUEST['redirect'].'" />';
	}
}

function irmConnect(){
	$irmConnect = '<input type="hidden" name="name" value="IRMConnect" />' . "\n";
	return $irmConnect;
}

function formAction ($extra = ""){
	$formAction = '<form method="post" '.$extra.' action="login.php">' . "\n";
	$formAction .= make_dblist(); 
	return $formAction;
}

function loginCheck(){
	switch ($_REQUEST['auth'])
	{
		case 'fail':
			return "<b>"._('Incorrect username or password')."</b>\n";
			break;
		
		case 'sess':
			return "<b>"._('Session expired')."</b>\n";
			break;
	}
}

function make_dblist()
{
	$dblist = Databases::All();

	$dblistOutput = "";

	if (count($dblist) > 1)
	{
		$dblistOutput .= '<select name="dbuse" size="1">' . "\n";  
		foreach ($dblist as $k => $d) {    
			$dblistOutput .= '<option value="'.$k.'">'.$d.'</option>' . "\n";
		}
		$dblistOutput .= "</select>" . "\n"; 
	}
	else if (count($dblist) == 1)
	{
		$f = array_keys($dblist);
		$dblistOutput .= '<input type="hidden" name="dbuse" value="'.$f[0].'" />' . "\n";
	}
	else
	{
		trigger_error(_("There are no defined databases"), E_USER_ERROR);
	}
	
	return $dblistOutput;
}

function currentStatus()
{
	$uninitdblist = Databases::Uninitialised();

	// get array of all databases.
	$dblist = Databases::All();

	$statusText = "";

	foreach ($dblist as $key => $value)
	{
		$status = 0;

		foreach($uninitdblist as $db){
			if ($db == $value)
			{		
				$statusText .= $db . _(" is not initialised") . " - <a href=admin.php>" . _("click here to setup it up") . "</a><br />";
				$status = 1;
			}
		}
		
		if ($status != 1){
			$_SESSION['_sess_database'] = $key;

			User::Authenticate("IRMConnect","password");

			$DB = Config::Database();
			$statusText .= '<p id="warning">';
			$statusText .=  $value .  " : " . Config::Get('status');
			$statusText .= "</p>";
		}
	}
	
	return $statusText;
}

function SetupStyle($stylesheet)
{
	if ($stylesheet == "default")
	{
		$stylesheet = "default.css";
	}
  	print '<link href="'.Config::AbsLoc('styles/' . $stylesheet).'" rel="stylesheet" type="text/css" />' . "\n";
}

function TreeMenuRights($usertype)
{
	//Check if user type should be able to see the treemenu.
	if($usertype == "post-only"){
		$sidemenu = false;
	} else {
		$sidemenu = true;
	}
	return $sidemenu;
}

// Parse a provided URL and add the extra arguments given as an assoc. array
function appendURLArguments($baseURL, $newargs)
{
	preg_match('/^([^?]+)\??(.*)$/', $baseURL, $matches);
	$URL = $matches[1];
	parse_str($matches[2], $argarray);
	foreach ($newargs as $f => $v)
	{
		$argarray[$f] = $v;
	}

	// Now reassemble
	$args = array();
	foreach ($argarray as $f => $v)
	{
		$args[] = urlencode($f)."=".urlencode($v);
	}
	
	return $URL . "?" . join('&', $args);
}

function SetupSortableTables()
{
	PRINT '<script src="'.Config::AbsLoc('javascript/sorttable.js').'" language="JavaScript" type="text/javascript"></script>' . "\n";
}

function SetupDHTMLTree()
{

	PRINT '<script src="'.Config::AbsLoc('javascript/TreeMenu.js').'" language="JavaScript" type="text/javascript"></script>' . "\n";
}

function PrintIcon($icon)
{	
	return '<img src="'.Config::AbsLoc('images/icons/' . $icon).'" border="0" alt="' . $icon . '" />' . "\n";
}

function MenuItem($userbase, $link, $icon, $text)
{
	$displayType = "";
	
	switch($displayType)
	{
		case "text":
			$graphicDisplay = "";
			$textDisplay = $text;
			break;
		case "graphic":
			$graphicDisplay = PrintIcon($icon);
			$textDisplay = "";
			break;
		default:
			$graphicDisplay = PrintIcon($icon) . "<br />" ;
			$textDisplay = $text;
			break;
	}

	return '<td class="nav" align="center"><a href="' . $userbase . "/" . $link . '">' . $graphicDisplay . $textDisplay . "</a></td>";
}
function commonHeader($title) 
{
	global $IRMName;

	if (Config::Get('sendexpire')) {
		header("Expires: Fri, Jun 12 1981 08:20:00 GMT Pragma: no-cache\n");
	}
	PRINT "<!-- IRM is (c) 1999-2007 Yann Ramin, Keith Schoenefeld, and others -->\n";
	PRINT "<!-- Yann Ramin atrus@atrustrivalie.org -->\n";
	PRINT "<!-- Keith Schoenefeld keith-p@schoenefeld.org -->\n";
	PRINT "<!-- Some code is (c) 1999 Brandon Neill bneill@learn2.com	-->\n";
	PRINT "<!-- http://www.stackworks.net/irm/ -->\n";
	PRINT '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	PRINT "<html>\n";
	PRINT "<head>\n";
	PRINT "<title>IRM: $title</title>\n";
	if (Config::Get('sendexpire'))
	{
		PRINT "<META HTTP-EQUIV=\"Expires\" CONTENT=\"Fri, Jun 12 1981 08:20:00 GMT\">\n";
		PRINT "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n";
		PRINT "<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">\n";
	}
	$stylesheet = Config::Get('stylesheet');
	SetupStyle($stylesheet);
	SetupSortableTables();
	SetupDHTMLTree();
	PRINT "</head>\n";
	PRINT "<body>\n";
	
	//Display the main navigation bar
	PRINT '<div id="banner">';
	PRINT "<!-- Main Information -->\n";
	PRINT "<table>\n";
	PRINT '<tr class="nav">';

	$user = new User($IRMName);
	$usertype = $user->getType();
	$uname = $user->getName();
	$userbase = Config::AbsLoc('users');

	$homeImage = "go-home.png";
	$requestImage = "accessories-text-editor.png";
	$inventoryImage = "address-book-new.png";
	$trackingImage = "battery.png";
	$reportImage = "printer.png";
	$setupImage = "preferences-desktop.png";
	$knowledgeBaseImage = "applications-internet.png";
	$logoutImage = "system-log-out.png";
	$faqImage = "system-search.png";

	PRINT MenuItem($userbase, "index.php", $homeImage, _("Home"));
	PRINT MenuItem($userbase, "helper-index.php", $requestImage, _("Request Help"));
	PRINT MenuItem($userbase, "tracking-index.php?action=display&amp;show=u:$uname", $trackingImage, _("Tracking"));

	if($usertype == "tech" || $usertype == "admin")
	{
		# Inventory Things
		PRINT MenuItem($userbase, "inventory-index.php", $inventoryImage, _("Inventory"));
		PRINT MenuItem($userbase, "reports-index.php", $reportImage, _("Reports"));
		PRINT MenuItem($userbase, "setup-index.php", $setupImage, _("Setup"));
		
		if(Config::Get('knowledgebase'))
		{
			PRINT MenuItem($userbase, "knowledgebase-index.php", $knowledgeBaseImage, _("Knowledge Base"));
		}
	}
	if (Config::Get('knowledgebase'))
	{
		PRINT MenuItem($userbase, "faq-index.php", $faqImage, _("FAQ"));
	}
	PRINT MenuItem($userbase, "logout.php", $logoutImage, _("Logout"));
	
	PRINT '<td class="nav">';
	PRINT date("M d H:i");
	PRINT " </td>\n";


	PRINT "</tr>\n";

	PRINT "</table>\n";

	PRINT "</div>";

	if (Config::FileAvailable('HTML/TreeMenu.php') && Config::Get('tree_menu'))
	{
		$sidemenu = TreeMenuRights($usertype);	
	} else {
		$sidemenu = false;
	}

	if ($sidemenu)
	{
		require_once 'include/tree.php';
		//Display the DHTML Tree Menu
		PRINT '<div id="leftcontent">';
		$tree = buildTree();
		$tree->printMenu();
		PRINT "</div>";
	}
	
	//Display the Main Information
	if ($sidemenu)
	{
		PRINT '<div id="centercontent">';
	}
	logo(); 
	PRINT "<h3>$title</h3>\n";
	PRINT "<hr />\n";
}

function logo()
{
	$LOGO = Config::Get('logo');
	if ($LOGO != ""){
		PRINT '<a href="' .Config::Absloc('users/') . 'index.php">';
		PRINT '<img src="'.Config::AbsLoc('images/' . $LOGO) .'" class="logographic"  alt="logo"/></a><br />';
	}
}

function displayConnectedDatabase()
{	
	$connectedDatabase =  _("You are logged into database : ") . @$_SESSION['_sess_database'] . "<br />\n";
	return $connectedDatabase;
}

function irmVersion()
{
	$irmVersion = _("IRM Version ") . Config::Version();
	return $irmVersion;
}

function copyright()
{
	$copyright = _("Distribution of IRM is permitted under the terms of the GNU GPL Version 2");
	$copyright .= "<br />\n";
	$copyright .= _("Copyright &copy; 1999-2007 Yann Ramin, Keith Schoenefeld, Matthew Palmer, Martin Stevens, and others. See the files AUTHORS and CONTRIBUTORS for more information.");
	$copyright .= "<br />\n";
	return $copyright;
}

function website()
{
	$website = '<a href="http://www.stackworks.net/irm/">' .  _("Website") . "</a><br />\n";
	return $website;
}

function commonFooter() 
{

	PRINT "<hr />";
	PRINT "<br />\n";
	PRINT '<div class="footer">';
	PRINT displayConnectedDatabase();
	PRINT irmVersion();
	PRINT website();
	PRINT copyright();
	PRINT "</div>";

	PRINT "</body>";
	PRINT "</html>";
}

function AuthCheck($authtype) 
{
	$username = @$_SESSION['_sess_username'];

	if (!$username)
	{
		$webroot = Config::AbsLoc('');
		$relpath = urlencode(ereg_replace("^$webroot", '', $_SERVER['REQUEST_URI']));
		$dest = Config::AbsLoc('index.php?auth=sess&amp;redirect='.$relpath);
		header("Location: $dest");
		__("Session expired.  Returning you to the login page.");
		PRINT "(<a href=\"$dest\">$dest</a>)";
		exit;
	}
	
	$user = new User($username);
	$authorised = $user->permissionCheck($authtype);
	$type = $user->getType();
	
	if (!$authorised)
	{
		// If we got here, the user's level is lower than the required level.
		commonHeader(_("Permission Denied"));
		printf(_("You (%s) only have %s level privileges, but to access this function you need %s privileges.  Sorry."),
			$username, $type, $authtype);
		commonFooter();
		exit();
	}
}

function irmSetupSection($section)
{
	PRINT "<tr>";
	PRINT "<th>" . $section . "</th>";
	PRINT "</tr>\n";
}

function irmSetup()
{
	$sysconfig = Config::All();
	$notifyassignedbyemail = Checked($sysconfig['notifyassignedbyemail']);
	$notifynewtrackingbyemail = Checked($sysconfig['notifynewtrackingbyemail']);
	$groups = Checked($sysconfig['groups']);
	$usenamesearch = Checked($sysconfig['usenamesearch']);
	$userupdates = Checked($sysconfig['userupdates']);
	$sendexpire = Checked($sysconfig['sendexpire']);
	$showjobsonlogin = Checked($sysconfig['showjobsonlogin']);
	$snmp = Checked($sysconfig['snmp']);
	$snmp_ping = Checked($sysconfig['snmp_ping']);
	$knowledgebase = Checked($sysconfig['knowledgebase']);
	$fasttrack = Checked($sysconfig['fasttrack']);
	$anonymous = Checked($sysconfig['anonymous']);
	$anon_faq = Checked($sysconfig['anon_faq']);
	$anon_req = Checked($sysconfig['anon_req']);
	$treemenu = Checked($sysconfig['tree_menu']); 
	$mrtg = Checked($sysconfig['mrtg']); 
	$snmp_nmap= Checked($sysconfig['snmp_nmap']); 
	$show_events = Checked($sysconfig['show_events']); 

	$status = $sysconfig['status']; 

	$pop3server = $sysconfig['pop3server'];

	$loglevels = array(1 => _('Critical'),
			2 => _('Severe'),
			3 => _('Important'),
			4 => _('Notice'),
			5 => _('Junk'));

	$stylesheets = array(	'default.css' => 'default',
				'green.css' => 'green',
				'blue.css' => 'blue',
				'funky.css' => 'funky'
				);

	PRINT _("Welcome to IRM's System Setup.  Here is where we will set up IRM's
	system configuration.  On this page you will be able to set system wide
	settings such as whether IRM should support computer groups, whether someone
	should be emailed when a new work request is entered etc.");

	PRINT '<form method=get action="'.Config::AbsLoc('users/setup-irm.php').'">';
	PRINT "<table>";
	
	irmSetupSection(_("Front Page Status"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td>";
	fckeditor("status",$status);
	PRINT "</td>\n";
	PRINT "</tr>\n";
	
	/*
	 *  Setup Outgoing Email Options 
	 */

	irmSetupSection(_("Outgoing Email Options"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=notifyassignedbyemail value=\"1\" $notifyassignedbyemail>";
	__("Notify a person who has been assigned a work request via email.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=notifynewtrackingbyemail value=\"1\" $notifynewtrackingbyemail>";
	__("Notify someone via email when a user has entered a new work request.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text size=20 name=newtrackingemail value=\"".$sysconfig['newtrackingemail']."\">";
	__("The email address that should receive notification when a user has entered a work request (seperate multiple email addresses with a comma).");
	PRINT "</td>\n";
	PRINT "</tr>\n";
	
	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=userupdates value=\"1\" $userupdates>";
	__("This option allows users to request updates via email when a tracking job they entered is update in any way (e.g. someone adds a followup, it is marked complete, etc.).");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	/*
	 *  Setup Incoming Email Options 
	 */
	irmSetupSection(_("Incoming Email Options"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=pop3server value=\"" .  $sysconfig['pop3server'] . "\">";
	__("POP3 Server to collect mail from");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=pop3user value=\"" .  $sysconfig['pop3user'] . "\">";
	__("POP3 account name");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=pop3password value=\"" .  $sysconfig['pop3password'] . "\">";
	__("POP3 account password");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	/*
	 *  Setup OCS-NG Connection 
	 */
	irmSetupSection('<a href="http://ocsinventory.sourceforge.net/">' . _("OCS-NG Connection") .'</a>');

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=ocsdb value=\"" .  $sysconfig['ocsdb'] . "\">";
	__("OCS-NG Database Name - Development only do not use");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=ocsserver value=\"" .  $sysconfig['ocsserver'] . "\">";
	__("OCS-NG Server Name");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=ocsport value=\"" .  $sysconfig['ocsport'] . "\">";
	__("OCS-NG Port Number");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=ocsuser value=\"" .  $sysconfig['ocsuser'] . "\">";
	__("OCS-NG User Name");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=ocspassword value=\"" .  $sysconfig['ocspassword'] . "\">";
	__("OCS-NG Password");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	/*
	 *  Setup Functional Options 
	 */
	irmSetupSection(_("Functional Options"));
	
	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=show_events value=\"1\" $show_events>";
	__("Select this option if you would like to view the last 5 system events on the home page..");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=groups value=\"1\" $groups>";
	__("Select this option if you would like to be able to group computers together.  This is valuable if you would like people to be able to submit work requests against large numbers of computers, such as a computer lab.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=usenamesearch value=\"1\" $usenamesearch>";
	__("If this option is selected, users will be able to search for their computer by name instead of being forced to type in an IRM ID to enter a work request.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=sendexpire value=\"1\" $sendexpire>";
	__("Send expires and pragma: nocache headers.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=showjobsonlogin value=\"1\" $showjobsonlogin>";
	__("Show a user the jobs assigned to him or her immediately after logging on.  If this is not selected, only the number of jobs the user has assigned is displayed.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><select name=minloglevel size=1>\n";
	PRINT select_options($loglevels, $sysconfig['minloglevel']);
	PRINT "</SELECT>\n";
	__("Select the Minimum Log Level.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><select name=stylesheet size=1>\n";
	PRINT select_options($stylesheets, $sysconfig['stylesheet']);
	PRINT "</SELECT>\n";
	__("Select the Stylesheet.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text size=20 name=logo value=\"".$sysconfig['logo']."\">";
	__("The name of the image file you would like used for the IRM logo. Note: the filename should be specified relative to the root of the IRM installation, or leave blank for no logo.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=knowledgebase value=\"1\" $knowledgebase>";
	__("Would you like to use the Knowledge Base system that is now built in to IRM?");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=fasttrack value=\"1\" $fasttrack>";
	__("Would you like to use the the FastTrack capability?");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	/*
	 *  Setup SNMP Options 
	 */
	irmSetupSection(_("Simple Network Management Protocol (SNMP)"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=snmp value=\"1\" $snmp>";
	__("Do you wish to enable snmp monitoring (ignore the rest of the questions in this section if you don't check this option).");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text size=20 name=snmp_rcommunity value=\"".$sysconfig['snmp_rcommunity']."\">";
	__(" The name of the \"read\" or \"public\" snmp community.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=snmp_ping value=\"1\" $snmp_ping>";
	__("Ping this host when it is loaded into the computer editor.  This option can cause big delays if the host is down - use with caution.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=snmp_nmap value=\"1\" $snmp_nmap>";
	__("Allow NMAP port scanning.  This option can cause big delays if the host is down - use with caution.");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	/*
	 *  Setup MRTG Options 
	 */
	irmSetupSection(_("MRTG Graphs"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=checkbox name=mrtg value=\"1\" $mrtg>";
	__("Do you wish to enable mrtg graphs against network ports");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "<td><input type=text name=mrtglocation value=\"" .  $sysconfig['mrtglocation'] . "\">";
	__("Location of MRTG graphs e.g. http://www.myserver.com/mrtg/");
	PRINT "</td>\n";
	PRINT "</tr>\n";



	/*
	 *  Setup Interface Options 
	 */
	irmSetupSection(_("Interface options"));

	PRINT '<tr class="setupdetail">';
	PRINT "<td>";
	PRINT "<input type=checkbox name=anonymous value=\"1\" $anonymous>";
	PRINT "<input type=hidden name=anon_faq value=\"1\" $anon_faq>";
	PRINT "<input type=hidden name=anon_req value=\"1\" $anon_req>";
	PRINT _("Do you wish to enable anonymous actions? (Submit ticket, read FAQ, etc).");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupdetail">';
	PRINT "	<td>";
	PRINT '<input type="checkbox" name="tree_menu"'.  $treemenu . ">";
	PRINT _("Use the side menu?  (Requires HTML::TreeMenu package)");
	PRINT "</td>\n";
	PRINT "</tr>\n";

	PRINT '<tr class="setupupdate">';
	PRINT "<td>";
	PRINT "<INPUT TYPE=SUBMIT VALUE=" . _("Update") . ">";
	PRINT '<INPUT TYPE=HIDDEN NAME="submit" VALUE="update">';
	PRINT "</td>\n";
	PRINT "</tr>\n";
	PRINT "</table>";
	PRINT "</form>";
}

function TableNameCheck($dropdownType, $table)
{
	if (!preg_match('/^[a-zA-Z0-9_]+$/', $table))
	{
		trigger_error(sprintf(_("%s: Illegal table name %s"),$dropdownType, $table), E_USER_ERROR);
		return;
	}
}


function Checked($field)
{
	if ($field && $field !== 'no')
	{
		return 'checked';
	} else {
		return '';
	}
}

/** Return a series of <option value="x">label</option> type lines
 * $opts is an assoc. array of value => label pairs.  If $selected is
 * given, it should be a scalar containing the value (not label!) you
 * want to be marked as 'selected' in the option set.
 */
function select_options($opts, $selected = NULL)
{
	$rv = '';
	
	foreach ($opts as $v => $l)
	{
		if ($v == $selected)
		{
			$sel = ' selected';
		} else {
			$sel = '';
		}
		
		$rv .= "<option value=\"$v\"$sel>$l</option>\n";
	}
	return $rv;
}

function Dropdown($table,$myname) 
{
	$lookup = new Lookup(ucfirst(substr($table, 9)));
	echo $lookup->Dropdown($myname);
}

function Dropdown_device($deviceType)
{
	// Ugly hack to change computer to computers
	if ($deviceType == "computer")
	{
		$deviceType = "computers";
	}

	$DB = Config::Database();
	$devices = $DB->getAll("SELECT ID, name FROM $deviceType ORDER BY name");

	PRINT '<select name="ID" size="1">' . "\n";
	foreach ($devices as $c)
	{
		$id = $c['ID'];
		$name = $c['name'];
		PRINT "<option value=\"$id\">$name</option>\n";
	}
	
	PRINT "</select>\n";
}

function Dropdown_groups($table,$myname) 
{
	TableNameCheck(_("Dropdown_groups()"), $table);

	$DB = Config::Database();
	$result = $DB->getAll("SELECT * FROM $table ORDER BY name");
	PRINT "<SELECT NAME=\"$myname\" SIZE=1>";

	foreach ($result as $row)
	{
		$label = $row["name"];
		$key = $row['ID'];
		PRINT "<OPTION VALUE=\"$key\">$label</OPTION>";
	}

	PRINT "</SELECT>\n";
}

function Dropdown_group_label($table,$myname) 
{
	TableNameCheck(_("Dropdown_groups()"), $table);

	$DB = Config::Database();
	$result = $DB->getAll("SELECT * FROM $table ORDER BY name");
	PRINT "<SELECT NAME=\"$myname\" SIZE=1>";

	foreach ($result as $row)
	{
		$label = $row["name"];
		$key = $row['ID'];
		PRINT "<OPTION VALUE=\"$label\">$label</OPTION>";
	}

	PRINT "</SELECT>\n";
}

function Dropdown_value($table,$myname, $value) 
{
	$lookup = new Lookup(ucfirst(substr($table, 9)));
	return $lookup->Dropdown($myname, $value);
}

function SoftwareDropdown($where = "")
{
	$query = "SELECT name,ID FROM software $where ORDER BY name";

	$DB = Config::Database();
	$data = $DB->getAll($query);

	PRINT "<SELECT NAME=sID SIZE=1>\n";

	foreach ($data as $result) 
	{
		$name = $result["name"];
		$sID = $result["ID"];
		PRINT "<OPTION VALUE=$sID>$name</OPTION>\n";
	}
	PRINT "</SELECT>\n";
}

function getComputerName($ID)
{
        $DB = Config::Database();

        $qID = $DB->getTextValue($ID);
        $query = "SELECT name FROM computers WHERE (ID = $qID)";

        $result = $DB->getRow($query);

        $name = $result["name"];
}
function SnmpPing($ip)
{
	$userbase = Config::AbsLoc('users');
	if (Config::Get('snmp_ping'))
	{
		if ($ip != "" OR $ip != "DHCP" OR $ip != "dhcp")
		{
			$out = exec(EscapeShellCmd("ping -c 1 -n -i 1 -w 3 $ip"),$dummy_array, $ping_return);
		}
		if ($ping_return == 2)
		{
			$hstatus = "<div class=\"snmpdown\">"._("Host:")." "._("DOWN")."</div>";
		}
		else if ($ping_return == 0)
		{
			$hstatus = "";
			if (Config::Get('snmp_nmap'))
			{
				$hstatus .= " | <a href=\"$userbase/nmap.php?ip=$ip\">"._("Nmap Port Scan")."</a>";
			}
			$hstatus .= "<div class=\"snmpup\">"._("Host:")." "._("UP")."</div>";
		}
		else
		{
			$hstatus = "| "._("Host:")." "._("UNKNOWN ERROR");
		}
	}
	else
	{
		$hstatus = "| "._("Ping disabled");
	}
	return $hstatus;
}

function SnmpNmap($ip)
{
	if (Config::Get('snmp_nmap'))
	{
		$out = exec(EscapeShellCmd("nmap $ip"),$dummy_array, $nmap_return);
		PRINT $out;
		foreach($dummy_array as $key=>$value)
		{
			PRINT "$value<br />";
		}
	}
}

function SnmpStatus($ip,$ID,$devicetype)
{
	$userbase = Config::AbsLoc('users');
	if (Config::Get('snmp'))
	{
		$hstatus = SnmpPing($ip);	
		$snmp_link = " | <a href=\"$userbase/snmp-stat.php?ID=$ID&amp;evice=$devicetype\">"._("Runtime Information (SNMP)")."</a> $hstatus";
	}
	else
	{
		$snmp_link = '';
	}
	return $snmp_link;
}

/* Added March 12th, 2001 (micajc)
 * This function attempts to find an lID of an unassigned 
 * license for the passed software ID. Remember
 * to lock the inst_software table if your going to use this
 * to assign a license.
 */
function find_license($ID, $lcnt_needed) {
	$DB = Config::Database();

	$qID = $DB->getTextValue($ID);

	$q = "SELECT software_licenses.ID AS lID,
		     (IF(software_licenses.entitlement,software_licenses.entitlement,0)
		       - IF(SUM(inst_software.lCnt),SUM(inst_software.lCnt),0))
		       AS available
		   FROM inst_software RIGHT JOIN software_licenses
		   		ON software_licenses.ID=inst_software.lID
		   WHERE software_licenses.sID=$qID
		   GROUP BY software_licenses.ID";

	$license_info = $DB->getAll($q);

	foreach ($license_info as $ld)
	{
		if ($ld['available'] >= $lcnt_needed)
		{
			return $ld['lID'];
		}
	}

	return NULL;
}

function showBundled($ID) { 
	print "<b>"._("Software Bundle Information (\$numRows)")." </b>";
	PRINT "<table>";
	PRINT "<tr>";
	PRINT "<td>"._("Software ID")."</td>";
	PRINT "<td>"._("Name")."</td>";
	PRINT "</tr>";

	PRINT "<form method=post  action=software-bundle-add-software.php>";
	PRINT "<input type=hidden name=bID value=$ID>";
	
	PRINT "<tr>";
	PRINT "<td><input type=submit value=\""._("Add")."\"></td>";
	PRINT "<td>"; 
	SoftwareDropdown("WHERE class!='Application Bundle'");
	print "</td>";
	PRINT "</tr>";
	PRINT "</form>";

	$DB = Config::Database();

	$qID = $DB->getTextValue($ID);
	$query = "SELECT software_bundles.*,software.name FROM software_bundles
					LEFT JOIN software ON software.ID=sID
					WHERE software_bundles.bID=$qID ORDER by software.name";


	$data = $DB->getAll($query);

	foreach ($data as $result)
	{
		$sID=$result['sID'];
		$name=$result['name'];
		print "<tr>";
		PRINT "<td>$sID</td>";
		PRINT "<td>$name</td>";
		PRINT "</tr>";
	}
	print "</table>";
	PRINT "<br />";
}

function Count_Installations($sID)
{
	$DB = Config::Database();
	$sID = $DB->getTextValue($sID);
	$query = "SELECT SUM(lCnt) AS cnt FROM inst_software WHERE (sID=$sID)";

	return $DB->getOne($query);
}

function Count_licenses($sID) 
{
	$DB = Config::Database();
	$sID = $DB->getTextValue($sID);
	$query = "SELECT SUM(entitlement) FROM software_licenses WHERE (sID = $sID)";

	return $DB->getOne($query);
}

function templcompsoftShow($showID)
{
	$DB = Config::Database();
	$showID = $DB->getTextValue($showID);
	$query = "SELECT * FROM templ_inst_software WHERE (cID = $showID)";

	$data = $DB->getAll($query);

	PRINT "<table>";
	PRINT '<tr class="computerdetail">';
	PRINT "<th colspan=2>"._("Installed Software")."</th>";
	PRINT "</tr>\n";
	
	foreach ($data as $result)
	{
		$sID = $result["sID"];
		$ID = $result["ID"];
		$qID = $DB->getTextValue($sID);
		$query = "SELECT * FROM software WHERE (ID = $qID)";
		$result2 = $DB->getRow($query);
		$name = $result2["name"];

		PRINT '<tr class="computerdetail">';
		PRINT '<td><i><A HREF="'.Config::AbsLoc("users/software-index.php?ID=$sID&amp;ction=info").'">';
		PRINT "$name</A></i></TD>";
		PRINT "<td WIDTH=10%>";
		PRINT '<A HREF="'.Config::AbsLoc("users/setup-templates-software-del.php?ID=$ID").'">['._("Delete").']</A></TD></TR>';
	}
	
	PRINT '<tr class="computerupdate">';
	PRINT "<td>";
	PRINT '<form method=post action="'.Config::AbsLoc('users/setup-templates-software-add.php').'">';
	PRINT "<input type=hidden name=cID value=$showID>"._("Add software:");
	SoftwareDropdown();
	PRINT " "._("to template.")."</td>\n";
	PRINT "<TD><input type=submit value=\""._("Add")."\"></form></TD>";
	PRINT "</tr>\n";
	PRINT "</TABLE>";
}

function compsoftShow($showID) 
{
	//TODO Needs refactoring to allow usage by networking devices
	$DB = Config::Database();

	$qID = $DB->getTextValue($showID);
	$query = "SELECT inst_software.ID AS ID,
			inst_software.sID AS sID,
			inst_software.lCnt AS lCnt,
			software.name AS name,
			software_licenses.ID AS lID,
			software_licenses.licensekey AS licensekey
			FROM (inst_software
				INNER JOIN software
					ON inst_software.sID=software.ID)
				LEFT JOIN software_licenses
					ON inst_software.lID=software_licenses.ID
			WHERE (inst_software.cID = $qID)
			ORDER BY software.class, software.name";

	$data = $DB->getAll($query);

	PRINT "<table>\n";
	PRINT '<tr class="softwareheader" >';
	PRINT "<th colspan=3>"._("Installed Software")."</th>\n";
	PRINT "</tr>\n";
		
	foreach ($data as $result)
	{
		$sID = $result["sID"];
		$ID = $result["ID"];
		$lID = $result['lID'];
		$slots = $result["lCnt"];
		$name = $result['name'];
		$key = $result['licensekey'];
		
		if ($lID === NULL)
		{
			$key = '<font color="red">'._("license key not found").'</font>';
		}
		
		PRINT '<tr class="softwaredetail">';
		PRINT "<td>";
		PRINT '<i><a href="'.Config::AbsLoc("users/software-info.php?ID=$sID").'">';
		PRINT "$name</A></i>, ".sprintf(_("%s license(s)."), $slots)."</td>\n";
		PRINT "<td>$key</td>\n";
		PRINT "<td>";
		PRINT '<a href="'.Config::AbsLoc("users/computers-software-del.php?ID=$ID")."\">["._("Delete")."]</a>";
		PRINT "</td>\n";
		PRINT "</tr>\n";
	}
	PRINT '<tr class="softwareupdate">';
	PRINT '<td colspan="2">';
	PRINT '<form method=post action="'.Config::AbsLoc('users/computers-software-add.php').'">';
	PRINT "<input type=hidden name=cID value=$showID>"._("Add software")." ";
	SoftwareDropdown();
	PRINT " "._("to computer, using")." <input type=\"text\" name=\"reqdliccnt\" size=\"3\" value=\"1\"> "._("license(s).")."</TD><TD><input type=submit value=\""._("Add")."\">";
	PRINT "</form>";
	PRINT "</td>\n";
	PRINT "</td>\n";
	PRINT "</table>";
}

function Tech_list($value, $myname, $readonly = false) 
{
	$query = "SELECT * FROM users WHERE (type = 'admin' || type = 'tech') ORDER BY name";

	$DB = Config::Database();
	$data = $DB->getAll($query);

	$techlist[''] = '[ Nobody ]';
	foreach ($data as $result)
    	{
    		$techlist[$result['name']] = $result['fullname'];
	}

	if ($readonly)
	{
		PRINT $techlist[$value]."\n";
	}
	else
	{
	  	PRINT "<SELECT NAME=\"$myname\" SIZE=1>";

	  	foreach ($techlist as $v => $l)
	  	{
	  		if ($v == $value)
	  		{
	  			$sel = ' selected';
			} else {
				$sel = '';
			}

	  		PRINT "<OPTION VALUE=\"$v\"$sel>$l</OPTION>\n";
	    	}
	  	PRINT "</SELECT>";
	}
}

function logevent($item, $itemtype, $level, $service, $event)
{
	if ($level <= Config::Get('minloglevel')) 
	{
		$vals = array(
			'item' => $item,
			'itemtype' => $itemtype,
			'date' => date('Y-m-d H:i:s'),
			'service' => $service,
			'level' => $level,
			'message' => $event
			);
		$DB = Config::Database();
		$DB->InsertQuery('event_log', $vals);
	}
}

function show_events($events)
{
	if (Config::Get('show_events') == true){
		if (!count($events))
		{
			PRINT "<p>"._("No events")."</p><br />";
			return;
		}
		PRINT '<table id="tracking-overview">';
		PRINT '<tr class="trackingheader"><th colspan="5">' . _("Last ") . count($events). (" Events") . "</th></tr>\n";
		PRINT "</table>";

		PRINT '<table class="sortable" id="event-tracking">';
		PRINT "<tr>";
		PRINT "<th>"._("Item")."</th>";
		PRINT "<th>"._("Date")."</th>";
		PRINT "<th>"._("Service")."</th>";
		PRINT "<th>"._("Level")."</th>";
		PRINT "<th>"._("Message")."</th>";
		PRINT "</tr>\n";
		
		foreach ($events as $result)
		{
			$ID = $result["ID"];
			$item = $result["item"];
			$itemtype = $result["itemtype"];
			$date = $result["date"];
			$service = $result["service"];
			$level = $result["level"];
			$message = $result["message"];
			PRINT "<tr>";
			PRINT "<td>$itemtype: $item</td>";
			PRINT "<td>$date</td>";
			PRINT "<td>$service</td>";
			PRINT "<td>$level</td>";
			PRINT "<td>$message</td>";
			PRINT "</tr>\n";
		}
		PRINT "</table>";
	}
}

function computerInGroup($ID)
{
	$query = "select COUNT(*) from comp_group where (comp_id = $ID)";
	$DB = Config::Database();
	return $DB->getOne($query);
}

function namestatus($status, $html = true)
{
	switch ($status)
	{
		case "new":
			$name = _('NEW');
			break;

		case "wait":
			$name = _('WAIT');
			break;

		case "active":
			$name = _('ACTIVE');
			break;

		case "assigned":
			$name = _('Assigned');
			break;

		case "old":
			$name = _('OLD');
			break;

		case "complete":
			$name = _('COMPLETE');
			break;

		case "duplicate":
			$name = _('Duplicate');
			break;

		default:
			$status = 'unknown';
			$name = _('Unknown!');
	}

	if ($html)
	{
		return "<td class=\"TrackingStatus$status\">$name</td>";
	}
	else
	{
		return $name;
	}
}

function namepriority($priority, $html = true)
{
	switch ($priority)
	{
		case 5:
			$name = _('Very High');
			break;

		case 4:
			$name = _('High');
			break;

		case 3:
			$name = _('Normal');
			break;

		case 2:
			$name = _('Low');
			break;

		case 1:
			$name = _('Very Low');
			break;

		default:
			$priority = 'unknown';
			$name = _('Unknown');
	}

	if ($html)
	{
		return "<td class=\"TrackingPriority$priority\">$name</td>";
	}
	else
	{
		return $name;
	}
}

function array_diff_for_real($full, $remove)
{
	$new = array();
	foreach ($full as $f)
	{
		$found = false;
		
		foreach ($remove as $r)
		{
			if ($f == $r)
			{
				$found = true;
				continue;
			}
		}
		
		if (!$found)
		{
			$new[] = $f;
		}
	}
	
	return $new;
}

/*
string usersDropdown(string ddname[, string ddvalue])
	Returns an HTML SELECT dropdown of all users in the
	database.

	ddname: The name attribute for the SELECT
	ddvalue (optional): The default value of the SELECT
*/
function usersDropdown($ddname, $ddvalue = '')
{
	$dropdown = '';
	$DB = Config::Database();
	$query = "SELECT `name`,fullname FROM users ORDER BY fullname";
	$result = $DB->getAll($query);
	if (!MDB::isError($result))
	{
		$dropdown = "<select name=\"$ddname\">\n";
		// Include a blank
		$dropdown .= "<option value=\"\">None</option>\n";
		foreach ($result as $row)
		{
			$dropdown .= "<option value=\"" . $row['name'] . "\"";
			if (($ddvalue <> '') && ($ddvalue == $row['name']))
			{
				$dropdown .= " selected";
			}
			$dropdown .= ">" . $row['fullname'] . " (" . $row['name'] .
									 ")</option>\n";
		}
	}
	return $dropdown;
}

