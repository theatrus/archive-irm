<?

require_once('../include/irm.inc');
require_once('../lib/class.httpupload.php');

$config_path = 'files';

$object = new httpupload($config_path , 'up_file');

if ($object->hasUpload()) {
	if ($object->upload()) {
		$result = 'Your file has been uploaded successfuly.<br>';
		$result .= "Your file (" . $object->getsavedname(false) . ") ";
		$result .= "has been saved to " . $object->getsavedname(true) . "<BR><br>";
		$result .= "File size : " . $object->getuploadsize() . " byte<BR>";
		$result .= "File MIME : " . $object->getuploadtype() . "<BR>";
		$result .= "Orginal name : " . $object->getuploadname() . "<BR>";
		$result .= "Tmp file : " . $object->getuploadtmp() . "<BR>";

		$filename = $object->getsavedname(false);
		echo $filename;

		$files = new Files();
		$files->setDeviceType($_POST['deviceType']);
		$files->setDeviceID($_POST['deviceID']);
		$files->setFileName($filename);
		$files->addRecord();

	} else {
		$result = "There was a problem with your uploaded file.<br>";
		$result .= "Error Code : <b>" . $object->error_code . "</b><br>";
		$result .= "Error Description : <br><br>";
		$result .= $object->get_error();
	}
} else {
	 $result = 'No file was submited.';
}

commonHeader(_('File Upload'));

if (@$result) {
?>

<br><br>
<b>Upload Result:</b><br><br>
<?
echo $result;
}

commonFooter();
