function createUUID() {
    return (`${1e7}-${1e3}-${4e3}-${8e3}-${1e11}`).replace(/[018]/g, (c) => {
        const num = +c;
        return (num ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> num / 4).toString(16)
    });
}

window.amplitude_config = {
    AMPLITUDE_EVENT: {
        SCRIPT_STARTED: 'PDP_SCRIPT.SCRIPT_STARTED',
        WIDGET_INSERTED: 'PDP_SCRIPT.WIDGET_INSERTED',
        WIDGET_NOT_INSERTED: 'PDP_SCRIPT.WIDGET_NOT_INSERTED',
        WIDGET_RENDERED: 'PDP_SCRIPT.WIDGET_RENDERED',
        WIDGET_CLICKED: 'PDP_SCRIPT.WIDGET_CLICKED',
        CHECKOUT_DISPLAYED: 'ALLY_INTEGRATION_CHECKOUT.DISPLAYED_PAYMENT_METHOD',
        CHECKOUT_SELECTED: 'ALLY_INTEGRATION_CHECKOUT.SELECTED_PAYMENT_METHOD',
    },
    CHANNEL: 'E_COMMERCE_WOOCOMMERCE',
    LOGGER_APP_DOMAIN: 'https://logger.addi.com',
    // LOGGER_APP_DOMAIN: 'http://localhost:3013',
    AppSources: {
        ALLY_INTEGRATION_PRODUCT: 'ALLY_INTEGRATION_PRODUCT'
    },
    UUID: createUUID()
}

function widgetTrackerAmplitude(title, params) {
    const id = window.amplitude_config.UUID;
    const source = window.amplitude_config.AppSources.ALLY_INTEGRATION_PRODUCT;
    const channel = window.amplitude_config.CHANNEL;
    const ADDI_TAG = document.querySelector('script[data-name="wooAddiHomeBanner"]');
    const allySlug = ADDI_TAG ? ADDI_TAG.getAttribute('data-ally-slug') : null;

    const body = {
        id,
        title,
        params: {
            executedAt: (new Date()).toString(),
            source: source,
            ...params
        },
        properties: {
            channel,
            allySlug
        },
        country: addiParams.country
    };

    // Posting the data
    jQuery.ajax(window.amplitude_config.LOGGER_APP_DOMAIN+  `/api/events/${source}`, {
        data: JSON.stringify(body),
        method: "POST",
        contentType: "application/json"
    });
}