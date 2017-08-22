<?php

namespace Drupal\social_post\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
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
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

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
      $container->get('url_generator'),
      $container->get('current_route_match')
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
                              UrlGeneratorInterface $url_generator,
                              CurrentRouteMatch $route_match) {

    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
    $this->userEntity = $user_entity;
    $this->routeMatch = $route_match;
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
   */
  public function buildRow(EntityInterface $entity) {
    $provider = $this->routeMatch->getParameter('provider');
    $socialNetworkName = $entity->getSocialNetworkName();
    if ($socialNetworkName == 'social_post_' . $provider) {
      /* @var $entity \Drupal\social_post\Entity\SocialPost */
      $row['social_id'] = $entity->getSocialNetworkID();
      $row['social_post_name'] = $entity->getName();

      $user = $this->userEntity->load($entity->getUserId());
      $row['user'] = $user->toLink();
      return $row + parent::buildRow($entity);
      return parent::buildRow($entity);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $provider = $this->routeMatch->getParameter('provider');
    $operations = parent::getDefaultOperations($entity);

    $operations['delete'] = [
      'title' => t('Delete'),
      'url' => Url::fromRoute('entity.social_post.delete_form', ['provider' => $provider, 'social_post' => $entity->getId(), 'user' => TRUE]),
    ];

    return $operations;
  }

}
