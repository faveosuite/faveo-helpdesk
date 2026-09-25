/*
 * Faveo submit guard — one shared place that stops duplicate form submissions.
 *
 * Loaded once by every layout (see themes/default1/common/submit-guard.blade.php).
 * It needs no per-form code and does not replace any existing handler: it only
 * watches what the page's own code does with a click / submit and reacts.
 *
 *  1. Normal HTML submit (POST, same window)
 *       After every other submit handler has run, if nobody cancelled the event the
 *       form is really leaving the page: its submit buttons are disabled and the
 *       clicked one shows the loading label. A cancelled event (client-side
 *       validation failed, `return false`, confirm() declined) locks nothing.
 *
 *  2. AJAX submit (a submit or click handler that calls $.ajax / $.post / fetch)
 *       The request that starts while the handler is running is tied to the form or
 *       button that triggered it. The button is locked until that request completes
 *       — success or failure — and is then restored, so a failed request can be
 *       corrected and sent again.
 *
 *  3. While locked, further clicks / submits are swallowed before any page handler
 *     sees them, so no second request is sent.
 *
 * Opt out:      data-submit-guard="off" on a form, a button, or any ancestor.
 * Short lock:   data-submit-guard-timeout="4000" on a form whose response is a file
 *               download (the page never navigates, so nothing else would unlock it).
 * Manual use:   FaveoSubmitGuard.lock(el) / .release(el) / .releaseAll()
 */
(function (window, document) {
    'use strict';

    // Never bind twice, even if a view includes the script again.
    if (window.FaveoSubmitGuard) {
        return;
    }

    var config = window.faveoSubmitGuardConfig || {};
    var LOADING_TEXT = config.text || 'Please wait...';
    // Safety net for a normal submit that never leaves the page (navigation
    // cancelled with Esc, unexpected file download...). Long on purpose: a slow
    // server must not hand the button back while the first request is still running.
    var NATIVE_TIMEOUT = config.nativeTimeout || 60000;

    var BUTTON_SELECTOR = 'button, input[type="submit"], input[type="button"], input[type="image"], a.btn';
    var SUBMIT_SELECTOR = 'button:not([type]), button[type="submit"], input[type="submit"], input[type="image"]';
    var LOCK_KEY = '__faveoSubmitLock';
    var PENDING_KEY = '__faveoSubmitPending';
    var LAST_BUTTON_KEY = '__faveoLastSubmitter';
    var XHR_KEY = '__faveoSubmitLock';

    var trigger = null;       // what the user just activated, while its handlers run
    var activeLocks = [];
    var hookedJQuery = [];
    var nativeSubmit = window.HTMLFormElement && window.HTMLFormElement.prototype.submit;

    /* ------------------------------------------------------------------ helpers */

    function closest(el, selector) {
        while (el && el.nodeType === 1) {
            if (el.matches(selector)) {
                return el;
            }
            el = el.parentNode;
        }

        return null;
    }

    function optedOut(el) {
        return !!closest(el, '[data-submit-guard="off"]');
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));

        return div.innerHTML;
    }

    function formMethod(form) {
        return (form.getAttribute('method') || 'get').toLowerCase();
    }

    /** Only a POST that replaces this page is locked as a "normal" submit. */
    function navigatesAway(form) {
        var target = (form.getAttribute('target') || '').toLowerCase();
        var action = (form.getAttribute('action') || '').toLowerCase();

        return formMethod(form) === 'post'
            && (target === '' || target === '_self')
            && action.indexOf('javascript:') !== 0;
    }

    function submitButtons(form) {
        var buttons = Array.prototype.slice.call(form.querySelectorAll(SUBMIT_SELECTOR));
        if (form.id) {
            var outside = document.querySelectorAll('[form="' + form.id.replace(/"/g, '\\"') + '"]');
            Array.prototype.forEach.call(outside, function (el) {
                if (el.matches(SUBMIT_SELECTOR) && buttons.indexOf(el) === -1) {
                    buttons.push(el);
                }
            });
        }

        return buttons;
    }

    function injectStyle() {
        if (document.getElementById('faveo-submit-guard-style')) {
            return;
        }
        var style = document.createElement('style');
        style.id = 'faveo-submit-guard-style';
        style.textContent =
            '.faveo-submitting{pointer-events:none!important;cursor:not-allowed}' +
            'a.faveo-submitting{opacity:.65}' +
            '.faveo-submit-spinner{display:inline-block;width:1em;height:1em;vertical-align:-.125em;' +
            'border:.15em solid currentColor;border-right-color:transparent;border-radius:50%;' +
            'animation:faveo-submit-spin .75s linear infinite}' +
            '@keyframes faveo-submit-spin{to{transform:rotate(360deg)}}' +
            '@media (prefers-reduced-motion:reduce){.faveo-submit-spinner{animation-duration:2s}}';
        (document.head || document.documentElement).appendChild(style);
    }

    /* ------------------------------------------------------------ lock / release */

    function lockButton(el, showLabel) {
        var state = { el: el, setDisabled: false, labelled: false };
        var isAnchor = el.tagName === 'A';
        var isInput = el.tagName === 'INPUT';

        el.classList.add('faveo-submitting');
        el.setAttribute('aria-busy', 'true');

        // A button the page already disabled itself stays under the page's control.
        if (isAnchor) {
            el.setAttribute('aria-disabled', 'true');
        } else if (!el.disabled) {
            el.disabled = true;
            state.setDisabled = true;
        }

        if (showLabel && !(isInput && el.type === 'image')) {
            state.labelled = true;
            if (isInput) {
                state.original = el.value;
                el.value = LOADING_TEXT;
                state.applied = el.value;
            } else {
                state.original = el.innerHTML;
                state.minWidth = el.style.minWidth;
                var hasText = (el.textContent || '').replace(/\s+/g, '') !== '';
                var width = el.offsetWidth;
                if (width) {
                    el.style.minWidth = width + 'px';
                }
                el.innerHTML = '<span class="faveo-submit-spinner" aria-hidden="true"></span>' +
                    (hasText ? ' ' + escapeHtml(LOADING_TEXT) : '');
                state.applied = el.innerHTML;
            }
        }

        return state;
    }

    function releaseButton(state) {
        var el = state.el;

        el.classList.remove('faveo-submitting');
        el.removeAttribute('aria-busy');
        if (el.tagName === 'A') {
            el.removeAttribute('aria-disabled');
        } else if (state.setDisabled) {
            el.disabled = false;
        }

        if (state.labelled) {
            // Put the label back only if it is still ours: a success callback may
            // have written its own text into the button in the meantime.
            if (el.tagName === 'INPUT') {
                if (el.value === state.applied) {
                    el.value = state.original;
                }
            } else {
                if (el.innerHTML === state.applied) {
                    el.innerHTML = state.original;
                }
                el.style.minWidth = state.minWidth;
            }
        }
    }

    /**
     * @param {Element}  owner    the form, or the button for a click outside a form
     * @param {Element}  primary  the button that shows the loading label
     */
    function createLock(owner, primary) {
        if (owner[LOCK_KEY]) {
            return owner[LOCK_KEY];
        }
        injectStyle();

        var buttons = owner.tagName === 'FORM' ? submitButtons(owner) : [owner];
        if (owner.tagName === 'FORM') {
            if (!primary || buttons.indexOf(primary) === -1) {
                primary = buttons[0] || null;
            }
        } else {
            primary = owner;
        }

        var hadFocus = document.activeElement;
        var lock = { owner: owner, pending: 0, timer: null, native: false, released: false, states: [] };
        buttons.forEach(function (button) {
            if (!button[LOCK_KEY] && !optedOut(button)) {
                lock.states.push(lockButton(button, button === primary));
                button[LOCK_KEY] = lock;
            }
        });
        lock.focus = buttons.indexOf(hadFocus) !== -1 ? hadFocus : null;

        owner[LOCK_KEY] = lock;
        activeLocks.push(lock);

        return lock;
    }

    function releaseLock(lock) {
        if (!lock || lock.released) {
            return;
        }
        lock.released = true;
        if (lock.timer) {
            window.clearTimeout(lock.timer);
        }
        lock.states.forEach(function (state) {
            releaseButton(state);
            if (state.el[LOCK_KEY] === lock) {
                state.el[LOCK_KEY] = null;
            }
        });
        if (lock.owner[LOCK_KEY] === lock) {
            lock.owner[LOCK_KEY] = null;
        }
        var index = activeLocks.indexOf(lock);
        if (index !== -1) {
            activeLocks.splice(index, 1);
        }
        if (lock.focus && document.activeElement === document.body && document.contains(lock.focus)) {
            try {
                lock.focus.focus();
            } catch (e) { /* not focusable any more */ }
        }
    }

    function releaseAll() {
        activeLocks.slice().forEach(releaseLock);
    }

    /** Keep a lock until the page is replaced: bfcache restore or the safety timer ends it. */
    function holdUntilNavigation(lock, form) {
        if (lock.native) {
            return;
        }
        var custom = parseInt(form.getAttribute('data-submit-guard-timeout'), 10);
        lock.native = true;
        lock.timer = window.setTimeout(function () {
            releaseLock(lock);
        }, custom > 0 ? custom : NATIVE_TIMEOUT);
    }

    /** Lock for a submit that navigates away. */
    function lockNative(form, submitter) {
        if (!navigatesAway(form) || optedOut(form)) {
            return;
        }
        // An AJAX lock already on the form (a pre-check request whose success
        // callback now submits for real) is simply kept past the request.
        holdUntilNavigation(form[LOCK_KEY] || createLock(form, submitter), form);
    }

    /* ------------------------------------------ tying a request to its trigger */

    /** A write request started while the trigger's handlers were running. */
    function claimRequest(method) {
        if (!trigger) {
            return null;
        }
        method = (method || 'GET').toUpperCase();
        // A form submit handled by AJAX is a submission whatever verb it uses.
        // A bare button click is only locked for requests that change something,
        // so buttons that merely load data (tabs, pagination, pickers) are left alone.
        if (!trigger.form && (method === 'GET' || method === 'HEAD' || method === 'OPTIONS')) {
            return null;
        }
        if (!trigger.lock) {
            trigger.lock = createLock(trigger.form || trigger.el, trigger.submitter);
        }
        trigger.lock.pending++;

        return trigger.lock;
    }

    function requestDone(lock) {
        if (lock && --lock.pending <= 0 && !lock.native) {
            releaseLock(lock);
        }
    }

    /**
     * Views sometimes load a second copy of jQuery, which replaces window.jQuery
     * and has its own global AJAX events — so hook whichever copy is current.
     */
    function hookJQuery() {
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.ajaxSend || hookedJQuery.indexOf($) !== -1) {
            return;
        }
        hookedJQuery.push($);
        $(document).ajaxSend(function (event, jqXHR, settings) {
            if (!jqXHR || jqXHR[XHR_KEY]) {
                return;
            }
            var lock = claimRequest(settings && (settings.type || settings.method));
            if (lock) {
                jqXHR[XHR_KEY] = lock;
            }
        });
        $(document).ajaxComplete(function (event, jqXHR) {
            if (jqXHR && jqXHR[XHR_KEY]) {
                var lock = jqXHR[XHR_KEY];
                jqXHR[XHR_KEY] = null;
                requestDone(lock);
            }
        });
    }

    if (typeof window.fetch === 'function') {
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var method = (init && init.method) || (input && typeof input === 'object' && input.method) || 'GET';
            var lock = claimRequest(method);
            var promise = originalFetch.apply(this, arguments);
            if (lock) {
                var done = function () {
                    requestDone(lock);
                };
                promise.then(done, done);
            }

            return promise;
        };
    }

    /* ------------------------------------------------------------- DOM events */

    function setTrigger(next) {
        trigger = next;
        window.setTimeout(function () {
            if (trigger === next) {
                trigger = null;
            }
        }, 0);
    }

    /** Runs once every other submit handler has had its say. */
    function settleSubmit(pending) {
        if (pending.settled) {
            return;
        }
        pending.settled = true;
        if (pending.form[PENDING_KEY] === pending) {
            pending.form[PENDING_KEY] = null;
        }
        if (trigger === pending) {
            trigger = null;
        }
        // Not cancelled and not taken over by AJAX: the browser is submitting it.
        if (!pending.lock && !pending.event.defaultPrevented) {
            lockNative(pending.form, pending.submitter);
        }
    }

    function swallow(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }

    // Capture phase on document: runs before any handler the page bound itself.
    document.addEventListener('click', function (event) {
        var button = closest(event.target, BUTTON_SELECTOR);
        if (!button) {
            return;
        }
        if (button[LOCK_KEY]) {
            swallow(event);

            return;
        }
        if (optedOut(button)) {
            return;
        }
        hookJQuery();
        if (button.form && button.matches(SUBMIT_SELECTOR)) {
            button.form[LAST_BUTTON_KEY] = button;
        }
        setTrigger({ el: button, form: null, submitter: button, lock: null });
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM') {
            return;
        }
        // A very fast second submit can arrive before the first was settled.
        if (form[PENDING_KEY]) {
            settleSubmit(form[PENDING_KEY]);
        }
        if (form[LOCK_KEY]) {
            swallow(event);

            return;
        }
        if (optedOut(form)) {
            return;
        }
        hookJQuery();

        var submitter = event.submitter || form[LAST_BUTTON_KEY] || null;
        if (submitter && submitter.form !== form) {
            submitter = null;
        }
        var pending = { el: null, form: form, submitter: submitter, event: event, lock: null, settled: false };
        form[PENDING_KEY] = pending;
        setTrigger(pending);
        // Not in the bubble phase: a jQuery handler that returns false stops
        // propagation, and disabling the button before the browser has collected
        // the form data would drop the clicked button's own name/value.
        window.setTimeout(function () {
            settleSubmit(pending);
        }, 0);
    }, true);

    // form.submit() called from script fires no submit event at all.
    if (nativeSubmit) {
        window.HTMLFormElement.prototype.submit = function () {
            // Already on its way to the server: a second call would post it twice.
            if (this[LOCK_KEY] && this[LOCK_KEY].native && !optedOut(this)) {
                return;
            }
            nativeSubmit.apply(this, arguments);
            lockNative(this, this[LAST_BUTTON_KEY] || null);
        };
    }

    // Back/forward cache restores the page exactly as it was left — locked.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            releaseAll();
        }
    });

    hookJQuery();

    window.FaveoSubmitGuard = {
        lock: function (el) {
            return el ? createLock(el, el.tagName === 'FORM' ? null : el) : null;
        },
        release: function (el) {
            if (el && el[LOCK_KEY]) {
                releaseLock(el[LOCK_KEY]);
            }
        },
        releaseAll: releaseAll,
    };
})(window, document);
