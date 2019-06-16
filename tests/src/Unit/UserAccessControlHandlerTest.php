<?php

use Drupal\social_post\UserAccessControlHandler;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Defines UserAccessControlerHandler class.
 *
 * @Annotation
 */
class UserAccessControlHandlerTest extends UnitTestCase {

  /**
   * Tests for class UserAccessControlHandler.
   */
  public function testUserAccessControlHandler() {
    $entity = $this->createMock(EntityInterface::class);
    $account = $this->createMock(AccountInterface::class);
    $entity_type = $this->createMock(EntityTypeInterface::class);

    $collection = $this->getMockBuilder(EntityAccessControlHandler::class)
                                     ->setConstructorArgs(array($entity_type))
                                     ->getMock();

    $userAccessControlHandler = $this->getMockBuilder(UserAccessControlHandler::class)
                                     ->setConstructorArgs(array($entity_type))
                                     ->getMock();

    // $userAccessControlHandler->checkAccess($entity, 'view', $account);
    // $reflection = new ReflectionClass(UserAccessControlHandler::class);
    // $method = $reflection->getMethod('checkAccess');
    // $method->setAccessible(true);
    // return $method;
    $this->assertTrue(
          method_exists($userAccessControlHandler, 'checkAccess'),
            'ControllerBase does not implements checkAccess function/method'
    );

    $this->assertTrue(
          method_exists($userAccessControlHandler, 'checkCreateAccess'),
            'ControllerBase does not implements checkCreateAccess function/method'
    );
  }

}
