<?php

use Drupal\social_post\Controller\ControllerBase;
use Drupal\social_post\Entity\Controller\SocialPostListBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * Defines Controller class.
 *
 * @Annotation
 */
class ControllerTest extends UnitTestCase {

  /**
   * Tests for class Network.
   */
  public function testControllerBase() {
    $controllerBase = $this->getMockBuilder(ControllerBase::class)
      ->getMock();

    $listBuilder = $this->createMock(SocialPostListBuilder::class);

    $this->assertTrue(
          method_exists($controllerBase, 'buildList'),
            'ControllerBase does not implements buildList function/method'
    );
  }

}
