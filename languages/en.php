<?php
/**
 * Development tools English language file.
 */

$english = array(

	// messages
    'form:error:not_elggobject' => 'Invalid Object entity received!',
    'form:error:cannot_write_to_container' => 'Insufficient access to save to group.',
    'form:error:assign' => 'Unable to successfully save form, please try again.',
    'form:error:fileupload' => 'Unable to upload file for %s',
    
    // rules messages
    'form:error:validation' => 'The form had the following errors:',
    'form:error:valiation:required' => '%s field is required.',
    'form:error:valiation:email' => '%s must be a valid email address.',
    'form:error:valiation:telephone' => '%s must be a valid South African telephone number in format: 0129876543.',
    
    'form:error:valiation:fileupload' => 'Error uploading %s',
    'form:error:valiation:filetype' => '%s must be one of the following file types: %s',
    'form:error:valiation:filesize' => 'The file for %s is too big. Please ensure it is no bigger than %s',
    
);

add_translation('en', $english);
