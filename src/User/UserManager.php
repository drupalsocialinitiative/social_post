<?php

namespace Drupal\social_post\User;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\social_post\SocialPostDataHandler;

/**
 * Contains all logic that is related to Drupal user management.
 */
class UserManager {

  use StringTranslationTrait;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current logged in Drupal user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The Social Post data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * The Drupal logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The implementer plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * Session keys to nullify is user could not be logged in.
   *
   * @var array
   */
  protected $sessionKeys;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Drupal user objects.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Used to get current active user.
   * @param \Drupal\social_post\SocialPostDataHandler $data_handler
   *   Used to handle session values.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              AccountProxy $current_user,
                              SocialPostDataHandler $data_handler,
                              LoggerChannelFactoryInterface $logger_factory) {

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->dataHandler = $data_handler;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Sets the implementer plugin id.
   *
   * This value is used to generate customized logs, drupal messages, and event
   * dispatchers.
   *
   * @param string $plugin_id
   *   The plugin id.
   */
  public function setPluginId($plugin_id) {
    $this->pluginId = $plugin_id;
  }

  /**
   * Gets the implementer plugin id.
   *
   * This value is used to generate customized logs, drupal messages, and events
   * dispatchers.
   *
   * @return string
   *   The plugin id.
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * Checks if user exist in entity.
   *
   * @param string $provider_user_id
   *   User's name on Provider.
   *
   * @return int|bool
   *   The Drupal user ID associate with the account.
   *   False if record does not exist.
   */
  public function checkIfUserExists($provider_user_id) {
    // Check user for social post implementer.
    /** @var \Drupal\social_post\Entity\SocialPost|false $social_post_user */
    $social_post_user = current(
      $this->entityTypeManager->getStorage('social_post')
        ->loadByProperties([
          'plugin_id' => $this->pluginId,
          'provider_user_id' => $provider_user_id,
        ])
    );

    if ($social_post_user === FALSE) {
      return FALSE;
    }

    return $social_post_user->getUserId();
  }

  /**
   * Gets the Social Post records associated with a user and a provider.
   *
   * @param string $user_id
   *   The Drupal user ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of Social Post records associated with the user.
   */
  public function getAccountsByUserId($user_id) {
    $storage = $this->entityTypeManager->getStorage('social_post');
    // Perform query on social auth entity.
    $accounts = $storage->loadByProperties([
      'user_id' => $user_id,
      'plugin_id' => $this->pluginId,
    ]);

    return $accounts;
  }

  /**
   * Get ID of logged in user.
   *
   * @return int
   *   The current Drupal user ID.
   */
  public function getCurrentUser() {
    return $this->currentUser->id();
  }

  /**
   * Add user record in Social Post Entity.
   *
   * @param string $name
   *   The user name in the provider.
   * @param int|string $provider_user_id
   *   Unique Social ID returned by social network.
   * @param string $token
   *   Token to be used for autoposting.
   * @param string $additional_data
   *   Additional data to be stored in record.
   *
   * @return bool
   *   True if User record was created or False otherwise
   */
  public function addRecord($name, $provider_user_id, $token, $additional_data = NULL) {
    // Get User ID of logged in user.
    $user_id = $this->getCurrentUser();

    if ($this->checkIfUserExists($provider_user_id)) {
      return FALSE;
    }

    // Adds user record.
    $values = [
      'user_id' => $user_id,
      'plugin_id' => $this->pluginId,
      'provider_user_id' => $provider_user_id,
      'name' => $name,
      'additional_data' => $additional_data,
    ];

    $user_info = $this->entityTypeManager->getStorage('social_post')->create($values);

    if (!$user_info) {
      return FALSE;
    }

    try {
      $user_info->setToken($token);

      // Saves the entity.
      $user_info->save();

      return TRUE;

    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to add record. Exception: @message', ['@message' => $ex->getMessage()]);
    }

    return FALSE;

  }

  /**
   * Gets the session keys.
   *
   * @return array
   *   The session keys array
   */
  public function getSessionKeys() {
    return $this->sessionKeys;
  }

  /**
   * Sets the session keys to nullify if user could not logged in.
   *
   * @param array $session_keys
   *   The session keys to nullify.
   */
  public function setSessionKeysToNullify(array $session_keys) {
    $this->sessionKeys = $session_keys;
  }

  /**
   * Nullifies session keys if user could not logged in.
   */
  public function nullifySessionKeys() {
    if (!empty($this->sessionKeys)) {
      array_walk($this->sessionKeys, function ($session_key) {
        $this->dataHandler->set($session_key, NULL);
      });
    }
  }

  /**
   * Update token of a particular record.
   *
   * @param string $plugin_id
   *   Type of social network.
   * @param string $provider_user_id
   *   Unique Social ID returned by social network.
   * @param string $token
   *   Token provided by social_network.
   *
   * @return bool
   *   True if updated
   *   False otherwise
   */
  public function updateToken($plugin_id, $provider_user_id, $token) {
    /** @var \Drupal\social_post\Entity\SocialPost|false $social_post_user */
    $social_post_user = current(
      $this->entityTypeManager
        ->getStorage('social_post')
        ->loadByProperties([
          'plugin_id' => $plugin_id,
          'provider_user_id' => $provider_user_id,
        ])
    );

    if ($social_post_user === FALSE) {
      return FALSE;
    }

    try {
      $social_post_user->setToken($token)->save();

      return TRUE;
    }
    catch (EntityStorageException $e) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error(
          'Failed to save user with updated token. Error @error', [
            '@error' => $e->getMessage(),
          ]
        );

      return FALSE;
    }
  }

  /**
   * Returns the user's token for a provider.
   *
   * @param string $provider_user_id
   *   Unique user ID in the provider.
   *
   * @return string|null
   *   The token or null if user is not found.
   */
  public function getToken($provider_user_id) {

    // Checks user for social post implementer.
    /** @var \Drupal\social_post\Entity\SocialPost|false $social_post_user */
    $social_post_user = current(
      $this->entityTypeManager->getStorage('social_post')
        ->loadByProperties([
          'plugin_id' => $this->pluginId,
          'provider_user_id' => $provider_user_id,
        ])
    );

    if ($social_post_user === FALSE) {
      return NULL;
    }

    return $social_post_user->getToken();
  }

}
