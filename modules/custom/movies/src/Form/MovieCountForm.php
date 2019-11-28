<?php

/**

 * @file

 * Contains \Drupal\movies\Form\MovieCountForm.

 */

namespace Drupal\movies\Form;

use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Form\FormStateInterface;

class MovieCountForm extends ConfigFormBase {

  /**

   * {@inheritdoc}

   */

  public function getFormId() {

    return 'configMovieNumber';

  }

  /**

   * {@inheritdoc}

   */

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('movie.settings');

    $form['count'] = array(

      '#type' => 'textfield',

      '#title' => $this->t('Number of results per page'),

      '#default_value' => $config->get('count')

    );



    return $form;

  }

  /**

   * {@inheritdoc}

   */

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $movies = $form_state->getValue('count');

    $this->config('movie.settings')
      ->set('movies_count', $movies)
      ->save();
    return parent::submitForm($form, $form_state);

  }

  /**

   * {@inheritdoc}

   */

  protected function getEditableConfigNames() {

    return [

      'movie.settings',

    ];

  }

}
