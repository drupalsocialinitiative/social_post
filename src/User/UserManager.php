<?php

namespace Drupal\social_post\User;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\social_api\User\UserManager as SocialApiUserManager;
use Drupal\social_post\Entity\SocialPost;

/**
 * Manages database related tasks.
 */
class UserManager extends SocialApiUserManager {

  use StringTranslationTrait;

  /**
   * The current logged in Drupal user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Social Post entities.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Used to display messages to user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Used to get current active user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              MessengerInterface $messenger,
                              LoggerChannelFactoryInterface $logger_factory,
                              AccountProxyInterface $current_user) {

    parent::__construct('social_post', $entity_type_manager, $messenger, $logger_factory);

    $this->currentUser = $current_user;

  }

  /**
   * Add user record in Social Post Entity.
   *
   * @param string $name
   *   The user name in the provider.
   * @param int $user_id
   *   The Drupal User ID associated to the record.
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
  public function addUserRecord($name, $user_id, $provider_user_id, $url, $token) {

    if ($this->getDrupalUserId($provider_user_id)) {
      return FALSE;
    }

    $values = [
      'user_id' => $user_id,
      'plugin_id' => $this->pluginId,
      'provider_user_id' => $provider_user_id,
      'name' => $name,
      'token' => $token,
    ];

    // If URL to profile is provided.
    if ($url) {
      $values['link'] = [
        'uri' => $url,
        'title' => $name,
      ];
    }

    try {
      $user_info = SocialPost::create($values);

      // Save the entity.
      $user_info->save();
    }
    catch (\Exception $ex) {
      $this->loggerFactory
        ->get($this->getPluginId())
        ->error('Failed to add user record in Social Auth entity.
            Exception: @message', ['@message' => $ex->getMessage()]);

      $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));

      return FALSE;
    }

    return TRUE;

  }

  /**
   * Gets the Social Post records associated with a user and a provider.
   *
   * @param string $plugin_id
   *   The plugin for which to get the accounts.
   * @param string|null $user_id
   *   The Drupal user ID.
   *
   * @return \Drupal\social_post\Entity\SocialPost[]
   *   An array of Social Post records associated with the user.
   */
  public function getAccounts($plugin_id, $user_id = NULL) {
    $storage = $this->entityTypeManager->getStorage($this->entityType);

    if (!$user_id) {
      $user_id = $this->currentUser->id();
    }

    // Get the accounts associated to the user.
    $accounts = $storage->loadByProperties([
      'user_id' => $user_id,
      'plugin_id' => $plugin_id,
    ]);

    return $accounts;
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
        ->getStorage($this->entityType)
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

}
