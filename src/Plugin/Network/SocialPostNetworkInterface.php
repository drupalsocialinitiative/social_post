<?php

namespace Drupal\social_post\Plugin\Network;

use Drupal\social_api\Plugin\NetworkInterface;

/**
 * Defines a Social Post Network Plugin interface.
 */
interface SocialPostNetworkInterface extends NetworkInterface {

  /**
   * Executes posting request.
   */
  public function post();

}
