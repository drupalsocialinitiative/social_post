<?php

namespace Drupal\social_post;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Site\Settings;

/**
 * Contains all logic that is related to Drupal user management.
 */
class SocialPostManager {

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
   * The unique salt generated for drupal installation.
   *
   * @var string
   */
  protected $key;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Drupal user objects.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Used to get current active user.
   * @param \Drupal\social_post\SocialPostDataHandler $data_handler
   *   Class to interact with session.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              AccountProxy $current_user,
                              SocialPostDataHandler $data_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->dataHandler = $data_handler;

    $this->key = $this->getSalt();
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
   * @return false
   *   if user doesn't exist
   *   Else return Drupal User Id associate with the account.
   */
  public function checkIfUserExists($provider_user_id) {
    // Check user for social post implementer.
    $user_data = current($this->entityTypeManager->getStorage('social_post')->loadByProperties(['plugin_id' => $this->pluginId, 'provider_user_id' => $provider_user_id]));

    if (!$user_data) {
      return FALSE;
    }
    return $user_data->get('user_id')->getValue()[0]["target_id"];
  }

  /**
   * Checks if user exist in entity.
   *
   * @param string $plugin_id
   *   Plugin Id.
   * @param string $user_id
   *   User's name on Provider.
   *
   * @return false
   *   if user doesn't exist
   *   Else return Drupal User Id associate with the account.
   */
  public function getAccountsByUserId($plugin_id, $user_id) {
    $storage = $this->entityTypeManager->getStorage('social_post');
    // Perform query on social auth entity.
    $accounts = $storage->loadByProperties([
      'user_id' => $user_id,
      'plugin_id' => $plugin_id,
    ]);
    return $accounts;
  }

  /**
   * Get ID of logged in user.
   *
   * @return int
   *   User Id.
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
    $user_id = $this->currentUser->id();
    if ($this->checkIfUserExists($provider_user_id)) {
      return FALSE;
    }
    // Add user record.
    $values = [
      'user_id' => $user_id,
      'plugin_id' => $this->pluginId,
      'provider_user_id' => $provider_user_id,
      'token' => $this->encryptToken($token),
      'name' => $name,
      'additional_data' => $additional_data,
    ];

    $user_info = $this->entityTypeManager->getStorage('social_post')->create($values);

    // Save the entity.
    $user_info->save();

    if ($user_info) {
      return TRUE;
    }

    return FALSE;
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
        $this->dataHandler->set($this->dataHandler->getSessionPrefix() . $session_key, NULL);
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
   *   True if updated else False otherwise.
   */
  public function updateToken($plugin_id, $provider_user_id, $token) {
    $field_storage_configs = $this->entityTypeManager
      ->getStorage('social_post')
      ->loadByProperties(['plugin_id' => $plugin_id, 'provider_user_id' => $provider_user_id]);

    $save_token = '';

    foreach ($field_storage_configs as $field_storage) {
      $field_storage->token = $token;
      $field_storage->enforceIsNew(FALSE);
      $save_token = $field_storage->save();
    }

    if ($save_token) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Used to get token for autoposting by implementers.
   *
   * @param string $provider_user_id
   *   Unique Social ID returned by social network.
   *
   * @return string
   *   The token.
   */
  public function getToken($provider_user_id) {

    // Check user for social post implementer.
    $user_data = current(
      $this->entityTypeManager->getStorage('social_post')
        ->loadByProperties([
          'plugin_id' => $this->pluginId,
          'provider_user_id' => $provider_user_id,
        ])
    );

    if (!$user_data) {
      return FALSE;
    }
    else {
      // Get token and decrypt it.
      return $this->decryptToken($user_data->get('token')->getValue()[0]['value']);
    }

  }

  /**
   * Encrypt the token.
   *
   * @param string $token
   *   Tokens provided by social provider.
   *
   * @return string
   *   Encrypted_token to be stored in database.
   */
  private function encryptToken($token) {
    $key = $this->key;

    // Remove the base64 encoding from our key.
    $encryption_key = base64_decode($key);

    // Generate an initialization vector.
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encrypt the data using AES 256 encryption in CBC mode
    // using our encryption key and initialization vector.
    $encrypted = openssl_encrypt($token, 'aes-256-cbc', $encryption_key, 0, $iv);

    // The $iv is just as important as the key for decrypting,
    // so save it with our encrypted data using a unique separator (::).
    return base64_encode($encrypted . '::' . $iv);
  }

  /**
   * Decrypt the encrypted token.
   *
   * @param string $token
   *   Encrypted token stored in database.
   *
   * @return string
   *   Token in JSON format.
   */
  private function decryptToken($token) {
    $key = $this->key;

    // Remove the base64 encoding from our key.
    $encryption_key = base64_decode($key);

    // To decrypt, split the encrypted data from our IV -
    // our unique separator used was "::".
    list($encrypted_data, $iv) = explode('::', base64_decode($token), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
  }

  /**
   * Get salt for this drupal installation.
   *
   * @return string
   *   Hash salt.
   */
  public function getSalt() {
    $hash_salt = Settings::getHashSalt();

    if (empty($hash_salt)) {
      return FALSE;
    }
    return $hash_salt;
  }

}
