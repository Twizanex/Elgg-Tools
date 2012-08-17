<?php
/**
 * Icon display
 */

require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");

// get input
$guid = get_input('guid');
$size = strtolower(get_input('size'));

// get entity
$entity = get_entity($guid);
if (!($entity instanceof ElggEntity)) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

// if is the same ETag, content didn't changed.
$etag = $entity->icon . $guid;
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
	header("HTTP/1.1 304 Not Modified");
	exit;
}

// set size
if (!in_array($size, array('large', 'medium', 'small', 'tiny', 'master'))) {
	$size = "medium";
}
if ($size == 'master') {
    $file = $entity->icon;
}
else {
    $file = $size . ".jpg";
}

$success = false;

$prefix = get_entity_file_prefix($entity, 'icon');
$filehandler = new ElggFile();
$filehandler->owner_guid = $entity->owner_guid;
$filehandler->setFilename($prefix . "_" . $file);

$success = false;
if ($filehandler->open("read")) {
	if ($contents = $filehandler->read($filehandler->size())) {
		$success = true;
	}
}

if (!$success) {
	$location = elgg_get_root_path() . "_graphics/icons/default/{$size}.png";
	$contents = @file_get_contents($location);
}

header("Content-type: image/jpeg");
header('Expires: ' . date('r',time() + 864000));
header("Pragma: public");
header("Cache-Control: public");
header("Content-Length: " . strlen($contents));
header("ETag: $etag");
echo $contents;
