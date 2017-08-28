<?php

namespace Commerce\Utils\Payment\Form;

/**
 * Base settings form for payment method.
 */
abstract class SettingsFormBase {

  /**
   * AJAX action. Here will be the last of "#parents" of "triggering_elements".
   *
   * @var string
   */
  private $ajaxAction = '';
  /**
   * AJAX parents. Here will be the element "#parents".
   *
   * @var string[]
   */
  private $ajaxParents = [];

  /**
   * Build configuration form for payment method.
   *
   * @param array $form
   *   Payment method settings.
   * @param array $settings
   *   Previously saved settings.
   *
   * @see commerce_utils_form_rules_ui_edit_element_alter()
   */
  abstract public function form(array &$form, array &$settings);

  /**
   * Perform validation of submitted values.
   *
   * @param array $form
   *   Payment method settings.
   * @param array $settings
   *   Submitted settings.
   *
   * @see commerce_utils_settings_form_validate()
   */
  public function validate(array &$form, array &$settings) {
  }

  /**
   * Perform submission operations.
   *
   * @param array $form
   *   Payment method settings.
   * @param array $settings
   *   Submitted settings.
   *
   * @see commerce_utils_settings_form_submit()
   */
  public function submit(array $form, array &$settings) {
  }

  /**
   * Set parents of "$form_state['triggering_element']".
   *
   * @param string[] $parents
   *   Element parents.
   *
   * @see commerce_utils_form_rules_ui_edit_element_alter()
   */
  final public function setAjaxParents(array $parents) {
    $this->ajaxAction = array_pop($parents);
    $this->ajaxParents = $parents;
  }

  /**
   * Returns AJAX parents.
   *
   * @return string[]
   *   AJAX parents.
   */
  final public function getAjaxParents() {
    return $this->ajaxParents;
  }

  /**
   * Returns AJAX action.
   *
   * @return string
   *   AJAX action.
   */
  final public function getAjaxAction() {
    return $this->ajaxAction;
  }

}
