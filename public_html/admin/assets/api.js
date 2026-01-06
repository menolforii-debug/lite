(() => {
    const api = {
        baseUrl: '',
        csrf: '',
        init(baseUrl, csrf) {
            this.baseUrl = baseUrl || '';
            this.csrf = csrf || '';
        },
        async post(url, payload = {}) {
            const body = { ...payload, csrf_token: this.csrf };
            const response = await fetch(this.baseUrl + url.replace('/admin', ''), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            return response.json();
        },
    };

    window.LiteCMSApi = api;
})();
