<?php
namespace Civi\Casetokens;

use Civi\Token\Event\TokenRegisterEvent;
use Civi\Token\Event\TokenValueEvent;
use CRM_Casetokens_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TokenListener implements EventSubscriberInterface {

  /**
   * @return array
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.token.list' => 'onTokenList',
      'civi.token.eval' => 'onTokenEval',
    ];
  }

  /**
   * @param \Civi\Token\Event\TokenRegisterEvent $e
   */
  public function onTokenList(TokenRegisterEvent $e) {
    $caseIdString = \_casetokens_get_case_id();
    if (!$caseIdString) {
      return;
    }

    $caseIds = explode(',', $caseIdString);
    $entity = $e->entity('case_roles');
    $entity->register('client', E::ts('Case Client(s)'));
    $allFields = \_casetokens_get_contact_all_fields();

    // Get roles that are related to case types of selected cases only
    $roles = $this->getRolesForCases($caseIds);
    foreach ($roles as $roleName => $label) {
      $mungedRole = strtolower(\CRM_Utils_String::munge($roleName));
      foreach ($allFields as $key => $field) {
        $entity->register("{$mungedRole}_{$key}", $label . ' - ' . E::ts(ucwords($field)));
      }
    }

    // Add client fields
    foreach ($allFields as $key => $field) {
      $entity->register("client_{$key}", E::ts("Case Client") . " - " . E::ts(ucwords($field)));
    }
  }

  /**
   * @param \Civi\Token\Event\TokenValueEvent $e
   */
  public function onTokenEval(TokenValueEvent $e) {
    $allFields = \_casetokens_get_contact_all_fields();
    $rows = $e->getRows();
    $caseIds = [];
    foreach ($rows as $row) {
      if (!empty($row->context['caseId'])) {
        $caseIds[] = $row->context['caseId'];
      }
    }

    if (empty($caseIds)) {
      return;
    }

    $allCaseData = $this->prefetchCaseData($caseIds, $allFields);

    foreach ($rows as $row) {
      $caseId = $row->context['caseId'] ?? NULL;
      if ($caseId && isset($allCaseData[$caseId])) {
        foreach ($allCaseData[$caseId] as $tokenName => $tokenValue) {
          $row->format('text/html')->tokens('case_roles', $tokenName, $tokenValue);
          $row->format('text/plain')->tokens('case_roles', $tokenName, $tokenValue);
        }
      }
    }
  }

  /**
   * @param array $caseIds
   * @return array
   */
  protected function getRolesForCases($caseIds) {
    $roles = [];
    try {
      $cases = \civicrm_api3('Case', 'get', [
        'id' => ['IN' => $caseIds],
        'return' => ['case_type_id'],
        'options' => ['limit' => 0],
      ]);
      $caseTypeIds = array_unique(\CRM_Utils_Array::collect('case_type_id', $cases['values']));

      if (!empty($caseTypeIds)) {
        $caseTypes = \civicrm_api3('CaseType', 'get', [
          'id' => ['IN' => $caseTypeIds],
          'options' => ['limit' => 0],
          'return' => ['definition'],
        ]);
        foreach ($caseTypes['values'] as $caseType) {
          if (!empty($caseType['definition']['caseRoles'])) {
            foreach ($caseType['definition']['caseRoles'] as $role) {
              $roles[$role['name']] = $role['name'];
            }
          }
        }
      }
    } catch (\Exception $ex) {
    }

    $labels = [];
    foreach ($roles as $name) {
      try {
        $relationshipType = \civicrm_api3('RelationshipType', 'getsingle', ['name_b_a' => $name]);
        $labels[$name] = $relationshipType['label_b_a'];
      } catch (\Exception $ex) {
        $labels[$name] = $name;
      }
    }

    return $labels;
  }

  /**
   * @param array $caseIds
   * @param array $allFields
   * @return array
   */
  protected function prefetchCaseData($caseIds, $allFields) {
    $data = [];
    $today = date('Y-m-d');
    $caseIdsStr = implode(',', array_map('intval', $caseIds));

    // 1. Get clients
    $caseContacts = \civicrm_api3('CaseContact', 'get', [
      'case_id' => ['IN' => $caseIds],
      'options' => ['limit' => 0],
      'contact_id.is_deleted' => 0,
      'sequential' => 1,
      'return' => ['case_id', 'contact_id.display_name', 'contact_id.id'],
    ]);

    $caseClients = [];
    $caseClientIds = [];
    foreach ($caseContacts['values'] as $cc) {
      $caseClients[$cc['case_id']][] = $cc['contact_id.display_name'];
      $caseClientIds[$cc['case_id']][] = $cc['contact_id.id'];
    }

    // 2. Get relationships
    $query = "SELECT cr.case_id, crt.name_b_a, cr.contact_id_b " .
      "FROM civicrm_relationship cr " .
      "INNER JOIN civicrm_relationship_type crt ON cr.relationship_type_id = crt.id " .
      "INNER JOIN civicrm_contact cc ON cr.contact_id_b = cc.id " .
      "WHERE cr.is_active = 1 AND cr.case_id IN ($caseIdsStr) AND cc.is_deleted = 0 " .
      "AND ((cr.start_date <= '$today' OR cr.start_date IS NULL) AND (cr.end_date >= '$today' OR cr.end_date IS NULL)) " .
      "order by cr.id";
    $dao = \CRM_Core_DAO::executeQuery($query);
    $caseRolesContacts = [];
    while ($dao->fetch()) {
      $role = strtolower(\CRM_Utils_String::munge($dao->name_b_a));
      $caseRolesContacts[$dao->case_id][$role][] = $dao->contact_id_b;
    }

    // 3. Collect all contact IDs to fetch
    $allContactIds = [];
    foreach ($caseClientIds as $clientIds) {
      $allContactIds = array_merge($allContactIds, $clientIds);
    }
    foreach ($caseRolesContacts as $caseId => $roles) {
      foreach ($roles as $roleIds) {
        $allContactIds = array_merge($allContactIds, $roleIds);
      }
    }
    $allContactIds = array_unique(array_filter($allContactIds));

    $contactData = [];
    if (!empty($allContactIds)) {
      $contacts = \civicrm_api3('Contact', 'get', [
        'id' => ['IN' => $allContactIds],
        'options' => ['limit' => 0],
        'return' => array_keys($allFields),
      ]);
      foreach ($contacts['values'] as $contact) {
        $contactData[$contact['id']] = $contact;
      }
    }

    // 4. Assemble data for each case
    foreach ($caseIds as $caseId) {
      $caseData = [];

      // Clients token (Display Names)
      $caseData['client'] = isset($caseClients[$caseId]) ? implode(', ', $caseClients[$caseId]) : '';

      // Client fields tokens (Aggregate all clients for this case)
      if (isset($caseClientIds[$caseId])) {
        foreach ($allFields as $fieldName => $label) {
          $fieldValues = [];
          foreach ($caseClientIds[$caseId] as $clientId) {
            if (isset($contactData[$clientId][$fieldName])) {
              $val = $contactData[$clientId][$fieldName];
              $fieldValues[] = is_array($val) ? implode(', ', $val) : (string) $val;
            }
          }
          if (!empty($fieldValues)) {
            $caseData["client_{$fieldName}"] = implode(', ', array_unique($fieldValues));
          }
        }
      }

      // Role fields tokens (Aggregate all contacts in each role for this case)
      if (isset($caseRolesContacts[$caseId])) {
        foreach ($caseRolesContacts[$caseId] as $role => $roleContactIds) {
          foreach ($allFields as $fieldName => $label) {
            $fieldValues = [];
            foreach ($roleContactIds as $contactId) {
              if (isset($contactData[$contactId][$fieldName])) {
                $val = $contactData[$contactId][$fieldName];
                $fieldValues[] = is_array($val) ? implode(', ', $val) : (string) $val;
              }
            }
            if (!empty($fieldValues)) {
              $caseData["{$role}_{$fieldName}"] = implode(', ', array_unique($fieldValues));
            }
          }
        }
      }

      $data[$caseId] = $caseData;
    }

    return $data;
  }
}
