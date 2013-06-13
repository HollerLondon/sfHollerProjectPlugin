<?php

/**
 * index actions.
 *
 * @package    sfHollerProjectPlugin
 * @subpackage index
 * @author     Jo Carter <jocarter@holler.co.uk>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class indexActions extends sfActions
{
  /**
   * Prepare user object for use in tab (assuming use of sfGuardUser)
   */
  public function preExecute()
  {
    $this->user = null;

    if ($this->getUser()->isAuthenticated() && !$this->getUser()->isSuperAdmin()) 
    {
      $this->user = $this->getUser()->getGuardUser();
      $this->getResponse()->setHttpHeader('X-Logged-In', $this->user->first_name);
    }
    
    $this->getResponse()->setSlot('user', $this->user);
    
    // If AJAX - don't include layout
    if ($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->hasParameter('ajax')) 
    {
      $this->isAjax = true;
      $this->setLayout(false);
      sfConfig::set('sf_web_debug', false);
    }
    else $this->isAjax = false;
  }
  
  
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Asset'));
    
    // IMPORTANT: So Facebook can pick up the meta data for the app / site
    $metas           = sfConfig::get('app_facebook_default_og_data', array());
    $metas['app_id'] = sfConfig::get('app_facebook_app_id');
    $metas['image']  = image_path(sfConfig::get('app_image_prefix').'og.image.jpg', true);
    $metas['url']    = sfConfig::get('app_facebook_canvas_url');
    
    $this->setVar('metas', $metas, true);
    
    // If in Facebook
    if (!empty($this->data))
    {
      sfConfig::set('sf_web_debug', false);
      return $this->renderText(sprintf('<script>top.location.href="%s"</script>', $this->generateUrl('homepage', array(), true)));
    }
    // If come directly to site - / url
    else
    {
      
    }
  }
  
  /**
   * Ajax request logs user in - creating new user if doesn't exist
   * 
   * @param sfWebRequest $request
   */
  public function executeAuthUser(sfWebRequest $request)
  {
    // Set access token in session
    $this->access_token = $request->getParameter('access_token');
    $this->getUser()->setAttribute('access_token', $this->access_token);
    $this->user         = sfGuardUserTable::getInstance()->findOneById($request->getParameter('user_id'));
    $user_data          = array();
    
    // New user, save into DB - or no data - so update
    if (!$this->user || !$this->user->email_address) 
    {
      // Get /me from Facebook plugin with access token
      $fb = sfFacebook::getInstance();
      $fb->setAccessToken($this->access_token);
      
      try 
      {
        $user_data           = $fb->api('/me');
        $user_data['fb_uid'] = $user_data['id'];
      }
      // Facebook gone wonky
      catch (FacebookApiException $e) 
      {
        $user_data = array('fb_uid' => $request->getParameter('user_id'));
      }
    }
    
    $this->user = $this->checkUser($this->user, $user_data, ($this->getUser()->isMobile() ? 'mobile' : 'microsite'), $this->access_token);
        
    // If $this->user - log in
    if ($this->user) $this->getUser()->signin($this->user);
    
    // @TODO: Return URL to redirect to / or partial - depending on situation
    return $this->renderText(json_encode(array('redirect' => $this->generateUrl('homepage', array(), true))));
  }
  
	/**
   * Check, validate and save/ update user (assuming use of sfGuardUser with email address)
   *
   * @param sfGuardUser $user
   * @param array $user_data
   * @param string $source
   * @param string $access_token
   * @return sfGuardUser
   */
  private function checkUser($user, $user_data, $source, $access_token = null)
  {
    $new = false; 
    
    // New user, save into DB - or update missing info
    // If there was an open graph error then may not have user data - this ensures if missing it gets updated
    if ((!$user || !$user->email_address) && !empty($user_data)) 
    {
      if (isset($user_data['email'])) $user_data['email_address'] = $user_data['email']; 
      if (empty($user_data['email_address']) && !empty($user_data['username'])) $user_data['email_address'] = $user_data['username'].'@facebook.com';
     
      if (!$user) 
      {
        $new  = true;
        $user = new sfGuardUser();
        $user_data['source'] = $source;
        if (empty($user_data['username'])) $user_data['username'] = 'facebook_' . $user_data['fb_uid'];
      }
     
      // Validate email address
      try 
      {
        $validator = new sfValidatorEmail(array('required'=>true));
        $user_data['email_address'] = $validator->clean($user_data['email_address']);
      }
      catch (sfValidatorError $e) 
      {
        $user_data['email_address'] = null; // invalid email address
      }
     
      $user->fromArray($user_data);
      if ($new) $user->upgradeAccessToken($access_token); // If using Cacophony and tokens get upgraded access token here
      $user->save();
    }
    
    // Check access token
    if ($user && !$new)
    {
      // Check access token retrieved
      $token = $user->getTokenFor('facebook');
      
      if (!$token || $token->isExpired()) 
      {
        $user->upgradeAccessToken($access_token);
        $user->save();
      }
    }
   
    return $user;
  }
}
