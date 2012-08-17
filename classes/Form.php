<?php
/**
 * Basic Form Handling class for Elgg Object Entities
 *
 * @author Charles Coleman
 */
class Form {
    
    /**
     * Entity
     * @var ElggObject
     */
    protected $entity = NULL;
    
    /**
     * Sticky form name to use, derived from entity object subtype
     * @var string 
     */
    protected $sticky = '';

    /**
     * Form fields
     * @var array
     */
    protected $fields = array();
    
    /**
     * If submitted values has been set for fields
     * @var array
     */
    protected $setSubmittedValues = FALSE;

    /**
     * Error messages
     * @var array
     */
    protected $validationErrors = array();

    /**
     * Setup form
     * 
     * @param ElggObject $entity Elgg Object entity (can be new or existing)
     * @return array Form details
     */
    function __construct($entity) {
        
        // ensure entity is instance of ElggObject
        if (!($entity instanceof ElggObject)) {
            register_error(elgg_echo('form:error:not_elggobject'));
            return FALSE;
        }

        // set variables
        $this->entity = $entity;
        $this->fields = $this->entity->form;
        
        // set sticky based on entity subtype
        $this->sticky = $this->entity->getSubtype();
    }

    /**
     * Display form
     * 
     * @param array $options Set options for display. Array in format:
     * 
     * form_vars => NULL|ARR form vars to be sent to form view
     * 
     * submit => NULL|STR submit button text
     * 
     * action => NULL|STR action URL for form
     * 
     * @return string Form HTML
     */
    public function display($options = array()) {
        
        // set default options
        $defaults = array(
            'form_vars' => array(),
            'submit' => elgg_echo('save'),
            'action' => $this->entity->getSubtype() . '/save'
        );
        $options = array_merge($defaults, $options);
        $file_uploads = FALSE;
        
        // get form values
        $values = $this->getFormValues();
        
        // get form
        $form_body = '';
        foreach ($this->fields as $name => $data) {
            
            // check if there are file uploads
            if (!$file_uploads && $data['type'] == 'file') {
                $file_uploads = TRUE;
            }
            
            // get field HTML
            switch ($data['type']) {
                
                case 'html':
                    // add html to form
                    $form_body .= $data;
                    break;
                
                case 'categories':
                    // display categories field
                    $data['input'] = elgg_view('input/categories', array('entity' => $this->entity));
                    $form_body .= elgg_view('forms/templates/default', $data);
                    break;
                
                default:
                    
                    // set input
                    $data['input_vars']['name'] = $name;
                    $data['input_vars']['value'] = $values[$name];
                    $data['input'] = elgg_view("input/{$data['type']}", $data['input_vars']);
                    
                    // display form field
                    $form_body .= elgg_view('forms/templates/default', $data);
                    break;
                
            }
        }
        
        // add submit button and hidden entity guid
        $form_body .= elgg_view('forms/templates/submit', array('value' => $options['submit']));
        $form_body .= elgg_view('input/hidden', array('name' => 'guid', 'value' => $this->entity->getGUID()));
        
        // set body for form
        $options['form_vars']['body'] = $form_body;
        
        // if there were any file uploads
        if ($file_uploads) {
            $options['form_vars']['enctype'] = 'multipart/form-data';
        }
        
        // get and return from
        return elgg_view_form($options['action'], $options['form_vars']);
    }

    /**
     * Save the entity with form parameters
     * 
     * @param boolean $validate Set TRUE if validatation rules should be checked. Default TRUE
     * @return boolean Return TRUE if save successful, otherwise return FALSE.
     */
    public function save($validate = TRUE) {
        
        // start a new sticky form session in case of failure
        elgg_make_sticky_form($this->sticky);
        
        // set submitted values
        $this->setSubmittedValues();
        
        // validate form, if required
        if ($validate) {
            if (!$this->validate()) {
                return FALSE;
            }
        }
        
        // checks to ensure that entity will be saved correctly without permission problems
        if (isset($this->fields['container_guid'])) {
            $user = elgg_get_logged_in_user_entity();
            if (!empty($this->fields['container_guid'])) {
                if (!can_write_to_container($user->getGUID(), $this->fields['container_guid'])) {
                    register_error(elgg_echo('form:error:cannot_write_to_container'));
                    return FALSE;
                }
            }
            else {
                unset($this->fields['container_guid']);
            }
        }
        
        // assign values to entity
        $files = array();
        foreach ($this->fields as $name => $data) {
            // files need to be saved separately
            if ($data['type'] == 'file') {
                $files[$name] = $data;
            }
            // site wide categories are saved in categories plugin
            elseif ($data['type'] == 'categories') {
                continue;
            }
            elseif (FALSE === ($this->entity->$name = $data['value'])) {
                register_error(elgg_echo('form:error:assign'));
                elgg_log("Unable to assign $name='{$data['value']}' to entity", 'ERROR');
                return FALSE;
            }
        }
        
        // save entity and return result
        if ($this->entity->save()) {
            
            // once entity is saved, save uploaded files as well
            if (!empty($files)) {
                foreach ($files as $name => $data) {
                    // save file
                    if (!save_entity_file($this->entity, $name)) {
                        register_error(elgg_echo('form:error:fileupload', $data['label']));
                    }
                }
            }
            
            // remove sticky form entries
            elgg_clear_sticky_form($this->sticky);
            
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    /**
     * Validated based on field rules and values
     * 
     * @param boolean $register_errors Set TRUE if errors should register error messages. Default TRUE
     * @return boolean Return TRUE on successfull validation, otherwise return FALSE
     */
    public function validate($register_errors = TRUE) {
        
        // load field values from POST and do sanity, access checking and validation
        foreach ($this->fields as $name => $data) {
            
            $errors = array();
            
            // value for field
            $value = $data['value'];
            
            // error message for required fields
            $args = array(elgg_echo($data['label']));
            $required_error = elgg_echo('form:error:valiation:required', $args);
            
            // do required validation based on type
            switch ($data['type']) {
                
                // validation for files
                case 'file':
                    
                    // if file is an icon, add icon rules if not set
                    if ($name == 'icon' && (!isset($data['rules']) || empty($data['rules']))) {
                        $data['rules']['filetype']['types'] = 'image';
                    }

                    // check if file successfully uploaded
                    if (!isset($value['error']) || $value['error'] != UPLOAD_ERR_OK) {

                        // if no file was uploaded, but is required check if file already exists
                        if ($value['error'] == UPLOAD_ERR_NO_FILE) {
                            // check if file already exists
                            if (isset($data['required']) && $data['required'] && !$this->entity->$name) {
                                $errors[] = $required_error;
                            }
                            // file does not have to be uploaded, so remove from validation and fields
                            else {
                                unset($this->fields[$name]);
                                continue 2;
                            }
                        }
                        // error uploading file
                        else {
                            $errors[] = elgg_echo('form:error:valiation:fileupload', $args);
                        }
                    }
                    
                    break;
                    
                // site wide categories are handled in category plugin
                case 'categories':
                    continue 2;
                    break;
                
                // normal validation
                default:
                    // is field required
                    if (empty($value) && isset($data['required']) && $data['required']) {
                        $errors[] = $required_error;
                    }
                    break;
            }
            
            // if any errors encountered, do not continue validating rules
            if (!empty($errors)) {
                $this->addValidationErrors($errors);
                continue;
            }
            
            // validate field rules
            $this->validateRules($data);
        }
        
        // check if any errors found
        if (empty($this->validationErrors)) {
            return TRUE;
        }
        else {
            if ($register_errors) {
                // add errors to form errors
                $error_msg = elgg_view('forms/templates/errors', array(
                    'error' => elgg_echo('form:error:validation'),
                    'errors' => $this->validationErrors
                ));
                register_error($error_msg);
            }
            return FALSE;
        }
    }
    
    /**
     * Validate rules for a field field
     * 
     * @param array $data Field data
     */
    protected function validateRules($data) {
        
        $errors = array();
        
        // value for field
        $value = $data['value'];
        
        // check if field has any rules that need to be checked
        if (isset($data['rules'])) {
            foreach ($data['rules'] as $rule => $options) {

                // check if rule has any options
                // NB: if it has no options then rule variable should be set
                if (!is_array($options)) {
                    $rule = $options;
                    $options = array();
                }

                // if no message set in options set default error for rule
                if (!isset($options['message'])) {
                    $args = array(elgg_echo($data['label']));
                    $options['message'] = elgg_echo("form:error:valiation:{$rule}", $args);
                    $using_default_message = TRUE;
                }
                else {
                    $using_default_message = FALSE;
                }

                // rule validation
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[] = $options['message'];
                        }
                        break;

                    case 'email':
                        if (!is_email_address($value)) {
                            $errors[] = $options['message'];
                        }
                        break;

                    case 'telephone':
                        if (preg_match("/^[0-9]+$/", $value) || strlen($value) != 10) {
                            $errors[] = $options['message'];
                        }
                        break;
                        
                    case 'filetype':
                        // ensure that types are in array format
                        if (!is_array($options['types'])) {
                            $options['types'] = array($options['types']);
                        }
                        
                        // check filetype
                        $filetype = file_get_simple_type($value['type']);
                        if (!in_array($filetype,$options['types'] )) {
                            
                            // if no error message has been set, use rule default
                            if ($using_default_message) {
                                $args = array(
                                    elgg_echo($data['label']),
                                    implode(', ', $options['types'])
                                );
                                $options['message'] = elgg_echo('form:error:valiation:filetype', $args);
                            }
                            $errors[] = $options['message'];
                        }
                        break;
                    
                    case 'filesize':
                        // @todo: implement filesize rule checking
                        break;
                }
            }
        }
        
        // add errors
        $this->addValidationErrors($errors);
    }
    
    /**
     * Get values for form (retrieved from defaults, existing entity and sticky from)
     * 
     * @return array Array of form values
     */
    public function getFormValues() {
        
        // default values
        $values = array();
        $fields = array_keys($this->fields);
        foreach ($this->fields as $field => $data) {
            if (isset($data['default'])) {
                $values[$field] = $data['default'];
            }
        }
        
        // use existing entity data, if available
        if ($this->entity->guid) {
            foreach ($fields as $field) {
                $values[$field] = $this->entity->$field;
            }
        }
        
        // use sticky form values, if available
        if (elgg_is_sticky_form($this->sticky)) {
            $sticky_values = elgg_get_sticky_values($this->sticky);
            foreach ($sticky_values as $key => $value) {
                $values[$key] = $value;
            }
        }
        
        // clear sticky form
        elgg_clear_sticky_form($this->sticky);
        
        return $values;
        
    }
    
    /**
     * Set submitted values for form fields (retrieved from post values)
     */
    public function setSubmittedValues() {
        
        if (!$this->setSubmittedValues) {
            
            // mark that values has been set
            $this->setSubmittedValues = TRUE;

            // load field values from POST and defaults and do sanity checking
            foreach ($this->fields as $name => $data) {

                // set default value, if available
                if (isset($data['default'])) {
                    $default = $data['default'];
                }
                else {
                    $default = NULL;
                }

                // get input
                $value = get_input($name, $default);

                // sanity checking before adding to values array
                switch ($data['type']) {
                    case 'tags':
                        if ($value) {
                            $this->fields[$name]['value'] = string_to_tag_array($value);
                        } else {
                            unset($this->fields[$name]);
                        }
                        break;
                        
                    case 'file':
                        $this->fields[$name]['value'] = $_FILES[$name];
                        break;
                        
                    // @todo: Check what other inputs should have custom functionality

                    default:
                        $this->fields[$name]['value'] = $value;
                        break;
                }
            }
        }
    }
    
    /**
     * Get submitted values for form. If submitted values not set return empty array
     * @todo Find better method to implement this
     * 
     * @return array Array of submitted form values
     */
    public function getSubmittedValues() {
        
        if (!$this->setSubmittedValues) {
            $this->setSubmittedValues();
        }
        
        $values = array();
        foreach ($this->fields as $name => $data) {
            // ignore files
            if ($data['type'] != 'file') {
                $values[$name] = $data['value'];
            }
        }
        return $values;
    }
    
    /**
     * Get all form errors
     * 
     * @param mixed Array/String containing validation errors
     */
    protected function addValidationErrors($errors) {
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        $this->validationErrors = array_merge($this->validationErrors, $errors);
    }
    
    /**
     * Get all form errors
     * 
     * @return array Array error messages for form
     */
    protected function getValidationErrors() {
        return $this->validationErrors;
    }

}

?>