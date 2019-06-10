<?php

use Drupal\social_post\SocialPostManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Site\Settings;
use Drupal\social_post\SocialPostDataHandler;
use Drupal\Tests\UnitTestCase;

/**
 * Defines SocialPostManager class.
 *
 * @Annotation
 */
class SocialPostManagerTest extends UnitTestCase {

  protected $plugin_id = 'drupal';
  /**
   * Define __construct function.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests for class UserAccessControlHandler.
   */
  public function testSocialPostManager() {
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $current_user = $this->createMock(AccountProxy::class);
    $data_handler = $this->createMock(SocialPostDataHandler::class);
    $socialPostManager = $this->getMockBuilder(SocialPostManager::class)
                                     ->setConstructorArgs(array($entity_type_manager,
                                                                $current_user,
                                                                $data_handler,
                                                          ))
                                     ->getMock();
    $this->assertTrue(
          method_exists($socialPostManager, 'setPluginId'),
            'SocialPostManager does not implements setPluginId function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'getPluginId'),
            'SocialPostManager does not implements getPluginId function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'getAccountsByUserId'),
            'SocialPostManager does not implements getAccountsByUserId function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'checkIfUserExists'),
            'SocialPostManager does not implements checkIfUserExists function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'getCurrentUser'),
            'SocialPostManager does not implements getCurrentUser function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'addRecord'),
            'SocialPostManager does not implements addRecord function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'setSessionKeysToNullify'),
            'SocialPostManager does not implements setSessionKeysToNullify function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'nullifySessionKeys'),
            'SocialPostManager does not implements cnullifySessionKeys function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'updateToken'),
            'SocialPostManager does not implements updateToken function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'getToken'),
            'SocialPostManager does not implements getToken function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'encryptToken'),
            'SocialPostManager does not implements encryptToken function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'decryptToken'),
            'SocialPostManager does not implements decryptToken function/method'
    );
    $this->assertTrue(
          method_exists($socialPostManager, 'getSalt'),
            'SocialPostManager does not implements getSalt function/method'
    );
  }

}
