<?php
#    IRM - The Information Resource Manager
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
#################################################################################


require_once '../../include/irm.inc';
require_once 'include/reports.inc.php';
require_once 'lib/Config.php';
require_once 'include/i18n.php';
require_once '../../pdf/class.ezpdf.php';

AuthCheck("tech");

$pdf = new Cezpdf("","landscape");
$pdf->selectFont('../../pdf/fonts/Helvetica.afm');

$tracking = new Tracking();
$i = 5;
while($i >= 1){
	$tracking->setPriority($i);
	$tracking->getTrackingByPriority();
	$opentracking = $tracking->result;
	$text = _("Open work requests with priority ") . $i;
	$pdf->ezText($text);
	$pdf->ezText("");
	$pdf->ezTable($opentracking,'','',array('width'=>700));
	$pdf->ezNewPage();
	$i--;
}
$pdf->ezStream();
?>
