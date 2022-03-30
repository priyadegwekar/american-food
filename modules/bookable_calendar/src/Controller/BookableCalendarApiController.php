<?php

namespace Drupal\bookable_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\bookable_calendar\Entity\BookableCalendarOpeningInstance;
use Drupal\bookable_calendar\Entity\BookableCalendar;
use Drupal\bookable_calendar\Entity\BookingContact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for Bookable Calendar routes.
 */
class BookableCalendarApiController extends ControllerBase {
  protected $entity_type_manager;
  protected $instance_storage;
  protected $contact_storage;

  function __construct()
  {
    $this->entity_type_manager = \Drupal::service('entity_type.manager');
    $this->instance_storage = $this->entity_type_manager->getStorage('bookable_calendar_opening_inst');
    $this->contact_storage = $this->entity_type_manager->getStorage('booking_contact');
  }

  public function book(BookableCalendarOpeningInstance $opening_instance, Request $req) {
    // allow for both POST body and form-data
    $post_body = $req->getContent();
    $post_form_data = $req->request->all();
    if ($post_body || $post_form_data) {
      if ($post_body) {
        $body = (array) json_decode($post_body);
        $contact = (array) $body['contact_info'];
      }
      else {
        $contact = $post_form_data;
      }

      $contact['booking_instance'] = [
        'target_id' => $opening_instance->id()
      ];

      if (isset($contact['email']) && isset($contact['party_size'])) {
        $new_contact = $this->contact_storage->create($contact);
        $violations = $new_contact->validate();

        if ($violations->count() > 0) {
          return new JsonResponse([
            'status' => 'failed',
            'message' => 'There was some validation errors'
          ]);
        }
        else {
          $new_contact->save();
          $sucess_message = $opening_instance->getSuccessMessage();
          return new JsonResponse([
            'status' => 'success',
            'message' => $sucess_message
          ]);
        }
      }
      else {
        return new JsonResponse([
          'status' => 'failed',
          'message' => 'not all required parameters were provided'
        ]);
      }
    }
    else {
      return new JsonResponse([
        'status' => 'failed',
        'message' => 'No request body sent'
      ]);
    }
  }


  /**
   * Get all bookines for a given Calendar and time range
   *
   * To deine a custom date range pass in
   * as query params
   * 'start' and 'end' both anything that strtotime will understand
   *
   * @param BookableCalendar $bookable_calendar
   * @param Request $req
   * @return void
   */
  public function getBookings(BookableCalendar $bookable_calendar, Request $req) {
    $date_start = strtotime('today');
    $date_end = strtotime('tomorrow');
    $params = $req->query->all();

    if ($params) {
      if (isset($params['start'])) {
        $date_start = strtotime($params['start']);
      }
      if (isset($params['end'])) {
        $date_end = strtotime($params['end']);
      }
    }
    $opening_storage = \Drupal::entityTypeManager()->getStorage('bookable_calendar_opening');
    $opening_array = [];
    $openings = $opening_storage->loadByProperties([
      'bookable_calendar' => [
        'target_id' => $bookable_calendar->id()
      ]
    ]);
    foreach ($openings as $opening) {
      $opening_array[] = $opening->id();
    }

    $database = \Drupal::database();
    $query = $database->select('bookable_calendar_opening_inst', 'instance');
    $query->condition('instance.booking_opening', $opening_array, 'IN');
    $query->condition('instance.date__value', $date_start, '>=');
    $query->condition('instance.date__end_value', $date_end, '<=');
    $query->fields('instance', [
      'booking_opening',
      'date__value',
      'date__end_value',
      'id'
    ]);
    $todays_instances = $query->execute()->fetchAll();

    $contact_storage = \Drupal::entityTypeManager()->getStorage('booking_contact');
    $booking_storage = \Drupal::entityTypeManager()->getStorage('booking');
    $rows = [];
    foreach ($todays_instances as $instance) {
      $contacts = $contact_storage->loadByProperties([
        'booking_instance' => [
          'target_id' => $instance->id
        ]
      ]);
      foreach ($contacts as $contact) {
        $row_contact = [
          'id' => $contact->id(),
          'checked_in' => (bool) $contact->checked_in->value,
          'email' => $contact->email->value,
          'date' => date('D, m/d/Y - g:ia', $instance->date__value),
          'party_size' => $contact->party_size->value
        ];
        if ($contact->booking->target_id) {
          $booking = $booking_storage->load($contact->booking->target_id);
          $row_contact['created'] = date('D, m/d/Y - g:ia', $booking->created->value);
        }
        $rows[] = $row_contact;
      }
    }
    // sort results by dates
    $dates = array_column($rows, 'date');
    array_multisort($dates, SORT_ASC, $rows);
    return new JsonResponse([
      'data' => $rows
    ]);
  }

  /**
   * Take a Contact and "Check Them In"
   *
   * @param BookingContact $booking_contact
   * @return void
   */
  public function checkIn(BookingContact $booking_contact) {
    $booking_contact->set('checked_in', true);
    $booking_contact->save();
    return new JsonResponse([
      'status' => 'success',
      'message' => $booking_contact->id() . ' successfully checked in'
    ]);
  }

  /**
   * Take a Contact and "Check Them Out"
   *
   * @param BookingContact $booking_contact
   * @return void
   */
  public function checkOut(BookingContact $booking_contact) {
    $booking_contact->set('checked_in', false);
    $booking_contact->save();
    return new JsonResponse([
      'status' => 'success',
      'message' => $booking_contact->id() . ' successfully checked out'
    ]);
  }
}
