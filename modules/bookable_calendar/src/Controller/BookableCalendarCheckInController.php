<?php

namespace Drupal\bookable_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\bookable_calendar\Entity\BookableCalendar;
use Drupal\bookable_calendar\Entity\BookingContact;

/**
 * Returns responses for Bookable Calendar routes.
 */
class BookableCalendarCheckInController extends ControllerBase {

  /**
  * Builds the response.
  */
  public function build(BookableCalendar $bookable_calendar) {

    return [
      '#theme' => 'admin_booking_list',
      'rows' => []
    ];
  }
}
