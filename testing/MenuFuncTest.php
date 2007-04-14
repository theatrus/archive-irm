<?php

class MenuFuncTest extends IRM_WebTestCase
{
	function testMenu()
	{
		$this->_Login('Admin', 'admin');
		$this->get(TESTING_BASEURI.'/users/setup-irm.php');
		$this->assertTitle('IRM: System Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertWantedPattern('/Email Options/');
	
		$this->click('Home');
		$this->assertTitle('IRM: Command Center');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Command Center/');

		$this->click('Request Help');
		$this->assertTitle('IRM: Welcome to the IRM Help Desk');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Help Desk/');

		$this->click('Tracking');
		$this->assertTitle('IRM: Tracking');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Tracking/');

		$this->click('Inventory');
		$this->click('Computers');
		$this->assertTitle('IRM: Computers');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Computers/');

		$this->click('Inventory');
		$this->click('Networking');
		$this->assertTitle('IRM: Networking');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Networking/');

		$this->click('Inventory');
		$this->click('Software');
		$this->assertTitle('IRM: Software');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Software/');

		$this->click('Reports');
		$this->assertTitle('IRM: Reports');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Default Report/');

		$this->click('Setup');
		$this->assertTitle('IRM: Setup');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/IRM Configuration/');

		$this->click('Preferences');
		$this->assertTitle('IRM: Setup - Your Preferences');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/change what fields/');

		$this->click('Knowledge Base');
		$this->assertTitle('IRM: Knowledge Base');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/Knowledge Base system/');

		$this->click('FAQ');
		$this->assertTitle('IRM: Frequently Asked Questions');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);
		$this->assertWantedPattern('/IRM FAQ system/');
	}

}
