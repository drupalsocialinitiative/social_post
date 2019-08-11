<?php

namespace Drupal\social_post\PostManager;

use Drupal\social_api\AuthManager\OAuth2Manager as OAuth2ManagerBase;

/**
 * Defines a basic PostManager.
 *
 * @package Drupal\social_post\PostManager
 */
abstract class OAuth2Manager extends OAuth2ManagerBase implements OAuth2ManagerInterface {}
