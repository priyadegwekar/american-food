<?php

namespace Drupal\bookable_calendar;
use Drupal\views\EntityViewsData;
/**
 * Provides the views data for the entity.
 */
class BookingViewsData extends EntityViewsData {
    /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $this->attachDateTimeViewsData($data);
    return $data;
  }

  /**
   * Fix views data integration for the smartdate field.
   */
  protected function attachDateTimeViewsData(&$data) {
    // Automatic integration blocked behind https://www.drupal.org/node/2489476.
    $columns = [
      'value' => 'date',
      'end_value' => 'date',
      'duration' => 'numeric',
      'timezone' => 'standard',
      'rrule' => 'standard',
    ];
    // Provide human-readable property names.
    $labels = [
      'value' => t('Start'),
      'end_value' => t('End'),
      'duration' => t('Duration'),
      'timezone' => t('Timezone'),
      'rrule' => t('Recurring'),
    ];
    // Provide human-readable property help text.
    $desc = [
      'value' => t('The start of the specified date/time range.'),
      'end_value' => t('The end of the specified date/time range.'),
      'duration' => t('The duration of the specified date/time range.'),
      'timezone' => t('The timezone of the specified date/time range.'),
      'rrule' => t('The recurrence rule for the specified date/time range.'),
    ];
    // The set of views handlers we want to manipulate.
    $types = [
      'field',
      'filter',
      'sort',
      'argument',
    ];
    $field_name_base = 'booking_date';
    foreach ($data as $table_name => $table_data) {
      foreach ($columns as $column => $plugin_id) {
        foreach ($types as $type) {
          if (isset($data[$table_name][$field_name_base . '__' . $column][$type]) || $type == 'field') {
            $plugin_id_adjusted = $plugin_id;
            // For certain types, the plugin id needs to change.
            if ($plugin_id == 'standard' && in_array($type, ['filter', 'argument'])) {
              $plugin_id_adjusted = 'string';
            }
            // Override the default data with our custom values.
            $data[$table_name][$field_name_base . '__' . $column][$type]['title'] = 'Date' . ' - ' . $labels[$column];
            $data[$table_name][$field_name_base . '__' . $column][$type]['id'] = $plugin_id_adjusted;
            $data[$table_name][$field_name_base . '__' . $column][$type]['help'] = $desc[$column];
            $data[$table_name][$field_name_base . '__' . $column][$type]['field_name'] = $field_name_base;
            $data[$table_name][$field_name_base . '__' . $column][$type]['property'] = $column;
          }
        }
      }
      // Provide a relationship for the entity type with the entity reference
      // revisions field.
      $args = [
        '@label' => t('Smart date recurring rule'),
        '@field_name' => 'Date',
      ];

      $data[$table_name][$field_name_base . '_rrule']['relationship'] = [
        'title' => t('@label referenced from @field_name', $args),
        'label' => t('@field_name: @label', $args),
        'group' => t('Booking'),
        'help' => t('Appears in: @bundles.', ['@bundles' => implode(', ', ['booking' => 'booking'])]),
        'id' => 'standard',
        'base' => 'smart_date_rule',
        'entity type' => 'smart_date_rule',
        'base field' => 'rid',
        'relationship field' => $field_name_base . '_rrule',
      ];
    }
  }
}