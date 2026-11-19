<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PageMedia;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Re-encodes every uploaded image with GD. The original bytes are never kept,
 * so anything hidden inside a "photo" (scripts, polyglots, EXIF/GPS) is discarded.
 */
class ImageService
{
    private const MAX_PIXELS = 50_000_000; // refuse decompression bombs

    public function disk()
    {
        return Storage::disk('uploads');
    }

    /** @return array{path:string,thumb:string,width:int,height:int,size:int,mime:string} */
    public function process(UploadedFile $file, string $dir, bool $thumb = true, ?int $maxDim = null): array
    {
        $info = @getimagesize($file->getRealPath());
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('এই ফাইলটি সঠিক ছবি নয়। JPG, PNG বা WebP দিন।');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new RuntimeException('ছবিটি অনেক বড় (pixel)। একটু ছোট ছবি দিন।');
        }

        @ini_set('memory_limit', '512M');

        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file->getRealPath()),
        };
        if (! $img instanceof GdImage) {
            throw new RuntimeException('ছবিটি পড়া যাচ্ছে না। অন্য একটি ছবি দিন।');
        }

        if ($info[2] === IMAGETYPE_JPEG) {
            $img = $this->applyExifOrientation($img, $file->getRealPath());
        }
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $max = $maxDim ?? (int) config('golpobari.images.max_dimension', 1920);
        $full = $this->resize($img, $max);
        $name = Str::random(32);
        $path = trim($dir, '/').'/'.$name.'.webp';
        $this->disk()->put($path, $this->encode($full));
        $width = imagesx($full);
        $height = imagesy($full);

        $thumbPath = $path;
        if ($thumb) {
            $t = $this->resize($full, (int) config('golpobari.images.thumb_dimension', 480));
            $thumbPath = trim($dir, '/').'/'.$name.'_thumb.webp';
            $this->disk()->put($thumbPath, $this->encode($t, 78));
            if ($t !== $full) {
                imagedestroy($t);
            }
        }
        if ($full !== $img) {
            imagedestroy($full);
        }
        imagedestroy($img);

        return ['path' => $path, 'thumb' => $thumbPath, 'width' => $width, 'height' => $height, 'size' => $this->disk()->size($path), 'mime' => 'image/webp'];
    }

    public function storeDraft(UploadedFile $file, string $draftToken, string $fieldKey, int $sort = 0): PageMedia
    {
        $r = $this->process($file, 'drafts/'.$draftToken);

        return PageMedia::create([
            'draft_token' => $draftToken,
            'field_key' => $fieldKey,
            'path' => $r['path'],
            'thumb_path' => $r['thumb'],
            'mime' => $r['mime'],
            'original_name' => Str::limit($this->safeName($file->getClientOriginalName()), 120, ''),
            'size' => $r['size'],
            'width' => $r['width'],
            'height' => $r['height'],
            'sort_order' => $sort,
        ]);
    }

    /** Admin adds a photo to an order/page directly. */
    public function storeForOrder(UploadedFile $file, Order $order, string $fieldKey, ?int $pageId = null): PageMedia
    {
        $r = $this->process($file, 'orders/'.$order->id.'/'.$this->safeKey($fieldKey));
        $sort = (int) PageMedia::where('order_id', $order->id)->where('field_key', $fieldKey)->max('sort_order') + 1;

        return PageMedia::create([
            'order_id' => $order->id, 'page_id' => $pageId, 'field_key' => $fieldKey,
            'path' => $r['path'], 'thumb_path' => $r['thumb'], 'mime' => $r['mime'],
            'original_name' => Str::limit($this->safeName($file->getClientOriginalName()), 120, ''),
            'size' => $r['size'], 'width' => $r['width'], 'height' => $r['height'], 'sort_order' => $sort,
        ]);
    }

    /** Moves a submitted order's draft files to uploads/orders/{id}/{field}/. Best effort. */
    public function moveToOrderFolder(Order $order): void
    {
        foreach (PageMedia::where('order_id', $order->id)->get() as $m) {
            if (! str_starts_with($m->path, 'drafts/')) {
                continue;
            }
            $dir = 'orders/'.$order->id.'/'.$this->safeKey($m->field_key);
            $new = $dir.'/'.basename($m->path);
            $newThumb = $m->thumb_path ? $dir.'/'.basename($m->thumb_path) : null;
            try {
                $this->disk()->move($m->path, $new);
                if ($m->thumb_path && $m->thumb_path !== $m->path && $this->disk()->exists($m->thumb_path)) {
                    $this->disk()->move($m->thumb_path, $newThumb);
                } else {
                    $newThumb = $new;
                }
                $m->forceFill(['path' => $new, 'thumb_path' => $newThumb])->save();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function delete(PageMedia $media): void
    {
        // Duplicated pages share files, so only remove files nobody else points at.
        $shared = PageMedia::where('id', '!=', $media->id)->where('path', $media->path)->exists();
        if (! $shared) {
            $this->disk()->delete(array_unique(array_filter([$media->path, $media->thumb_path])));
        }
        $media->delete();
    }

    /** Admin site assets (logo, icons, previews, OG images) on the public disk. */
    public function storePublic(UploadedFile $file, string $dir, int $maxDim = 1600): string
    {
        $tmp = $this->process($file, 'tmp-public', false, $maxDim);
        $target = trim($dir, '/').'/'.basename($tmp['path']);
        Storage::disk('public')->put($target, $this->disk()->get($tmp['path']));
        $this->disk()->delete($tmp['path']);

        return $target;
    }

    private function resize(GdImage $img, int $max): GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= $max && $h <= $max) {
            return $img;
        }
        $ratio = min($max / $w, $max / $h);
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return $dst;
    }

    private function encode(GdImage $img, ?int $quality = null): string
    {
        ob_start();
        imagewebp($img, null, $quality ?? (int) config('golpobari.images.quality', 82));

        return (string) ob_get_clean();
    }

    private function applyExifOrientation(GdImage $img, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($path);
        $o = (int) ($exif['Orientation'] ?? 1);
        $rotated = match ($o) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => null,
        };
        if ($rotated instanceof GdImage) {
            imagedestroy($img);

            return $rotated;
        }

        return $img;
    }

    private function safeName(string $name): string
    {
        return preg_replace('/[^\pL\pN._ -]/u', '', $name) ?: 'image';
    }

    private function safeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_]/', '', strtolower($key)) ?: 'misc';
    }
}
