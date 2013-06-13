<div id="logged-status"><?php // NOTE: Used by main.js when JS log in - if this needs to refactor - check out checkLoginStatus and fix there too :) ?>
  <?php if ($sf_user->isAuthenticated() && !$sf_user->isSuperAdmin()) : ?>
    <?php $user = get_slot('user', $sf_user->getGuardUser()); ?>
    Logged in as <?php echo $user->first_name; ?> |
    <?php echo link_to('Log out', '@sf_guard_signout', array('class' => 'logout', 'data-click'=>'logout')); ?>
  <?php endif; ?>
</div>