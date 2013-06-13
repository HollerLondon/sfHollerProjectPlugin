var PROJECT = {
  init : function() {
    // Scroll to hide address bar on mobile (iOS / Android / etc) - timeout is necessary
    if ($('#wrapper').hasClass('mobile')) {
      setTimeout(function() {
        try { window.scrollTo(0, 1); } catch(e) { }
      }, 0);
    }
    
    $(document).on('click', 'data-click[logout]', PROJECT.logout);
  },
  /** Check authorisation status of app and act accordingly
   * =========================== */
  checkLoginStatus : function(response) {
    $('#facebook-connect').html('Checking with Facebook...');
    
    if (response && response.status == 'connected') {
      try {
        _gaq.push(['_trackPageview', '/login']);
      } catch(e) { }
      
      $.ajax({
        type: 'GET',
        dataType: 'html', 
        url: Settings.authUser + '?signed_request=' + response.authResponse.signedRequest + '&access_token=' + response.authResponse.accessToken + '&user_id=' + response.authResponse.userID,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#facebook-connect').html('Connect with Facebook'); 
        },
        success: function(response, success, jqXHR) {
          // Update content
          $('#top').html(response); 
          
          // Parse JS
          eval($('#top').find('script'));
          
          // add logout button
          var name = jqXHR.getResponseHeader('X-Logged-In');
          $('#logged-status').html('Logged in as '+name+' | <a class="logout" data-click="logout" href="'+Settings.logout+'">Log out</a>');
        }
      });
    }
    // Not auth'd or not logged in
    else {
      $('#facebook-connect').html('Connect with Facebook'); 
    }
  },
  
  /** Log user out from Facebook and site (as per Facebook guidelines)
   * ============================= */
  logout: function(ev) {
    ev.preventDefault();
    var href = $(ev.target).attr('href');
    $(ev.target).html('Logging out...');
    
    // Check if logged into FB still
    FB.getLoginStatus(function(response) {
      if (response.status === 'connected' || response.status === 'not_authorized') {
        try {
          FB.logout(function(response) {
            window.location = href;
          });
        }
        catch (ex) { }
      } else {
        // Not logged into Facebook - just log out of site
        window.location = href;
      }
    });
  }
};

/**
 * Set up the site when document ready
 */
$(document).ready(function() {
  // Try and hide web debug to keep out of the way on dev
  try { sfWebDebugToggleMenu(); } catch(e) { }
  
  // IE fix for errant console.log
  if (typeof console === "undefined") console = { log: function() { } };
  
  // Init project
  PROJECT.init();
});