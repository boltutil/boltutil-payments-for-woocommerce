# v0.6.3 — Unified BoltUtil checkout

## English

The invoice payment page, Classic Checkout and Checkout Blocks now share BoltUtil Hosted Checkout's light card, dark header, typography, original token/network icons and USDT/USDC switch.

- Show a dedicated invoice card when BoltUtil is the only available payment gateway; retain other WooCommerce payment options when present.
- Clear the network choice when changing stablecoin, and preserve a valid same-coin selection through classic checkout recalculation.
- Load styles for Checkout Blocks outside the default checkout URL.
- Preserve native order permissions, terms, nonce, submit hooks and verified payment completion.
- Use the authorised invoice balance when other payment gateways remain available.
- Refresh the English and eleven translated introductions, runtime translations and six actual local WooCommerce screenshots.

Requirements: WordPress 7.0+, PHP 7.4+, WooCommerce 11.1+, HTTPS, USD store currency and a configured BoltUtil merchant account. Six networks and eleven USDT/USDC combinations are available subject to the merchant's enabled routes. BoltUtil service and blockchain fees are separate.

Validation: PHP 7.4/8.3 syntax and isolated payment/API/view fixtures, JavaScript route/Blocks/classic-refresh fixtures, translation and distribution checks, plus local WooCommerce 11.1.0 desktop/mobile interactions. No production payment or blockchain transfer was performed.

Install the versioned ZIP using WordPress → Plugins → Add New → Upload Plugin. Check SHA256SUMS.txt. GitHub's automatic source archives are not the install package.

## 简体中文

v0.6.3 统一账单付款页、经典结账和 Checkout Blocks 的卡片、字体、按钮、原始图标及 USDT／USDC 切换。切换币种后重新选择网络，经典结账重新计算时保留有效的同币种选择，并修复非默认 Blocks 地址未加载样式的问题。

WooCommerce 原生订单权限、条款、nonce、提交钩子和付款核验流程继续生效。更新十一种翻译及真实本地 WooCommerce 截图。插件要求 USD 商店和 BoltUtil 商户账号，服务费与链上费用另计。

通过 PHP 7.4／8.3 隔离回归、JavaScript、翻译、安装包和本地桌面／手机检查；没有进行生产付款或链上转账测试。
