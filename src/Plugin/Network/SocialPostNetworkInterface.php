<?php

namespace Drupal\social_post\Plugin\Network;

use Drupal\social_api\Plugin\NetworkInterface;

/**
 * Defines a Social Post Network Plugin interface.
 */
interface SocialPostNetworkInterface extends NetworkInterface {

  /**
   * Execute the posting action.
   *
   * Uses the underlying SDK library to publish to the social network.
   *
   * @return bool
   *   True if the post action was done properly
   *   False otherwise
   */
  public function post();

}
