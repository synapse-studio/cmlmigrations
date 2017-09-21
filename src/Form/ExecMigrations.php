<?php

namespace Drupal\cmlmigrations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements ExecMigrations.
 */
class ExecMigrations extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmlmigrations_exec';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $form_state->setCached(FALSE);

    $options = [];
    foreach ($extra as $key => $migration) {
      $options[$key] = $migration->label();
    }
    $form['migration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Migration'),
      '#options' => $options,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['inport'] = [
      '#type' => 'submit',
      '#value' => 'Import',
    ];
    $form['actions']['update'] = [
      '#type' => 'submit',
      '#value' => 'Update',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $update = "";
    if ($form_state->getValue('op') == 'Update') {
      $update = "--update";
    }
    $select = $form_state->getValue('migration');
    foreach ($select as $key => $value) {
      if ($value) {
        $command = "drush mi $value $update";
        drupal_set_message($command);
        exec($command);
      }
    }
  }

}
