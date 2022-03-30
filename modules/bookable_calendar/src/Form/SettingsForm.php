<?php

namespace Drupal\bookable_calendar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Bookable Calendar settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bookable_calendar_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bookable_calendar.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bookable_calendar.settings');
    $email_settings = $config->get('email_settings');

    $form['email_settings'] = [
      '#type' => 'container',
      '#tree' => TRUE
    ];
    $form['email_settings']['admin_email'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Admin Email Notification Settings'),
      '#description' => $this->t('The emails sent to admins when bookings are created'),
      '#weight' => 1,
    ];
    $form['email_settings']['admin_email']['subject'] = [
      '#type' => 'textfield',
      '#title' => 'Admin Email Subject',
      '#default_value' => $email_settings !== NULL ? $email_settings['admin_email']['subject'] : 'A new booking was created for [booking_contact:calendar_title]',
    ];
    $form['email_settings']['admin_email']['body'] = [
      '#type' => 'textarea',
      '#title' => 'Admin Email body',
      '#default_value' => $email_settings !== NULL ? $email_settings['admin_email']['body'] : "A new booking was created: \n [booking_contact:values]",
    ];

    $form['email_settings']['user_email'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('User Email Notification Settings'),
      '#description' => $this->t('The emails sent to the user who books a calendar'),
      '#weight' => 2,
    ];
    $form['email_settings']['user_email']['subject'] = [
      '#type' => 'textfield',
      '#title' => 'User Email Subject',
      '#default_value' => $email_settings !== NULL ? $email_settings['user_email']['subject'] : 'Your booking is confirmed',
    ];
    $form['email_settings']['user_email']['body'] = [
      '#type' => 'textarea',
      '#title' => 'User Email body',
      '#default_value' => $email_settings !== NULL ? $email_settings['user_email']['body'] : "You can view your booking here: [booking_contact:hashed_login_url] \n [booking_contact:values]",
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('bookable_calendar.settings')
      ->set('email_settings', $form_state->getValue('email_settings'))
      ->save();

  }

}
