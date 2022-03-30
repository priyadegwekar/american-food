<?php

namespace Drupal\bookable_calendar;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Notification service.
 */
class Notification {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Undocumented variable
   *
   * @var \Drupal\bookable_calendar\Entity\BookingContact
   */
  protected $bookableContact;

  /**
   * Undocumented variable
   *
   * @var \Drupal\bookable_calendar\Entity\BookingCalendar
   */
  protected $bookableCalendar;

  /**
   * Creates a new ModerationInformation instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager = $mail_manager;
    $this->messenger = $messenger;
  }

  /**
   * Notify the proper users of a new booking taking place
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we need to send a notification on.
   *
   * @return void
   */
  public function sendNotification(EntityInterface $entity) {
    $this->bookableContact = $entity;
    $this->bookableCalendar = $this->bookableContact->getParentCalendar();
    $token_service = \Drupal::token();
    $bookable_calendar_config = \Drupal::config('bookable_calendar.settings')->get('email_settings');

    if ($this->shouldNotifyAdmins()) {
      $admin_emails = $this->getAdminEmails();

      // should grab users langcode
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      foreach ($admin_emails as $email) {
        $to = $email;
        $params['subject'] = $token_service->replace($bookable_calendar_config['admin_email']['subject'], ['booking_contact' => $entity]);
        $params['format'] = 'html';
        $params['message'] = $token_service->replace($bookable_calendar_config['admin_email']['body'], ['booking_contact' => $entity]);
        $result = $this->mailManager->mail('bookable_calendar', 'bookable_calendar_notification', $to, $langcode, $params, NULL, TRUE);
        if ($result['result'] == FALSE) {
          $this->messenger->addError('There was a problem sending notifying an admin of your booking.');
        }
      }
    }

    if ($this->shouldNotifyUser()) {
      if ($this->bookableCalendar->overriddenUserEmails()) {
        $params['subject'] = $token_service->replace($this->bookableCalendar->notification_email_subject->value, ['booking_contact' => $entity]);
        $params['message'] = $token_service->replace($this->bookableCalendar->notification_email_body->value, ['booking_contact' => $entity]);
      }
      else {
        $params['subject'] = $token_service->replace($bookable_calendar_config['user_email']['subject'], ['booking_contact' => $entity]);
        $params['message'] = $token_service->replace($bookable_calendar_config['user_email']['body'], ['booking_contact' => $entity]);
      }
      $to = $this->bookableContact->email->value;

      $params['format'] = 'html';
      ;
      $result = $this->mailManager->mail('bookable_calendar', 'bookable_calendar_notification', $to, $langcode, $params, NULL, TRUE);
      if ($result['result'] == FALSE) {
        $this->messenger->addError('There was a problem emailing you your booking receipt.');
      }
    }
  }

  /**
   * Should we notify the user making the Booking
   *
   * @return boolean
   */
  public function shouldNotifyUser() {
    if ($this->bookableCalendar) {
      return (boolean) $this->bookableCalendar->notification_email->value;
    }
    return false;
  }

  /**
   * Should we notify Admins on new Bookings
   *
   * @return boolean
   */
  public function shouldNotifyAdmins() {
    if ($this->bookableCalendar) {
      return (boolean) $this->bookableCalendar->admin_notification_email->value;
    }
    return false;
  }

  /**
   * Get all emails by Roles and Manually entered and return them in an array.
   *
   * @return array
   */
  public function getAdminEmails() {
    $role_emails = $this->emailRoles();
    $manual_emails = $this->emailManual();
    return array_merge($role_emails, $manual_emails);
  }

  /**
   * Get all emails associated with roles selected on the Bookable Calendar and return them in an array.
   *
   * @return void
   */
  public function emailRoles() {
    $emails_recipients = [];
    $selected_roles = $this->bookableCalendar->get('notify_email_recipient_role')->getValue();

    foreach ($selected_roles as $role) {
      // Get all authenticated users assigned to a specified role.
      $query = \Drupal::database()->select('user__roles', 'ur');
      $query->distinct();
      $query->join('users_field_data', 'u', 'u.uid = ur.entity_id');
      $query->fields('u', ['mail']);
      $query->condition('ur.roles_target_id', $role['target_id']);
      $query->condition('u.status', 1);
      $query->condition('u.mail', '', '<>');
      $query->orderBy('mail');
      $emails = $query->execute()->fetchCol();
      foreach ($emails as $email) {
        $emails_recipients[] = $email;
      }
    }

    return $emails_recipients;
  }

  /**
   * Get all emails manually entered on the Bookable Calendar and return them in an array.
   *
   * @return void
   */
  public function emailManual() {
    $emails_recipients = [];
    $emails_recipients_manual = $this->bookableCalendar->get('notify_email_recipients')->getValue();

    foreach ($emails_recipients_manual as $email) {
      $emails_recipients[] = $email['value'];
    }

    return $emails_recipients;
  }

}
