const results = [];

function assert(condition, message) {
    if (!condition) {
        throw new Error(message);
    }
}

function context(url = 'https://shop.test/?tn_q=jacket', readyState = 'complete', bootstrap = true) {
    const window = new EventTarget();
    const location = new URL(url);
    const calls = {lister: [], suggestions: [], recommendations: [], events: [], scripts: [], cart: [], wishlist: []};
    const form = {removeAttribute() {}};
    const input = {value: 'red & blue', closest: () => form};
    const document = {
        readyState,
        querySelectorAll: () => [input],
        getElementById: () => form,
        createElement: () => ({}),
        head: {appendChild: script => calls.scripts.push(script)}
    };
    window.location = location;
    window['twn-starter-config'] = {input: ['#search'], history: 'query'};
    window.twnAddTo = {
        addToCartByItemNo: item => calls.cart.push(item),
        addToWishlistByItemNo: item => calls.wishlist.push(item)
    };
    const run = html => {
        for (const match of html.matchAll(/<script>([\s\S]*?)<\/script>/g)) {
            new Function('window', 'document', 'location', 'pushEvent', 'getProfileKey', match[1])(
                window, document, location, event => calls.events.push(event), () => 'profile'
            );
        }
    };
    const sdk = () => {
        window.tweakwiseListerPage = config => calls.lister.push(config);
        window.tweakwiseSuggestions = config => calls.suggestions.push(config);
    };
    const fire = name => window.dispatchEvent(new Event(name));
    const domReady = () => {
        document.readyState = 'interactive';
        fire('DOMContentLoaded');
    };
    if (bootstrap) {
        run(templates.bootstrap);
    }
    return {window, document, location, calls, form, input, run, sdk, fire, domReady};
}

function test(name, callback) {
    callback();
    results.push('PASS ' + name);
}

try {
    for (const template of ['search', 'category', 'landing']) {
        test(template + ': delayed SDK initializes once', () => {
            const ctx = context();
            ctx.run(templates[template]);
            assert(ctx.calls.lister.length === 0, 'must wait for SDK');
            ctx.sdk();
            ctx.fire('twn.starter.ready');
            ctx.fire('twn.starter.ready');
            assert(ctx.calls.lister.length === 1, 'exactly one lister');
            assert(ctx.calls.lister[0].on === ctx.window.tweakwiseJs.addToHandlers, 'shared handlers');
        });

        test(template + ': cached SDK event before template is not lost', () => {
            const ctx = context();
            ctx.sdk();
            ctx.fire('twn.starter.ready');
            ctx.run(templates[template]);
            assert(ctx.calls.lister.length === 1, 'initialize after an early ready event');
        });

        test(template + ': SDK readiness before DOM waits for output', () => {
            const ctx = context('https://shop.test/', 'loading');
            ctx.sdk();
            ctx.fire('twn.starter.ready');
            ctx.run(templates[template]);
            assert(ctx.calls.lister.length === 0, 'DOM is still parsing');
            ctx.domReady();
            assert(ctx.calls.lister.length === 1, 'initialize when output is in DOM');
        });
    }

    test('SDK ready before bootstrap is recovered from the constructor', () => {
        const ctx = context();
        ctx.sdk();
        ctx.fire('twn.starter.ready');
        ctx.run(templates.bootstrap);
        ctx.run(templates.search);
        assert(ctx.calls.lister.length === 1, 'constructor detects a previously fired event');
    });

    test('failover SDK arriving after DOM initializes exactly once', () => {
        const ctx = context('https://shop.test/', 'loading');
        ctx.run(templates.search);
        ctx.window.tweakwiseFailover('https://gateway.test/tweakwise.js');
        assert(ctx.calls.scripts[0].src === 'https://gateway.test/tweakwise.js', 'failover script inserted');
        ctx.domReady();
        assert(ctx.calls.lister.length === 0, 'failed gateway does not initialize');
        ctx.sdk();
        ctx.fire('twn.starter.ready');
        ctx.fire('twn.starter.ready');
        assert(ctx.calls.lister.length === 1, 'failover readiness initializes only once');
    });

    test('gateway failure in head waits until the bootstrap exists', () => {
        const ctx = context('https://shop.test/', 'loading', false);
        const script = {dataset: {failover: 'https://gateway.test/tweakwise.js'}};
        new Function('window', 'document', failoverHandler).call(script, ctx.window, ctx.document);
        assert(ctx.calls.scripts.length === 0, 'do not call undefined body helper from head');
        ctx.run(templates.bootstrap);
        ctx.run(templates.search);
        ctx.domReady();
        assert(ctx.calls.scripts.length === 1, 'failover loads after bootstrap');
        ctx.sdk();
        ctx.fire('twn.starter.ready');
        assert(ctx.calls.lister.length === 1, 'failover renders lister');
    });

    test('gateway failure after DOM immediately loads failover', () => {
        const ctx = context();
        const script = {dataset: {failover: 'https://gateway.test/tweakwise.js'}};
        new Function('window', 'document', failoverHandler).call(script, ctx.window, ctx.document);
        assert(ctx.calls.scripts.length === 1, 'DOMContentLoaded is not missed');
    });

    for (const [url, expected] of [
        ['?q=native', 'native'],
        ['?tn_q=tweakwise', 'tweakwise'],
        ['?q=native&tn_q=tweakwise', 'tweakwise'],
        ['#twn|?tn_q=hash%20term', 'hash term'],
        ['?q=old&tn_q=stale#twn|?tn_q=current', 'current'],
        ['?tn_q=red%20%26%20blue', 'red & blue'],
        ['?q=red+blue', 'red blue'],
        ['', '']
    ]) {
        test('listing and analytics agree for ' + (url || 'empty query'), () => {
            const ctx = context('https://shop.test/' + url);
            ctx.sdk();
            ctx.run(templates.search);
            ctx.run(templates.event);
            assert(ctx.calls.lister[0].query === expected, 'displayed term');
            assert(ctx.calls.events.length === 1, 'one search event');
            assert(ctx.calls.events[0].data.searchTerm === expected, 'tracked term');
        });
    }

    for (const mode of ['query', 'hash', undefined]) {
        for (const submit of ['Enter', 'suggestion']) {
            test((mode || 'default query') + ' history: ' + submit + ' redirects and tracks once on results', () => {
                const ctx = context();
                ctx.window['twn-starter-config'].history = mode;
                ctx.run(templates.suggestions);
                ctx.sdk();
                ctx.fire('twn.suggestions.ready');
                ctx.fire('twn.suggestions.ready');
                assert(ctx.calls.suggestions.length === 1, 'one suggestions instance');
                if (submit === 'Enter') {
                    let prevented = false;
                    ctx.form.onsubmit({preventDefault() { prevented = true; }});
                    assert(prevented, 'native submit intercepted');
                } else {
                    ctx.calls.suggestions[0].searchPhrases.handle({data: {match: ctx.input.value}});
                }
                const target = new URL(ctx.location.href);
                assert(target.searchParams.get('existing') === '1', 'existing query preserved');
                assert(target.searchParams.get('quote') === "'", 'URL is JS escaped, not HTML escaped');
                assert(mode !== 'hash' ? !target.hash : target.hash.startsWith('#twn|?tn_q='), 'history mode');
                assert(ctx.calls.events.length === 0, 'no duplicate pre-redirect event');
                const destination = context(target.href);
                destination.sdk();
                destination.run(templates.search);
                destination.run(templates.event);
                assert(destination.calls.events.length === 1, 'exactly one destination event');
                assert(destination.calls.events[0].data.searchTerm === ctx.input.value, 'submitted term tracked');
                assert(destination.calls.lister[0].query === ctx.input.value, 'submitted term displayed');
            });
        }
    }

    test('suggestions handles cached SDK and string input selectors', () => {
        const ctx = context('https://shop.test/', 'loading');
        ctx.window['twn-starter-config'].input = '#search';
        ctx.sdk();
        ctx.fire('twn.suggestions.ready');
        ctx.run(templates.suggestions);
        assert(ctx.calls.suggestions.length === 0, 'wait for DOM');
        ctx.domReady();
        assert(ctx.calls.suggestions.length === 1, 'cached suggestions initialize');
        assert(typeof ctx.form.onsubmit === 'function', 'form intercepted');
    });

    test('instant results keep SDK auto-init without an explicit second lister', () => {
        const ctx = context();
        ctx.sdk();
        ctx.run(templates.instant);
        ctx.run(templates.instantLister);
        const config = ctx.window['twn-starter-config'];
        assert(config.auto === true, 'SDK auto-init preserved');
        assert(config.ui.closeSearchButton === false, 'no close button on results');
        assert(config.output === '#tweakwise-output', 'output target provided');
        assert(config.query === 'jacket', 'instant query passed');
        assert(templates.instantLister.includes('id="tweakwise-output"'), 'output rendered');
        assert(ctx.calls.lister.length === 0, 'no explicit lister call');
        config.on['twn.add-to-cart']({data: {itemno: '123'}});
        config.on['twn.add-to-favorites']({data: {itemno: '456'}});
        assert(ctx.calls.cart[0] === '123', 'instant add to cart');
        assert(ctx.calls.wishlist[0] === '456', 'instant add to wishlist');
    });

    test('recommendations wait for their own SDK event rather than the lister', () => {
        const ctx = context();
        ctx.sdk();
        ctx.run(templates.recommendations);
        assert(ctx.calls.recommendations.length === 0, 'recommendations SDK is not ready');
        ctx.window.tweakwiseRecommendations = config => ctx.calls.recommendations.push(config);
        ctx.fire('twn.recommendations.ready');
        ctx.fire('twn.recommendations.ready');
        assert(ctx.calls.recommendations.length === 1, 'one recommendation initialization');
        assert(ctx.calls.recommendations[0].on === ctx.window.tweakwiseJs.addToHandlers, 'shared handlers');
    });

    test('cached recommendations initialize without the lister SDK', () => {
        const ctx = context();
        ctx.window.tweakwiseRecommendations = config => ctx.calls.recommendations.push(config);
        ctx.fire('twn.recommendations.ready');
        ctx.run(templates.recommendations);
        assert(ctx.calls.recommendations.length === 1, 'cached recommendations initialize');
        assert(ctx.calls.recommendations[0].featured.id === 'rule-1', 'widget rule preserved');
    });

    document.getElementById('result').textContent = results.join('\n') + '\nPASS: ' + results.length + ' scenarios';
} catch (error) {
    document.getElementById('result').textContent = results.join('\n') + '\nFAIL: ' + error.stack;
    throw error;
}
