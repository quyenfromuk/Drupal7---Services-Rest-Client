<?php
/**
 * @file
 * services_rest_client_action_resource.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function _services_rest_client_installed_instances() {
  $field_instances = array();


    // Exported field_instance: 'node-test-field_src_services_end_point'
  $field_instances['node-test-field_src_services_end_point'] = array(
    'bundle' => 'test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'E.g: http://mywebsitename.com/rest/
Include trailing slash',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_src_services_end_point',
    'label' => 'Services End Point',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => -3,
    ),
  );


  // Exported field_instance: 'node-test-field_src_services_password'
  $field_instances['node-test-field_src_services_password'] = array(
    'bundle' => 'test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The password to login to services based website',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 3,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_src_services_password',
    'label' => 'Services Password',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => -1,
    ),
  );

  // Exported field_instance: 'node-test-field_src_services_username'
  $field_instances['node-test-field_src_services_username'] = array(
    'bundle' => 'test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The username to login to services based website',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_src_services_username',
    'label' => 'Services Username',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => -2,
    ),
  );

  

  // Exported field_instance: 'node-src_actionresource-field_src_active'
  $field_instances['node-src_actionresource-field_src_active'] = array(
    'bundle' => 'src_actionresource',
    'default_value' => array(
      0 => array(
        'value' => 0,
      ),
    ),
    'deleted' => 0,
    'description' => 'Select whether this resource is active',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 2,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_src_active',
    'label' => 'Active',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(
        'display_label' => 0,
      ),
      'type' => 'options_onoff',
      'weight' => 3,
    ),
  );

  // Exported field_instance: 'node-src_actionresource-field_src_resource_type'
  $field_instances['node-src_actionresource-field_src_resource_type'] = array(
    'bundle' => 'src_actionresource',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Select the entity type.  You can add to this list by supplying machine name of entity',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 3,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_src_resource_type',
    'label' => 'Resource Type',
    'required' => 1,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 1,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Active');
  t('Resource Type');
  t('Select the entity type.  You can add to this list by supplying machine name of entity');
  t('Select whether this resource is active');

  return $field_instances;
}
