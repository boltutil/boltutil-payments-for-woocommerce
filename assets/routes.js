( function () {
    // UI choices always come from merchant-scoped server capabilities.
    const tokens = ( routes ) => [ 'USDT', 'USDC' ].filter( ( token ) =>
        Object.values( routes ).some( ( route ) => route.token === token ) );
    const forToken = ( routes, token ) => Object.keys( routes ).filter( ( id ) => routes[ id ].token === token );
    const select = ( routes, token, previous ) => {
        const eligible = forToken( routes, token );
        return eligible.includes( previous ) ? previous : '';
    };
    const amount = ( minor, digits = 2 ) => {
        if ( ! Number.isInteger( digits ) || digits < 0 || digits > 6 || ! /^[0-9]{1,19}$/.test( String( minor ) ) ) return '';
        const value = String( minor ).replace( /^0+(?=\d)/, '' ).padStart( digits + 1, '0' );
        return digits ? value.slice( 0, -digits ) + '.' + value.slice( -digits ) : value;
    };
    window.BoltUtilCheckoutRoutes = { tokens, forToken, select, amount };
} )();
