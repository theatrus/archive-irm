<?php

// A SNMP OO wrapper

require_once('PEAR.php');

class Net_SNMP extends PEAR
{
	// The host or IP-Address
	var $host;

	// snmp v2 community
	var $community;
	var $oid;

	// return 
	var $result = array();

	// debug access
	var $raw_result;

	function Net_SNMP($host,$community="public")
	{
		$this->result=NULL;
		$this->host = $host;
		$this->community=$community;
	}

	function snmpget($oid)
	{
		$this->result=NULL;	
		$this->raw_result=NULL;	
		$this->raw_result = snmpget($this->host,$this->community,$oid);
		$temp=explode(': ', $this->raw_result);
		$this->result['Type']=$temp[0];
		$this->result['Value']=$temp[1];
  
		return $this->result;
	}
  
	function snmpwalk($oid)
	{
		$this->result=NULL;
		$this->raw_result=NULL;
		$this->raw_result = snmprealwalk($this->host,$this->community,$oid);
		
		foreach ($this->raw_result as $key => $value)
		{
			list($type,$val) = explode(': ',$value);
			$this->result[$key]['Type'] = $type;
			$this->result[$key]['Value'] = $val;
		}

		return $this->result;
	}

	function getFirstIpFromIfIndex($ifIndex)
	{
		$list = $this->snmpwalk('IP-MIB::ipAdEntIfIndex');

		foreach ($list as $key => $value)
		{
			if ($value["Value"] == $ifIndex)
			{
				$parts=explode(".",$key);
				$ip = implode(".",array_slice($parts,-4));
				break;
			}
		}
		return $ip;
	}

	function SNMPHTMLping() {
		$DB = Config::Database();
		$ip = $this->host;

		if ($ip != "" OR $ip != "DHCP" OR $ip != "dhcp") {
			$out = exec(EscapeShellCmd("ping -c 1 -n -i 1 $ip"),$dummy_array, $ping_return);
		}
		if  ( $ip == "DHCP" OR $ip == "dhcp" ) {
			$hstatus = "<font color=orange>"._("DHCP")."</font>";	
		} else if ($ping_return == 2) {
			$hstatus = "<font color=red>"._("DOWN")."</font>";
		} else if ($ping_return == 0) {
			$hstatus = "<font color=green>"._("UP")."</font>";
		} else {
			$hstatus = _("UNKNOWN ERROR");
		}
		return $hstatus;
	}
}
