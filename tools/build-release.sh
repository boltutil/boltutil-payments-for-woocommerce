#!/usr/bin/env bash
set -euo pipefail

plugin_dir="$(cd "$(dirname "$0")/.." && pwd)"
project_dir="$(cd "$plugin_dir/.." && pwd)"
main_file="$plugin_dir/boltutil-woocommerce.php"
readme_file="$plugin_dir/readme.txt"
release_slug="boltutil-payments-for-woocommerce"
mode="${1:---release}"
if [[ "$mode" != "--release" && "$mode" != "--preview" ]]; then
    printf 'Usage: %s [--release|--preview]\n' "$0" >&2
    exit 1
fi

version="$(sed -n 's/^ \* Version: //p' "$main_file" | head -1)"
stable="$(sed -n 's/^Stable tag: //p' "$readme_file" | head -1)"
if [[ -z "$version" || "$version" != "$stable" ]]; then
    printf 'Plugin version and readme Stable tag must match.\n' >&2
    exit 1
fi
if [[ "$mode" == "--preview" ]]; then
    python3 "$plugin_dir/tools/check-distribution.py" --preview
else
    python3 "$plugin_dir/tools/check-distribution.py"
fi

release_dir="$(mktemp -d "${TMPDIR:-/tmp}/boltutil-wc-release.XXXXXX")"
trap 'rm -rf "$release_dir"' EXIT
mkdir -p "$release_dir/$release_slug"
cp "$main_file" "$release_dir/$release_slug/$release_slug.php"
cp "$readme_file" "$plugin_dir/LICENSE" "$plugin_dir/ASSETS-LICENSE.txt" "$release_dir/$release_slug/"
cp -R "$plugin_dir/includes" "$plugin_dir/assets" "$plugin_dir/languages" "$release_dir/$release_slug/"
# Editors can leave backup catalogs beside the real PO files. They are not
# runtime assets and Plugin Check rejects their trailing-tilde filenames.
find "$release_dir/$release_slug/languages" -type f -name '*~' -delete

# Source manifests and historical notices are development records, not runtime files.
# The concise licence notice remains in the package root; provenance is verified above.
find "$release_dir/$release_slug/assets" -type f \( -name '*sources.json' -o -name 'ASSETS-NOTICE.txt' -o -name 'checkout-standard.css' \) -delete
if grep -RIlE 'bt_live_[A-Za-z0-9_-]{20,}|whsec_[A-Za-z0-9_-]{20,}|api-test\.boltutil\.com|192\.168\.[0-9]+' "$release_dir/$release_slug" | grep -q .; then
    printf 'Potential credential or test-only origin found in release package.\n' >&2
    exit 1
fi

suffix=""
if [[ "$mode" == "--preview" ]]; then
    suffix="-preview"
fi
# Keep local packages out of the workspace root. Existing GitHub workflows
# deliberately consume the historical parent-directory path.
output_dir="${BOLTUTIL_RELEASE_DIR:-}"
if [[ -z "$output_dir" ]]; then
    if [[ "${GITHUB_ACTIONS:-false}" == "true" ]]; then
        output_dir="$project_dir"
    else
        output_dir="$project_dir/release-artifacts/wordpress/$version"
    fi
fi
mkdir -p "$output_dir"
archive="$output_dir/$release_slug-$version$suffix.zip"
temporary_archive="$release_dir/$release_slug-$version.zip"
# Fixed UTC timestamps, sorted entries and no platform-specific attributes make
# the same runtime files produce the same ZIP locally and on GitHub Actions.
export TZ=UTC
find "$release_dir/$release_slug" -exec touch -t 200001010000 {} +
( cd "$release_dir" && LC_ALL=C find "$release_slug" -print | LC_ALL=C sort | zip -X -q "$temporary_archive" -@ )
unzip -tq "$temporary_archive"
mv -f "$temporary_archive" "$archive"
shasum -a 256 "$archive"
if [[ "$mode" == "--release" && "${GITHUB_ACTIONS:-false}" != "true" ]]; then
    # Keep a local release and its sidecar in sync when rebuilding a version.
    ( cd "$output_dir" && shasum -a 256 "$release_slug-$version.zip" ) > "$output_dir/$release_slug-$version-SHA256SUMS.txt"
fi
printf 'Package: %s\n' "$archive"
