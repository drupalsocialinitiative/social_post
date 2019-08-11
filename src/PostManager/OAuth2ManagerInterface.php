<?php

namespace Drupal\social_post\PostManager;

use Drupal\social_api\AuthManager\OAuth2ManagerInterface as OAuth2ManagerInterfaceBase;

/**
 * Defines an PostManager Interface.
 *
 * @package Drupal\social_post\PostManager
 */
interface OAuth2ManagerInterface extends OAuth2ManagerInterfaceBase {

  /**
   * Executes posting request.
   */
  public function post();

}
