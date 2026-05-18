<?php

require_once 'casetokens.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function casetokens_civicrm_config(&$config) {
  _casetokens_civix_civicrm_config($config);
  \Civi::dispatcher()->addSubscriber(new \Civi\Casetokens\TokenListener());
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function casetokens_civicrm_install() {
  _casetokens_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function casetokens_civicrm_enable() {
  _casetokens_civix_civicrm_enable();
}

/**
 * Get all the contact fields that are to be removed.
 *
 * @return array
 */
function _casetokens_get_contact_fields_to_remove() {
  return array (
    'hash' => '',
    'api_key' => '',
    'contact_source' => '',
    'email_greeting_id' => '',
    'email_greeting_custom' => '',
    'email_greeting_display' => '',
    'postal_greeting_id' => '',
    'postal_greeting_custom' => '',
    'postal_greeting_display' => '',
    'addressee_id' => '',
    'addressee_custom' => '',
    'addressee_display' => '',
    'primary_contact_id' => '',
    'user_unique_id' => '',
    'current_employer_id' => '',
    'created_date' => '',
    'modified_date' => '',
    'worldregion' => '',
    'group' => '',
    'tag' => '',
    'uf_user' => '',
    'birth_date_low' => '',
    'birth_date_high' => '',
    'deceased_date_low' => '',
    'deceased_date_high' => ''
  );
}

/**
 * Get all the contact fields.
 *
 * @return array
 */
function _casetokens_get_contact_all_fields() {
  try {
    $allFields = civicrm_api3('contact', 'getfields');
  } catch (Throwable $ex) {
  }
  $fields = array();
  $fieldsToRemove = _casetokens_get_contact_fields_to_remove();
  if (!empty($allFields) && !empty($allFields['values'])) {
    foreach ($allFields['values'] as $key => $field) {
      if (!isset($fieldsToRemove[$key])) {
        $fields[$key] = $field['title'];
      }
    }
  }

  return $fields;
}

/**
 * Get the case id when loading tokens.
 *
 * This is hacky for now because of limitations in the token hooks.
 * Ideally case_id would be passed to the hooks; instead we have to rely on _GET and _POST.
 *
 * @return int|string|null
 */
function _casetokens_get_case_id() {
  // Hack to get case id from the url
  if (!empty($_GET['caseid'])) {
    \Civi::$statics['casetokens']['case_id'] = $_GET['caseid'];
  }
  // Extra hack to get it from the entry url after a form is posted
  if (empty(\Civi::$statics['casetokens']['case_id']) && !empty($_POST['entryURL'])) {
    $matches = array();
    preg_match('#caseid=([0-9,]+)#', $_POST['entryURL'], $matches);
    \Civi::$statics['casetokens']['case_id'] = $matches[1] ?? NULL;
  }
  return isset(\Civi::$statics['casetokens']['case_id']) ? \Civi::$statics['casetokens']['case_id'] : NULL;
}
