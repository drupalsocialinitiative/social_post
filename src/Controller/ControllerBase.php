<?php

namespace Drupal\social_post\Controller;

use Drupal\Core\Controller\ControllerBase as DrupalControllerBase;

/**
 * Controller base for Social Post implementers.
 */
class ControllerBase extends DrupalControllerBase {

  /**
   * The Social Post entity list builder.
   *
   * @var \Drupal\social_post\Entity\Controller\SocialPostListBuilder
   */
  protected $listBuilder;

  /**
   * Builds the list of users for the specified provider.
   *
   * @param string $provider
   *   The provider for which to build the list.
   *
   * @return array
   *   The render array.
   */
  public function buildList($provider) {
    $this->listBuilder->setProvider($provider);

    return $this->listBuilder->render();
  }

}
