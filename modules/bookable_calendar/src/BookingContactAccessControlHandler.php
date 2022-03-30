<?php

namespace Drupal\bookable_calendar;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the booking contact entity type.
 */
class BookingContactAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        $result = AccessResult::allowedIfHasPermission($account, 'view booking contact');
        if (!$result->isAllowed() && $this->checkAccessAlt($entity)) {
          return new AccessResultAllowed();
        }
        return $result;

      case 'update':
        $result = AccessResult::allowedIfHasPermissions($account, ['edit booking contact', 'administer booking contact'], 'OR');
        if (!$result->isAllowed() && $this->checkAccessAlt($entity)) {
          return new AccessResultAllowed();
        }
        return $result;

      case 'delete':
        $result = AccessResult::allowedIfHasPermissions($account, ['delete booking contact', 'administer booking contact'], 'OR');
        if (!$result->isAllowed() && $this->checkAccessAlt($entity)) {
          return new AccessResultAllowed();
        }
        return $result;

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create booking contact', 'administer booking contact'], 'OR');
  }

  /**
   * Take a Booking Contact entity and check for query params
   * email and login_token and validate those match the Entity
   *
   * @param Drupal\bookable_calendar\Entity\BookingContact $entity
   * @return boolean
   */
  protected function checkTokenAccess($entity) {
    $email = \Drupal::request()->query->get('email');
    $token = \Drupal::request()->query->get('login_token');
    if ($email && $token) {
      $valid = $entity->validateLoginToken($email, $token);
      if ($valid) {
        return true;
      }
    }
    return false;
  }

  /**
   * Check if tempstore allows user on this entity
   *
   * @param Drupal\bookable_calendar\Entity\BookingContact $entity
   * @return boolean
   */
  protected function checkTempStore($entity) {
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('booking_contact');
    return $store->get($entity->id());
  }

  /**
   * Check different methods whether this non logged in user
   * has access to this Entity
   *
   * @param Drupal\bookable_calendar\Entity\BookingContact $entity
   * @return boolean
   */
  protected function checkAccessAlt($entity) {
    if ($this->checkTokenAccess($entity) || $this->checkTempStore($entity)) {
      return true;
    }
    return false;
  }

}
