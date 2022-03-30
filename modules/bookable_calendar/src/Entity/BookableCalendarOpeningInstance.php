<?php

namespace Drupal\bookable_calendar\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bookable_calendar\BookableCalendarOpeningInstanceInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the bookable calendar opening instance entity class.
 *
 * @ContentEntityType(
 *   id = "bookable_calendar_opening_inst",
 *   label = @Translation("Bookable Calendar Opening Instance"),
 *   label_collection = @Translation("Bookable Calendar Opening Instances"),
 *   label_singular = @Translation("bookable calendar opening instance"),
 *   label_plural = @Translation("bookable calendar opening instances"),
 *   label_count = @PluralTranslation(
 *     singular = "@count bookable calendar opening instances",
 *     plural = "@count bookable calendar opening instances",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\bookable_calendar\BookableCalendarOpeningInstanceListBuilder",
 *     "views_data" = "Drupal\bookable_calendar\BookableCalendarOpeningInstanceViewsData",
 *     "access" = "Drupal\bookable_calendar\BookableCalendarOpeningInstanceAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bookable_calendar\Form\BookableCalendarOpeningInstanceForm",
 *       "edit" = "Drupal\bookable_calendar\Form\BookableCalendarOpeningInstanceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "bookable_calendar_opening_inst",
 *   admin_permission = "administer bookable calendar opening instance",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/bookable-calendar/bookable-calendar-opening-instance",
 *     "add-form" = "/admin/content/bookable-calendar/booking-calendar-opening-instance/add",
 *     "canonical" = "/bookable-calendar/booking-calendar-opening-instance/{bookable_calendar_opening_inst}",
 *     "edit-form" = "/admin/content/bookable-calendar/booking-calendar-opening-instance/{bookable_calendar_opening_inst}/edit",
 *     "delete-form" = "/admin/content/bookable-calendar/booking-calendar-opening-instance/{bookable_calendar_opening_inst}/delete",
 *   },
 *   field_ui_base_route = "entity.bookable_calendar_opening_inst.settings",
 * )
 */
class BookableCalendarOpeningInstance extends ContentEntityBase implements BookableCalendarOpeningInstanceInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['booking'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Booking'))
      ->setDescription(t('Each booking that references this instance.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'target_type' => 'booking'
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => ''
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['booking_opening'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Booking Opening'))
      ->setDescription(t('The Opening this Instance points to.'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setSettings([
        'target_type' => 'bookable_calendar_opening'
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => ''
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['date'] = BaseFieldDefinition::create('smartdate')
      ->setLabel(t('Date'))
      ->setDescription(t('The dates for this opening instance.'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDefaultValue([
        'default_date_type' => 'next_hour',
        'default_date' => '',
        'default_duration_increments' => "30\r\n60|1 hour\r\n90\r\n120|2 hours\r\ncustom",
        'default_duration' => '60',
      ])
      ->setDisplayOptions('form', [
        'type' => 'smartdate_default',
        'weight' => 0,
        'settings' => [
          'modal' => TRUE,
          'default_tz' => 'user'
        ]
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * When an Instance is created update the parent opening to have this one saved on it
   *
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  public function slotsAvailable() {
    $max_slots = $this->maxSlotsAvailable();
    if ($this->slotsAsParties()) {
      $currenty_party_count = $this->partyCount();
      $slots_availble = $max_slots - $currenty_party_count;
    }
    else {
      $existing_bookings = $this->get('booking')->getValue();
      $existing_bookings_count = count($existing_bookings);
      $slots_availble = $max_slots - $existing_bookings_count;
    }
    return $slots_availble;
  }

  public function getParentCalendar() {
    return $this->booking_opening->entity->bookable_calendar->entity;
  }

  public function maxSlotsAvailable() {
    return (int) $this->getParentCalendar()->slots_per_opening->value;
  }

  public function maxPartySize() {
    return (int) $this->getParentCalendar()->max_party_size->value;
  }

  public function isAcceptingBookings() {
    return (boolean) $this->getParentCalendar()->active->value;
  }

  public function calendarName() {
    return (string) $this->getParentCalendar()->title->value;
  }

  public function slotsAsParties() {
    return (bool) $this->getParentCalendar()->slots_as_parties->value;
  }

  /**
   * Get all bookings, then their contacts to know how many
   * parties have been added to this Opening Instance
   *
   * @return int
   */
  public function partyCount() {
    $bookings = $this->get('booking')->getValue();
    $booking_parties = [];
    $booking_storage = \Drupal::entityTypeManager()->getStorage('booking');
    foreach ($bookings as $booking) {
      $loaded_booking = $booking_storage->load($booking['target_id']);
      $booking_contact_id = $loaded_booking->contact->target_id;
      if (!in_array($booking_contact_id, $booking_parties, true)) {
        array_push($booking_parties, $booking_contact_id);
      }
    }

    return (int) count($booking_parties);
  }

  /**
   * Confirm this Instance is not in the past
   *
   * @return boolean
   */
  public function isInPast() {
    $now = strtotime('now');
    $instance_start_date = (int) $this->date->value;
    if ($instance_start_date <= $now) {
      return true;
    }
  }

  /**
   * Grab parent Cal Booking Lead Time and confirm
   * that right now isn't too close to that.
   *
   * @return boolean
   */
  public function isTooSoon() {
    $lead_time_raw = $this->getBookingLeadTime();
    $lead_time = strtotime('now ' . $lead_time_raw);
    $instance_start_date = (int) $this->date->value;

    if ($lead_time >= $instance_start_date) {
      return true;
    }
    return false;
  }

  public function getBookingLeadTime() {
    return $this->getParentCalendar()->booking_lead_time->value;
  }

  /**
   * Grab parent Cal Booking Future Time and confirm
   * that right now isn't too far away from this instance
   *
   * @return boolean
   */
  public function isTooFarAway() {
    $booking_future_time = $this->getBookingFutureTime();

    if (!is_null($booking_future_time)) {
      $future_time = strtotime('now ' . $booking_future_time);
      $instance_start_date = (int) $this->date->value;

      if ($future_time <= $instance_start_date) {
        return true;
      }
    }
    return false;
  }

  public function getBookingFutureTime() {
    return $this->getParentCalendar()->booking_future_time->value;
  }

  public function getSuccessMessage() {
    return $this->getParentCalendar()->success_message->value;
  }
}
