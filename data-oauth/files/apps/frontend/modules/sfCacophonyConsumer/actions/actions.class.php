<?php
require_once(dirname(__FILE__).'/../../../../../plugins/sfCacophonyPlugin/modules/sfCacophonyConsumer/lib/BasesfCacophonyConsumerActions.class.php');

/**
 * sfCacophonyConsumer actions.
 *
 * @package    sfCacophonyPlugin
 * @subpackage sfCacophonyConsumer
 */
class sfCacophonyConsumerActions extends BasesfCacophonyConsumerActions
{
  /**
   * Oath 2.0 callback
   * 
   * @param sfWebRequest $request
   * @throws Exception
   */
  public function executeCallback2(sfWebRequest $request)
  {
    $config     = sfConfig::get('app_cacophony');
    $provider = $request->getParameter('provider');
    
    if ($request->hasParameter('state'))
    {
      // CSFR protection as adviced on the http://developers.facebook.com/docs/authentication/
      if ($request->getParameter('state') != $this->getUser()->getAttribute('state', null , sprintf('sfCacophonyPlugin/%s', $provider)))
      {
        throw new Exception('CSRF attack detected');
      }  
    }

    if (!$this->getUser()->isAuthenticated() || $config['plugin']['allow_multiple_tokens'])
    {
      try
      {
        sfApplicationConfiguration::getActive()->loadHelpers(array('Url'));
        
        $this->getUser()->setAttribute('state', $request->getParameter('state'));
        
        $fb = sfFacebook::getInstance();
        $at = $fb->getAccessTokenParamsFromCode($request->getParameter('code'), (sfContext::getInstance()->getRouting()->hasRouteName('sf_cacophony_callback') ? url_for('@sf_cacophony_callback?provider=facebook', true) : 'oob'));
        
        if (!$at) 
        {
          // throw new Exception('Could not get access token');
          $this->redirect('@homepage'); // will get picked up by JS or maybe already logged in
        }
        
        $at['expires_at'] = date('c', time() + ($at['expires'] ?: 0));
        
        $this->getUser()->setAttribute(
          'accessToken',
          $at,
          sprintf('sfCacophonyPlugin/%s', $provider)
        );

        // add me to session
        // Use sfFacebook api call for me - and catch exceptions - this may time out if graph API down
        $fb = sfFacebook::getInstance();
        $fb->setAccessToken($at['access_token']);
        
        $result               = $fb->api('/me');
        $result['fb_uid']     = $result['id'];
        $result['normalized'] = $result; // for backward compatiblity
        
        $this->getUser()->setAttribute('me', $result, sprintf('sfCacophonyPlugin/%s', $provider));
      }
      catch (Exception $e)
      {
        // FB gone wonky
        $this->redirect('@homepage');
      }
    }
    else $this->redirect('@homepage');
    
    return sfView::NONE;
  }
  
  /**
   * Register user from Facebook
   *
   * @param sfRequest $request 
   */
  public function executeRegister(sfRequest $request)
  {
    $provider = $request->getParameter('provider');
    
    // Use sfFacebook api call for me - and catch exceptions - this may time out if graph API down
    $fb = sfFacebook::getInstance();
    $at = $this->getUser()->getAttribute('accessToken', null, sprintf('sfCacophonyPlugin/%s', $provider));
    $fb->setAccessToken($at['access_token']);
    
    try
    {
      $result           = $fb->api('/me');
      $result['fb_uid'] = $result['id'];
      
      if (isset($result['email'])) $result['email_address'] = $result['email']; 
      if (empty($result['email_address']) && !empty($result['username'])) $result['email_address'] = $result['username'].'@facebook.com';
      if (empty($result['username'])) $result['username'] = 'facebook_' . $result['fb_uid'];
      
      // Validate email address
      $validator = new sfValidatorEmail(array('required'=>true));
      $result['email_address'] = $validator->clean($result['email_address']);
    }
    // Facebook gone wonky
    catch (FacebookApiException $e)
    {
      throw $e;
    }
    catch (sfValidatorError $e) 
    {
      $result['email_address'] = null; // invalid email address
    }
    
    // Check if user exists
    if ($this->getUser()->isAuthenticated()) $sf_guard_user = $this->getUser()->getGuardUser();
    else $sf_guard_user = sfGuardUserTable::getInstance()->findOneById($result['fb_uid']);
    
    if (!$sf_guard_user) 
    {
      // If user doesn't exist
      $token = new Token();
      $token->setProvidersUserId($result['fb_uid']);
      $token->setContent($at);
      if (isset($at['expires_at'])) $token->setExpiresAt(date('Y-m-d H:i:s', strtotime($at['expires_at'])));
      $token->setProvider($provider);

      $sf_guard_user = new sfGuardUser();
      $sf_guard_user->setSource(($this->getUser()->isMobile() ? 'mobile' : 'microsite'));
      $sf_guard_user->fromArray($result);
      $sf_guard_user['Tokens']->add($token);
      $sf_guard_user->save();
      
      // Upgrade access token
     // $sf_guard_user->upgradeAccessToken($at['access_token']);
     // $sf_guard_user->save();
    }
    else 
    {
      $hasToken = false;
      
      // Or if the user exists, update the token
      foreach ($sf_guard_user['Tokens'] as $token) 
      {
        if ($token['provider'] == $provider) 
        {
          $token->setContent($at);
          if (isset($at['expires_at'])) $token->setExpiresAt(date('Y-m-d H:i:s', strtotime($at['expires_at'])));
          $token->save();
          $hasToken = true;
          break;
        }
      }
      
      // If it's a new token - add it
      if (!$hasToken) 
      {
        $token = new Token();
        $token->setProvidersUserId($result['fb_uid']);
        $token->setContent($at);
        if (isset($at['expires_at'])) $token->setExpiresAt(date('Y-m-d H:i:s', strtotime($at['expires_at'])));
        $token->setProvider($provider);
        $sf_guard_user['Tokens']->add($token);
        $sf_guard_user->save();
      }
    }
    
    // Log in user
    $this->getUser()->signin($sf_guard_user);
    
    // and redirect to homepage, or wherever you want
    $this->redirect('@homepage');
  }
}
