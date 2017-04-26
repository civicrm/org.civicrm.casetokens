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
 * Implements hook_civicrm_tokens().
 */
function casetokens_civicrm_tokens(&$tokens) {
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
  if (!empty(\Civi::$statics['casetokens']['case_id'])) {
    $case = civicrm_api3('Case', 'getsingle', array(
      'id' => \Civi::$statics['casetokens']['case_id'],
      'return' => 'case_type_id.definition',
    ));
    $tokens['case_roles'] = array(
      'case_roles.client' => ts('Case Client(s)'),
    );
    foreach ($case['case_type_id.definition']['caseRoles'] as $relation) {
      $relationship = civicrm_api3('RelationshipType', 'getsingle', array('name_b_a' => $relation['name']));
      $role = strtolower(CRM_Utils_String::munge($relation['name']));
      $tokens['case_roles'] += array(
        "case_roles.{$role}_display_name" => $relationship['label_b_a'] . ' - ' . ts('Display Name'),
        "case_roles.{$role}_address" => $relationship['label_b_a'] . ' - ' . ts('Address'),
        "case_roles.{$role}_phone" => $relationship['label_b_a'] . ' - ' . ts('Phone'),
        "case_roles.{$role}_email" => $relationship['label_b_a'] . ' - ' . ts('Email'),
      );
    }
  }
}

/**
 * Implements hook_civicrm_tokens().
 */
function casetokens_civicrm_tokenvalues(&$values, $cids, $job = NULL, $tokens = array(), $context = NULL) {
  if (!empty(\Civi::$statics['casetokens']['case_id'])) {
    $caseId = \Civi::$statics['casetokens']['case_id'];

    // Get client(s)
    $caseContact = civicrm_api3('CaseContact', 'get', array(
      'case_id' => $caseId,
      'options' => array('limit' => 0),
      'contact_id.is_deleted' => 0,
      'return' => array('contact_id.display_name'),
    ));
    $clients = implode(', ', CRM_Utils_Array::collect('contact_id.display_name', $caseContact['values']));

    // Get contacts from case roles
    $relations = civicrm_api3('Relationship', 'get', array(
      'case_id' => $caseId,
      'options' => array('limit' => 0),
      'is_active' => 1,
      'contact_id_a.is_deleted' => 0,
      'return' => array('relationship_type_id.name_b_a', 'contact_id_b'),
    ));
    $contacts = array();
    foreach ($relations['values'] as $rel) {
      $role = strtolower(CRM_Utils_String::munge($rel['relationship_type_id.name_b_a']));
      if (empty($contacts[$role])) {
        $contacts[$role] = civicrm_api3('Contact', 'getsingle', array('id' => $rel['contact_id_b']));
      }
    }

    // Fill tokens
    foreach ($values as &$set) {
      $set['case_roles.client'] = $clients;
      foreach ($contacts as $role => $contact) {
        $set["case_roles.{$role}_display_name"] = $contact['display_name'];
        $set["case_roles.{$role}_email"] = CRM_Utils_Array::value('email', $contact);
        $set["case_roles.{$role}_phone"] = CRM_Utils_Array::value('phone', $contact);
        $set["case_roles.{$role}_address"] = CRM_Utils_Address::format($contact);
      }
    }
  }
}
