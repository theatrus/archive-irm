<?php

class PageHistory
{
	var $history = array();
	
	function Add($page)
	{
		array_unshift($this->history, $page);
		
		// Keep the size of this thing under control
		if (count($this->history) > 10)
		{
			$this->history = array_slice($this->history, 0, 10);
		}
	}
	
	function Rollback()
	{
		array_shift($this->history);
	}

	function Current()
	{
		return $this->history[0];
	}

	function Previous()
	{
		return $this->history[1];
	}
}
