<?php

set_error_handler('backtrace_handler');

function backtrace_handler($errno, $str, $file, $line)
{
	if (error_reporting() == 0)
	{
		return;
	}
	
	echo "$file: $line ($errno) $str\n";
	
	print_r(array_slice(debug_backtrace(), 2, 4));
}
