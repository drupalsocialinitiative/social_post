<?php

use Drupal\Tests\UnitTestCase;
use Drupal\social_post\SocialPostDataHandler;

/**
 * Tests for class SocialPostDataHandler.
 */
class SocialPostDataHandlerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function testSocialPostDataHandler() {
    $collection = $this->getMockBuilder('Drupal\social_post\SocialPostDataHandler')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertTrue($collection instanceof SocialPostDataHandler);
  }

}
