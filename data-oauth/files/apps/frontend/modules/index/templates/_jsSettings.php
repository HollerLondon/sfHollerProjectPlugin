<script type="text/javascript">
  var Settings = {
    scope           : '<?php echo sfConfig::get('app_facebook_app_scope'); ?>',
    authUser        : '<?php echo url_for('@authUser'); ?>',
    homepage        : '<?php echo url_for('@homepage', true); ?>',
    logout          : '<?php echo url_for('@sf_guard_signout'); ?>'
  };
</script>
