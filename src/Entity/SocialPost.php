<?php

namespace Drupal\social_post\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\social_api\Entity\SocialApi;

/**
 * Defines the Social Post entity.
 *
 * @ingroup social_post
 *
 * @ContentEntityType(
 *   id = "social_post",
 *   label = @Translation("Social Post"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\social_post\Entity\Controller\SocialPostListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\social_post\Form\SocialPostEntityDeleteForm",
 *     },
 *     "access" = "Drupal\social_post\UserAccessControlHandler",
 *   },
 *   base_table = "social_post",
 *   list_cache_contexts = { "user" },
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "user_id" = "user_id",
 *     "plugin_id" = "plugin_id",
 *     "provider_user_id" = "provider_user_id"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/social-api/social-post/users/social_post/{provider}/{social_post}/delete/{user}",
 *     "collection" = "/admin/config/social-api/social-post/{provider}/users/"
 *   }
 * )
 */
class SocialPost extends SocialApi implements ContentEntityInterface {
  use EntityChangedTrait;

  /**
   * Gets the record ID in this entity.
   *
   * This ID is mainly used to perform operations such as deleting or updating
   * the record.
   *
   * @return string
   *   The record ID.
   */
  public function getId() {
    return (int) $this->get('id')->value;
  }

  /**
   * Gets User ID in provider.
   *
   * @return string
   *   User ID in provider.
   */
  public function getProviderUserId() {
    return $this->get('provider_user_id')->value;
  }

  /**
   * Gets implementer used to create the record.
   *
   * @return string
   *   Impelementer's name.
   */
  public function getPluginId() {
    return $this->get('plugin_id')->value;
  }

  /**
   * Gets the user's name in the provider.
   *
   * @return string
   *   User's name in the provider
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Gets the Drupal user ID associated to the record.
   *
   * @return int
   *   User ID.
   */
  public function getUserId() {
    return (int) $this->get('user_id')->target_id;
  }

  /**
   * Gets the link to the user's profile in provider.
   *
   * @return \Drupal\link\Plugin\Field\FieldType\LinkItem
   *   The link object containing the url to the user's profile.
   */
  public function getLink() {
    return $this->get('link')[0];
  }

  /**
   * Creating fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Social Post record.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The Social Post user UUID.'))
      ->setReadOnly(TRUE);

    // The Drupal user ID associated to the record.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The Drupal user ID associated to the record.'));

    // The implementer used to register the user.
    $fields['plugin_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plugin ID'))
      ->setDescription(t('The implementer used to register the user.'));

    // Unique Account ID returned by provider.
    $fields['provider_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Provider User ID'))
      ->setDescription(t('The unique user ID in the provider.'));

    // User's name in the provider.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t("User's name in the provider"));

    // Access token returned by provider, used for autoposting.
    $fields['token'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Token'))
      ->setDescription(t('Token returned after authentication'));

    // Additional data about the user.
    $fields['additional_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Additional Data'))
      ->setDescription(t('Additional data about the user'));

    // Link to the user's profile in the provider.
    $fields['link'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Link'))
      ->setDescription(t("Link to the user's profile in the provider."));

    return $fields;
  }

}
