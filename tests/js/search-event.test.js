const fs = require('fs');
const path = require('path');

const templateDir = path.resolve(__dirname, '../../src/view/frontend/templates/js');

const readInlineScript = template => {
    const source = fs.readFileSync(path.join(templateDir, template), 'utf8');
    const match = source.match(/<script>([\s\S]*?)<\/script>/);
    if (!match) {
        throw new Error(`No inline script found in ${template}`);
    }

    return match[1];
};

const defaultScript = readInlineScript('default.phtml');
const searchEventScript = readInlineScript('event/search-event.phtml');

describe('search-event.phtml', () => {
    let listeners;
    let originalAddEventListener;

    const setUrl = url => window.history.replaceState(null, '', url);
    const navigateHash = hash => {
        setUrl(hash);
        window.dispatchEvent(new HashChangeEvent('hashchange'));
    };
    const selectionChange = data => window.dispatchEvent(new CustomEvent('twn.selection.change', {detail: {data}}));
    const pushedTerms = () => window.pushEvent.mock.calls.map(([event]) => event.data.searchTerm);

    const boot = url => {
        setUrl(url);
        new Function(defaultScript)();
        new Function(searchEventScript)();
    };

    beforeEach(() => {
        listeners = [];
        originalAddEventListener = window.addEventListener;
        window.addEventListener = function (type, listener, options) {
            listeners.push([type, listener, options]);
            return originalAddEventListener.call(this, type, listener, options);
        };

        window.pushEvent = jest.fn();
        window.getProfileKey = jest.fn(() => 'profile-key');
    });

    afterEach(() => {
        window.addEventListener = originalAddEventListener;
        listeners.forEach(([type, listener, options]) => window.removeEventListener(type, listener, options));
        delete window.pushEvent;
        delete window.getProfileKey;
        delete window.tweakwiseJs;
        setUrl('/');
    });

    test('pushes one search event with the profile key on page load', () => {
        boot('/catalogsearch/result/?q=yoga');

        expect(window.pushEvent).toHaveBeenCalledTimes(1);
        expect(window.pushEvent).toHaveBeenCalledWith({
            event: 'search',
            data: {profileKey: 'profile-key', searchTerm: 'yoga'}
        });
    });

    test('does not push an event for the same term again', () => {
        boot('/catalogsearch/results/#twn|?tn_q=jacket');

        navigateHash('/catalogsearch/results/#twn|?tn_q=jacket&tn_p=2');
        window.dispatchEvent(new PopStateEvent('popstate'));
        selectionChange({tn_q: 'jacket', tn_p: '3'});

        expect(pushedTerms()).toEqual(['jacket']);
    });

    test('pushes an event when the term changes on the same page', () => {
        boot('/catalogsearch/results/#twn|?tn_q=jacket');

        navigateHash('/catalogsearch/results/#twn|?tn_q=shirt');
        selectionChange({tn_q: 'pants'});

        expect(pushedTerms()).toEqual(['jacket', 'shirt', 'pants']);
    });

    test('does not push an event for an empty term', () => {
        boot('/catalogsearch/results/');
        selectionChange({tn_q: ''});

        expect(window.pushEvent).not.toHaveBeenCalled();
    });

    test('pushes the same term again after it was cleared', () => {
        boot('/catalogsearch/results/#twn|?tn_q=jacket');

        selectionChange({tn_q: ''});
        selectionChange({tn_q: 'jacket'});

        expect(pushedTerms()).toEqual(['jacket', 'jacket']);
    });

    test('falls back to the URL term when the SDK event has no tn_q', () => {
        boot('/catalogsearch/results/#twn|?tn_q=jacket');

        selectionChange({tn_p: '2'});
        setUrl('/catalogsearch/results/#twn|?tn_q=shirt');
        selectionChange({tn_p: '1'});

        expect(pushedTerms()).toEqual(['jacket', 'shirt']);
    });
});
