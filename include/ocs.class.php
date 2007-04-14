<?php

class OCS
{
	function OCS(){
		$this->ConnectionInfo();
		$this->buildDSN();
		$this->connectToOCSDB();
#		$this->networkMapping();
		$this->softwareList();
	}

	function ConnectionInfo(){
		$this->ocsdb = Config::Get('ocsdb');
		$this->ocsserver = Config::Get('ocsserver');
		$this->ocsport = Config::Get('ocsport');
		$this->ocsuser = Config::Get('ocsuser');
		$this->ocspassword = Config::Get('ocspassword');
	}

	function buildDSN(){
		$this->DSN = 	'mysql://' . 
				$this->ocsuser . ":" . $this->ocspassword . 
				'@' .
				$this->ocsserver . ":" . $this->ocsport .
				'/' . 
				$this->ocsdb ;
	}

	function connectToOCSDB(){
		require_once dirname(__FILE__) . '/../lib/IRMDB.php';
		$this->DB= new IRMDB($this->DSN);
		
	}

	function getData(){
		$this->data = $this->DB->getAll($this->sql);
	}

	function networkMapping(){
		$this->sql = "select * from netmap";
		$this->getData();

		PRINT "<table>";
		foreach($this->data as $row){
			PRINT "<tr><td>" . $row['IP'] . "</td><td>" . $row['MAC'] . "</td></tr>";
		}
		PRINT "</table>";
	}

	function softwareList(){
		$this->sql = "select name,count(*) as Number from softwares group by name order by name asc";
		$this->getData();

		PRINT "<table>";
		foreach($this->data as $row){
			PRINT "<tr>";
			foreach($row as $key=>$value){
				PRINT "<td>$value</td>";
			}
			PRINT "</tr>";
		}
		PRINT "</table>";
	}

}

?>
