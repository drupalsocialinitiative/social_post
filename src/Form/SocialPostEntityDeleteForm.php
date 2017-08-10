<?php

namespace Drupal\social_post\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting Social Post user entities.
 *
 * @ingroup social_post
 */
class SocialPostEntityDeleteForm extends ContentEntityDeleteForm {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * SocialPostUserEntityDeleteForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The current route match.
   */
  public function __construct(EntityManagerInterface $entity_manager, CurrentRouteMatch $route_match) {
    parent::__construct($entity_manager);

    $this->routeMatch = $route_match;
  }



  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */

    $entity = $this->getEntity();
    $entity->delete();
    $form_state->setRedirectUrl($this->getRedirectUrl());

    drupal_set_message($this->getDeletionMessage());
    $this->logDeletionMessage();

  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl()
  {

    $uid = $this->routeMatch->getParameter('user');
    $provider = $this->routeMatch->getParameter('provider');
    // If a user id is passed as a parameter,
    // the form is being invoked from a user edit form.
    if ($uid) {
      return Url::fromRoute('entity.user.edit_form', ['user' => $uid]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $uid = $this->routeMatch->getParameter('user');
    $provider = $this->routeMatch->getParameter('provider');
    // If a user id is passed as a parameter,
    // the form is being invoked from a user edit form.
    if ($uid) {
      return Url::fromRoute('entity.user.edit_form', ['user' => $uid]);
    }
    return Url::fromRoute('entity.social_post.collection', ['provider' => $provider]);
  }
}
