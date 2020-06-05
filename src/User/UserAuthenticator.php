<?php

namespace Drupal\social_post\User;

use Drupal\social_api\User\UserAuthenticator as SocialApiUserAuthenticator;

/**
 * Manages Drupal authentication tasks for Social Post.
 */
class UserAuthenticator extends SocialApiUserAuthenticator {

  /**
   * The Social Post User Manager.
   *
   * @var \Drupal\social_post\User\UserManager
   */
  protected $userManager;

  /**
   * Gets the Drupal user id based on the provider user id.
   *
   * @param string $provider_user_id
   *   User's id on provider.
   *
   * @return int|false
   *   The Drupal user id if it exists.
   *   False otherwise.
   */
  public function getDrupalUserId($provider_user_id) {
    return $this->userManager->getDrupalUserId($provider_user_id);
  }

  /**
   * Add user record in Social Post Entity.
   *
   * @param string $name
   *   The user name in the provider.
   * @param int|string $provider_user_id
   *   Unique Social ID returned by social network.
   * @param string $url
   *   The URL to the profile in the provider.
   * @param string $token
   *   Token to be used for autoposting.
   *
   * @return bool
   *   True if User record was created or False otherwise
   */
  public function addUserRecord($name, $provider_user_id, $url, $token) {
    return $this->userManager->addUserRecord($name,
                                             $this->currentUser()->id(),
                                             $provider_user_id,
                                             $url,
                                             $token);
  }

}
