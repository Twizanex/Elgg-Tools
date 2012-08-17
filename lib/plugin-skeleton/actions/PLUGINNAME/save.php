<?php
/**
 * Save PLUGINNAME entity
 */

// keep track if entity is new
$new = false;

// edit or create a new entity
$guid = get_input('guid');
if ($guid) {
	$entity = get_entity($guid);
	if (!elgg_instanceof($entity, 'object', 'SUBTYPE') && $entity->canEdit()) {
		register_error(elgg_echo('PLUGINNAME:error:cannot_edit'));
		forward(get_input('forward', REFERER));
	}
} else {
    $new = true;
	$entity = new ENTITY_CLASS();
	$entity->subtype = 'SUBTYPE';
}

// save entity with form (this will automatically also do validation)
$form = new Form($entity);
if ($form->save()) {
    
    // show success message
    system_message(elgg_echo('PLUGINNAME:message:saved'));
    
    // add to river if new
    if ($new) {
        add_to_river('river/object/SUBTYPE/create', 'create', elgg_get_logged_in_user_guid(), $entity->getGUID());
    }
    
    // redirect to new entity
    forward($entity->getURL());
}
else {
    // form errors are automatically registered
    
    // redirect back to referer
    forward(REFERER);
}
