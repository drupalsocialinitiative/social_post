<?php

namespace Drupal\social_post\Controller;

use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_post\DataHandler;
use Drupal\social_post\Entity\Controller\SocialPostListBuilder;
use Drupal\social_post\PostManager\OAuth2ManagerInterface;
use Drupal\social_post\User\UserAuthenticator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle responses for Social Post implementer controllers.
 */
class OAuth2ControllerBase extends ControllerBase {

  /**
   * The module name.
   *
   * @var string
   */
  protected $module;

  /**
   * The implement plugin id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $networkManager;

  /**
   * The Social Post user authenticator..
   *
   * @var \Drupal\social_post\User\UserAuthenticator
   */
  protected $userAuthenticator;

  /**
   * The provider authentication manager.
   *
   * @var \Drupal\social_post\PostManager\OAuth2ManagerInterface
   */
  protected $providerManager;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The Social Auth data handler.
   *
   * @var \Drupal\social_post\DataHandler
   */
  protected $dataHandler;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * SocialAuthControllerBase constructor.
   *
   * @param string $module
   *   The module name.
   * @param string $plugin_id
   *   The plugin id.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of the network plugin.
   * @param \Drupal\social_post\User\UserAuthenticator $user_authenticator
   *   Used to manage user authentication/registration.
   * @param \Drupal\social_post\PostManager\OAuth2ManagerInterface $provider_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_post\DataHandler $data_handler
   *   The Social Auth data handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Used to handle metadata for redirection to authentication URL.
   * @param \Drupal\social_post\Entity\Controller\SocialPostListBuilder $list_builder
   *   The Social Post entity list builder.
   */
  public function __construct($module,
                              $plugin_id,
                              NetworkManager $network_manager,
                              UserAuthenticator $user_authenticator,
                              OAuth2ManagerInterface $provider_manager,
                              RequestStack $request,
                              DataHandler $data_handler,
                              RendererInterface $renderer,
                              SocialPostListBuilder $list_builder) {

    parent::__construct($list_builder);

    $this->module = $module;
    $this->pluginId = $plugin_id;
    $this->networkManager = $network_manager;
    $this->userAuthenticator = $user_authenticator;
    $this->providerManager = $provider_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;
    $this->renderer = $renderer;

    // Sets the plugin id in user authenticator.
    $this->userAuthenticator->setPluginId($plugin_id);
  }

  /**
   * Response for implementer authentication url.
   *
   * Redirects the user to provider for authentication.
   *
   * This is done in a render context in order to bubble cacheable metadata
   * created during authentication URL generation.
   *
   * @see https://www.drupal.org/project/social_auth/issues/3033444
   */
  public function redirectToProvider() {
    $context = new RenderContext();

    /** @var \Drupal\Core\Routing\TrustedRedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse $response */
    $response = $this->renderer->executeInRenderContext($context, function () {
      try {

        /** @var \League\OAuth2\Client\Provider\AbstractProvider|false $client */
        $client = $this->networkManager->createInstance($this->pluginId)->getSdk();

        // If provider client could not be obtained.
        if (!$client) {
          $this->messenger()->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));

          return $this->redirect('entity.user.edit_form', [
            'user' => $this->userAuthenticator->currentUser()->id(),
          ]);
        }

        // Provider service was returned, inject it to $providerManager.
        $this->providerManager->setClient($client);

        // Generates the URL for authentication.
        $auth_url = $this->providerManager->getAuthorizationUrl();

        $state = $this->providerManager->getState();
        $this->dataHandler->set('oauth2state', $state);

        return new TrustedRedirectResponse($auth_url);
      }
      catch (\Exception $e) {
        $this->messenger()->addError($this->t('There has been an error during authentication.'));
        $this->getLogger($this->pluginId)->error($e->getMessage());

        return $this->redirect('entity.user.edit_form', [
          'user' => $this->userAuthenticator->currentUser()->id(),
        ]);
      }
    });

    // Add bubbleable metadata to the response.
    if ($response instanceof TrustedRedirectResponse && !$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      $response->addCacheableDependency($bubbleable_metadata);
    }

    return $response;
  }

  /**
   * Process implementer callback path.
   *
   * @return \League\OAuth2\Client\Provider\GenericResourceOwner|null
   *   The user info if successful.
   *   Null otherwise.
   */
  public function processCallback() {
    try {

      /**  @var \League\OAuth2\Client\Provider\AbstractProvider|false $client */
      $client = $this->networkManager->createInstance($this->pluginId)->getSdk();

      // If provider client could not be obtained.
      if (!$client) {
        $this->messenger()->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));

        return NULL;
      }

      $state = $this->dataHandler->get('oauth2state');

      // Retrieves $_GET['state'].
      $retrievedState = $this->request->getCurrentRequest()->query->get('state');

      if (empty($retrievedState) || ($retrievedState !== $state)) {
        $this->userAuthenticator->nullifySessionKeys();
        $this->messenger()->addError($this->t('Login failed. Invalid OAuth2 state.'));

        return NULL;
      }

      $this->providerManager->setClient($client)->authenticate();

      // Gets user's info from provider.
      if (!$profile = $this->providerManager->getUserInfo()) {
        $this->messenger()->addError($this->t('Login failed, could not load user profile. Contact site administrator.'));
        return NULL;
      }

      return $profile;
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('There has been an error during authentication.'));
      $this->getLogger($this->pluginId)->error($e->getMessage());

      return NULL;
    }
  }

  /**
   * Checks if there was an error during authentication with provider.
   *
   * When there is an authentication problem in a provider (e.g. user did not
   * authorize the app), a query to the client containing an error key is often
   * returned. This method checks for such key, dispatches an event, and returns
   * a redirect object where there is an authentication error.
   *
   * @param string $key
   *   The query parameter key to check for authentication error.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
   *   Redirect response object that may be returned by the controller or null.
   */
  protected function checkAuthError($key = 'error') {
    $request_query = $this->request->getCurrentRequest()->query;

    // Checks if authentication failed.
    if ($request_query->has($key)) {
      $this->messenger()->addError($this->t('You could not be authenticated.'));

      return $this->redirect('entity.user.edit_form', [
        'user' => $this->userAuthenticator->currentUser()->id(),
      ]);
    }

    return NULL;
  }

}
