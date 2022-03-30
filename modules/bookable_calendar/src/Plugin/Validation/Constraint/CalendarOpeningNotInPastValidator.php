<?php

namespace Drupal\bookable_calendar\Plugin\Validation\Constraint;

use Drupal\Core\Entity\EntityPublishedInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the CalendarOpeningNotInPast constraint.
 */
class CalendarOpeningNotInPastValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->context->getRoot()->getValue();

    $parent_opening_instance = $entity->booking_instance->entity;
    $in_past = $parent_opening_instance->isInPast();

    if ($in_past) {
      $this->context->addViolation($constraint->inPast, []);
    }
  }

}
