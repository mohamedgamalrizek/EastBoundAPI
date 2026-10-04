/* ============================================================
   FLOW — frontend engine
   Sticky header · mobile drawer · search overlay · search tabs
   · scroll-to-top · AOS · Swiper · counters
   Depends: jQuery, Bootstrap 5, AOS, Swiper (all via CDN)
   ============================================================ */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Light / dark theme ---------- */
    var themeToggle = document.getElementById('themeToggle');
    var themeMeta = document.querySelector('meta[name="theme-color"]');
    var applyTheme = function (theme, persist) {
      var isDark = theme === 'dark';
      document.documentElement.setAttribute('data-theme', theme);
      document.documentElement.setAttribute('data-bs-theme', theme);

      if (themeMeta) themeMeta.setAttribute('content', isDark ? '#0B1220' : '#4DA6FF');

      if (themeToggle) {
        var label = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        themeToggle.setAttribute('aria-label', label);
        themeToggle.setAttribute('aria-pressed', String(isDark));
        themeToggle.setAttribute('title', label);
        themeToggle.innerHTML = isDark
          ? '<i class="fa-solid fa-sun" aria-hidden="true"></i>'
          : '<i class="fa-solid fa-moon" aria-hidden="true"></i>';
      }

      if (persist) {
        try { localStorage.setItem('flow-theme', theme); } catch (e) {}
      }
    };

    var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(currentTheme, false);

    if (themeToggle) {
      themeToggle.addEventListener('click', function () {
        var nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme, true);
      });
    }

    /* ---------- Sticky header shadow ---------- */
    var header = document.getElementById('siteHeader');
    var onScroll = function () {
      if (header) header.classList.toggle('is-stuck', window.scrollY > 8);
      var st = document.getElementById('scrollTop');
      if (st) st.classList.toggle('show', window.scrollY > 400);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- Scroll to top ---------- */
    var scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
      scrollTopBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    /* ---------- Mobile drawer ---------- */
    var drawer   = document.getElementById('mobileDrawer');
    var backdrop = document.getElementById('drawerBackdrop');
    var openDrawer  = function () { drawer && drawer.classList.add('open'); backdrop && backdrop.classList.add('open'); document.body.style.overflow = 'hidden'; };
    var closeDrawer = function () { drawer && drawer.classList.remove('open'); backdrop && backdrop.classList.remove('open'); document.body.style.overflow = ''; };

    var navToggler  = document.getElementById('navToggler');
    var drawerClose = document.getElementById('drawerClose');
    navToggler  && navToggler.addEventListener('click', openDrawer);
    drawerClose && drawerClose.addEventListener('click', closeDrawer);
    backdrop    && backdrop.addEventListener('click', closeDrawer);

    /* ---------- Drawer collapsible sub-menus ---------- */
    document.querySelectorAll('[data-collapse]').forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var target = document.querySelector(trigger.getAttribute('data-collapse'));
        if (target) target.hidden = !target.hidden;
        var icon = trigger.querySelector('.fa-chevron-down');
        if (icon) icon.style.transform = target && !target.hidden ? 'rotate(180deg)' : '';
      });
    });

    /* ---------- Search overlay ---------- */
    var overlay     = document.getElementById('searchOverlay');
    var searchOpen  = document.getElementById('searchOpen');
    var searchClose = document.getElementById('searchClose');
    var openSearch  = function () {
      if (!overlay) return;
      overlay.classList.add('open');
      var input = overlay.querySelector('.search-input');
      setTimeout(function () { input && input.focus(); }, 80);
    };
    var closeSearch = function () { overlay && overlay.classList.remove('open'); };
    searchOpen  && searchOpen.addEventListener('click', openSearch);
    searchClose && searchClose.addEventListener('click', closeSearch);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { closeSearch(); closeDrawer(); }
    });

    /* ---------- Searchable selects (select2) ---------- */
    // Opt-in via a `.select2` class on the <select> — the native select still
    // submits normally, this only replaces how it's picked. Long lists
    // (nationality) is what this is actually for; short ones (status/sort)
    // don't carry the class and stay plain.
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
      });
    }

    /* ---------- Hero search tabs ---------- */
    document.querySelectorAll('.search-widget').forEach(function (widget) {
      var tabs   = widget.querySelectorAll('.stab');
      var panels = widget.querySelectorAll('.search-panel');
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.classList.remove('active'); });
          panels.forEach(function (p) { p.classList.remove('active'); });
          tab.classList.add('active');
          var panel = widget.querySelector('#' + tab.getAttribute('data-target'));
          if (panel) panel.classList.add('active');
        });
      });
    });

    /* ---------- Flight fare estimate (From/To selects → published route fare) ---------- */
    // Read-only hint, not a locked price: the form still only opens a lead,
    // and the real fare is whatever staff quote once a ticket is booked.
    document.querySelectorAll('[data-flight-fare-form]').forEach(function (form) {
      var dataEl = form.querySelector('[data-flight-fares]');
      var fares = [];
      try { fares = JSON.parse(dataEl ? dataEl.textContent : '[]'); } catch (e) {}

      var fromEl = form.querySelector('select[name="from"]');
      var toEl   = form.querySelector('select[name="to"]');
      var hintEl = form.querySelector('[data-flight-fare-hint]');
      if (!fromEl || !toEl || !hintEl) return;

      var currency = hintEl.getAttribute('data-currency') || '';
      var noFareText = hintEl.getAttribute('data-no-fare-text') || "We'll quote you a fare.";
      var fareEstimateFormat = hintEl.getAttribute('data-fare-estimate-format') || 'From :amount (estimate)';

      var updateHint = function () {
        var from = fromEl.value, to = toEl.value;
        if (!from || !to) { hintEl.hidden = true; hintEl.textContent = ''; return; }

        var matches = fares.filter(function (r) {
          return (r.from === from && r.to === to) || (r.from === to && r.to === from);
        });

        if (!matches.length) {
          hintEl.textContent = noFareText;
        } else {
          var min = matches.reduce(function (m, r) { return r.fare < m ? r.fare : m; }, matches[0].fare);
          hintEl.textContent = fareEstimateFormat.replace(':amount', currency + Math.round(min).toLocaleString());
        }
        hintEl.hidden = false;
      };

      fromEl.addEventListener('change', updateHint);
      toEl.addEventListener('change', updateHint);
      updateHint();
    });

    /* ---------- Visa fee estimate (Country + Visa Type → published fee) ---------- */
    // Same caveat as the flight fare: a hint, not a locked price. The form
    // still only opens a case; the real fee is quoted from this same
    // catalogue when the case is actually created.
    document.querySelectorAll('[data-visa-fee-form]').forEach(function (form) {
      var dataEl = form.querySelector('[data-visa-fees]');
      var fees = [];
      try { fees = JSON.parse(dataEl ? dataEl.textContent : '[]'); } catch (e) {}

      var countryEl = form.querySelector('select[name="country"]');
      var typeEl    = form.querySelector('select[name="visa_type"]');
      var hintEl    = form.querySelector('[data-visa-fee-hint]');
      if (!countryEl || !typeEl || !hintEl) return;

      var currency = hintEl.getAttribute('data-currency') || '';
      var noFeeText = hintEl.getAttribute('data-no-fee-text') || "We'll quote you a fee.";
      var feeEstimateFormat = hintEl.getAttribute('data-fee-estimate-format') || 'Estimated fee: :amount (govt + service)';

      var updateHint = function () {
        var country = countryEl.value, type = typeEl.value;
        if (!country || !type) { hintEl.hidden = true; hintEl.textContent = ''; return; }

        var match = fees.find(function (r) { return r.country === country && r.visa_type === type; });

        hintEl.textContent = match
          ? feeEstimateFormat.replace(':amount', currency + Math.round(match.fee).toLocaleString())
          : noFeeText;
        hintEl.hidden = false;
      };

      countryEl.addEventListener('change', updateHint);
      typeEl.addEventListener('change', updateHint);
      updateHint();
    });

    /* ---------- Date pickers (flatpickr) ---------- */
    // Replaces native date inputs with a themed calendar; the hidden original
    // keeps submitting Y-m-d while the visible field shows a friendly format.
    // Mobile keeps the native picker (flatpickr's default behaviour).
    if (window.flatpickr) {
      document.querySelectorAll('input[type="date"]').forEach(function (el) {
        window.flatpickr(el, {
          altInput: true,
          altFormat: 'D, j M Y',
          dateFormat: 'Y-m-d',
          minDate: el.min || 'today',
          maxDate: el.max || null,
          onReady: function (dates, str, inst) {
            if (inst.altInput) {
              inst.altInput.placeholder = el.getAttribute('placeholder') || '';
            }
          }
        });
      });
    }

    /* ---------- Booking total ---------- */
    // The package price is per traveller and the server bills it that way, so
    // the enquiry form shows the multiplied figure while the count is edited.
    var travelersInput = document.getElementById('bookTravelers');
    var totalOutput = document.getElementById('bookTotal');

    if (travelersInput && totalOutput) {
      var unitPrice = parseFloat(travelersInput.getAttribute('data-unit-price')) || 0;
      var totalLabel = document.getElementById('bookTotalLabel');

      var renderTotal = function () {
        var count = Math.max(1, parseInt(travelersInput.value, 10) || 1);
        totalOutput.textContent = '৳' + (unitPrice * count).toLocaleString('en-US');
        if (totalLabel) {
          totalLabel.textContent = count + ' × ৳' + unitPrice.toLocaleString('en-US');
        }
      };

      travelersInput.addEventListener('input', renderTotal);
      travelersInput.addEventListener('change', renderTotal);
      renderTotal();
    }

    /* ---------- Counters (animate on view) ---------- */
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var target = parseFloat(el.getAttribute('data-count'));
          var suffix = el.getAttribute('data-suffix') || '';
          var dur = 1600, start = null;
          var step = function (ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var val = Math.floor(p * target);
            el.textContent = val.toLocaleString() + suffix;
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString() + suffix;
          };
          requestAnimationFrame(step);
          io.unobserve(el);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (c) { io.observe(c); });
    }

    /* ---------- AOS ---------- */
    if (window.AOS) {
      AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 80 });
    }

    /* ---------- Direction-aware Swiper lifecycle ---------- */
    if (window.Swiper) {
      var swiperRegistry = new Map();
      var swiperRefreshFrame = 0;
      var activePageDirection = '';
      var swiperSelector = '[data-swiper], .swiper, .swiper-initialized';

      var getPageDirection = function () {
        var bodyDirection = document.body && document.body.getAttribute('dir');
        var htmlDirection = document.documentElement.getAttribute('dir');
        return String(bodyDirection || htmlDirection || getComputedStyle(document.documentElement).direction || 'ltr')
          .toLowerCase() === 'rtl' ? 'rtl' : 'ltr';
      };

      var isPlainObject = function (value) {
        if (!value || Object.prototype.toString.call(value) !== '[object Object]') return false;
        var prototype = Object.getPrototypeOf(value);
        return prototype === Object.prototype || prototype === null;
      };

      // Clone only arrays and plain option objects. Elements, functions,
      // Swiper modules and other host objects must retain their identity.
      var cloneOptions = function (value, seen) {
        if (!value || typeof value !== 'object') return value;
        if (!Array.isArray(value) && !isPlainObject(value)) return value;

        seen = seen || new WeakMap();
        if (seen.has(value)) return seen.get(value);

        var copy = Array.isArray(value) ? [] : {};
        seen.set(value, copy);

        Object.keys(value).forEach(function (key) {
          copy[key] = cloneOptions(value[key], seen);
        });

        return copy;
      };

      var readSwiperOptions = function (node) {
        var options = {};
        try {
          options = JSON.parse(node.getAttribute('data-swiper') || '{}');
        } catch (e) {
          options = {};
        }

        return Object.assign({
          slidesPerView: 1,
          spaceBetween: 24,
          loop: true,
          autoplay: { delay: 4500, disableOnInteraction: false },
          pagination: {
            el: node.querySelector('.swiper-pagination'),
            clickable: true
          },
          navigation: {
            nextEl: node.querySelector('.swiper-button-next'),
            prevEl: node.querySelector('.swiper-button-prev')
          }
        }, options);
      };

      var captureSwiperState = function (instance) {
        return {
          realIndex: Number.isFinite(instance.realIndex) ? instance.realIndex : 0,
          autoplayRunning: !!(instance.autoplay && instance.autoplay.running),
          autoplayPaused: !!(instance.autoplay && instance.autoplay.paused)
        };
      };

      var restoreSwiperState = function (instance, state) {
        if (!instance || instance.destroyed || !state) return;

        if (instance.params.loop && typeof instance.slideToLoop === 'function') {
          instance.slideToLoop(state.realIndex, 0, false);
        } else if (typeof instance.slideTo === 'function') {
          instance.slideTo(state.realIndex, 0, false);
        }

        if (!instance.autoplay) return;
        if (!state.autoplayRunning && typeof instance.autoplay.stop === 'function') {
          instance.autoplay.stop();
        } else if (state.autoplayPaused && typeof instance.autoplay.pause === 'function') {
          instance.autoplay.pause();
        }
      };

      var registerExistingSwiper = function (node) {
        var instance = node && node.swiper;
        if (!instance || instance.destroyed || swiperRegistry.has(node)) return;

        swiperRegistry.set(node, {
          node: node,
          instance: instance,
          options: cloneOptions(instance.passedParams || instance.originalParams || instance.params || {}),
          managed: false
        });
      };

      var createSwiper = function (record, options, state) {
        var node = record.node;
        if (!node || !node.isConnected) return null;

        node.setAttribute('dir', activePageDirection);
        var instance = new window.Swiper(node, options);
        record.instance = instance;
        restoreSwiperState(instance, state);
        return instance;
      };

      var initializeSwiper = function (node) {
        if (!node || !node.isConnected || swiperRegistry.has(node)) return;

        if (node.swiper && !node.swiper.destroyed) {
          registerExistingSwiper(node);
          return;
        }

        if (!node.hasAttribute('data-swiper')) return;

        var record = {
          node: node,
          instance: null,
          options: readSwiperOptions(node),
          managed: true
        };

        swiperRegistry.set(node, record);
        createSwiper(record, cloneOptions(record.options), null);
      };

      var discoverSwipers = function (root) {
        if (!root || root.nodeType !== 1) return;

        if (root.matches(swiperSelector)) {
          initializeSwiper(root);
        }

        root.querySelectorAll(swiperSelector).forEach(function (node) {
          initializeSwiper(node);
        });
      };

      var removeDetachedSwipers = function () {
        swiperRegistry.forEach(function (record, node) {
          if (node.isConnected) return;
          if (record.instance && !record.instance.destroyed) {
            record.instance.destroy(true, true);
          }
          swiperRegistry.delete(node);
        });
      };

      var collectSwiperDependencies = function (value, oldInstances, dependencies, seen) {
        if (!value || typeof value !== 'object') return;
        if (oldInstances.has(value)) {
          dependencies.add(value);
          return;
        }
        if (!Array.isArray(value) && !isPlainObject(value)) return;

        seen = seen || new WeakSet();
        if (seen.has(value)) return;
        seen.add(value);

        Object.keys(value).forEach(function (key) {
          collectSwiperDependencies(value[key], oldInstances, dependencies, seen);
        });
      };

      var replaceSwiperReferences = function (value, replacements, oldInstances, seen) {
        if (!value || typeof value !== 'object') return value;
        if (oldInstances.has(value)) {
          return replacements.has(value) ? replacements.get(value) : undefined;
        }
        if (!Array.isArray(value) && !isPlainObject(value)) return value;

        seen = seen || new WeakMap();
        if (seen.has(value)) return seen.get(value);

        var copy = Array.isArray(value) ? [] : {};
        seen.set(value, copy);

        Object.keys(value).forEach(function (key) {
          var replacement = replaceSwiperReferences(value[key], replacements, oldInstances, seen);
          if (replacement !== undefined) copy[key] = replacement;
        });

        return copy;
      };

      var reinitializeAllSwipers = function () {
        removeDetachedSwipers();
        discoverSwipers(document.body);

        var records = Array.from(swiperRegistry.values()).filter(function (record) {
          return record.node.isConnected && record.instance && !record.instance.destroyed;
        });
        var oldInstances = new Set(records.map(function (record) { return record.instance; }));
        var oldToNew = new Map();

        records.forEach(function (record) {
          record.state = captureSwiperState(record.instance);
          record.reinitOptions = cloneOptions(record.options);
          record.dependencies = new Set();
          collectSwiperDependencies(record.reinitOptions, oldInstances, record.dependencies);
        });

        // Destruction happens for every instance before any replacement is made.
        records.forEach(function (record) {
          if (record.instance && !record.instance.destroyed) {
            record.instance.destroy(true, true);
          }
          record.oldInstance = record.instance;
          record.instance = null;
        });

        // Thumbs and controller dependencies are recreated before their owners.
        var pending = records.slice();
        while (pending.length) {
          var readyIndex = pending.findIndex(function (record) {
            return Array.from(record.dependencies).every(function (dependency) {
              return oldToNew.has(dependency);
            });
          });

          // Cyclic controller relationships cannot be topologically sorted.
          // Initialize one side first, then reconnect references below.
          if (readyIndex < 0) readyIndex = 0;

          var record = pending.splice(readyIndex, 1)[0];
          var options = replaceSwiperReferences(
            record.reinitOptions,
            oldToNew,
            oldInstances
          );
          var replacement = createSwiper(record, options, record.state);
          if (replacement) oldToNew.set(record.oldInstance, replacement);
        }

        // Restore cyclic controller references after every replacement exists.
        records.forEach(function (record) {
          if (!record.instance || record.instance.destroyed) return;
          var originalController = record.reinitOptions && record.reinitOptions.controller;
          if (!originalController || !originalController.control || !record.instance.controller) return;

          var restoredControl = replaceSwiperReferences(
            originalController.control,
            oldToNew,
            oldInstances
          );
          record.instance.controller.control = restoredControl;
          if (record.instance.params.controller) {
            record.instance.params.controller.control = restoredControl;
          }
          if (record.instance.passedParams && record.instance.passedParams.controller) {
            record.instance.passedParams.controller.control = restoredControl;
          }
        });

        // Persist the newly materialized configuration so repeated direction
        // changes never retain references to an already destroyed instance.
        records.forEach(function (record) {
          if (!record.instance || record.instance.destroyed) return;
          record.options = cloneOptions(
            record.instance.passedParams
            || record.instance.originalParams
            || record.reinitOptions
          );
          delete record.state;
          delete record.reinitOptions;
          delete record.dependencies;
          delete record.oldInstance;
        });
      };

      var refreshDirection = function () {
        var nextDirection = getPageDirection();
        if (nextDirection === activePageDirection) return;
        activePageDirection = nextDirection;
        reinitializeAllSwipers();
      };

      var scheduleSwiperRefresh = function (directionMayHaveChanged) {
        if (swiperRefreshFrame) cancelAnimationFrame(swiperRefreshFrame);
        swiperRefreshFrame = requestAnimationFrame(function () {
          swiperRefreshFrame = 0;

          if (directionMayHaveChanged && getPageDirection() !== activePageDirection) {
            refreshDirection();
            return;
          }

          removeDetachedSwipers();
          discoverSwipers(document.body);
        });
      };

      activePageDirection = getPageDirection();
      discoverSwipers(document.body);

      var swiperObserver = new MutationObserver(function (mutations) {
        var directionMayHaveChanged = mutations.some(function (mutation) {
          return mutation.type === 'attributes'
            && mutation.attributeName === 'dir'
            && (mutation.target === document.body || mutation.target === document.documentElement);
        });

        var swiperOptionsChanged = mutations.some(function (mutation) {
          return mutation.type === 'attributes'
            && mutation.attributeName === 'data-swiper'
            && mutation.target.matches('[data-swiper]');
        });
        var externalSwiperAppeared = mutations.some(function (mutation) {
          return mutation.type === 'attributes'
            && mutation.attributeName === 'class'
            && mutation.target.swiper
            && !mutation.target.swiper.destroyed
            && !swiperRegistry.has(mutation.target);
        });
        var swiperDomChanged = mutations.some(function (mutation) {
          return mutation.type === 'childList';
        });

        if (swiperOptionsChanged) {
          mutations.forEach(function (mutation) {
            if (mutation.type !== 'attributes' || mutation.attributeName !== 'data-swiper') return;
            var node = mutation.target;
            var record = swiperRegistry.get(node);
            if (record && record.instance && !record.instance.destroyed) {
              record.instance.destroy(true, true);
            }
            swiperRegistry.delete(node);
          });
        }

        if (directionMayHaveChanged || swiperOptionsChanged || externalSwiperAppeared || swiperDomChanged) {
          scheduleSwiperRefresh(directionMayHaveChanged);
        }
      });

      swiperObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['dir', 'data-swiper', 'class'],
        childList: true,
        subtree: true
      });

      // Allows AJAX/page-fragment code to request discovery without creating
      // another instance for nodes that are already managed.
      window.FLOWSwiper = Object.assign(window.FLOWSwiper || {}, {
        refresh: function () {
          scheduleSwiperRefresh(true);
        },
        reinitialize: function () {
          activePageDirection = getPageDirection();
          reinitializeAllSwipers();
        }
      });
    }

  });
})();
