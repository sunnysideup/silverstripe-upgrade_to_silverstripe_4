#!/usr/bin/env bash
set -euo pipefail

dir="${1:-.}"
needle='SS3->SS4'
target="${dir%/}/SS3-SS4"

mkdir -p "$target"

# Find files containing the text, then move them (preserving relative paths)
cd "$dir"
grep -RIl --null --exclude-dir='SS3-SS4' -- "$needle" . \
  | while IFS= read -r -d '' file; do
      rel="${file#./}"
      mkdir -p "$target/$(dirname "$rel")"
      mv -n -- "$rel" "$target/$rel"
    done
