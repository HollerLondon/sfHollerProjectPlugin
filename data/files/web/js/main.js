var PROJECT = {
  init : function() {
    
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
  
  // ADD PROJECT CODE HERE
  PROJECT.init();
});