<?php

namespace Drupal\social_post\PostManager;

use Drupal\social_api\BaseManager;
/**
 * Defines an PostManager Interface.
 *
 * @package Drupal\social_post\PostManager
 */
interface PostManagerInterface extends BaseManager\BaseManagerInterface {

  /**
   * Execute the api to post on social network.
   *
   * @return mixed
   *   The user data.
   */
  public function doExecute();

}
