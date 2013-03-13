// ADD YOUR CODE HERE
var PROJECT = {
  init : function() {
    // Scroll to hide address bar on mobile (iOS / Android / etc) - timeout is necessary
    if ($('#wrapper').hasClass('mobile')) {
      setTimeout(function() {
        try { window.scrollTo(0, 1); } catch(e) { }
      }, 0);
    }
    
    // ADD YOUR CODE HERE
  }  
  // ADD YOUR CODE HERE
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