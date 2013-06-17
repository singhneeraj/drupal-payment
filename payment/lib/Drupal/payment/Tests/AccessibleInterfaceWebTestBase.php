<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\AccessibleInterfaceWebTestBase.
 */

namespace Drupal\payment\Tests;

use Drupal\Core\TypedData\AccessibleInterface;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides tools to test \Drupal\Core\TypedData\AccessibleInterface objects.
 */
class AccessibleInterfaceWebTestBase extends WebTestBase {

  /**
   * Returns permissions' human-readable titles and their machine names.
   *
   * @param array $permissions
   *   The permissions' machine names.
   *
   * @return string
   */
  function permissionLabel(array $permissions) {
    static $info = NULL;

    if (is_null($info)) {
      $info = module_invoke_all('permission');
    }

    $labels = array();
    foreach ($permissions as $permission) {
      $labels[] = '<strong>' . $info[$permission]['title'] . ' (' . $permission . ')</strong>';
    }

    return implode(', ', $labels);
  }

  /**
   * Tests access to typed data.
   *
   * @param Drupal\Core\TypedData\AccessibleInterface $data
   * @param string $data_label
   *   The entity's human-readable type.
   * @param string $operation
   *   The operation to perform on the entity.
   * @param \Drupal\Core\Session\AccountInterface $authenticated
   *   The account of the authenticated user to test with.
   * @param array $permissions
   *   Permissions to grant authenticated users before testing their access.
   * @param array $access
   *   An array with the following items:
   *   - anonymous (boolean): Whether anonymous users should be able to perform
   *     the operation. Defaults to FALSE.
   *   - root (boolean): Whether the root user (with UID 1) should be able to
   *     perform the operation. Defaults to TRUE.
   *   - authenticated_with_permissions (boolean): Whether authenticated users
   *     (with UID 2) who have all the required permissions should be able to
   *     perform the operation. Defaults to TRUE.
   *   - authenticated_without_permissions (boolean): Whether authenticated
   *     users that do not have all the required permissions should be able to
   *     perform the operation. Defaults to FALSE.
   *
   * @return NULL
   */
  function assertDataAccess(AccessibleInterface $data, $data_label, $operation, AccountInterface $authenticated, array $permissions = array(), array $access = array()) {
    $user_access_permissions = &drupal_static('user_access');

    // Create the user accounts.
    $anonymous = drupal_anonymous_user();
    $root = entity_load('user', 1);

    $comment = $data && isset($data->uid) ? ' with UID ' . $data->uid : NULL;

    // Merge in defaults.
    $access += array(
      'anonymous' => FALSE,
      'root' => TRUE,
      'authenticated_with_permissions' => TRUE,
      'authenticated_without_permissions' => FALSE,
    );

    // Test anonymous users.
    $can = $access['anonymous'] ? 'can' : 'cannot';
    $this->assertEqual($data->access($operation, $anonymous), $access['anonymous'], "An anonymous user $can perform operation <strong>$operation</strong> on <strong>$data_label</strong>$comment without permission(s) " . $this->permissionLabel($permissions));

    // Test UID 1.
    $can = $access['root'] ? 'can' : 'cannot';
    $this->assertEqual($data->access($operation, $root), $access['root'], "The root user (UID 1) $can perform operation <strong>$operation</strong> on <strong>$data_label</strong>$comment without permission(s) " . $this->permissionLabel($permissions));

    // Test authenticated users with all permissions.
    if ($permissions) {
      foreach ($authenticated->getRoles() as $rid) {
        $authenticated->removeRole($rid);
      }
      $rid = $this->drupalCreateRole($permissions);
      $authenticated->addRole($rid);
      $authenticated->save();
      $with = $permissions ? 'with' : 'without';
      $can = $access['authenticated_with_permissions'] ? 'can' : 'cannot';
      $this->assertEqual($data->access($operation, $authenticated), $access['authenticated_with_permissions'], "An authenticated user (UID $authenticated->uid) $can perform operation <strong>$operation</strong> on <strong>$data_label</strong>$comment with permission(s) " . $this->permissionLabel($permissions));
    }

    // Test authenticated users without all permissions.
    foreach ($permissions as $i => $permission) {
      $assert_permissions = $permissions;
      unset($assert_permissions[$i]);
      foreach ($authenticated->getRoles() as $rid) {
        $authenticated->removeRole($rid);
      }
      $rid = $this->drupalCreateRole($assert_permissions);
      $authenticated->addRole($rid);
      $authenticated->save();
      drupal_static_reset();
      drupal_flush_all_caches();
      $can = $access['authenticated_without_permissions'] ? 'can' : 'cannot';
      $this->assertFalse($data->access($operation, $authenticated), "An authenticated user (UID $authenticated->uid) $can perform operation <strong>$operation</strong> on <strong>$data_label</strong>$comment without permission " . $this->permissionLabel(array($permissions[$i])));
    }
  }
}
