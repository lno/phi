// page init
jQuery(function(){
  initOpenClose();
});

// sticky header init
function initStickyHeader(){
  var win = jQuery(window);

  jQuery('.header-top.js-fixed').each(function(){
    var header = jQuery(this);
    var headerClone = header.clone();
    var activeClass = 'header-fixed';
    var fixedFlag = false;

    headerClone.addClass(activeClass).insertBefore(header);

    if(win.scrollTop() > header.offset().top + header.outerHeight()){
      headerClone.css({
        top: 0
      });
    } else {
      headerClone.css({
        top: -headerClone.outerHeight()
      });
    }

    function getFixed(){
      if(win.scrollTop() > header.offset().top + header.outerHeight()) {
        if(!fixedFlag) {
          fixedFlag = true;
          headerClone.stop().animate({
            top: 0
          });
        }
      } else {
        if(fixedFlag) {
          fixedFlag = false;
          headerClone.stop().animate({
            top: -headerClone.outerHeight()
          });
        }
      }
    }

    win.bind('resize orientationchange scroll', getFixed);
  });
}

// open-close init
function initOpenClose() {
  jQuery('#nav').openClose({
    activeClass: 'active',
    opener: '.opener',
    slider: 'ul',
    animSpeed: 400,
    effect: 'slide'
  });
}

/*
 * jQuery Open/Close plugin
 */
;(function($) {
  function OpenClose(options) {
    this.options = $.extend({
      addClassBeforeAnimation: true,
      hideOnClickOutside: false,
      activeClass:'active',
      opener:'.opener',
      slider:'.slide',
      animSpeed: 400,
      effect:'fade',
      event:'click'
    }, options);
    this.init();
  }
  OpenClose.prototype = {
    init: function() {
      if(this.options.holder) {
        this.findElements();
        this.attachEvents();
        this.makeCallback('onInit');
      }
    },
    findElements: function() {
      this.holder = $(this.options.holder);
      this.opener = this.holder.find(this.options.opener);
      this.slider = this.holder.find(this.options.slider);
    },
    attachEvents: function() {
      // add handler
      var self = this;
      this.eventHandler = function(e) {
        e.preventDefault();
        if (self.slider.hasClass(slideHiddenClass)) {
          self.showSlide();
        } else {
          self.hideSlide();
        }
      };
      self.opener.bind(self.options.event, this.eventHandler);

      // hover mode handler
      if(self.options.event === 'over') {
        self.opener.bind('mouseenter', function() {
          self.showSlide();
        });
        self.holder.bind('mouseleave', function() {
          self.hideSlide();
        });
      }

      // outside click handler
      self.outsideClickHandler = function(e) {
        if(self.options.hideOnClickOutside) {
          var target = $(e.target);
          if (!target.is(self.holder) && !target.closest(self.holder).length) {
            self.hideSlide();
          }
        }
      };

      // set initial styles
      if (this.holder.hasClass(this.options.activeClass)) {
        $(document).bind('click touchstart', self.outsideClickHandler);
      } else {
        this.slider.addClass(slideHiddenClass);
      }
    },
    showSlide: function() {
      var self = this;
      if (self.options.addClassBeforeAnimation) {
        self.holder.addClass(self.options.activeClass);
      }
      self.slider.removeClass(slideHiddenClass);
      $(document).bind('click touchstart', self.outsideClickHandler);

      self.makeCallback('animStart', true);
      toggleEffects[self.options.effect].show({
        box: self.slider,
        speed: self.options.animSpeed,
        complete: function() {
          if (!self.options.addClassBeforeAnimation) {
            self.holder.addClass(self.options.activeClass);
          }
          self.makeCallback('animEnd', true);
        }
      });
    },
    hideSlide: function() {
      var self = this;
      if (self.options.addClassBeforeAnimation) {
        self.holder.removeClass(self.options.activeClass);
      }
      $(document).unbind('click touchstart', self.outsideClickHandler);

      self.makeCallback('animStart', false);
      toggleEffects[self.options.effect].hide({
        box: self.slider,
        speed: self.options.animSpeed,
        complete: function() {
          if (!self.options.addClassBeforeAnimation) {
            self.holder.removeClass(self.options.activeClass);
          }
          self.slider.addClass(slideHiddenClass);
          self.makeCallback('animEnd', false);
        }
      });
    },
    destroy: function() {
      this.slider.removeClass(slideHiddenClass).css({display:''});
      this.opener.unbind(this.options.event, this.eventHandler);
      this.holder.removeClass(this.options.activeClass).removeData('OpenClose');
      $(document).unbind('click touchstart', this.outsideClickHandler);
    },
    makeCallback: function(name) {
      if(typeof this.options[name] === 'function') {
        var args = Array.prototype.slice.call(arguments);
        args.shift();
        this.options[name].apply(this, args);
      }
    }
  };

  // add stylesheet for slide on DOMReady
  var slideHiddenClass = 'js-slide-hidden';
  $(function() {
    var tabStyleSheet = $('<style type="text/css">')[0];
    var tabStyleRule = '.' + slideHiddenClass;
    tabStyleRule += '{position:absolute !important;left:-9999px !important;top:-9999px !important;display:block !important}';
    if (tabStyleSheet.styleSheet) {
      tabStyleSheet.styleSheet.cssText = tabStyleRule;
    } else {
      tabStyleSheet.appendChild(document.createTextNode(tabStyleRule));
    }
    $('head').append(tabStyleSheet);
  });

  // animation effects
  var toggleEffects = {
    slide: {
      show: function(o) {
        o.box.stop(true).hide().slideDown(o.speed, o.complete);
      },
      hide: function(o) {
        o.box.stop(true).slideUp(o.speed, o.complete);
      }
    },
    fade: {
      show: function(o) {
        o.box.stop(true).hide().fadeIn(o.speed, o.complete);
      },
      hide: function(o) {
        o.box.stop(true).fadeOut(o.speed, o.complete);
      }
    },
    none: {
      show: function(o) {
        o.box.hide().show(0, o.complete);
      },
      hide: function(o) {
        o.box.hide(0, o.complete);
      }
    }
  };

  // jQuery plugin interface
  $.fn.openClose = function(opt) {
    return this.each(function() {
      jQuery(this).data('OpenClose', new OpenClose($.extend(opt, {holder: this})));
    });
  };
}(jQuery));
