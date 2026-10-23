const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../src/view/frontend/templates/js/default.phtml'),
    'utf8'
);
const defaultScript = source.match(/<script>([\s\S]*?)<\/script>/)[1];

describe('default.phtml', () => {
    let listeners;
    let originalAddEventListener;

    // Simulates the deferred instance script, which merges its module config into the
    // window config (window values win) and then loads the module scripts.
    const runInstanceScript = moduleConfig => {
        const existing = window['twn-starter-config'];
        window['twn-starter-config'] = {...moduleConfig, ...existing};
    };

    beforeEach(() => {
        listeners = [];
        originalAddEventListener = window.addEventListener;
        window.addEventListener = function (type, listener, options) {
            listeners.push([type, listener, options]);
            return originalAddEventListener.call(this, type, listener, options);
        };

        document.body.innerHTML = '<form id="search_mini_form"><input id="search" name="q"></form>';
        delete window['twn-starter-config'];
        new Function(defaultScript)();
    });

    afterEach(() => {
        window.addEventListener = originalAddEventListener;
        listeners.forEach(([type, listener, options]) => window.removeEventListener(type, listener, options));
        delete window.tweakwiseSuggestions;
        delete window.tweakwiseListerPage;
        delete window.twnAddTo;
    });

    describe('initSuggestions', () => {
        it('uses the input merged by the deferred instance script', () => {
            window.tweakwiseJs.initSuggestions({instancekey: 'key', cid: '100'}, '/catalogsearch/results');
            runInstanceScript({input: ['#search'], output: '#maincontent'});
            window.tweakwiseSuggestions = jest.fn();
            window.dispatchEvent(new Event('twn.suggestions.ready'));

            expect(window.tweakwiseSuggestions).toHaveBeenCalledTimes(1);
            expect(window.tweakwiseSuggestions).toHaveBeenCalledWith(expect.objectContaining({
                input: ['#search'],
                instancekey: 'key',
                cid: '100',
                on: window.tweakwiseJs.addToHandlers
            }));
        });

        it('submits the search form to the search results page', () => {
            window.tweakwiseJs.initSuggestions({}, 'http://localhost/catalogsearch/results');
            runInstanceScript({input: ['#search']});
            window.tweakwiseSuggestions = jest.fn();
            window.dispatchEvent(new Event('twn.suggestions.ready'));

            const form = document.getElementById('search_mini_form');
            expect(form.onsubmit).toEqual(expect.any(Function));
        });

        it('does nothing when no search input is configured', () => {
            window['twn-starter-config'].input = [];
            window.tweakwiseJs.initSuggestions({}, '/catalogsearch/results');
            runInstanceScript({input: ['#search']});
            window.tweakwiseSuggestions = jest.fn();
            window.dispatchEvent(new Event('twn.suggestions.ready'));

            expect(window.tweakwiseSuggestions).not.toHaveBeenCalled();
        });
    });

    describe('initNavigation', () => {
        it('does not bind the search input to an explicit lister page', () => {
            window.tweakwiseJs.initNavigation({cid: '5', output: '#tweakwise-output'}, false);
            runInstanceScript({input: ['#search']});
            window.tweakwiseListerPage = jest.fn();
            window.dispatchEvent(new Event('twn.starter.ready'));

            expect(window.tweakwiseListerPage).toHaveBeenCalledWith(expect.objectContaining({
                cid: '5',
                input: null
            }));
        });
    });

    describe('addToHandlers', () => {
        it('queues add-to-cart until the add-to script is ready', () => {
            window.tweakwiseJs.addToHandlers['twn.add-to-cart']({data: {itemno: 'SKU-1'}});

            window.twnAddTo = {addToCartByItemNo: jest.fn()};
            window.dispatchEvent(new Event('twn.add-to.ready'));

            expect(window.twnAddTo.addToCartByItemNo).toHaveBeenCalledWith('SKU-1');
        });

        it('calls add-to-favorites directly when the add-to script is ready', () => {
            window.twnAddTo = {addToWishlistByItemNo: jest.fn()};
            window.tweakwiseJs.addToHandlers['twn.add-to-favorites']({data: {itemno: 'SKU-2'}});

            expect(window.twnAddTo.addToWishlistByItemNo).toHaveBeenCalledWith('SKU-2');
        });
    });
});
