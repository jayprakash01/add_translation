<?php

namespace Drupal\add_translation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\add_translation\AddTranslation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows admin users to add and update translation.
 */
class AddTranslationForm extends FormBase {

  /**
   * The translation update service.
   *
   * @var \Drupal\add_translation\AddTranslation
   */
  protected $translationUpdate;

  /**
   * AddTranslationForm constructor.
   *
   * @param \Drupal\add_translation\AddTranslation $translation_update
   *   The translation update.
   */
  public function __construct(AddTranslation $translation_update) {
    $this->translationUpdate = $translation_update;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('add_translation.translation_update')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_node_translation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['entity_translation_label'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Please click on the button to add a translation node for an existing item.'),
      '#weight' => 1,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Translation'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation if require.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->translationUpdate->setBatch();
  }

}
