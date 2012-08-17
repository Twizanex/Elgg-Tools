<?php
/**
 * Register the ENTITY_CLASS class for the object/SUBTYPE subtype
 */

if (get_subtype_id('object', 'SUBTYPE')) {
	update_subtype('object', 'SUBTYPE', 'ENTITY_CLASS');
} else {
	add_subtype('object', 'SUBTYPE', 'ENTITY_CLASS');
}
