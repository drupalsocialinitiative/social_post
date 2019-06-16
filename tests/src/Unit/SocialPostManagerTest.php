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
    $provider_user_id = 'drupaluser';
  }

  /**
   * Tests for class UserAccessControlHandler.
   */
  public function testSocialPostManager() {
    $plugin_id = 'drupal';
    $user_id = 'drupaluser';
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $current_user = $this->createMock(AccountProxy::class);
    $data_handler = $this->createMock(SocialPostDataHandler::class);
    $socialPostManager = $this->getMockBuilder(SocialPostManager::class)
                              ->setConstructorArgs(array($entity_type_manager,
                                                         $current_user,
                                                         $data_handler,
                                                   ))
                              ->getMock();

    $socialPostManager->setPluginId('drupal123');

    $socialPostManager->method('getPluginId')
                      ->willReturn('drupal123');

    $socialPostManager->method('getCurrentUser')
                      ->willReturn(123);

    $socialPostManager->method('getAccountsByUserId')
                      ->with($plugin_id, $user_id)
                      ->will($this->returnValue(array(
                        'user_id' => 'drupaluser',
                        'plugin_id' => 'drupal',
                      )));

    $socialPostManager->method('addRecord')
                      ->with('drupaluser','drupal123', 'drupal', $additional_data = NULL)
                      ->will($this->returnValue(TRUE));

    $socialPostManager->setSessionKeysToNullify(array('drupal'));

    $socialPostManager->method('getSalt')
                      ->willReturn('drupal3ab9');

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

    $this->assertEquals('drupal123', $socialPostManager->getPluginId());
    $this->assertEquals(123, $socialPostManager->getCurrentUser());
    $this->assertEquals(['user_id' => 'drupaluser', 'plugin_id' => 'drupal'],
                       $socialPostManager->getAccountsByUserId($plugin_id, $user_id));
    $this->assertEquals('drupal3ab9', $socialPostManager->getSalt());
    $this->assertTrue($socialPostManager->addRecord('drupaluser','drupal123', 'drupal', $additional_data = NULL));
  }

  // public function testencryptToken($token = "drupal") {
  //   $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
  //   $current_user = $this->createMock(AccountProxy::class);
  //   $data_handler = $this->createMock(SocialPostDataHandler::class);
  //   $socialPostManager = $this->getMockBuilder(SocialPostManager::class)
  //                             ->setConstructorArgs(array($entity_type_manager,
  //                                                        $current_user,
  //                                                        $data_handler,
  //                                                  ))
  //                             ->getMock();
  //
  //   $socialPostManager->method('getSalt')
  //                     ->willReturn('drupal3ab9');
  //   $key = $socialPostManager->getSalt();
  //
  //   // Remove the base64 encoding from our key.
  //   $encryption_key = base64_decode($key);
  //
  //   // Generate an initialization vector.
  //   $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
  //
  //   // Encrypt the data using AES 256 encryption in CBC mode
  //   // using our encryption key and initialization vector.
  //   $encrypted = openssl_encrypt($token, 'aes-256-cbc', $encryption_key, 0, $iv);
  //
  //   // The $iv is just as important as the key for decrypting,
  //   // so save it with our encrypted data using a unique separator (::).
  //   var_dump(base64_encode($encrypted . '::' . $iv));
  // }
}
