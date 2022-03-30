/**
 * @file
 * JavaScript behaviors for jquery.inputmask integration.
 */

(function ($, Drupal) {

  'use strict';

  // Revert: Set currency prefix to empty by default #2066.
  // @see https://github.com/RobinHerbots/Inputmask/issues/2066
  if (window.Inputmask) {
    window.Inputmask.extendAliases({
      currency: {
        prefix: '$ ',
        groupSeparator: ',',
        alias: 'numeric',
        placeholder: '0',
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        clearMaskOnLostFocus: false
      },
      currency_negative: {
        prefix: '$ ',
        groupSeparator: ',',
        alias: 'numeric',
        placeholder: '0',
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        clearMaskOnLostFocus: false
      },
      currency_positive_negative: {
        prefix: '$ ',
        groupSeparator: ',',
        alias: 'numeric',
        placeholder: '0',
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        clearMaskOnLostFocus: false
      }
    });
  }

  /**
   * Initialize input masks.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformInputMask = {
    attach: function (context) {
      if (!$.fn.inputmask) {
        return;
      }

      $(context).find('input.js-webform-input-mask').once('webform-input-mask').inputmask();
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for checkboxes.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Adds check all or none checkboxes support.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesAllorNone = {
    attach: function (context) {
      $('[data-options-all], [data-options-none]', context)
        .once('webform-checkboxes-all-or-none')
        .each(function () {
          var $element = $(this);

          var options_all_value = $element.data('options-all');
          var options_none_value = $element.data('options-none');

          // Get all checkboxes.
          var $checkboxes = $element.find('input[type="checkbox"]');

          // Get all options/checkboxes.
          var $options = $checkboxes
            .not('[value="' + options_all_value + '"]')
            .not('[value="' + options_none_value + '"]');

          // Get options all and none checkboxes.
          var $options_all = $element
            .find(':checkbox[value="' + options_all_value + '"]');
          var $options_none = $element
            .find(':checkbox[value="' + options_none_value + '"]');

          // All of the above.
          if ($options_all.length) {
            $options_all.on('click', toggleCheckAllEventHandler);
            if ($options_all.prop('checked')) {
              toggleCheckAllEventHandler();
            }
          }

          // None of the above.
          if ($options_none.length) {
            $options_none.on('click', toggleCheckNoneEventHandler);
            toggleCheckNoneEventHandler();
          }

          $options.on('click', toggleCheckboxesEventHandler);
          toggleCheckboxesEventHandler();

          /**
           * Toggle check all checkbox checked state.
           */
          function toggleCheckAllEventHandler() {
            if ($options_all.prop('checked')) {
              // Uncheck options none.
              if ($options_none.is(':checked')) {
                $options_none
                  .prop('checked', false)
                  .trigger('change', ['webform.states']);
              }
              // Check check all unchecked options.
              $options.not(':checked')
                .prop('checked', true)
                .trigger('change', ['webform.states']);
            }
            else {
              // Check uncheck all checked options.
              $options.filter(':checked')
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
          }

          /**
           * Toggle check none checkbox checked state.
           */
          function toggleCheckNoneEventHandler() {
            if ($options_none.prop('checked')) {
              $checkboxes
                .not('[value="' + options_none_value + '"]')
                .filter(':checked')
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
          }

          /**
           * Toggle check all checkbox checked state.
           */
          function toggleCheckboxesEventHandler() {
            var isAllChecked = ($options.filter(':checked').length === $options.length);
            if ($options_all.length
              && $options_all.prop('checked') !== isAllChecked) {
              $options_all
                .prop('checked', isAllChecked)
                .trigger('change', ['webform.states']);
            }
            var isOneChecked = $options.is(':checked');
            if ($options_none.length
              && isOneChecked) {
              $options_none
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
          }
        });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Toggle other input (text) field.
   *
   * @param {boolean} show
   *   TRUE will display the text field. FALSE with hide and clear the text field.
   * @param {object} $element
   *   The input (text) field to be toggled.
   * @param {string} effect
   *   Effect.
   */
  function toggleOther(show, $element, effect) {
    var $input = $element.find('input');
    var hideEffect = (effect === false) ? 'hide' : 'slideUp';
    var showEffect = (effect === false) ? 'show' : 'slideDown';

    if (show) {
      // Limit the other inputs width to the parent's container.
      // If the parent container is not visible it's width will be 0
      // and ignored.
      var width = $element.parent().width();
      if (width) {
        $element.width(width);
      }

      // Display the element.
      $element[showEffect]();
      // If not initializing, then focus the other element.
      if (effect !== false) {
        $input.trigger('focus');
      }
      // Require the input.
      $input.prop('required', true).attr('aria-required', 'true');
      // Restore the input's value.
      var value = $input.data('webform-value');
      if (typeof value !== 'undefined') {
        $input.val(value);
        var input = $input.get(0);
        // Move cursor to the beginning of the other text input.
        // @see https://stackoverflow.com/questions/21177489/selectionstart-selectionend-on-input-type-number-no-longer-allowed-in-chrome
        if ($.inArray(input.type, ['text', 'search', 'url', 'tel', 'password']) !== -1) {
          input.setSelectionRange(0, 0);
        }
      }
      // Refresh CodeMirror used as other element.
      $element.parent().find('.CodeMirror').each(function (index, $element) {
        $element.CodeMirror.refresh();
      });
    }
    else {
      // Hide the element.
      $element[hideEffect]();
      // Save the input's value.
      $input.data('webform-value', $input.val());
      // Empty and un-required the input.
      $input.val('').prop('required', false).removeAttr('aria-required');
    }
  }

  /**
   * Attach handlers to select other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelectOther = {
    attach: function (context) {
      $(context).find('.js-webform-select-other').once('webform-select-other').each(function () {
        var $element = $(this);

        var $select = $element.find('select');
        var $input = $element.find('.js-webform-select-other-input');

        $select.on('change', function () {
          var isOtherSelected = $select
            .find('option[value="_other_"]')
            .is(':selected');
          toggleOther(isOtherSelected, $input);
        });

        var isOtherSelected = $select
          .find('option[value="_other_"]')
          .is(':selected');
        toggleOther(isOtherSelected, $input, false);
      });
    }
  };

  /**
   * Attach handlers to checkboxes other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformCheckboxesOther = {
    attach: function (context) {
      $(context).find('.js-webform-checkboxes-other').once('webform-checkboxes-other').each(function () {
        var $element = $(this);
        var $checkbox = $element.find('input[value="_other_"]');
        var $input = $element.find('.js-webform-checkboxes-other-input');

        $checkbox.on('change', function () {
          toggleOther(this.checked, $input);
        });

        toggleOther($checkbox.is(':checked'), $input, false);
      });
    }
  };

  /**
   * Attach handlers to radios other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRadiosOther = {
    attach: function (context) {
      $(context).find('.js-webform-radios-other').once('webform-radios-other').each(function () {
        var $element = $(this);

        var $radios = $element.find('input[type="radio"]');
        var $input = $element.find('.js-webform-radios-other-input');

        $radios.on('change', function () {
          toggleOther(($radios.filter(':checked').val() === '_other_'), $input);
        });

        toggleOther(($radios.filter(':checked').val() === '_other_'), $input, false);
      });
    }
  };

  /**
   * Attach handlers to buttons other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformButtonsOther = {
    attach: function (context) {
      $(context).find('.js-webform-buttons-other').once('webform-buttons-other').each(function () {
        var $element = $(this);

        var $buttons = $element.find('input[type="radio"]');
        var $input = $element.find('.js-webform-buttons-other-input');
        var $container = $(this).find('.js-webform-webform-buttons');

        // Create set onchange handler.
        $container.on('change', function () {
          toggleOther(($(this).find(':radio:checked').val() === '_other_'), $input);
        });

        toggleOther(($buttons.filter(':checked').val() === '_other_'), $input, false);
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for options elements.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach handlers to options buttons element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformOptionsButtons = {
    attach: function (context) {
      // Place <input> inside of <label> before the label.
      $(context).find('label.webform-options-display-buttons-label > input[type="checkbox"], label.webform-options-display-buttons-label > input[type="radio"]').each(function () {
        var $input = $(this);
        var $label = $input.parent();
        $input.detach().insertBefore($label);
      });
    }
  };

})(jQuery, Drupal);
;
/**
 * @file
 * Extends autocomplete based on jQuery UI.
 *
 * @todo Remove once jQuery UI is no longer used?
 */

(function ($, Drupal) {
  'use strict';

  // Ensure the input element has a "change" event triggered. This is important
  // so that summaries in vertical tabs can be updated properly.
  // @see Drupal.behaviors.formUpdated
  $(document).on('autocompleteselect', '.form-autocomplete', function (e) {
    $(e.target).trigger('change.formUpdated');
  });

  // Extend ui.autocomplete widget so it triggers the glyphicon throbber.
  $.widget('ui.autocomplete', $.ui.autocomplete, {
    _search: function (value) {
      this.pending++;
      Drupal.Ajax.prototype.glyphiconStart(this.element);
      this.cancelSearch = false;
      this.source({term: value}, this._response());
    },
    _response: function () {
      var index = ++this.requestIndex;
      return $.proxy(function (content) {
        if (index === this.requestIndex) this.__response(content);
        this.pending--;
        if (!this.pending) Drupal.Ajax.prototype.glyphiconStop(this.element);
      }, this);
    }
  });

})(jQuery, Drupal);
;
/**
 * @file
 * JavaScript behaviors for Likert element.
 */

(function ($, Drupal) {

  'use strict';

  $(document).on('state:required', function (e) {
    if (e.trigger && e.target && e.target.id) {
      var $element = $('#' + e.target.id);
      // Add/remove required from the question labels.
      if ($element.hasClass('webform-likert-table')) {
        if (e.value) {
          $element.find('tr td:first-child label').addClass('js-form-required form-required');
        }
        else {
          $element.find('tr td:first-child label').removeClass('js-form-required form-required');
        }
      }
    }
  });

})(jQuery, Drupal);
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal, displace) {
  function TableHeader(table) {
    var $table = $(table);
    this.$originalTable = $table;
    this.$originalHeader = $table.children('thead');
    this.$originalHeaderCells = this.$originalHeader.find('> tr > th');
    this.displayWeight = null;
    this.$originalTable.addClass('sticky-table');
    this.tableHeight = $table[0].clientHeight;
    this.tableOffset = this.$originalTable.offset();
    this.$originalTable.on('columnschange', {
      tableHeader: this
    }, function (e, display) {
      var tableHeader = e.data.tableHeader;

      if (tableHeader.displayWeight === null || tableHeader.displayWeight !== display) {
        tableHeader.recalculateSticky();
      }

      tableHeader.displayWeight = display;
    });
    this.createSticky();
  }

  function forTables(method, arg) {
    var tables = TableHeader.tables;
    var il = tables.length;

    for (var i = 0; i < il; i++) {
      tables[i][method](arg);
    }
  }

  function tableHeaderInitHandler(e) {
    once('tableheader', $(e.data.context).find('table.sticky-enabled')).forEach(function (table) {
      TableHeader.tables.push(new TableHeader(table));
    });
    forTables('onScroll');
  }

  Drupal.behaviors.tableHeader = {
    attach: function attach(context) {
      $(window).one('scroll.TableHeaderInit', {
        context: context
      }, tableHeaderInitHandler);
    }
  };

  function scrollValue(position) {
    return document.documentElement[position] || document.body[position];
  }

  function tableHeaderResizeHandler(e) {
    forTables('recalculateSticky');
  }

  function tableHeaderOnScrollHandler(e) {
    forTables('onScroll');
  }

  function tableHeaderOffsetChangeHandler(e, offsets) {
    forTables('stickyPosition', offsets.top);
  }

  $(window).on({
    'resize.TableHeader': tableHeaderResizeHandler,
    'scroll.TableHeader': tableHeaderOnScrollHandler
  });
  $(document).on({
    'columnschange.TableHeader drupalToolbarTrayChange': tableHeaderResizeHandler,
    'drupalViewportOffsetChange.TableHeader': tableHeaderOffsetChangeHandler
  });
  $.extend(TableHeader, {
    tables: []
  });
  $.extend(TableHeader.prototype, {
    minHeight: 100,
    tableOffset: null,
    tableHeight: null,
    stickyVisible: false,
    createSticky: function createSticky() {
      this.$html = $('html');
      var $stickyHeader = this.$originalHeader.clone(true);
      this.$stickyTable = $('<table class="sticky-header"></table>').css({
        visibility: 'hidden',
        position: 'fixed',
        top: '0px'
      }).append($stickyHeader).insertBefore(this.$originalTable);
      this.$stickyHeaderCells = $stickyHeader.find('> tr > th');
      this.recalculateSticky();
    },
    stickyPosition: function stickyPosition(offsetTop, offsetLeft) {
      var css = {};

      if (typeof offsetTop === 'number') {
        css.top = "".concat(offsetTop, "px");
      }

      if (typeof offsetLeft === 'number') {
        css.left = "".concat(this.tableOffset.left - offsetLeft, "px");
      }

      this.$html.css('scroll-padding-top', displace.offsets.top + (this.stickyVisible ? this.$stickyTable.height() : 0));
      return this.$stickyTable.css(css);
    },
    checkStickyVisible: function checkStickyVisible() {
      var scrollTop = scrollValue('scrollTop');
      var tableTop = this.tableOffset.top - displace.offsets.top;
      var tableBottom = tableTop + this.tableHeight;
      var visible = false;

      if (tableTop < scrollTop && scrollTop < tableBottom - this.minHeight) {
        visible = true;
      }

      this.stickyVisible = visible;
      return visible;
    },
    onScroll: function onScroll(e) {
      this.checkStickyVisible();
      this.stickyPosition(null, scrollValue('scrollLeft'));
      this.$stickyTable.css('visibility', this.stickyVisible ? 'visible' : 'hidden');
    },
    recalculateSticky: function recalculateSticky(event) {
      this.tableHeight = this.$originalTable[0].clientHeight;
      displace.offsets.top = displace.calculateOffset('top');
      this.tableOffset = this.$originalTable.offset();
      this.stickyPosition(displace.offsets.top, scrollValue('scrollLeft'));
      var $that = null;
      var $stickyCell = null;
      var display = null;
      var il = this.$originalHeaderCells.length;

      for (var i = 0; i < il; i++) {
        $that = $(this.$originalHeaderCells[i]);
        $stickyCell = this.$stickyHeaderCells.eq($that.index());
        display = $that.css('display');

        if (display !== 'none') {
          $stickyCell.css({
            width: $that.css('width'),
            display: display
          });
        } else {
          $stickyCell.css('display', 'none');
        }
      }

      this.$stickyTable.css('width', this.$originalTable.outerWidth());
    }
  });
  Drupal.TableHeader = TableHeader;
})(jQuery, Drupal, window.Drupal.displace);;
/**
 * @file
 * Overrides core/misc/vertical-tabs.js.
 */

(function ($, Drupal) {
  "use strict";

  var createSticky = Drupal.TableHeader.prototype.createSticky;
  Drupal.TableHeader.prototype.createSticky = function () {
    createSticky.call(this);
    this.$stickyTable.addClass(this.$originalTable.attr('class')).removeClass('sticky-enabled sticky-table');
  };

})(window.jQuery, window.Drupal);
;
