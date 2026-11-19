#!/usr/bin/env python3
"""Check submission metadata, shipped translations and licensed SVG provenance."""
import gettext
import hashlib
import json
from pathlib import Path
import re
import sys
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parent.parent
preview = '--preview' in sys.argv
main = (ROOT / 'boltutil-woocommerce.php').read_text()
readme = (ROOT / 'readme.txt').read_text()
version = re.search(r'^ \* Version: (.+)$', main, re.M).group(1)
assert re.search(r'^Stable tag: (.+)$', readme, re.M).group(1) == version, 'Version/stable tag mismatch'
assert "define( 'BOLTUTIL_WC_VERSION', '" + version + "' );" in main, 'Asset version mismatch'
assert re.search(r'^Contributors: [a-zA-Z0-9_-]+', readme, re.M), 'Missing publisher username'
assert all(text in readme for text in ('https://boltutil.com/pricing', 'https://boltutil.com/privacy', 'https://boltutil.com/terms', 'external service')), 'Missing service disclosure'
strings = set()
for path in [ROOT / 'boltutil-woocommerce.php', *ROOT.glob('includes/**/*.php')]:
    source = path.read_text()
    for text in re.findall(r"__\(\s*'((?:\\.|[^'\\])*)'\s*,\s*'boltutil-payments-for-woocommerce'", source):
        strings.add(text.replace("\\'", "'").replace('\\\\', '\\'))
    assert 'Free plan: up to 30 orders' not in source, 'Stale quota promise'
catalogs = list(ROOT.glob('languages/*.mo'))
assert len(catalogs) == 11, 'Missing bundled catalog'
for path in catalogs:
    with path.open('rb') as stream:
        catalog = gettext.GNUTranslations(stream)
    missing = strings - catalog._catalog.keys()
    assert not missing, f'{path.name}: untranslated source entries {missing}'
    assert all(catalog._catalog[text] for text in strings), f'{path.name}: empty runtime translations'
    allowance = 'Daily free allowance · Unlimited daily orders with a subscription'
    assert catalog.gettext(allowance) != allowance, f'{path.name}: untranslated allowance copy'
    assert 'Free plan: up to 30 orders created per day' not in catalog._catalog, 'Stale compiled quota copy'
manifest = json.loads((ROOT / 'assets/chains/sources.json').read_text())
documented = set()
for item in manifest:
    path = ROOT / item['file']
    assert item['license'] in ('CC0-1.0', 'GPL-2.0-or-later'), 'Unconfirmed asset license'
    assert hashlib.sha256(path.read_bytes()).hexdigest() == item['sha256'], f'Asset changed: {path}'
    assert ET.parse(path).getroot().tag == '{http://www.w3.org/2000/svg}svg', 'Invalid SVG'
    documented.add(path.resolve())
checkout = json.loads((ROOT / 'assets/checkout-sources.json').read_text())
assert hashlib.sha256((ROOT / 'assets/checkout-standard.css').read_bytes()).hexdigest() == checkout['styleSha256'], 'Shared checkout standard changed'
for item in checkout['images']:
    path = ROOT / item['file']
    assert hashlib.sha256(path.read_bytes()).hexdigest() == item['sha256'], f'Original checkout artwork changed: {path}'
    if path.suffix == '.svg':
        assert ET.parse(path).getroot().tag == '{http://www.w3.org/2000/svg}svg'
    else:
        assert path.suffix == '.png' and path.read_bytes()[:8] == b'\x89PNG\r\n\x1a\n'
    documented.add(path.resolve())
assert documented == {path.resolve() for path in ROOT.glob('assets/**/*') if path.suffix in ('.svg', '.png')}, 'Undocumented artwork'
permission_path = ROOT / 'docs/ASSET_PERMISSIONS.json'
permission = json.loads(permission_path.read_text()) if permission_path.is_file() else {}
confirmed = permission.get('assets', {}) if permission.get('confirmedBy') and permission.get('confirmedOn') and 'GPL-compatible' in permission.get('scope', '') else {}
unconfirmed = [item['file'] for item in checkout['images'] if item.get('license') not in ('CC0-1.0', 'GPL-2.0-or-later') and confirmed.get(item['file']) != item['sha256']]
if not preview and unconfirmed:
    raise SystemExit('Public distribution blocked: exact website brand assets retain their original terms; confirm GPL-compatible redistribution before a WordPress.org/release build. Use --preview for a local review package.')
print(f'{"Local preview" if preview else "Distribution"} checks passed: version {version}, service policies, {len(strings)} PHP strings in 11 MO catalogs, exact checkout style and {len(checkout["images"])} source images.' + (' Public asset redistribution confirmation is pending.' if unconfirmed else ''))
