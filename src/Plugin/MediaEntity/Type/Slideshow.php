<?php

/**
 * @file
 * Contains \Drupal\media_entity_slideshow\Plugin\MediaEntity\Type\Slideshow.
 */

namespace Drupal\media_entity_slideshow\Plugin\MediaEntity\Type;

use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;

/**
 * Provides media type plugin for Slideshows.
 *
 * @MediaType(
 *   id = "slideshow",
 *   label = @Translation("Slideshow"),
 *   description = @Translation("Provides business logic and metadata for slideshows.")
 * )
 */
class Slideshow extends MediaTypeBase {

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    $fields = array(
      'length' => t('Slideshow length'),
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    $source_field = $this->configuration['source_field'];

    switch ($name) {
      case 'length':
        return $media->{$source_field}->count();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(MediaBundleInterface $bundle) {
    $form = array();

    $options = array();
    $allowed_field_types = array('entity_reference');
    foreach ($this->entityManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = array(
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores slideshow items.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(MediaInterface $media) {
    // This should be handled by Drupal core.
    // - check if there is at least one slideshow item
    // - check if all items are media (entity reference references media)
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    $source_field = $this->configuration['source_field'];

    /** @var \Drupal\media_entity\MediaInterface $slideshow_item */
    $slideshow_item = $this->entityManager->getStorage('media')->load($media->{$source_field}->target_id);

    if (!$slideshow_item) {
      return $this->config->get('icon_base') . '/slideshow.png';
    }

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entityManager->getStorage('media_bundle')->load($slideshow_item->bundle());

    if (!$bundle) {
      return $this->config->get('icon_base') . '/slideshow.png';
    }

    $thumbnail = $bundle->getType()->thumbnail($media);

    if (!$thumbnail) {
      return $this->config->get('icon_base') . '/slideshow.png';
    }

    return $thumbnail;
  }
}
