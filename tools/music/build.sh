#!/usr/bin/env bash
# Rebuilds public/audio/*.mp3 from tools/music/synth.py. Needs python3 + numpy + ffmpeg.
set -euo pipefail
cd "$(dirname "$0")/../.."
tmp=$(mktemp -d)
python3 tools/music/synth.py "$tmp" "$@"
mkdir -p public/audio
for f in "$tmp"/*.wav; do
  ffmpeg -hide_banner -loglevel error -y -i "$f" -codec:a libmp3lame -b:a 96k -ac 2 "public/audio/$(basename "${f%.wav}").mp3"
done
rm -rf "$tmp"
ls -la public/audio
