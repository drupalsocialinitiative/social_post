<?php

use Drupal\social_post\Entity\SocialPost;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\social_post\Entity\Controller\SocialPostListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Defines Entity class.
 *
 * @Annotation
 */
class EntityTest extends UnitTestCase {

  /**
   * Tests for class SocialPost.
   */
  public function testSocialPost() {

    $socialPost = $this->getMockBuilder(SocialPost::class)
                       ->disableOriginalConstructor()
                       ->getMock();

    $socialPost->expects($this->any())
               ->method('getProviderUserId')
               ->willReturn('drupalUser');

    $socialPost->expects($this->any())
               ->method('getPluginId')
               ->willReturn('implementerName');

    $socialPost->expects($this->any())
               ->method('getName')
               ->willReturn('providerName');

    $socialPost->expects($this->any())
               ->method('getId')
               ->willReturn('providerId');

    $socialPost->expects($this->any())
               ->method('getUserId')
               ->willReturn(123);

    $this->assertTrue(
          method_exists($socialPost, 'getProviderUserId'),
            'SocialPost does not implements getProviderUserId function/method'
    );
    $this->assertTrue(
          method_exists($socialPost, 'getPluginId'),
            'SocialPost does not implements getPluginId function/method'
    );
    $this->assertTrue(
          method_exists($socialPost, 'getName'),
            'SocialPost does not implements getName function/method'
    );
    $this->assertTrue(
          method_exists($socialPost, 'getId'),
            'SocialPost does not implements getId function/method'
    );
    $this->assertTrue(
          method_exists($socialPost, 'getUserId'),
            'SocialPost does not implements getUserId function/method'
    );

    $this->assertSame('drupalUser', $socialPost->getProviderUserId());
    $this->assertSame('implementerName', $socialPost->getPluginId());
    $this->assertSame('providerName', $socialPost->getName());
    $this->assertSame('providerId', $socialPost->getId());
    $this->assertSame(123, $socialPost->getUserId());
  }

  /**
   * tests for class SocialPostListBuilder
   */
  public function testSocialPostListBuilder() {
    $entity_type = $this->createMock(EntityTypeInterface::class);
    $storage = $this->createMock(EntityStorageInterface::class);
    $user_entity = $this->createMock(EntityStorageInterface::class);
    $url_generator = $this->createMock(UrlGeneratorInterface::class);
    $entity = $this->createMock(EntityInterface::class);
    $container = $this->createMock(ContainerInterface::class);
    $entity_type = $this->createMock(EntityTypeInterface::class);

    $socialPostListBuilder = $this->getMockBuilder(SocialPostListBuilder::class)
                                  ->setConstructorArgs(array($entity_type,
                                                             $storage,
                                                             $user_entity,
                                                             $url_generator,
                                                       ))
                                  ->getMock();

    $socialPostListBuilder->setProvider('providerName');

    $this->assertTrue(
          method_exists($socialPostListBuilder, 'createInstance'),
            'SocialPostListBuilder does not implements createInstance function/method'
    );

    $this->assertTrue(
          method_exists($socialPostListBuilder, 'setProvider'),
            'SocialPostListBuilder does not implements setProvider function/method'
    );

    $this->assertTrue(
          method_exists($socialPostListBuilder, 'buildHeader'),
            'SocialPostListBuilder does not implements buildHeader function/method'
    );

    $this->assertTrue(
          method_exists($socialPostListBuilder, 'buildRow'),
            'SocialPostListBuilder does not implements buildRow function/method'
    );

    $this->assertTrue(
          method_exists($socialPostListBuilder, 'getDefaultOperations'),
            'SocialPostListBuilder does not implements getDefaultOperations function/method'
    );
  }

}
