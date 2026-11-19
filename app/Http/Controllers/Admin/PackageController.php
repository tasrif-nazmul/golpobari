<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-catalog')];
    }

    public function index()
    {
        return view('admin.packages.index', ['packages' => Package::withCount('orders')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.packages.form', ['package' => new Package(['is_active' => true, 'duration_unit' => 'month', 'duration_value' => 1, 'flags' => ['show_branding' => true], 'sort_order' => (int) Package::max('sort_order') + 1])]);
    }

    public function store(Request $request)
    {
        $p = Package::create($this->validated($request));
        ActivityLogger::log('package.created', $p, $p->name.' · ৳'.$p->price);

        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $before = (string) $package->price;
        $package->update($this->validated($request, $package));
        ActivityLogger::log('package.updated', $package, $package->name.' · ৳'.$before.' → ৳'.$package->price);

        return redirect()->route('admin.packages.index')->with('success', 'Package saved.');
    }

    public function destroy(Package $package)
    {
        if ($package->orders()->exists()) {
            return back()->with('error', 'This package has orders. Deactivate it instead.');
        }
        ActivityLogger::log('package.deleted', $package, $package->name);
        $package->delete();

        return back()->with('success', 'Package deleted.');
    }

    private function validated(Request $request, ?Package $package = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);
        $d = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'name_bn' => ['nullable', 'string', 'max:60'],
            'slug' => ['required', 'string', 'max:60', Rule::unique('packages', 'slug')->ignore($package?->id)],
            'duration_value' => ['required', 'integer', 'min:1', 'max:3650'],
            'duration_unit' => ['required', Rule::in(['day', 'month', 'year'])],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'string', 'max:2000'],
            'max_photos' => ['nullable', 'integer', 'min:0', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
        $flags = $package?->flags ?? [];
        $flags['max_photos'] = $d['max_photos'] ?? null;
        $flags['show_branding'] = $request->boolean('show_branding');
        $flags['premium_templates'] = $request->boolean('premium_templates');
        unset($d['max_photos']);

        return array_merge($d, [
            'features' => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($d['features'] ?? ''))))),
            'flags' => $flags,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) ($d['sort_order'] ?? 0),
        ]);
    }
}
