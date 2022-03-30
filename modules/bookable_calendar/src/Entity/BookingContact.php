<?php

namespace Drupal\bookable_calendar\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bookable_calendar\BookingContactInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Url;

/**
 * Defines the booking contact entity class.
 *
 * @ContentEntityType(
 *   id = "booking_contact",
 *   label = @Translation("Booking Contact"),
 *   label_collection = @Translation("Booking Contacts"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bookable_calendar\BookingContactListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\bookable_calendar\BookingContactAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\bookable_calendar\Form\BookingContactForm",
 *       "edit" = "Drupal\bookable_calendar\Form\BookingContactForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "booking_contact",
 *   admin_permission = "administer booking contact",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/bookable-calendar/booking-contact/add",
 *     "canonical" = "/bookable-calendar/booking_contact/{booking_contact}",
 *     "edit-form" = "/bookable-calendar/booking-contact/{booking_contact}/edit",
 *     "delete-form" = "/admin/content/bookable-calendar/booking-contact/{booking_contact}/delete",
 *     "collection" = "/admin/content/bookable-calendar/booking-contact"
 *   },
 *   field_ui_base_route = "entity.booking_contact.settings"
 * )
 */
class BookingContact extends ContentEntityBase implements BookingContactInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email for this booking'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'email_default',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'email_mailto',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['party_size'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Party Size'))
      ->setDescription(t('The amount of people this booking is for'))
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->addConstraint('CalendarOpeningVacancy')
      ->addConstraint('CalendarOpeningMaxPartySize')
      ->setDisplayOptions('form', [
        'type' => 'number',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'number_integer',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['booking_instance'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Booking Instance'))
      ->setDescription(t('The Opening Instance this Booking is for.'))
      ->setRequired(TRUE)
      ->addConstraint('CalendarOpeningIsActive')
      ->addConstraint('CalendarOpeningNotInPast')
      ->addConstraint('CalendarOpeningTooSoon')
      ->addConstraint('CalendarOpeningTooFarAway')
      ->setSettings([
        'target_type' => 'bookable_calendar_opening_inst'
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => ''
        ],
        'weight' => -5,
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

    $fields['booking'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Booking'))
      ->setDescription(t('Each booking that this contact owns.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'target_type' => 'booking'
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'label' => 'above',
        'weight' => 10,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['checked_in'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Checked In'))
      ->setDescription(t('Whether or not this user has showed up to their booked event'))
      ->setDefaultValue(FALSE)
      ->setSetting('on_label', 'User has checked in')
      ->setDisplayConfigurable('form', FALSE);

    return $fields;
  }

  /**
   * When a Booking Contact is deleted delete all it's individual Bookings
   *
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    $booking_storage = \Drupal::entityTypeManager()->getStorage('booking');
    // for each booking contact deleted delete all it's bookings
    foreach ($entities as $entity) {
      $bookings = $entity->get('booking')->getValue();
      foreach ($bookings as $booking) {
        $loaded_booking = $booking_storage->load($booking['target_id']);
        if ($loaded_booking) {
          $loaded_booking->delete();
        }
      }
    }
  }

  /**
   * On save we need to add all the individual bookings for this contact
   *
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $booking_storage = \Drupal::entityTypeManager()->getStorage('booking');
    $exising_bookings = $this->get('booking')->getValue();
    $party_size = (int) $this->party_size->value;
    $booking_opening_instance = $this->booking_instance->target_id;
    $booking_date = $this->booking_instance->entity->get('date')->getValue();
    $booking_date = reset($booking_date);
    $updating_bookings = false;

    // depending on if we have too much or not enough bookings
    // get those synced up
    if ($party_size > count($exising_bookings)) {
      $bookings_to_add = $party_size - count($exising_bookings);
      $updating_bookings = true;
      for ($i = 0; $i < $bookings_to_add; $i++) {
        $new_booking = $booking_storage->create([
          'contact' => [
            'target_id' => $this->id()
          ],
          'booking_date' => [
            'value' => $booking_date['value'],
            'end_value' => $booking_date['end_value']
          ],
          'booking_instance' => [
            'target_id' => $booking_opening_instance
          ],
        ]);
        $new_booking->save();
        $exising_bookings[] = [
          'target_id' => $new_booking->id()
        ];
      }
    }
    elseif ($party_size < count($exising_bookings)) {
      $bookings_to_remove = count($exising_bookings) - $party_size;
      $updating_bookings = true;
      for ($i = 0; $i < $bookings_to_remove; $i++) {
        $existing_id = $exising_bookings[$i]['target_id'];
        $loaded_booking = $booking_storage->load($existing_id);
        if ($loaded_booking) {
          $loaded_booking->delete();
          unset($exising_bookings[$i]);
        }
      }
    }
    if ($updating_bookings) {
      $this->set('booking', $exising_bookings);
      $this->save();
    }

    if (!$update) {
      // on first save send email and grant user access
      \Drupal::service('bookable_calendar.notification')->sendNotification($this);
      $this->tempGrantAccess();
    }
  }

  public function getParentCalendar() {
    $booking_instance = $this->booking_instance->entity;

    if ($booking_instance) {
      return $booking_instance->getParentCalendar();
    }
    return false;
  }

  public function getRawValues() {
    $timezone = date_default_timezone_get();
    $created = \Drupal::service('date.formatter')->format($this->booking->entity->created->value, 'long_date_12_hour', $timezone);
    $booking_date_formatted = \Drupal::service('date.formatter')->format($this->booking->entity->booking_date->value, 'long_date_12_hour', $timezone);
    $raw_value = 'Booking Created: ' . $created . "\n" .
      'Booking Date: ' . $booking_date_formatted . "\n" .
      'Email: ' . $this->email->value . "\n" .
      'Party Size:' . $this->party_size->value;
    return $raw_value;
  }

  /**
   * MD5 the Contact Email as a pseudo password
   *
   * @return string
   */
  public function generateLoginToken() {
    return md5($this->email->value);
  }

  /**
   * Create a tokenized link to the Contact Edit form
   *
   * @return string
   */
  public function generatePublicLoginLink() {
    $url = Url::fromRoute('entity.booking_contact.edit_form', [
      'booking_contact' => $this->id()
    ], [
      'query' => [
        'email' => $this->email->value,
        'login_token' => $this->generateLoginToken()
      ],
      'absolute' => TRUE
    ]);
    return $url->toString();
  }

  /**
   * Take an email and token and confirm it matches this entity
   *
   * @param string $email
   * @param string $token
   * @return boolean
   */
  public function validateLoginToken($email, $token) {
    $new_token = $this->generateLoginToken();
    if ($new_token == $token && $email == $this->email->value) {
      $this->tempGrantAccess();
      return true;
    }
    return false;
  }

  /**
   * Use Drupal Tempstorage to allow access to anonymous users
   * if they created this Contact
   */
  public function tempGrantAccess() {
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('booking_contact');
    $store->set($this->id(), true);
  }

}
