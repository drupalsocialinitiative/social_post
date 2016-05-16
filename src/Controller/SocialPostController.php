<?php
/**
 * @file
 * Contains Drupal\social_post\Controller
 */

namespace Drupal\social_post\Controller;

use Drupal\social_api\Controller\SocialApiController;

class SocialPostController extends SocialApiController
{
  public function integrations($type = 'social_post')
  {
    return parent::integrations($type);
  }
}
