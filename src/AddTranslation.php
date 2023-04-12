<?php

namespace Drupal\add_translation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manages translation update.
 */
class AddTranslation {

  use StringTranslationTrait;

  /**
   * The config manager service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a AddTranslation object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_manager, MessengerInterface $messenger) {
    $this->configFactory = $config_factory->getEditable('add_translation.settings');
    $this->entityManager = $entity_manager;
    $this->messenger = $messenger;
  }

  /**
   * Batch operation for adding translation.
   *
   * @param array $items
   *   The result of entity.
   * @param Iterable|array $context
   *   The context array.
   */
  public function batchUpdateTranslationItem(array $items, &$context) {
    if (empty($items['node_id'])) {
      return;
    }
    $langcode = $this->configFactory->get('destination_lang');
    try {
      $node = $this->entityManager->getStorage('node')->load($items['node_id']);
      $node->addTranslation($langcode, $node->toArray());
      $node->save();
    }
    catch (\Exception $e) {
      \Drupal::logger('add_translation')->error($e->getMessage());
      $items = [];
    }

    $context['message'] = 'Adding Translation...';
    $context['results'][] = $items;
  }

  /**
   * Batch dispatch submission finished callback.
   */
  public static function batchSubmitFinished($success, $results, $operations) {
    return \Drupal::service('add_translation.translation_update')->doBatchSubmitFinished($success, $results, $operations);
  }

  /**
   * Sets a batch for executing translation.
   */
  public function setBatch() {
    $content_type = $this->configFactory->get('content_type');
    $content_status = $this->configFactory->get('content_status');

    $storage = $this->entityManager->getStorage('node');
    $nids = $storage->getQuery()
      ->condition('type', $content_type)
      ->condition('status', $content_status)
      ->sort('nid', 'ASC')
      ->execute();

    $results = $storage->loadMultiple($nids);

    // Add the operations.
    $operations = [];
    $items = [];
    foreach ($results as $result) {
      $items = [
        'node_id' => $result->id(),
      ];

      $operations[] = ['_add_translation_batch_dispatcher',
        [
          'add_translation.translation_update:batchUpdateTranslationItem',
          $items,
        ],
      ];
    }

    $batch = [
      'title' => $this->t('Add Translation'),
      'operations' => $operations,
      'init_message' => $this->t('Adding...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('An error occurred during processing'),
      'finished' => [AddTranslation::class, 'batchSubmitFinished'],
    ];

    batch_set($batch);
  }

  /**
   * Finished callback for the batch process.
   *
   * @param bool $success
   *   Whether the batch completed successfully.
   * @param array $results
   *   The results array.
   * @param array $operations
   *   The operations array.
   */
  public function doBatchSubmitFinished($success, array $results, array $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    $this->messenger->addStatus($message);
  }

}
