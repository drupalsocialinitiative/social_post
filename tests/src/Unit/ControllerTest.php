<?php

use Drupal\social_post\Plugin\Network\SocialPostNetworkInterface;
use Drupal\social_api\Plugin\NetworkInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Defines Controller class.
 *
 * @Annotation
 */
class NetworkTest extends UnitTestCase {

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
  public function testSocialPostNetworkInterface() {
    $socialPostNetworkInterface = $this->getMockBuilder(SocialPostNetworkInterface::class)
                                       ->getMock();
    $this->assertTrue(
          method_exists($socialPostNetworkInterface, 'post'),
            'SocialPostNetworkInterface does not implements post function/method'
    );
  }

}
