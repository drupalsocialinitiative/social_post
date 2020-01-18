<?php

namespace Drupal\Tests\social_post\Unit;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\social_post\Entity\SocialPost;
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
   * @var \Drupal\social_post\User\UserManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $userManager;

  /**
   * The mocked Social Post Data Handler.
   *
   * @var \Drupal\social_post\SocialPostDataHandler|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataHandler;

  /**
   * The mocked current logged in Drupal user.
   *
   * @var \Drupal\Core\Session\AccountProxy|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The mocked LoggerChannelFactoryInterface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerFactory;

  /**
   * The mocked Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked array of the session keys.
   *
   * @var array
   */
  protected $sessionKeys;

  /**
   * The test plugin id.
   *
   * @var string
   */
  protected $pluginId = 'social_post_test';

  /**
   * The test user id.
   *
   * @var int
   */
  protected $userId = 21353;

  /**
   * The test provider user id.
   *
   * @var string
   */
  protected $providerUserId = 'some_id';

  /**
   * The mocked Social Post.
   *
   * @var \Drupal\social_post\Entity\SocialPost|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $socialPost;

  /**
   * {@inheritdoc}
   */
  public function setUp() {

    $this->currentUser = $this->createMock(AccountProxy::class);
    $this->loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->socialPost = $this->createMock(SocialPost::class);
    $this->userStorage = $this->createMock(EntityStorageInterface::class);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('social_post')
      ->will($this->returnValue($this->userStorage));

    $this->dataHandler = $this->getMockBuilder(SocialPostDataHandler::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'set', 'getSessionPrefix'])
      ->getMock();

    $this->sessionKeys = [];

    $this->userManager = $this->getMockBuilder(UserManager::class)
      ->setConstructorArgs([$this->entityTypeManager,
        $this->currentUser,
        $this->dataHandler,
        $this->loggerFactory,
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

  /**
   * Tests the checkIfUserExists method with no account returned.
   *
   * @covers Drupal\social_post\User\UserManager::checkIfUserExists
   */
  public function testCheckIfUserExistsWithNoUserReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertFalse($this->userManager->checkIfUserExists($this->providerUserId));
  }

  /**
   * Tests the checkIfUserExists method with account returned.
   *
   * @covers Drupal\social_post\User\UserManager::checkIfUserExists
   */
  public function testCheckIfUserExistsWithUserReturned() {
    $this->socialPost->expects($this->any())
      ->method('getUserId')
      ->will($this->returnValue(97212));

    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([$this->socialPost]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertEquals(97212, $this->userManager->checkIfUserExists($this->providerUserId));
  }

  /**
   * Tests the getAccountsByUserId method with no account returned.
   *
   * @covers Drupal\social_post\User\UserManager::getAccountsByUserId
   */
  public function testGetAccountsByUserIdWithNoAccountReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['user_id' => $this->userId, 'plugin_id' => $this->pluginId])
      ->will($this->returnValue([]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertEquals([], $this->userManager->getAccountsByUserId($this->userId));
  }

  /**
   * Tests the getAccountsByUserId method with account returned.
   *
   * @covers Drupal\social_post\User\UserManager::getAccountsByUserId
   */
  public function testGetAccountsByUserIdWithAccountReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['user_id' => $this->userId, 'plugin_id' => $this->pluginId])
      ->will($this->returnValue([$this->socialPost]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertEquals([$this->socialPost], $this->userManager->getAccountsByUserId($this->userId));
  }

  /**
   * @covers Drupal\social_post\User\UserManager::getCurrentUser
   */
  public function testGetCurrentUser() {
    $this->currentUser->expects($this->once())
      ->method('id')
      ->will($this->returnValue(123));

    $this->assertEquals(123, $this->userManager->getCurrentUser());
  }

  /**
   * Tests the addRecord method when user exists.
   *
   * @covers Drupal\social_post\User\UserManager::addRecord
   */
  public function testAddRecordExist() {
    $this->prepareAddRecord();

    $this->userManager->expects($this->once())
      ->method('checkIfUserExists')
      ->with($this->providerUserId)
      ->will($this->returnValue(12345));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertFalse($this->userManager->addRecord('test', $this->providerUserId, 'f31a2f3SA', NULL));

  }

  /**
   * Tests the addRecord method user doesn't exist and new created successfully.
   *
   * @covers Drupal\social_post\User\UserManager::addRecord
   */
  public function testAddRecordNoExistSuccess() {
    $this->prepareAddRecord();

    $this->userManager->expects($this->once())
      ->method('getCurrentUser')
      ->will($this->returnValue(12345));

    $this->userManager->expects($this->once())
      ->method('checkIfUserExists')
      ->with($this->providerUserId)
      ->will($this->returnValue(FALSE));

    $this->userStorage->expects($this->once())
      ->method('create')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->socialPost));

    $this->socialPost->expects($this->once())
      ->method('setToken')
      ->with($this->isType('string'));

    $this->socialPost->expects($this->once())
      ->method('save');

    $this->userManager->setPluginId($this->pluginId);
    $this->assertTrue($this->userManager->addRecord('test', $this->providerUserId, 'jnh3q3q', NULL));

  }

  /**
   * Tests the addRecord method user doesn't exist and failure creating new.
   *
   * @covers Drupal\social_post\User\UserManager::addRecord
   */
  public function testAddRecordNoExistFailure() {
    $this->prepareAddRecord();

    $this->userManager->expects($this->once())
      ->method('getCurrentUser')
      ->will($this->returnValue(12345));

    $this->userManager->expects($this->once())
      ->method('checkIfUserExists')
      ->with($this->providerUserId)
      ->will($this->returnValue(FALSE));

    $this->userStorage->expects($this->once())
      ->method('create')
      ->with($this->isType('array'))
      ->will($this->returnValue(NULL));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertFalse($this->userManager->addRecord('test', $this->providerUserId, 'jnh3q3q', NULL));
  }

  /**
   * Tests the addRecord method user doesn't exist and exception while creating.
   *
   * @covers Drupal\social_post\User\UserManager::addRecord
   */
  public function testAddRecordNoExistException() {
    $this->prepareAddRecord();
    $logger = $this->createMock(LoggerChannelInterface::class);

    $this->userManager->expects($this->once())
      ->method('getCurrentUser')
      ->will($this->returnValue(12345));

    $this->userManager->expects($this->once())
      ->method('checkIfUserExists')
      ->with($this->providerUserId)
      ->will($this->returnValue(FALSE));

    $this->userStorage->expects($this->once())
      ->method('create')
      ->with($this->isType('array'))
      ->will($this->returnValue($this->socialPost));

    $this->socialPost->expects($this->once())
      ->method('setToken')
      ->with($this->isType('string'));

    $this->socialPost->expects($this->once())
      ->method('save')
      ->will($this->throwException(new EntityStorageException("Message")));

    $this->loggerFactory->expects($this->once())
      ->method('get')
      ->with($this->pluginId)
      ->will($this->returnValue($logger));

    $logger->expects($this->once())
      ->method('error')
      ->with('Failed to add a record for the user with id: @user_id. Exception: @message');

    $this->userManager->setPluginId($this->pluginId);
    $this->assertFalse($this->userManager->addRecord('test', $this->providerUserId, 'jnh3q3q', NULL));
  }

  /**
   * Tests the updateToken method with no account returned.
   *
   * @covers Drupal\social_post\User\UserManager::updateToken
   */
  public function testUpdateTokenWithNoAccountReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertFalse($this->userManager->updateToken($this->pluginId, $this->providerUserId, 'ba2w8gf2a68f'));
  }

  /**
   * Tests the updateToken method account returned and successfully updating.
   *
   * @covers Drupal\social_post\User\UserManager::updateToken
   */
  public function testUpdateTokenWithAccountReturnedSuccess() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([$this->socialPost]));

    $this->socialPost->expects($this->once())
      ->method('setToken')
      ->with($this->isType('string'))
      ->will($this->returnValue($this->socialPost));

    $this->socialPost->expects($this->once())
      ->method('save');

    $this->userManager->setPluginId($this->pluginId);
    $this->assertTrue($this->userManager->updateToken($this->pluginId, $this->providerUserId, 'ba2w8gf2a68f'));
  }

  /**
   * Tests the updateToken method with account returned and failure updating.
   *
   * @covers Drupal\social_post\User\UserManager::updateToken
   */
  public function testUpdateTokenWithAccountReturnedFailure() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([$this->socialPost]));

    $this->socialPost->expects($this->once())
      ->method('setToken')
      ->with($this->isType('string'))
      ->will($this->returnValue($this->socialPost));

    $this->socialPost->expects($this->once())
      ->method('save')
      ->will($this->throwException(new \Exception("test")));

    $this->userManager->setPluginId($this->pluginId);
    $this->expectException("Exception");
    $this->assertFalse($this->userManager->updateToken($this->pluginId, $this->providerUserId, 'ba2w8gf2a68f'));
  }

  /**
   * Tests the getToken method with no account returned.
   *
   * @covers Drupal\social_post\User\UserManager::getToken
   */
  public function testGetTokenWithNoAccountReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([]));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertNull($this->userManager->getToken($this->providerUserId));
  }

  /**
   * Tests the getToken method with token returned.
   *
   * @covers Drupal\social_post\User\UserManager::getToken
   */
  public function testGetTokenWithTokenReturned() {
    $this->userStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['plugin_id' => $this->pluginId, 'provider_user_id' => $this->providerUserId])
      ->will($this->returnValue([$this->socialPost]));

    $this->socialPost->expects($this->once())
      ->method('getToken')
      ->will($this->returnValue('cn7a2ASh2'));

    $this->userManager->setPluginId($this->pluginId);
    $this->assertEquals('cn7a2ASh2', $this->userManager->getToken($this->providerUserId));
  }

  /**
   * UserManager with mocked methods for addRecord tests.
   */
  protected function prepareAddRecord() {
    unset($this->userManager);
    $this->userManager = $this->getMockBuilder(UserManager::class)
      ->setConstructorArgs([$this->entityTypeManager,
        $this->currentUser,
        $this->dataHandler,
        $this->loggerFactory,
      ])
      ->setMethods(['getCurrentUser', 'checkIfUserExists'])
      ->getMock();
  }

}
