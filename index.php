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

require_once dirname(__FILE__) . '/include/mdbcheck.php';
require_once dirname(__FILE__) . '/include/installer.php';
require_once dirname(__FILE__) . '/include/irm.inc';
require_once dirname(__FILE__) . '/include/i18n.php';
require_once dirname(__FILE__) . '/lib/Config.php';
require_once dirname(__FILE__) . '/lib/IRMDB.php';
require_once dirname(__FILE__) . '/lib/Databases.php';

$viewRequest = array (	'Section' => 'menu',
			'Header' => _("View Request"),
			'Info' => _('If you have put in a help request and you know the ID number you can 
 view the progress of the request by entering the ID in the box below'),
			'Submit' => _('View Request'),
			'Fields' => formAction()
				. irmConnect()
				. '<input type="hidden" name="redirect" value="users/tracking-index.php?action=detail&ID=">'
				. '<input type="text" name="ID" value="">'
			);

$introduction = array (	'Section' => 'main',
			'Header' =>  _('Introduction to IRM'),
			'Info' =>_('IRM is a multi-user computer, software, peripheral and problem tracking system.
You can use IRM, depending on your user-level, to view, edit, and add
computer systems to a database with an extensive list of fields.  You can
also view and post jobs if you have a problem with a computing resource.') ,
			'Submit' => '',
			'Fields' => ''
			);

$faq = array (	'Section' => 'main',
			'Header' => _('Frequently Asked Questions'),
			'Info' => _('Helpdesk personel get many questions - many of which are
repeated many times. A FAQ, which is a list of frequently asked questions -
and their answers, intends to provide a quick and easy way to help you get
an answer to a questions. If your query isn\'t in this list, feel free to
post a request for help.'),
			'Submit' =>_('Read FAQ') ,
			'Fields' => formAction() 
				. irmConnect()
				. '<input type="hidden" name="redirect" value="users/faq-index.php">'
			);

$request = array (	'Section' => 'main' ,
			'Header' => _('Request Help'),
			'Info' =>_('You can request help without logging in to IRM. To do this you
need to select the appropriate department, click the <b>Help</b> button
below and then follow the instructions. Your request will be filed under the
user name of <b>guest</b> so you will need to ensure that the contact
information is correct if you wish to recieve updates and keep in touch with
the helpdesk.') ,
			'Submit' => _('Request Help'),
			'Fields' => formAction()
				. irmConnect()
			);

$login = array (	'Section' => 'menu',
			'Header' => _('IRM Login'),
			'Submit' => _('Login'),
			'Fields' => loginCheck()
				. formAction()
				. "<br />"
				. _('Username') . '<br /><input type="text" name="name" value=""><br />'
				. _('Password') . '<br /><input type="password" name="password" value=""><br>'
				. redirectCheck()
			);

$status = array (	'Section' => 'menu',
			'Header' => _('Current Status'),
			'Info' => currentStatus(),
			'Submit' => '',
			);

$Content = array (	$login,
			$status,
			$viewRequest,
			$introduction,
			$faq,
			$request
			);

$allMenuContent = "";
$allMainContent = "";

$Page = new IrmFactory();

foreach($Content as $content){
	$Page->assign('header', $content['Header']);
	$Page->assign('body', $content['Info'] . formSubmit($content['Fields'], $content['Submit']));

	if($content['Section'] == "menu"){
		$allMenuContent .= $Page->fetch('section.html.php');
	} elseif($content['Section'] == "main"){
		$allMainContent .= $Page->fetch('section.html.php');
	}
}

$Page->assign('title', _('IRM - The Information Resource Manager'));
$Page->assign('stylesheet', 'styles/default.css');
$Page->assign('content_nav', '<h1>' . _('IRM - The Information Resource Manager') . '</h1>');
$Page->assign('content_menu', $allMenuContent);
$Page->assign('content_main', $allMainContent);

$Page->display('layout.html.php');

?>
