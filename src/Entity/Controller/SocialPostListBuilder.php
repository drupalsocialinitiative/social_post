<?php

namespace Drupal\social_post\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Social Post Entities.
 *
 * @ingroup social_post
 */
class SocialPostListBuilder extends EntityListBuilder {

  /**
   * The provider.
   *
   * @var string
   */
  protected $provider;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The user entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userEntity;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('url_generator')
    );
  }

  /**
   * SocialPostListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage for the social_post entity.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_entity
   *   The entity storage for the user entity.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              EntityStorageInterface $user_entity,
                              UrlGeneratorInterface $url_generator) {

    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
    $this->userEntity = $user_entity;
  }

  /**
   * Sets the provider for the users that should be listed.
   *
   * @param string $provider
   *   The provider id.
   */
  public function setProvider($provider) {
    $this->provider = $provider;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['social_id'] = $this->t('Social Network ID');
    $header['social_post_name'] = $this->t('Screen name');
    $header['user'] = $this->t('User ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\social_post\Entity\SocialPost|Drupal\Core\Entity\EntityTypeInterface $entity
   *   The Social Post entity to render.
   */
  public function buildRow(EntityInterface $entity) {
    $provider = $entity->getPluginId();

    if ($provider == 'social_post_' . $this->provider) {
      $row['provider_user_id'] = $entity->getProviderUserId();

      // Generates URL to user profile.
      $link = $entity->getLink();
      $row['social_post_name'] = Link::fromTextAndUrl($link->title, $link->getUrl());

      $user = $this->userEntity->load($entity->getUserId());
      $row['user'] = $user->toLink();

      return $row + parent::buildRow($entity);
    }

    return [];

  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\social_post\Entity\SocialPost|Drupal\Core\Entity\EntityTypeInterface $entity
   *   The Social Post entity to process.
   */
  public function getDefaultOperations(EntityInterface $entity) {

    $provider = $entity->getPluginId();

    if ($provider == 'social_post_' . $this->provider) {
      $operations = parent::getDefaultOperations($entity);
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute(
          'entity.social_post.delete_form',
          [
            'provider' => $this->provider,
            'social_post' => $entity->getId(),
            'user' => FALSE,
          ]
        ),
      ];

      return $operations;
    }

    return [];
  }

}
