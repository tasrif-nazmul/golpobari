"""Download the sample photos listed in sources.json into public/images/samples/<name>.webp.
Run from the repo root:  python3 tools/photos/fetch.py"""
import io, json, pathlib, urllib.request
from PIL import Image

root = pathlib.Path(__file__).resolve().parents[2]
src = json.loads((pathlib.Path(__file__).parent / 'sources.json').read_text())
out = root / 'public/images/samples'
for name, p in src['photos'].items():
    url = f"https://images.unsplash.com/photo-{p['id']}?w=1100&q=80&fm=jpg"
    img = Image.open(io.BytesIO(urllib.request.urlopen(url).read())).convert('RGB')
    img.thumbnail((1000, 1000))
    img.save(out / f'{name}.webp', 'WEBP', quality=72, method=6)
    print(name, img.size, (out / f'{name}.webp').stat().st_size // 1024, 'KB')
