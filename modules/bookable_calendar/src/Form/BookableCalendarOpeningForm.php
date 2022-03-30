<?php

namespace Drupal\bookable_calendar\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the bookable calendar opening entity edit forms.
 */
class BookableCalendarOpeningForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New bookable calendar opening %label has been created.', $message_arguments));
      $this->logger('bookable_calendar')->notice('Created new bookable calendar opening %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The bookable calendar opening %label has been updated.', $message_arguments));
      $this->logger('bookable_calendar')->notice('Updated new bookable calendar opening %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.bookable_calendar_opening.canonical', ['bookable_calendar_opening' => $entity->id()]);
  }

}
