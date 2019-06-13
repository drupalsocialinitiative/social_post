<?php

use Drupal\social_post\Controller\ControllerBase;
use Drupal\Core\Controller\ControllerBase as DrupalControllerBase;
use Drupal\social_post\Entity\Controller\SocialPostListBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * Defines Controller class.
 *
 * @Annotation
 */
class ControllerTest extends UnitTestCase {

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
   * Tests for class Network.
   */
  public function testControllerBase() {
    $controllerBase = $this->getMockBuilder(ControllerBase::class)
                           ->getMock();
    $listBuilder = $this->createMock(SocialPostListBuilder::class);
    $controllerBase->method('buildList')
                   ->with('facebook')
                   ->will($this->returnValue($listBuilder->render()));
    $this->assertTrue(
          method_exists($controllerBase, 'buildList'),
            'ControllerBase does not implements buildList function/method'
    );
    $this->assertEquals($listBuilder->render(), $controllerBase->buildList('facebook'));
  }

}
