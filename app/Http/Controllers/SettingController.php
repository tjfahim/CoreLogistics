<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        abort_unless(Gate::allows('view-settings'), 403);

        $s = Setting::all_();

        return view('admin.settings.index', [
            's'          => $s,
            'logoUrl'    => Setting::assetUrl('logo', 'admin/images/logo.png'),
            'miniUrl'    => Setting::assetUrl('logo_mini', 'admin/images/logo2.png'),
            'faviconUrl' => Setting::assetUrl('favicon', 'favicon.ico'),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Gate::allows('edit-settings'), 403);

        $data = $request->validate([
            'site_title'      => 'required|string|max:150',
            'site_tagline'    => 'nullable|string|max:150',
            'company_name'    => 'nullable|string|max:150',
            'company_address' => 'nullable|string|max:300',
            'company_phone'   => 'nullable|string|max:60',
            'company_email'   => 'nullable|email|max:150',
            'footer_text'     => 'nullable|string|max:300',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logo_mini'       => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon'         => 'nullable|file|mimes:ico,png,jpg,jpeg,svg|max:1024',
        ], [
            'logo.image'     => 'The logo must be an image (png, jpg, svg, webp).',
            'logo_mini.image'=> 'The small logo must be an image (png, jpg, svg, webp).',
            'favicon.mimes'  => 'The favicon must be an ico, png, jpg or svg file.',
        ]);

        foreach (['site_title', 'site_tagline', 'company_name', 'company_address', 'company_phone', 'company_email', 'footer_text'] as $k) {
            Setting::set($k, $data[$k] ?? null);
        }

        foreach (Setting::FILE_KEYS as $k) {
            // remove current file
            if ($request->boolean('remove_' . $k)) {
                $this->deleteFile(Setting::get($k));
                Setting::set($k, null);
            }
            if ($request->hasFile($k)) {
                $this->deleteFile(Setting::get($k));
                $path = $request->file($k)->store('settings', 'public');
                Setting::set($k, $path);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
