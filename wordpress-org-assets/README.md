# WordPress.org listing artwork

Screenshots captured on 2026-11-19 in a real local WordPress installation with WooCommerce 11.1.0 and plugin v0.6.3. All products, orders and merchant capabilities are synthetic; outbound payment HTTP is intercepted by a local fixture. No real funds or customer data are present. These are actual WooCommerce renders, not generated mockups. Store themes can change the surrounding layout.

1. Order-payment invoice, USDT and six enabled networks.
2. Order-payment invoice, USDC and five enabled networks.
3. Native merchant setup guide, cropped before credential inputs.
4. Native Checkout Blocks with USDC/Base selected.
5. Classic checkout with USDC/Base selected.
6. Native invoice at a 375px mobile viewport.

`directory-introduction.txt` preserves the WordPress.org feature overview, search terms and demo-payment disclosure. `tools/build-wordpress-release.py` combines it with the verified GitHub package and removes bundled language loading for the directory edition. The only content differences are the main PHP translation bootstrap, the listing readme and omitted language catalogs.

The versionless BoltUtil banner and own-brand icons are retained. Screenshot names match readme.txt. Publish only icon-128x128.png, icon-256x256.png, banner-772x250.png, banner-1544x500.png, their zh_CN banner counterparts, and screenshot-1.jpg through screenshot-6.jpg to the top-level SVN assets directory. Do not ship source banner variants or this developer note in the installable plugin.
