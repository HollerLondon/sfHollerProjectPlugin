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
  <body>
    <div id="wrapper"<?php if ($sf_user->isMobile()) echo ' class="mobile"'; ?>>
      <div id="header">
        
      </div>
      
      <div id="content">
        <?php echo $sf_content ?>
      </div>
      
      <div id="footer">
      
      </div>
    </div>
  </body>
</html>
