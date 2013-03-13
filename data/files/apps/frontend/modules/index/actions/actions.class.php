<?php

/**
 * index actions.
 *
 * @package    sfHollerProjectPlugin
 * @subpackage tab
 * @author     Jo Carter <jocarter@holler.co.uk>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class indexActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    // IMPORTANT: So Facebook can pick up the meta data for the site when added to a post
    $metas           = sfConfig::get('app_facebook_default_og_data', array());
    $metas['image']  = 'http://placehold.it/200x200'; // @TODO: Proper og image
    $metas['url']    = $this->generateUrl('homepage', array(), true);
    
    $this->setVar('metas', $metas, true);
    
    // SITE CONTENT HERE
  }
}
