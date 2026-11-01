# v0.6.2 — checkout alignment and network ordering

## Changes

- Align the BoltUtil mark, stablecoin icons and network badges in Classic Checkout and Checkout Blocks; preserve alignment on narrow and RTL screens.
- Replace the generic Base drawing with a documented, locally rendered blue-square identifier.
- Sort networks by the service-provided numeric network ID. Older services use the original catalog order: TRON, Ethereum, BNB Smart Chain, Polygon, Solana and Base.
- Version checkout CSS and network artwork by content hash so corrected assets refresh after reinstalling.
- Keep merchant settings, credentials, existing orders, precise amounts, signature verification and payment completion behavior unchanged.

The companion BoltUtil backend adds `networkId` to capabilities and returns routes in network-ID order. This is an additive response change and requires no database migration. Existing backend versions remain supported through the catalog-order fallback.

## Installation and compatibility

Install `boltutil-payments-for-woocommerce-0.6.2.zip` through WordPress Plugins → Add New → Upload Plugin, or update through the [WordPress.org directory](https://wordpress.org/plugins/boltutil-payments-for-woocommerce/). Check the GitHub download against `SHA256SUMS.txt`.

WordPress 7.0+, WooCommerce 11.1+, PHP 7.4+, HTTPS, USD store currency and a configured BoltUtil merchant account are required. Supported routes still depend on enabled service assets and receiving wallets. The GitHub ZIP includes bundled translations; the directory edition uses WordPress language packs.

## Validation

Classic and Blocks selection fixtures cover all eleven supported routes. Isolated PHP, API, Webhook and retry fixtures passed. Local browser checks covered 1440px, 375px and 320px, with coin switching and RTL checks. The backend query was tested using isolated H2 data. These checks do not represent a real-money payment acceptance test.

## 简体中文

修复结账页品牌、币种和网络图标的对齐，更新 Base 蓝色方块标识，网络按服务端数字 ID 排序，并为旧后端保留原目录顺序。样式与图标增加内容版本，避免覆盖安装后继续使用旧缓存。原有商户配置、订单、金额、验签及付款确认逻辑保持兼容，无需数据库迁移。
