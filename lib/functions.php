<?php

/**
 * Save a file for entity based on field
 * 
 * This function will copy the uploaded file to the correct location
 * and save file related details on the entity
 * 
 * @param ElggEntity $entity Entity to save file for
 * @param string $fieldname Name of field file info will be saved as
 * 
 * @return boolean Return TRUE on success, otherwise return FALSE
 */
function save_entity_file($entity, $fieldname) {
    
    // ensure entity is valid
    if (!($entity instanceof ElggEntity)) {
        return FALSE;
    }
    
    // check if valid upload
    if (isset($_FILES[$fieldname]) && $_FILES[$fieldname]['error'] == UPLOAD_ERR_OK) {
        
        // get prefix
        $prefix = get_entity_file_prefix($entity, $fieldname);
        
        // set filestorename, if icon use the time
        if ($fieldname == 'icon') {
            $filename = time() . '.jpg';
        }
        else {
            $filename = $_FILES[$fieldname]['name'];
        }
        $filestorename = $prefix . '_' . $filename;
        
        // if previous file, delete it
        delete_entity_file($entity, $fieldname);
        
        // setup new file
        $filehandler = new ElggFile();
        $filehandler->owner_guid = $entity->owner_guid;
        $filehandler->setFilename($filestorename);
        $filehandler->open("write");
        $filehandler->write(get_uploaded_file($fieldname));
        $filehandler->close();
        
        // save file information on entity
        $entity->$fieldname = $filename;
        
		
		// generate thumbnails (if image)
		if (substr_count($_FILES[$fieldname]['type'], 'image/')) {
            
            $icon_sizes = elgg_get_config('icon_sizes');

            $thumbtiny = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $icon_sizes['tiny']['w'], $icon_sizes['tiny']['h'], $icon_sizes['tiny']['square']);
            $thumbsmall = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $icon_sizes['small']['w'], $icon_sizes['small']['h'], $icon_sizes['small']['square']);
            $thumbmedium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $icon_sizes['medium']['w'], $icon_sizes['medium']['h'], $icon_sizes['medium']['square']);
            $thumblarge = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $icon_sizes['large']['w'], $icon_sizes['large']['h'], $icon_sizes['large']['square']);
            if ($thumbtiny) {
                $thumb = new ElggFile();
                $thumb->owner_guid = $entity->owner_guid;
                $thumb->setMimeType('image/jpeg');

                $thumb->setFilename($prefix."_tiny.jpg");
                $thumb->open("write");
                $thumb->write($thumbtiny);
                $thumb->close();

                $thumb->setFilename($prefix."_small.jpg");
                $thumb->open("write");
                $thumb->write($thumbsmall);
                $thumb->close();

                $thumb->setFilename($prefix."_medium.jpg");
                $thumb->open("write");
                $thumb->write($thumbmedium);
                $thumb->close();

                $thumb->setFilename($prefix."_large.jpg");
                $thumb->open("write");
                $thumb->write($thumblarge);
                $thumb->close();
            }
        }
        
        return TRUE;
    }
    else {
        return FALSE;
    }
}

/**
 * Delete a file for an entity
 * 
 * @param ElggEntity $entity Entity to delete file for
 * @param string $fieldname Name of field with file info
 * 
 * @return void
 */
function delete_entity_file($entity, $fieldname) {
    
    // ensure entity is valid
    if (!($entity instanceof ElggEntity)) {
        return FALSE;
    }
    
    // get prefix and filestorename
    $prefix = get_entity_file_prefix($entity, $fieldname);
    $filestorename = get_entity_file_storename($entity, $fieldname);
    
    // icon sizes (for images)
    $icons = array(
        'tiny.jpg',
        'small.jpg',
        'medium.jpg',
        'large.jpg'
    );
    
    // set files to delete
    $files = array();
    $files[] = $filestorename;
    foreach ($icons as $icon) {
        $files[] = $prefix . '_' . $icon;
    }
    
    // delete files
    foreach ($files as $file) {
        
        // delete field name metadata
        $entity->deleteMetadata($fieldname);
        
        // set file to delete
        $filehandler = new ElggFile();
        $filehandler->owner_guid = $entity->owner_guid;
        $filehandler->setFilename($file);
        
        // delete if exists
        if ($filehandler->exists()) {
            $filehandler->delete();
        }
    }
}

/**
 * Get file storename of a file for a entity
 * 
 * @param ElggEntity $entity Entity to get file details for
 * @param string $fieldname Name of field file
 * 
 * @return string The file storename for the entity
 */
function get_entity_file_storename($entity, $fieldname) {
    
    // set file storename
    $prefix = get_entity_file_prefix($entity, $fieldname);
    $filestorename = $prefix .  '_' . $entity->$fieldname;
    return $filestorename;
}

/**
 * Get file prefix of a file for a entity
 * 
 * @param ElggEntity $entity Entity to get file details for
 * @param string $fieldname Name of field file
 * 
 * @return string The file prefix for the entity
 */
function get_entity_file_prefix($entity, $fieldname) {
    
    // set prefix based on entity
    $prefix = $entity->type . '/' . $entity->getSubtype();
    $prefix .= '/' . $entity->getGuid() . '_' . $fieldname;
    return $prefix;
}

/**
 * Get image url for an uploaded entity icon
 *
 * @param ElggEntity $entity Entity to get icon URL for
 * @param string $fieldname Name of field image file
 * @param string $size Size of image to display. Default NULL (should only be used with images)
 * 
 * @return string URL of image. If none was found return default icon URL
 */
function get_file_url($entity, $fieldname, $size = NULL) {
    
    // handle icons seperately
    if ($fieldname == 'icon') {
        $path = "icon/$entity->guid/";
    }
    else {
        $path = "files/$entity->guid/$fieldname/";
    }
    
    // return file path
	$file = $entity->$fieldname;
	if ($file) {
        if ($size) {
            $path .= "$size/";
        }
        $path = elgg_get_site_url() . $path . $file;
        return $path;
	}

	return '';
}

/**
 * Advanced search for entities and their metadata
 * 
 * @param string $query Query to search
 * @param array $metadata Array of metadata => value to search. Will only match exact matches
 * @param array $options Array in format:
 * 
 * offset => NULL|INT Offset of query
 * 
 * limit => NULL|INT Limit of entities to retrieve
 * 
 * sort => NULL|STR SQL order clause
 * 
 * order => NULL|STR SQL order direction (ASC or DESC)
 * 
 * type => NULL|STR Entity type. Do not allow searching multiple types for performance issues.
 * 
 * Remaining options will be passed to elgg_get_entities (search_type and order_by will be ignored)
 * 
 * @return array
 */
function search_entities_metadata($query, $metadata, $options = array()) {
    
    $defaults = array(
        'offset' => 0,
        'limit' => 10,
        'sort' => 'relevence',
        'order' => '',
        'type' => '',
        'joins' => array(),
        'wheres' => array()
    );
    $options = array_merge($defaults, $options);
    
    $db_prefix = elgg_get_config('dbprefix');
    
    // search query as prefix
    $options['query'] = $query;
    
    // set search type to entities
    $options['search_type'] = 'entities';
    
    if (is_array($metadata) && !empty($metadata)) {
        
        // sanitise metadata
        $metastrings = array_keys($metadata);
        $metastrings = array_merge($metastrings, array_values($metadata));
        foreach ($metastrings as $k => $string) {
            $metastrings[$k] = sanitize_string($string);
        }

        // get metastrings ids and format
        $sql = "SELECT id, string FROM {$db_prefix}metastrings WHERE string IN ('" . implode("','", $metastrings) . "')";
        $result = get_data($sql);
        $metastrings = array();
        foreach ($result as $metastring) {
            $metastrings[ $metastring->string ] = $metastring->id;
        }

        // find entities that matches all metadata results
        $wheres = array();
        $metadata_count = count($metadata);
        foreach ($metadata as $metaname => $metastring) {
            $wheres[] = "md.name_id = {$metastrings[$metaname]} AND md.value_id = {$metastrings[$metastring]}";
        }
        $sql = "SELECT md.entity_guid, COUNT(md.id) AS count FROM {$db_prefix}metadata md ";
        $sql .= 'WHERE (' . implode(') OR (', $wheres) . ') ';
        $sql .= 'GROUP BY md.entity_guid ';
        $sql .= "HAVING count = {$metadata_count}";
        $result = get_data($sql);
        
        // add matched entities to where clause
        $metadata_entities = array();
        foreach ($result as $entity) {
            $metadata_entities[] = $entity->entity_guid;
        }
        if (!empty($metadata_entities)) {
            $options['wheres'][] = 'e.guid IN (' . implode(',', $metadata_entities) . ')';
        }
    }
    
    if ($options['type']) {
        $typefound = true;

        // set table, join and fields based on entity type
        $db_prefix = elgg_get_config('dbprefix');
        switch ($options['type']) {
            case 'object':
                $type_table = 'oe';
                $join = "JOIN {$db_prefix}objects_entity oe ON e.guid = oe.guid";
                $fields = array('title', 'description');
                break;

            case 'group':
                $type_table = 'ge';
                $join = "JOIN {$db_prefix}groups_entity ge ON e.guid = ge.guid";
                $fields = array('name', 'description');
                break;

            case 'user':
                $type_table = 'ue';
                $join = "JOIN {$db_prefix}users_entity ue ON e.guid = ue.guid";
                $fields = array('name', 'username');
                break;

            case 'site':
                $type_table = 'se';
                $join = "JOIN {$db_prefix}sites_entity se ON e.guid = se.guid";
                $fields = array('name', 'description');
                break;
            
            default:
                $typefound = false;
                break;
        }

        if ($typefound) {
            // set joins and wheres for type
            $options['joins'][] = $join;
            $where = search_get_where_sql($type_table, $fields, $options, FALSE);
            $options['wheres'][] = $where;
        }
    }
    
    // set order by
    $options['order_by'] = search_get_order_by_sql('e', $type_table, $options['sort'], $options['order']);
    
    // get query count
	$options['count'] = TRUE;
	$count = elgg_get_entities($options);
    
	// no need to continue if nothing here.
	if (!$count) {
		return array('entities' => array(), 'count' => $count);
	}
	
	$options['count'] = FALSE;
	$entities = elgg_get_entities($options);

	// add the volatile data for why these entities have been returned.
	foreach ($entities as $entity) {
		$title = search_get_highlighted_relevant_substrings($entity->title, $options['query']);
		$entity->setVolatileData('search_matched_title', $title);

		$desc = search_get_highlighted_relevant_substrings($entity->description, $options['query']);
		$entity->setVolatileData('search_matched_description', $desc);
	}

	return array(
		'entities' => $entities,
		'count' => $count,
	);
}

/**
 * Function to help output data for debugging
 *
 * @param mixed $var
 */
function pr($var) {
  // Get config
  global $CONFIG;

  if (isset($CONFIG->debug)) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
  }
}

/**
 * Function to help output data that exits for debugging
 *
 * @param mixed $var
 */
function prd($var) {
  // Get config
  global $CONFIG;

  if (isset($CONFIG->debug)) {
    pr($var);
    exit;
  }
}

?>
