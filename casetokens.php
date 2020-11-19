<?php

require_once 'casetokens.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function casetokens_civicrm_config(&$config) {
  _casetokens_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function casetokens_civicrm_xmlMenu(&$files) {
  _casetokens_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function casetokens_civicrm_postInstall() {
  _casetokens_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function casetokens_civicrm_uninstall() {
  _casetokens_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function casetokens_civicrm_disable() {
  _casetokens_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function casetokens_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _casetokens_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function casetokens_civicrm_managed(&$entities) {
  _casetokens_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function casetokens_civicrm_caseTypes(&$caseTypes) {
  _casetokens_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function casetokens_civicrm_angularModules(&$angularModules) {
  _casetokens_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function casetokens_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _casetokens_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Get the case id when loading tokens.
 *
 * This is hacky for now because of limitations in the token hooks.
 * Ideally case_id would be passed to the hooks; instead we have to rely on _GET and _POST.
 *
 * @return int|null
 */
function _casetokens_get_case_id() {
  // Hack to get case id from the url
  if (!empty($_GET['caseid'])) {
    \Civi::$statics['casetokens']['case_id'] = $_GET['caseid'];
  }
  // Extra hack to get it from the entry url after a form is posted
  if (empty(\Civi::$statics['casetokens']['case_id']) && !empty($_POST['entryURL'])) {
    $matches = array();
    preg_match('#caseid=(\d+)#', $_POST['entryURL'], $matches);
    \Civi::$statics['casetokens']['case_id'] = CRM_Utils_Array::value(1, $matches);
  }
  return isset(\Civi::$statics['casetokens']['case_id']) ? \Civi::$statics['casetokens']['case_id'] : NULL;
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
 * Implements hook_civicrm_tokens().
 */
function casetokens_civicrm_tokens(&$tokens) {
  $caseId = _casetokens_get_case_id();
  if ($caseId) {
    $case = civicrm_api3('Case', 'getsingle', array(
      'id' => $caseId,
      'return' => 'case_type_id.definition',
    ));
    $tokens['case_roles'] = array(
      'case_roles.client' => ts('Case Client(s)'),
    );
    $allFields = _casetokens_get_contact_all_fields();
    foreach ($case['case_type_id.definition']['caseRoles'] as $relation) {
      try {
        $relationship = civicrm_api3('RelationshipType', 'getsingle', array('name_b_a' => $relation['name']));
        $role = strtolower(CRM_Utils_String::munge($relation['name']));
        foreach ($allFields as $key =>$field) {
          $tokens['case_roles']["case_roles.{$role}_{$key}"] =
            $relationship['label_b_a'] . ' - ' . ts(ucwords($field));
        }
      }
      catch (Throwable $ex) {
      }
    }
    //adding tokens for client case role
    foreach ($allFields as $key =>$field) {
      $tokens['case_roles']["case_roles.client_{$key}"] =
        "Case Client". ' - ' . ts(ucwords($field));
    }
  }
}

/**
 * Implements hook_civicrm_tokens().
 */
function casetokens_civicrm_tokenvalues(&$values, $cids, $job = NULL, $tokens = array(), $context = NULL) {
  $caseId = _casetokens_get_case_id();
  if ($caseId && !empty($tokens['case_roles'])) {
    // Get client(s)
    $caseContact = civicrm_api3('CaseContact', 'get', array(
      'case_id' => $caseId,
      'options' => array('limit' => 0),
      'contact_id.is_deleted' => 0,
      'sequential' => 1,
      'return' => array('contact_id.display_name','contact_id.id'),
    ));
    $clients = implode(', ', CRM_Utils_Array::collect('contact_id.display_name', $caseContact['values']));

    $today = date('Y-m-d', time());

    $query = "SELECT crt.name_b_a, cr.contact_id_b " .
      "FROM civicrm_relationship cr " .
      "INNER JOIN civicrm_relationship_type crt ON cr.relationship_type_id = crt.id " .
      "INNER JOIN civicrm_contact cc ON cr.contact_id_b = cc.id " .
      "WHERE cr.is_active = 1 AND cr.case_id = $caseId AND cc.is_deleted = 0 " .
      "AND ((cr.start_date <= '$today' OR cr.start_date IS NULL) AND (cr.end_date >= '$today' OR cr.end_date IS NULL)) " .
      "order by cr.id";
    $relations = CRM_Core_DAO::executeQuery($query)->fetchAll();

    $contacts = array();
    $allFields = _casetokens_get_contact_all_fields();
    foreach ($relations as $rel) {
      $role = strtolower(CRM_Utils_String::munge($rel['name_b_a']));
      if (empty($contacts[$role])) {
        $contacts[$role] = civicrm_api3('Contact', 'getsingle', array(
          'id' => $rel['contact_id_b'],
          'return' => array_keys($allFields),
          ));
      }
    }
    //fill client values
    if (!empty($caseContact['values']) && !empty($caseContact['values'][0])) {
      $contacts['client'] = civicrm_api3('Contact', 'getsingle', [
        'id' => $caseContact['values'][0]['contact_id.id'],
        'return' => array_keys($allFields),
      ]);
    }
    // Fill tokens
    $caseRolesContact = array();
    foreach ($contacts as $role => $contact) {
      foreach ($contact as $fieldName => $value) {
        if (strpos($fieldName, 'civicrm_value_') !== FALSE) {
          continue;
        }
        if (in_array($fieldName, array_keys($allFields))) {
          $key = "case_roles.{$role}_" . $fieldName;
          $caseRolesContact[$key] = $value;
        }
      }
    }
    $caseRolesContact['case_roles.client'] = $clients;
    foreach ($cids as $cid) {
      $values[$cid] = empty($values[$cid]) ? $caseRolesContact : array_merge($values[$cid], $caseRolesContact);
    }
  }
}
