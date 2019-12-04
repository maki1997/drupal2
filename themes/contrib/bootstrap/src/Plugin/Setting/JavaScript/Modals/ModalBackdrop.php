<?php

namespace Drupal\bootstrap\Plugin\Setting\JavaScript\Modals;

use Drupal\bootstrap\Plugin\Setting\SettingBase;

/**
 * The "modal_backdrop" theme setting.
 *
 * @ingroup plugins_setting
 *
 * @BootstrapSetting(
 *   id = "modal_backdrop",
 *   type = "select",
 *   title = @Translation("backdrop"),
 *   description = @Translation("Includes a modal-backdrop element. Alternatively, specify <code>includes</code> for a backdrop which doesn't close the modal on click."),
 *   defaultValue = "true",
 *   groups = {
 *     "javascript" = @Translation("JavaScript"),
 *     "modals" = @Translation("Modals"),
 *     "options" = @Translation("Options"),
 *   },
 *   options = {
 *     "false" = @Translation("Disabled"),
 *     "true" = @Translation("Enabled"),
 *     "includes" = @Translation("Static"),
 *   },
 * )
 */
class ModalBackdrop extends SettingBase {

  /**
   * {@inheritdoc}
   */
  public function drupalSettings() {
    return !!$this->theme->getSetting('modal_enabled');
  }

}
