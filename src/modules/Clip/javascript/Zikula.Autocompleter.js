/**
 * Clip
 *
 * @link       http://code.zikula.org/clip/
 * @package    Clip
 * @subpackage Javascript
 */

/**
 * Proto!MultiSelect 0.2
 * - Prototype version required: 6.0
 *
 * Credits:
 * - Idea: Facebook + Apple Mail
 * - Caret position method: Diego Perini <http://javascript.nwbox.com/cursor_position/cursor.js>
 * - Guillermo Rauch: Original MooTools script
 * - Ran Grushkowsky/InteRiders Inc. : Porting into Prototype and further development
 *
 * Changelog:
 * - 0.1: translation of MooTools script
 * - 0.2: renamed from Proto!TextboxList to Proto!MultiSelect, added new features/bug fixes
 *        added feature: support to fetch list on-the-fly using AJAX - credit: Cheeseroll
 *        added feature: support for value/caption
 *        added feature: maximum results to display, when greater displays a scrollbar - credit: Marcel
 *        added feature: filter by the beginning of word only or everywhere in the word - credit: Kiliman
 *        added feature: shows hand cursor when going over options
 *        bug fix: the click event stopped working
 *        bug fix: the cursor does not 'travel' when going up/down the list - credit: Marcel
 *
 * Adaptation to zikula:
 * - Changed CSS classes to use the 'z-auto' prefix
 * - Added the 'parameters' option for the Ajax call
 * - Merged FacebookList.loptions.autocomplete into this.options
 * - Added a limit for selected items - options.maxItems
 * - Important note: Ajax response must retrieve a "data" field with {value, caption} (in that order)
 *
 * Copyright: InteRiders <http://interiders.com/> - Distributed under MIT - Keep this message!
 */

/* Element helper functions */
Element.addMethods({
  getCaretPosition: function() {
    if (this.createTextRange) {
      var r = document.selection.createRange().duplicate();
      r.moveEnd('character', this.value.length);
      if (r.text === '') {
        return this.value.length;
      }
      return this.value.lastIndexOf(r.text);
    } else {
      return this.selectionStart;
    }
  },

  cacheData: function(element, key, value) {
    if (Object.isUndefined(this['zkautocompleter'])) {
        this['zkautocompleter'] = {};
    }
    if (Object.isUndefined(this['zkautocompleter'][$(element).identify()]) || !Object.isHash(this['zkautocompleter'][$(element).identify()])) {
      this['zkautocompleter'][$(element).identify()] = $H();
    }
    this['zkautocompleter'][$(element).identify()].set(key, value);
    return element;
  },

  retrieveData: function(element, key) {
    return this['zkautocompleter'][$(element).identify()].get(key);
  }
});

/* ResizableTextbox */
var ResizableTextbox = Class.create(
{
  initialize: function(element, options) {
    this.options = $H({
      min: 5,
      max: 300,
      step: 7
    });
    this.options.update(options);

    var that = this;
    this.el = $(element);
    this.width = this.el.offsetWidth;
    this.el
      .observe('keyup', function() {
        var newsize = that.options.get('step') * $F(this).length;
        if (newsize <= that.options.get('min')) {
          newsize = that.width;
        }
        if (!($F(this).length == this.retrieveData('rt-value') || newsize <= that.options.min || newsize >= that.options.max)) {
          this.setStyle({'width': newsize});
        }
      })
      .observe('keydown', function() {
        this.cacheData('rt-value', $F(this).length);
      });
  }
});

/* TextboxList */
var TextboxList = Class.create(
{
  initialize: function(element, options) {
    this.options = $H({/*
      autoopacity: 0.8,
      maxresults: 10,
      minchars: 3,*/
      resizable: {},
      className: 'bit',
      separator: ':',
      extrainputs: true,
      startinput: true,
      hideempty: true,
      spaceReplace: '',
      wordMatch: true,
      fetchFile: undefined,
      fetchMethod: 'post',
      parameters: {},
      results: 10,
      maxItems: 10
    });
    this.options.update(options);

    this.id_base = $(element).identify() + '-';
    this.element = $(element).hide();
    this.bits = new Hash();
    this.events = new Hash();
    this.count = 0;
    this.numitems = 0;
    this.current = false;

    this.maininput = this.createInput({'class': 'z-auto-maininput'});
    this.holder = new Element('ul', {
      'class': 'z-auto-holder'
    }).insert(this.maininput);
    this.element.insert({'before': this.holder});
    this.holder.observe('click', function(event) {
      event.stop();
      if (this.maininput != this.current) this.focus(this.maininput);
    }.bind(this));

    this.makeResizable(this.maininput);
    this.setEvents();
  },

  makeResizable: function(li) {
    var el = li.retrieveData('input');
    el.cacheData('resizable', new ResizableTextbox(el, Object.extend(this.options.get('resizable'), {min: el.offsetWidth, max: (this.element.getWidth() ? this.element.getWidth() : 0)})));
    return this;
  },

  setEvents: function() {
    document.observe(Prototype.Browser.IE ? 'keydown' : 'keypress', function(e) {
      if (!this.current) {
        return;
      }
      if (this.current.retrieveData('type') == 'box' && e.keyCode == Event.KEY_BACKSPACE) {
        e.stop();
      }
    }.bind(this));

    document.observe('keyup', function(e) {
        e.stop();
        if (!this.current) {
          return this;
        }
        switch (e.keyCode) {
          case Event.KEY_LEFT: return this.move('left');
          case Event.KEY_RIGHT: return this.move('right');
          case Event.KEY_DELETE:
          case Event.KEY_BACKSPACE: return this.moveDispose();
        }
      }.bind(this)).observe('click', function() { document.fire('blur'); }.bindAsEventListener(this)
    );
  },

  update: function() {
    this.element.value = this.bits.values().join(this.options.get('separator'));
    return this;
  },

  add: function(text, html) {
    var id = this.id_base + this.options.get('className') + '-' + this.count++;
    var el = this.createBox($pick(html, text), {'id': id});
    (this.current || this.maininput).insert({'before': el});
    el.observe('click', function(e) {
      e.stop();
      this.focus(el);
    }.bind(this));
    this.bits.set(id, text.value);
    if (this.options.get('extrainputs') && (this.options.get('startinput') || el.previous())) {
      this.addSmallInput(el, 'before');
    }
    // dynamic update
    this.update();
    // hides the maininput if reach the items limit
    this.numitems++;
    if (this.numitems == this.options.get('maxItems')) {
      this.maininput.hide();
    }
    return el;
  },

  addSmallInput: function(el, where) {
    var input = this.createInput({'class': 'z-auto-smallinput'});
    el.insert({}[where] = input);
    input.cacheData('small', true);
    this.makeResizable(input);
    if (this.options.get('hideempty')) {
      input.hide();
    }
    return input;
  },

  dispose: function(el) {
    this.bits.unset(el.id);
    if (el.previous() && el.previous().retrieveData('small')){
      el.previous().remove();
    }
    if (this.current == el) {
      this.focus(el.next());
    }
    if (el.retrieveData('type') == 'box') {
      el.onBoxDispose(this);
    }
    el.remove();
    // dynamic update
    this.update();
    // updates the item counter
    this.numitems--;
    this.maininput.show();
    this.focus(this.maininput);
    return this;
  },

  focus: function(el, nofocus) {
    if (!this.current) {
      el.fire('focus');
    } else if (this.current == el) {
      return this;
    }
    this.blur();
    el.addClassName('z-auto-' + this.options.get('className') + '-' + el.retrieveData('type') + '-focus');
    if (el.retrieveData('small')) {
      el.setStyle({'display': 'block'});
    }
    if (el.retrieveData('type') == 'input') {
      el.onInputFocus(this);
      if (!nofocus) {
        this.callEvent(el.retrieveData('input'), 'focus');
      }
    } else {
      el.fire('onBoxFocus');
    }
    this.current = el;
    return this;
  },

  blur: function(noblur) {
    if (!this.current) {
      return this;
    }
    if (this.current.retrieveData('type') == 'input') {
      var input = this.current.retrieveData('input');
      if (!noblur) {
        this.callEvent(input, 'blur');
      }
      input.onInputBlur(this);
    } else {
      this.current.fire('onBoxBlur');
    }
    if (this.current.retrieveData('small') && ! input.get('value') && this.options.get('hideempty')) {
      this.current.hide();
    }
    this.current.removeClassName('z-auto-' + this.options.get('className') + '-' + this.current.retrieveData('type') + '-focus');
    this.current = false;
    return this;
  },

  createBox: function(text, options) {
    return new Element('li', options).addClassName('z-auto-' + this.options.get('className') + '-box')
                                     .update(text.caption)
                                     .cacheData('type', 'box');
  },

  createInput: function(options) {
    var li = new Element('li', {'class': 'z-auto-' + this.options.get('className') + '-input'});
    var el = new Element('input', Object.extend(options, {'type': 'text', 'autocomplete': 'off'}));

    el.observe('click', function(e) { e.stop(); })
      .observe('focus', function(e) { if (!this.isSelfEvent('focus')) this.focus(li, true); }.bind(this))
      .observe('blur', function() { if(! this.isSelfEvent('blur')) this.blur(true); }.bind(this))
      .observe('keydown', function(e) { this.cacheData('lastvalue', this.value).cacheData('lastcaret', this.getCaretPosition()); });

    var tmp = li.cacheData('type', 'input')
                .cacheData('input', el)
                .insert(el);
    return tmp;
  },

  callEvent: function(el, type) {
    this.events.set(type, el);
    el[type]();
  },

  isSelfEvent: function(type) {
    return (this.events.get(type)) ? !!this.events.unset(type) : false;
  },

  checkInput: function() {
    var input = this.current.retrieveData('input');
    return (!input.retrieveData('lastvalue') || (input.getCaretPosition() === 0 && input.retrieveData('lastcaret') === 0));
  },

  move: function(direction) {
    var el = this.current[(direction == 'left' ? 'previous' : 'next')]();
    if (el && (!this.current.retrieveData('input') || ((this.checkInput() || direction == 'right')))) {
      this.focus(el);
    }
    return this;
  },

  moveDispose: function() {
    if (this.current.retrieveData('type') == 'box') {
      return this.dispose(this.current);
    }
    if (this.checkInput() && this.bits.keys().length && this.current.previous()) {
      return this.focus(this.current.previous());
    }
  }
});

function $pick(){for(var B=0,A=arguments.length;B<A;B++){if(!Object.isUndefined(arguments[B])){return arguments[B];}}return null;}

/* FacebookList */
var FacebookList = Class.create(TextboxList,
{
  initialize: function($super, element, autoholder, options) {
    $super(element, options);
    this.options.update($H({
      'autoopacity': 1.0,
      'maxresults': 10,
      'minchars': 3
    }));
    this.options.update(options);

    this.data = [];
    this.searchcache = [];

    this.autoholder = $(autoholder).setOpacity(this.options.get('autoopacity'));
    this.autoholder.observe('mouseover', function() { this.curOn = true; }.bind(this))
                   .observe('mouseout', function() { this.curOn = false; }.bind(this));
    this.autoresults = this.autoholder.select('ul').first();

    var children = this.autoresults.select('li');
    children.each(function(el) { this.add({value: el.readAttribute('value'), caption: el.innerHTML}); }, this);
  },

  autoShow: function(search) {
    if (this.numitems == this.options.get('maxItems')) {
      return this;
    }
    this.autoholder.setStyle({'display': 'block'});
    this.autoholder.descendants().each(function(e) { e.hide() });
    if (!search || !search.strip() || (!search.length || search.length < this.options.get('minchars')))
    {
      this.autoholder.select('.z-auto-default').first().setStyle({'display': 'block'});
      this.resultsshown = false;
    } else {
      this.resultsshown = true;
      this.autoresults.setStyle({'display': 'block'}).update('');
      var regexp = null;
      regexp = new RegExp(search, 'i')
      // TODO take in account the OP filter here
      /*if (this.options.get('wordMatch')) {
        regexp = new RegExp("(^|\\s)"+search, 'i')
      } else {
        regexp = new RegExp(search, 'i')
      }*/
      var count = 0;
      this.data.filter(function(str) { return str ? regexp.test(str.evalJSON(true).caption) : false; })
               .each(
                     function(result, ti)
                     {
                       count++;
                       if (ti >= this.options.get('maxresults')) {
                         return;
                       }
                       var that = this;
                       var el = new Element('li');
                       el.observe('click', function(e) {
                         e.stop();
                         that.autoAdd(this);
                       }).observe('mouseover', function() {
                         that.autoFocus(this);
                       }).update(this.autoHighlight(result.evalJSON(true).caption, search));
                       this.autoresults.insert(el);
                       el.cacheData('result', result.evalJSON(true));
                       if (ti == 0) {
                         this.autoFocus(el);
                       }
                     }, this);
    }
    // adjust the height of the results list
    if (count > this.options.get('results')) {
      this.autoresults.setStyle({'height': (this.options.get('results')*24)+'px'});
    } else {
      this.autoresults.setStyle({'height': (count ? (count*24) : 0)+'px'});
    }
    // check this.autoresults count to see if we need the 'not found' message
    if (count == 0) {
        this.autoholder.select('.z-auto-default').first().hide();
        this.autoholder.select('.z-auto-notfound').first().setStyle({'display': 'block'});
    }
    return this;
  },

  autoHighlight: function(html, highlight) {
    return html.gsub(new RegExp(highlight, 'i'),
      function(match) {
        return '<em>' + match[0] + '</em>';
      }
    );
  },

  autoHide: function() {
    this.resultsshown = false;
    this.autoholder.hide();
    return this;
  },

  autoFocus: function(el) {
    if (!el) {
      return this;
    }
    if (this.autocurrent) {
      this.autocurrent.removeClassName('z-auto-focus');
    }
    this.autocurrent = el.addClassName('z-auto-focus');
    return this;
  },

  autoMove: function(direction) {
    if (!this.resultsshown) {
      return this;
    }
    this.autoFocus(this.autocurrent[(direction == 'up' ? 'previous' : 'next')]());
    this.autoresults.scrollTop = this.autocurrent.positionedOffset()[1] - this.autocurrent.getHeight();
    return this;
  },

  autoFeed: function(text) {
    /* do not feed existing values */
    if (this.bits.values().indexOf(text.value) == -1 && this.data.indexOf(Object.toJSON(text)) == -1) {
      this.data.push(Object.toJSON(text));
    }
    return this;
  },

  autoAdd: function(el) {
    if (!el || ! el.retrieveData('result')) {
      return this;
    }
    this.add(el.retrieveData('result'));
    delete this.data[this.data.indexOf(Object.toJSON(el.retrieveData('result')))];
    this.data = this.data.filter(function(val) { return !Object.isUndefined(val) ? val : false; })
    var input = this.lastinput || this.current.retrieveData('input');
    this.autoHide();
    input.clear().focus();
    return this;
  },

  createInput: function($super, options) {
    var li = $super(options);
    var input = li.retrieveData('input');

    input.observe('keydown', function(e) {
      this.dosearch = false;

      switch(e.keyCode) {
        case Event.KEY_UP: e.stop(); return this.autoMove('up');
        case Event.KEY_DOWN: e.stop(); return this.autoMove('down');
        case 188: // Comma
          var el = this.current.retrieveData('input');
          el.value = el.value.strip().gsub(',', '');
          if (!this.options.get('spaceReplace').blank()) {
              el.value.gsub(' ', this.options.get('spaceReplace'));
          }
          if (!el.value.blank()) {
            e.stop();
            this.autoAdd(el);
          }
          break;
        case Event.KEY_RETURN:
          e.stop();
          if (!this.autocurrent) {
            break;
          }
          this.autoAdd(this.autocurrent);
          this.autocurrent = false;
          this.autoenter = true;
          break;
        case Event.KEY_ESC:
          this.autoHide();
          if (this.current && this.current.retrieveData('input')) {
            this.current.retrieveData('input').clear();
          }
          break;
        default: this.dosearch = true;
      }
    }.bind(this));

    input.observe('keyup', function(e) {
      switch (e.keyCode) {
        case Event.KEY_UP:
        case Event.KEY_DOWN:
        case Event.KEY_RETURN:
        case Event.KEY_ESC:
        case 16: // Shift
          break;
        default:
          if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
          }
          if (this.searchcache.indexOf(input.value) != -1) {
              if (this.dosearch) {
                  this.autoShow(input.value);
              }
              break;
          }
          this.searchTimeout = setTimeout(function() {
            if (!Object.isUndefined(this.options.get('fetchFile')) && input.value.length >= this.options.get('minchars')) {
              var params = this.options.get('parameters');
              params.keyword = input.value;
              this.maininput.insert({before: Zikula.Autocompleter.Indicator()});
              new Zikula.Ajax.Request(this.options.get('fetchFile'), {
                method: this.options.get('fetchMethod'),
                parameters: params,
                onComplete: function(response) {
                  Zikula.Autocompleter.Indicator().remove();
                  var data = response.getData();
                  data.each(function(t){this.autoFeed(t)}.bind(this));
                  this.autoShow(input.value);
                }.bind(this)
              });
            } else if (this.dosearch) {
              this.autoShow(input.value);
            }
            this.searchcache.push(input.value);
            clearTimeout(this.searchTimeout);
          }.bind(this), 500);
      }
    }.bind(this));
    input.observe(Prototype.Browser.IE ? 'keydown' : 'keypress', function(e) {
      if (this.autoenter) {
        e.stop();
      }
      this.autoenter = false;
    }.bind(this));
    return li;
  },

  createBox: function($super, text, options) {
    var li = $super(text, options);
    li.observe('mouseover', function() {
      this.addClassName('z-auto-bit-hover');
    }).observe('mouseout', function() {
      this.removeClassName('z-auto-bit-hover')
    });
    var a = new Element('a', {
      'href': '#',
      'class': 'z-auto-closebutton'
      }
    );
    a.observe('click', function(e) {
      e.stop();
      if (!this.current) {
        this.focus(this.maininput);
      }
      this.dispose(li);
    }.bind(this));
    li.insert(a).cacheData('text', Object.toJSON(text));
    return li;
  }
});

Element.addMethods({
  onBoxDispose: function(item, obj) { obj.autoFeed(item.retrieveData('text').evalJSON(true)); },
  onInputFocus: function(el, obj) { obj.autoShow(); },
  onInputBlur: function(el, obj) {
    obj.lastinput = el;
    if (!obj.curOn) {
      obj.blurhide = obj.autoHide.bind(obj).delay(0.1);
    }
  },
  filter:function(D,E){var C=[];for(var B=0,A=this.length;B<A;B++){if(D.call(E,this[B],B,this)){C.push(this[B]);}}return C;}
});

/**
 * Zikula.Autocompleter rename
 *
 * TODO: Implement empty result multilanguage message
 */
Zikula.Autocompleter = Class.create(FacebookList);

Zikula.Autocompleter.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img', {id: 'ajax_indicator', src: Zikula.Config.baseURL + 'images/ajax/indicator_circle.gif', style: 'float: left'});
};
