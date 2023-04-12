<?php

namespace Drupal\add_translation\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows admin to configure add translation.
 */
class AddTranslationSettingForm extends ConfigFormBase {

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a AddTranslation object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityTypeManagerInterface $entity_manager,
    LanguageManagerInterface $language_manager,
    MessengerInterface $messenger) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_translation_setting_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['add_translation.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('add_translation.settings');
    $languages = $this->languageManager->getLanguages();
    $regionalLanguages = [];
    foreach ($languages as $language) {
      // Check if the language is regional.
      if (!$language->isDefault()) {
        $regionalLanguages[$language->getId()] = $language->getName();
      }
    }

    if (empty($regionalLanguages)) {
      $url = Url::fromRoute('entity.configurable_language.collection');
      $link = Link::fromTextAndUrl($this->t('Add Regional Translation'), $url)->toString();

      $form['not_found_regional_lang'] = [
        '#type' => 'markup',
        '#markup' => $this->t('You have not added regional languages yet. Please @link', ['@link' => $link]),
        '#prefix' => '<h3>',
        '#suffix' => '</h3>',
        '#weight' => 0,
      ];

      return $form;
    }

    $form['destination_lang'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $regionalLanguages,
      '#default_value' => $config->get('destination_lang'),
    ];

    $content_type = [];
    foreach ($this->entityTypeBundleInfo->getBundleInfo('node') as $bundle => $bundle_info) {
      $content_type[$bundle] = $bundle;
    }

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $content_type,
      '#default_value' => $config->get('content_type'),
    ];
    $form['content_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Status'),
      '#options' => ['1' => 'Publish', '0' => 'Un-Publish'],
      '#default_value' => $config->get('content_status'),
    ];

    return parent::buildForm($form, $form_state);
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
    $config = $this->config('add_translation.settings');
    $config->set('destination_lang', $form_state->getValue('destination_lang'));
    $config->set('content_type', $form_state->getValue('content_type'));
    $config->set('content_status', $form_state->getValue('content_status'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
