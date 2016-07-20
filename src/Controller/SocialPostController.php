<?php

namespace Drupal\social_post\Controller;

use Drupal\social_api\Controller\SocialApiController;

/**
 * Renders the integration list.
 */
class SocialPostController extends SocialApiController {

  /**
   * {@inheritdoc}
   */
  public function integrations($type = 'social_post') {
    return parent::integrations($type);
  }

}
