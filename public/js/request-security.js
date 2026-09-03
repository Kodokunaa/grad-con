(() => {
    const token = () => document.querySelector('meta[name="csrf-token"]')?.content;
    const originalFetch = window.fetch.bind(window);
    window.fetch = (input, init = {}) => {
        const target = new URL(input instanceof Request ? input.url : input, location.href);
        if (target.origin === location.origin) {
            const headers = new Headers(init.headers || (input instanceof Request ? input.headers : undefined));
            headers.set('X-CSRF-TOKEN', token() || '');
            init = {...init, headers};
        }
        return originalFetch(input, init);
    };
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url, ...rest) {
        this.gradconnSameOrigin = new URL(url, location.href).origin === location.origin;
        return originalOpen.call(this, method, url, ...rest);
    };
    XMLHttpRequest.prototype.send = function (body) {
        if (this.gradconnSameOrigin) this.setRequestHeader('X-CSRF-TOKEN', token() || '');
        return originalSend.call(this, body);
    };
})();
