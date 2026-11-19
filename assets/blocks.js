( function () {
    const settings = window.wc.wcSettings.getSetting( 'boltutil_usdt_data', {} );
    const el = window.wp.element.createElement;
    const useState = window.wp.element.useState;
    const useEffect = window.wp.element.useEffect;
    const decode = window.wp.htmlEntities.decodeEntities;
    const networks = settings.networks || {};
    const codes = Object.keys( networks );
    const routes = window.BoltUtilCheckoutRoutes;
    const tokens = routes.tokens( networks );
    const copy = settings.copy || {};

    function Brand() {
        return el( 'div', { className: 'brand', 'aria-label': 'BoltUtil' },
            el( 'span', { className: 'brand-mark' }, el( 'svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '2.5', strokeLinecap: 'round', strokeLinejoin: 'round', 'aria-hidden': true },
                el( 'path', { d: 'M13 10V3L4 14h7v7l9-11h-7z' } ) ) ), el( 'span', null, 'BoltUtil' ) );
    }

    function Content( props ) {
        const [ token, setToken ] = useState( tokens[ 0 ] || '' );
        const [ network, setNetwork ] = useState( '' );
        const available = routes.forToken( networks, token );
        const chooseToken = ( next ) => { setToken( next ); setNetwork( routes.select( networks, next, network ) ); };
        const billing = props.billing || {};
        const currency = billing.currency || {};
        const amount = billing.cartTotal ? routes.amount( billing.cartTotal.value, currency.minorUnit === undefined ? 2 : currency.minorUnit ) : '';
        const onPaymentSetup = props.eventRegistration && props.eventRegistration.onPaymentSetup;
        useEffect( () => {
            if ( ! onPaymentSetup ) return undefined;
            return onPaymentSetup( () => {
                if ( ! network || ! Object.prototype.hasOwnProperty.call( networks, network ) || networks[ network ].token !== token ) {
                    return { type: props.emitResponse.responseTypes.ERROR, message: settings.networkError || 'Choose an available stablecoin and network.' };
                }
                return { type: props.emitResponse.responseTypes.SUCCESS, meta: { paymentMethodData: { boltutil_route: network } } };
            } );
        }, [ network, token, onPaymentSetup ] );

        return el( 'div', { className: 'boltutil-payment-panel boltutil-checkout-standard' },
            el( 'section', { className: 'checkout-card' },
                el( 'header', { className: 'checkout-header' }, el( Brand ), el( 'p', { className: 'checkout-promo' }, copy.intro || '' ), el( 'h2', { className: 'checkout-title' }, decode( settings.title || 'Pay with BoltUtil' ) ) ),
                el( 'div', { className: 'checkout-body' },
                    amount ? el( 'div', { className: 'checkout-amount' }, el( 'p', null, copy.balance || 'Amount to pay' ), el( 'div', { className: 'checkout-amount-value', dir: 'ltr' }, el( 'strong', null, amount ), el( 'span', null, currency.code || 'USD' ) ) ) : null,
                    settings.description ? el( 'p', { className: 'boltutil-merchant-description' }, decode( settings.description ) ) : null,
                    tokens.length > 1 ? el( 'fieldset', { className: 'boltutil-token-fieldset' }, el( 'legend', null, copy.choose || settings.tokenLabel ),
                        el( 'div', { className: 'asset-switch boltutil-token-list' }, tokens.map( ( coin ) => el( 'label', { className: 'boltutil-token-option', key: coin },
                            el( 'input', { type: 'radio', name: 'boltutil-block-token', value: coin, checked: token === coin, onChange: () => chooseToken( coin ) } ),
                            el( 'img', { src: ( settings.tokenIcons || {} )[ coin ] || '', alt: '', width: 20, height: 20, 'aria-hidden': true } ), el( 'span', null, coin ) ) ) ) ) : null,
                    el( 'fieldset', { className: 'boltutil-network-fieldset' }, el( 'legend', { className: tokens.length > 1 ? 'boltutil-network-heading' : '' }, copy.choose || settings.networkLabel ),
                        el( 'div', { className: 'routes boltutil-network-list' }, available.map( ( code ) => {
                            const item = networks[ code ];
                            const asset = item.token === 'USDC' && item.network === 'BEP20' ? 'Binance-Peg USDC' : item.token === 'USDT' && item.network === 'BASE' ? 'Bridged USDT' : item.token;
                            return el( 'label', { className: 'route boltutil-network-option', key: code },
                                el( 'input', { type: 'radio', name: 'boltutil-block-network', value: code, checked: network === code, onChange: () => setNetwork( code ) } ),
                                el( 'span', { className: 'route-icon', 'aria-hidden': true }, el( 'span', { className: 'route-chain route-chain--' + item.network }, el( 'img', { src: item.icon || '', alt: '', width: 36, height: 36 } ) ),
                                    el( 'span', { className: 'route-token' }, el( 'img', { src: item.tokenIcon || '', alt: '', width: 24, height: 24 } ) ) ),
                                el( 'span', { className: 'route-label' }, el( 'strong', null, asset ), el( 'small', null, item.label ) ), el( 'span', { className: 'route-check', 'aria-hidden': true } ) );
                        } ) ) ),
                    el( 'p', { className: 'help boltutil-payment-note' }, settings.redirectNotice || '' ) ) ) );
    }

    window.wc.wcBlocksRegistry.registerPaymentMethod( {
        name: 'boltutil_usdt', paymentMethodId: 'boltutil_usdt',
        label: el( 'span', { className: 'boltutil-payment-label' }, el( 'img', { src: settings.icon || '', alt: '', 'aria-hidden': true } ), decode( settings.title || 'Pay with BoltUtil' ) ),
        content: el( Content ), edit: el( Content ), canMakePayment: () => codes.length > 0,
        ariaLabel: decode( settings.title || 'Pay with BoltUtil' ), supports: { features: settings.supports || [ 'products' ] },
    } );
} )();
