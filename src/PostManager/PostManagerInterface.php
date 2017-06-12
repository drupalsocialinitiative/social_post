<?php

namespace Drupal\social_post\PostManager;

use Drupal\social_api\OAuth2Manager\OAuth2ManagerInterface as BaseOAuth2ManagerInterface;

/**
 * Defines an PostManager Interface.
 *
 * @package Drupal\social_post\PostManager
 */
interface PostManagerInterface extends BaseOAuth2ManagerInterface {

  /**
   * Wrapper for post method.
   *
   * @param string $access_token
   *   The access token.
   * @param string $access_token_secret
   *   The access token secret.
   * @param string $status
   *   The tweet text.
   */
  public function doPost($access_token, $access_token_secret, $status);

}
