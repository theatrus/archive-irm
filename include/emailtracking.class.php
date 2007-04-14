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
/**
 * Handles collecting email from a specified pop3 account and adding
 * a job request to the database.
 */ 
require_once("lib/pop3.php");
require_once("lib/hildegard.class.php");

class EmailTracking
{
	function EmailTracking()
	{
		$this->GetEmail();
	}

	function GetConnectionDetails()
	{
		$this->pop3server = Config::Get('pop3server');
		$this->pop3user = Config::Get('pop3user');
		$this->pop3password = Config::Get('pop3password');
	}

	function EmailLogin()
	{
		$apop=0;
		$this->pop3->hostname = $this->pop3server;
		$this->pop3->port = 110;		

		$this->error = $this->pop3->Open();

		$this->error = $this->pop3->Login(	$this->pop3user,
							$this->pop3password,
							$apop
						);
	}

	function GetNumberOfMessages()
	{
		$this->pop3->Statistics($this->messages,$this->size);
	}

	function GetInformationFromHeader()
	{
		$mail = new hildegard($this->headers);

		$this->xirm = $mail->xirm;
		$this->Author = $mail->from_name;
		$this->AuthorEmail = $mail->from_addr;
		$this->mailbody .= "Subject: " . htmlentities($mail->subject) . "\n";
	}

	function GetEmail()
	{
		$this->pop3 = new pop3_class;
		$this->GetConnectionDetails();
		$this->EmailLogin();
		$this->GetNumberOfMessages();

		while ($this->messages > 0)
		{
			$this->mailbody = "";
			$this->pop3->RetrieveMessage($this->messages,$this->headers,$this->body,-1);
			$this->GetInformationFromHeader();
			
			for($this->line=0;$this->line<count($this->body);$this->line++)
				$this->mailbody .= $this->body[$this->line] . "\n"; 
			$this->add();
			$this->pop3->DeleteMessage($this->messages);
			$this->messages--;
		}
		$this->pop3->Close();
	}

	function Add()
	{
		if ($this->xirm == "IRM"){
			PRINT "<hr>" . _("A message has been received with an IRM header. Dumping the message") . "<hr />";
		} else {
			$tracking = new Tracking();
			$tracking->Author = $this->Author;
			$tracking->AuthorEmail = $this->AuthorEmail;
			$tracking->WorkRequest = $this->mailbody;
			$tracking->DateEntered = date("Y-m-d H:i:s");	
			$tracking->Status = "new";
			$tracking->ComputerID = 99999;
			$tracking->Priority = 3;
			$tracking->IsGroup = "no";
			$tracking->EmailUpdatesToAuthor = "yes";
			$tracking->Add();

			PRINT "<hr>Added new  email job request from : " . $this->Author . "<hr />";
		}
	}


}

?>
