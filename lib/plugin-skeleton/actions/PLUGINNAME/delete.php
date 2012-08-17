<?php
/**
 * Delete PLUGINNAME entity
 */

$guid = get_input('guid');
$entity = get_entity($guid);

if (elgg_instanceof($entity, 'object', 'SUBTYPE') && $entity->canEdit()) {
	$container = get_entity($entity->container_guid);
	if ($entity->delete()) {
		system_message(elgg_echo('PLUGINNAME:message:deleted'));
		if (elgg_instanceof($container, 'group')) {
			forward("PLUGINNAME/group/$container->guid/all");
		} else {
			forward("PLUGINNAME/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('PLUGINNAME:error:cannot_delete'));
	}
} else {
	register_error(elgg_echo('PLUGINNAME:error:cannot_edit'));
}

forward(REFERER);