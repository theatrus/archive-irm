<?php

class DeviceFuncTest extends IRM_WebTestCase
{
	function testDevices()
	{
		$this->_Login('Admin', 'admin');
		$this->get(TESTING_BASEURI.'/users/setup-irm.php');
		$this->assertTitle('IRM: System Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertWantedPattern('/Email Options/');
	
		$this->click('Setup');
		$this->assertTitle('IRM: Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Setup Devices/');

		$this->click('Setup Devices');
		$this->assertTitle('IRM: Setup - Devices');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Setup - Devices/');
		
		$this->assertWantedPattern('/Add new devices type/');
		$this->assertWantedPattern('/Existing device types/');
		$this->assertNoUnwantedPattern('/Printer/');

		$this->assertField('newdevice');
		$this->assertTrue($this->setField('newdevice', 'Printer'));
		$this->clickSubmit('Add Device');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Setup - Devices/');

		$this->assertWantedPattern('/Existing device types/');
		$this->assertWantedPattern('/Printer/');
		
		$this->assertWantedPattern('/Edit Fields/');
	
		$this->clickSubmit('Edit Fields');
		$this->assertWantedPattern('/Editing Fields for Printer device type/');
		$this->assertWantedPattern('/Field Name/');
		$this->assertWantedPattern('/Type of Data/');
		$this->assertWantedPattern('/name/');

		$this->assertNoUnwantedPattern('/Manufacturer/');

		$this->assertWantedPattern('/Add new field/');
	
		$this->assertField('field_name');
		$this->assertField('datatype');
		$this->assertField($this->setField('datatype','string'));
		$this->assertField($this->setField('datatype','textarea'));
		$this->assertField($this->setField('datatype','boolean'));
		$this->assertTrue($this->setField('datatype', 'Textarea'));
		$this->assertTrue($this->setField('datatype', 'Boolean'));
		$this->assertTrue($this->setField('datatype', 'String'));
		$this->assertTrue($this->setField('field_name','Manufacturer'));
		$this->clickSubmit('Add Field');

		$this->assertTrue($this->setField('datatype', 'boolean'));
		$this->assertTrue($this->setField('field_name','Working'));
		$this->clickSubmit('Add Field');

		$this->assertWantedPattern('/Manufacturer/');
		$this->assertWantedPattern('/Working/');
	}

	function testDeviceMenu()
	{
		$this->_Login('Admin', 'admin');
		$this->assertTitle('IRM: Command Center');

		$this->click('Home');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertNoUnwantedPattern('/Devices/');
		$this->assertNoUnwantedPattern('/Computers \|/');
		$this->assertNoUnwantedPattern('/Networking \|/');
		$this->assertWantedPattern('/Inventory/');
		$this->click('Inventory');
		$this->assertWantedPattern('/Computers/');
		$this->assertWantedPattern('/Networking/');
		$this->assertWantedPattern('/Software/');
		$this->click('Computers');
		$this->assertWantedPattern('/computer Management/');
		$this->click('Inventory');
		$this->click('Networking');
		$this->assertWantedPattern('/networking Management/');

		$this->click('Inventory');
		$this->assertWantedPattern('/Printer/');
		$this->click('Printer');
		$this->assertWantedPattern('/Printer Management/');
		$this->click('Add Printer');
		$this->assertWantedPattern('/Adding new Printer/');
		$this->assertWantedPattern('/Working/');
		$this->assertWantedPattern('/Manufacturer/');

	}	

	function testAddDevice()
	{
		$this->_Login('Admin', 'admin');
	
		//Add new printer
		$this->get(TESTING_BASEURI.'/users/device-add-form.php?device=Printer');
		$this->assertWantedPattern('/Adding new Printer/');
		$this->asserttrue($this->setfield('name','my printer'));
		$this->asserttrue($this->setfield('Manufacturer','hp'));
		$this->asserttrue($this->setfield('Working','1'));
		$this->clickSubmit('Add');
		// Check confirmation message
		$this->assertWantedPattern('/Added new Printer/');
	}
}
