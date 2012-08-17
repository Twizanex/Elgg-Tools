<?php
/**
 * File display
 */

require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");

// get input
$guid = get_input('guid');
$fieldname = get_input('fieldname');
$size = strtolower(get_input('size'));

// get entity
$entity = get_entity($guid);
if (!($entity instanceof ElggEntity)) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

// set filename
$filename = $entity->$fieldname;

// set size (should only be used for images)
if (in_array($size, array('large', 'medium', 'small', 'tiny'))) {
	$file = $size . ".jpg";
}
else {
    $file = $filename;
}

// set filehandler
$prefix = get_entity_file_prefix($entity, $fieldname);
$filehandler = new ElggFile();
$filehandler->owner_guid = $entity->owner_guid;
$filehandler->setFilename($prefix . "_" . $file);

// get mime type
$mime = ElggFile::detectMimeType($filehandler->getFilenameOnFilestore());
if (!$mime) {
	$mime = "application/octet-stream";
}

// fix for IE https issue
header("Pragma: public");

header("Content-type: $mime");
if (strpos($mime, "image/") !== false || $mime == "application/pdf") {
	header("Content-Disposition: inline; filename=\"$filename\"");
} else {
	header("Content-Disposition: attachment; filename=\"$filename\"");
}

ob_clean();
flush();
readfile($filehandler->getFilenameOnFilestore());
exit;
