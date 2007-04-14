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
require_once dirname(dirname(__FILE__)).'/lib/Config.php';

if (!Config::FileAvailable('lib/MDB.php'))
{
require_once dirname(dirname(__FILE__)).'/include/i18n.php';
?>
<html>
<head>
<title><?php __("Problem with IRM Installation") ?></title>
</head>
<body>
<h1><?php __("Error: MDB not installed or not in the include path") ?></h1>
<p>
<?php __("I failed to find the MDB package installed in any location in your
include path.  This may mean one of two things:") ?>

<ul>
<li><?php __("You haven't got MDB installed; or") ?>
<li><?php __("Your include path does not have the location of your MDB installation.") ?>
</ul>

<p>
<?php __("To remedy the former problem, please run the following command as an
administrator or root user:") ?>

<pre>
pear install -o MDB
</pre>

<p>
<?php __("(You may have to specify a path to the <tt>pear</tt> command).")?>
<?php __("If you do
not have a <tt>pear</tt> command, you should install the basic PEAR system
as packaged by your PHP or Linux distribution.  PEAR is the <b>PHP Extension
and Application Repository</b>, and provides a set of basic services for the
installation of PHP libraries such as MDB.") ?>

<p>
<?php __("If you are sure you have MDB installed, check your include path.
The include path I have is:"); ?>

<pre>
<?php echo ini_get('include_path'); ?>
</pre>

<p>
<?php __("Different paths will be separated with colons, or semicolons on windows).") ?>

<p>
<?php __("Please check that there is a file <tt>MDB.php</tt> in one of the
directories listed above.  If your <tt>MDB.php</tt> file is not in any of
the above locations, or <i>no</i> include path is shown above, you will need
to configure one in your system's <tt>php.ini</tt> file. Unfortunately, the
location for this file varies between Operating Systems and distributions,
so you may have to consult your system documentation or use a file searching
command to find it.") ?>

<p>
<?php __("Once you have found your <tt>php.ini</tt> file, you will need to edit it
using a text editor and modify the <tt>include_path</tt> parameter (search
for it in the <tt>php.ini</tt> file) to include the necessary paths.") ?>

<p>
<?php __("If you have verified that MDB.php exists in a directory specified in
your include_path but you are still seeing this error message, please send
an e-mail to <tt>irm-discuss@lists.sf.net</tt> with relevant information
such as OS and versions of relevant software, and we'll try to help you out.") ?>

</body>
</html>
<?php
}
