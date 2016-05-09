<?php
/**
 * @file
 * Contains \Drupal\social_post\Plugin\SocialPostNetworkInterface
 */

namespace Drupal\social_post\Plugin;

use Drupal\social_api\Plugin\NetworkInterface;

interface SocialPostNetworkInterface extends NetworkInterface
{
  /**
   * Execute the posting action.
   *
   * Uses the underlying SDK library to publish to the social network.
   */
  public function doPost();
}
