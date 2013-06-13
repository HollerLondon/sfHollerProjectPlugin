<?php function opengraph_meta($key,$value)
{
  return tag('meta', array(
    'property'    => sprintf("%s:%s",
      preg_match('/^(admins|app_id)$/', $key) ? 'fb' : 'og',
      $key
    ),
    'content'     => $value
  ));
} 

slot('meta');
  foreach ($metas as $key => $value)
  {
    echo opengraph_meta($key, $value);
  }
end_slot(); ?>

<?php if (!$user) : // If not authenticated - check authentication - or see main.js for suggestions ?>
  <?php slot('fb_js'); ?>
    // Check if authorized app (and logged in with Facebook)
    var sitePolling = setInterval(function(){
      if (typeof PROJECT != 'undefined' && typeof FB != 'undefined') {
        FB.getLoginStatus(PROJECT.checkLoginStatus);
        clearInterval(sitePolling);
      }
    }, 500);
  <?php end_slot(); ?>
<?php endif; ?>

<div id="top">
  <?php echo link_to('Connect with Facebook', '@sf_cacophony_connect?provider=facebook', array('id' => 'facebook-connect', 'class' => 'facebook-connect')); ?>
</div>