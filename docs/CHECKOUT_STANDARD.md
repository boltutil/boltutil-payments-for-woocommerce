# Shared BoltUtil checkout standard

The approved reference is `boltutil-whmcs/modules/gateways/boltutil/assets/checkout.css`, matching BoltUtil Hosted Checkout's layout and `BoltUtilLogo.vue`. WooCommerce's `assets/checkout-standard.css` is an exact source snapshot. `assets/checkout-base.css` is generated from it with selectors scoped to `.boltutil-checkout-standard`; `assets/checkout.css` contains only WooCommerce adapters and native-form integration styles.

| Element | Shared value |
| --- | --- |
| Canvas | Light `#f5f5f7`, including with a dark system preference |
| Card | Maximum 520px, white, 28px corners, `0 24px 64px #11182708` shadow |
| Header | `#18181b`, centred original lightning lockup, 32px icon/20px wordmark |
| Header padding | 32px 28px 26px; mobile 28px 24px 24px |
| Body padding | 28px; mobile 24px 20px |
| Amount | 48px / 800 / tabular; 42px on mobile |
| Asset switch | 14px track, 4px inset, 44px controls, white selected surface |
| Network cards | Two columns; one at 420px or narrower, 14px corners, neutral background |
| Selection | Dark border and radio marker; green keyboard focus outline |
| Main action | Native action, 48px minimum, 14px corners, `#111827` |
| Chain/token artwork | Exact current frontend files, 36px/24px canvases with -9px overlap |

## Synchronising the standard

From the WooCommerce source directory in the BoltUtil workspace:

```sh
python3 tools/sync-checkout-design.py
python3 tools/sync-checkout-design.py --check
```

The exporter copies the approved CSS snapshot, generates its scoped counterpart, and copies eight original files used by `payment/ChainAssetIcon.vue` and `utils/stablecoinIcons.ts`. `assets/checkout-sources.json` records source paths and exact hashes. All assets are bundled locally; checkout does not fetch images from the hosted site. When this repository is checked out alone, the checked-in CSS snapshot and image hashes remain available for validation.

Do not edit the scoped CSS export or substitute similar network logos. Adjust the approved standard first, regenerate, and compare the desktop and mobile renderings of both integrations.

## WooCommerce integration

- Classic checkout and Checkout Blocks use the same card structure and scoped design. Cart amounts come from WooCommerce; a customer never supplies the payment amount through a new hidden field. Blocks reads the documented billing cart total in minor units, using decimal-string formatting for display.
- The authorised order-pay form uses the full invoice card when BoltUtil is the only available gateway. The template is selected using WooCommerce's already-authorised template arguments. It adds no public endpoint and does not bypass login, guest verification, order-key checks or native payment processing.
- When multiple gateways are available, WooCommerce's original form remains, preserving alternatives. The BoltUtil card itself keeps the same style. Native cart checkout and Blocks also retain billing/address/terms and their native place-order action; those surrounding store elements are intentionally owned by WooCommerce.
- The invoice form retains the native `order_review` POST form, gateway/pay markers, terms template, before/after-submit hooks and `woocommerce-pay-nonce`. Item/tax/fee details remain available in a disclosure below the card.
- New payments require an explicit route choice. Changing stablecoin clears the previous route. Classic AJAX refresh preserves a still-valid explicit same-token choice; Blocks unregisters obsolete payment-setup callbacks.
- Checkout copy uses the existing WordPress language mechanism; new copy is included in all eleven bundled translations. Merchant-authored descriptions remain visible; the plugin's default description is omitted to avoid repeating standard guidance.

## Release provenance

Version 0.6.3 is the unified checkout release. The publisher confirmed public redistribution permission for the original images on 2026-11-19. The development record in ASSET_PERMISSIONS.json pins that confirmation to the exact file hashes. Source synchronisation still checks every image; a changed image requires a fresh rights record. The installable package contains a concise asset licence notice; detailed source records remain in this repository.
