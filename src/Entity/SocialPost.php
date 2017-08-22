<?php

namespace Drupal\social_post\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Social Post entity.
 *
 * @ingroup social_post
 *
 * @ContentEntityType(
 *   id = "social_post",
 *   label = @Translation("SocialPost"),
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
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/social-api/social-post/{provider}/users/{social_post_user}/delete",
 *     "collection" = "/admin/config/social-api/social-post/{provider}/users"
 *   }
 * )
 */
class SocialPost extends ContentEntityBase implements ContentEntityInterface {
  use EntityChangedTrait;

  /**
   *
   */
  public function getSocialNetworkID() {
    return $this->get('provider_user_id')->value;
  }

  /**
   *
   */
  public function getSocialNetworkName() {
    return $this->get('plugin_id')->value;
  }

  /**
   *
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   *
   */
  public function getId() {
    return (int) $this->get('id')->value;
  }

  /**
   *
   */
  public function getUserId() {
    return (int) $this->get('user_id')->target;
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
      ->setLabel(t('ID'))
      ->setDescription(t('The Social Post user UUID.'))
      ->setReadOnly(TRUE);

    // The ID of user account associated.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('user_id'))
      ->setDescription(t('The ID Of User Account Associated With Social Network.'))
      ->setReadOnly(TRUE);

    // Name of the social network account associated.
    $fields['plugin_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('PLUGIN ID'))
      ->setDescription(t('Identifier for social post implementer.'))
      ->setReadOnly(TRUE);

    // Unique Account ID returned by the social network provider.
    $fields['provider_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('PROVIDER USER ID'))
      ->setDescription(t('The Unique ID Provided by Social Network.'))
      ->setReadOnly(TRUE);

    // Unique Account ID returned by the social network provider.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Name provided by the Social Provider'))
      ->setReadOnly(TRUE);

    // Access Token returned by social network provider, used for autoposting.
    $fields['token'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Token'))
      ->setDescription(t('The unique user ID in the provider.'))
      ->setReadOnly(TRUE);

    // Access Token returned by social network provider, used for autoposting.
    $fields['additional_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Additional Data'))
      ->setDescription(t('Additional_dara'))
      ->setReadOnly(TRUE);
    return $fields;
  }

}
