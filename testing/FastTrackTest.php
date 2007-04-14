<?php

class FastTrackFillTest extends IRM_WebTestCase
{
	function testAddDisplay()
	{
		$rows[] = "INSERT INTO fasttracktemplates
				(ID, name, request, response)
				VALUES
				(17, 'foo', 'req', 'respond to me & only me!')";
	
		BulkQueries($rows);
		
		$this->get(TESTING_BASEURI.'/users/tracking-fasttrack.php?AUTOFILL=17');
		$this->assertNoUnwantedPattern(PHP_ERROR_REGEX);

		$this->assertTitle('IRM: FastTrack');

		// A few random fields...
		$this->assertField('ufname', 'Technician User');
		$this->assertField('uemail', 'tech@localhost');

		$this->assertWantedPattern('%<textarea.*name="contents">req</textarea>%i');
		$this->assertWantedPattern('%<textarea.*name="solution">respond to me &amp; only me!</textarea>%i');
	}
}

