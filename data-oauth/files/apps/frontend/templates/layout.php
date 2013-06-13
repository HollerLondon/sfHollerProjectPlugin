<!DOCTYPE HTML>
<html>
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php if (has_slot('meta')) include_slot('meta'); // open graph details ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <!--[if lte IE 8]>
      <link rel="stylesheet" type="text/css" href="/css/ie.css" />
    <![endif]-->
    <?php include_partial('index/jsSettings'); ?>
    <?php include_javascripts() ?>
  </head>
  
  <body<?php if ($sf_user->isMobile()) echo ' class="mobile"'; ?>>
    <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId  : '<?php echo sfConfig::get('app_facebook_app_id'); ?>',
          status : true,
          cookie : true,
          xfbml  : true
        });
        
        <?php if (has_slot('fb_js')) include_slot('fb_js'); ?>
      };
      // Load async as causing scrollbars in FF4 otherwise
      (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
    
    <div id="wrapper"<?php if (has_slot('body_class')) echo sprintf(' class="%s"', get_slot('body_class')); ?>>
      <?php include_partial('index/header'); ?>
      
      <div id="content">
        <?php echo $sf_content ?>
      </div>
      
      <?php include_partial('index/footer'); ?>
    </div>
  </body>
</html>