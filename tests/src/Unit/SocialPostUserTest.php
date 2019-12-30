<?php

namespace Drupal\Tests\social_post\Unit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\social_post\SocialPostDataHandler;
use Drupal\social_post\User\UserManager;
use Drupal\Tests\UnitTestCase;

/**
 * Tests social_post User.
 *
 * @group social_post
 */
class SocialPostUserTest extends UnitTestCase {

  /**
   * The tested Social Post UserManager.
   *
   * @var \Drupal\social_post\User\UserManager
   */
  protected $userManager;

  /**
   * The mocked Social Post Data Handler.
   *
   * @var \Drupal\social_post\SocialPostDataHandler
   */
  protected $dataHandler;

  /**
   * The mocked array of the session keys.
   *
   * @var array
   */
  protected $sessionKeys;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $current_user = $this->createMock(AccountProxy::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);

    $this->dataHandler = $this->getMockBuilder(SocialPostDataHandler::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'set', 'getSessionPrefix'])
      ->getMock();

    $this->sessionKeys = [];

    $this->userManager = $this->getMockBuilder(UserManager::class)
      ->setConstructorArgs([$entity_type_manager,
        $current_user,
        $this->dataHandler,
        $logger_factory,
      ])
      ->setMethods(NULL)
      ->getMock();

  }

  /**
   * @covers Drupal\social_post\User\UserManager::setPluginId
   */
  public function testSetPluginId() {
    $this->assertEquals(NULL, $this->userManager->getPluginId());
    $this->userManager->setPluginId('social_post_test');
    $this->assertEquals('social_post_test', $this->userManager->getPluginId());
  }

  /**
   * @covers Drupal\social_post\User\UserManager::getPluginId
   */
  public function testGetPluginId() {
    $this->userManager->setPluginId('social_post_test2');
    $this->assertEquals('social_post_test2', $this->userManager->getPluginId());
  }

  /**
   * @covers Drupal\social_post\User\UserManager::getSessionKeys
   */
  public function testGetSessionKeys() {
    $sample_session = ['x1Sn2lPZZ' => 'ikSn2AZj3', 'pL2bxA2xz' => 'l2AYxbA9a'];

    $this->userManager->setSessionKeysToNullify(array_keys($sample_session));
    $this->assertEquals(array_keys($sample_session), $this->userManager->getSessionKeys());
  }

  /**
   * @covers Drupal\social_post\User\UserManager::setSessionKeysToNullify
   */
  public function testSetSessionKeysToNullify() {
    $sample_session = ['x1Sn2lPZZ' => 'ikSn2AZj3', 'pL2bxA2xz' => 'l2AYxbA9a'];

    $this->assertNotEquals(array_keys($sample_session), $this->userManager->getSessionKeys());
    $this->userManager->setSessionKeysToNullify(array_keys($sample_session));
    $this->assertEquals(array_keys($sample_session), $this->userManager->getSessionKeys());
  }

  /**
   * @covers Drupal\social_post\User\UserManager::nullifySessionKeys
   */
  public function testNullifySessionKeys() {
    $sample_session = ['x1Sn2lPZZ' => 'ikSn2AZj3'];

    $this->dataHandler->expects($this->any())
      ->method('getSessionPrefix')
      ->will($this->returnCallback(function () {
         return 'xB2g22_';
      }));

    $this->dataHandler->expects($this->any())
      ->method('get')
      ->with($this->isType('string'))
      ->will($this->returnCallback(function ($key) {
         return $this->sessionKeys[$this->dataHandler->getSessionPrefix() . $key];
      }));

    $this->dataHandler->expects($this->any())
      ->method('set')
      ->with($this->isType('string'), $this->anything())
      ->will($this->returnCallback(function ($key, $value) {
         $this->sessionKeys[$this->dataHandler->getSessionPrefix() . $key] = $value;
      }));

    $this->dataHandler->set('x1Sn2lPZZ', 'ikSn2AZj3');
    $this->assertEquals('ikSn2AZj3', $this->dataHandler->get('x1Sn2lPZZ'));

    $this->userManager->setSessionKeysToNullify(array_keys($sample_session));
    $this->assertEquals(array_keys($sample_session), $this->userManager->getSessionKeys());

    $this->userManager->nullifySessionKeys();

    $this->assertEquals(NULL, $this->dataHandler->get('x1Sn2lPZZ'));
  }

}
