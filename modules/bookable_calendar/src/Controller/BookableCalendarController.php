<?php

namespace Drupal\bookable_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\bookable_calendar\Entity\BookableCalendarOpeningInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for Bookable Calendar routes.
 */
class BookableCalendarController extends ControllerBase {
  protected $entity_type_manager;
  protected $instance_storage;
  protected $contact_storage;

  function __construct()
  {
    $this->entity_type_manager = \Drupal::service('entity_type.manager');
    $this->instance_storage = $this->entity_type_manager->getStorage('bookable_calendar_opening_inst');
    $this->contact_storage = $this->entity_type_manager->getStorage('booking_contact');
  }

}
