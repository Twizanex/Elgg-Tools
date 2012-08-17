<?php
/**
 * PLUGINNAME class
 */
class ENTITY_CLASS extends ElggObject {
    
    /**
     * Form fields setup
     */
    public $form = array(
        'icon' => array(
            'type' => 'file',
            'label' => 'icon',
            'required' => false
        ),
        'title' => array(
            'type' => 'text',
            'label' => 'title',
            'required' => true
        ),
        'description' => array(
            'type' => 'longtext',
            'label' => 'description',
            'required' => true
        ),
        'tags' => array(
            'type' => 'tags',
            'label' => 'tags'
        ),
        'categories' => array(
            'type' => 'categories'
        )
    );

	/**
	 * Initialize entity
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();

        // set subtype
		$this->attributes['subtype'] = "SUBTYPE";
        
        // set default access to public
        $this->attributes['access_id'] = ACCESS_PUBLIC;
	}
    
    /**
	 * Delete this entity.
	 *
	 * @param bool $recursive Whether to delete all the entities contained by this entity
	 *
	 * @return bool
	 */
    public function delete($recursive = true) {
        
        // delete image files
        delete_entity_file($this, 'icon');
        
        // delete entity
		return parent::delete($recursive);
    }

}