@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left"><div class="page-title"><h1>Settings</h1></div></div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right"><div class="page-title">
            <ol class="breadcrumb text-right"><li class="active">Settings</li></ol>
        </div></div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12"><div class="alert alert-success">{{ session('success') }}</div></div>
    @endif
    @if($errors->any())
        <div class="col-sm-12"><div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
    @endif

    <div class="col-lg-12">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header"><strong>Site</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Site title <span class="text-danger">*</span></label>
                            <input type="text" name="site_title" class="form-control" value="{{ old('site_title', $s['site_title'] ?? '') }}" required>
                            <small class="text-muted">Shown in the browser tab.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Tagline</label>
                            <input type="text" name="site_tagline" class="form-control" value="{{ old('site_tagline', $s['site_tagline'] ?? '') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Footer text</label>
                            <input type="text" name="footer_text" class="form-control" value="{{ old('footer_text', $s['footer_text'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Logo &amp; favicon</strong></div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $files = [
                                ['logo',      'Logo (sidebar, full)',            $logoUrl,    'image/*', 'dark'],
                                ['logo_mini', 'Small logo (collapsed sidebar)',  $miniUrl,    'image/*', 'dark'],
                                ['favicon',   'Favicon (browser tab icon)',      $faviconUrl, '.ico,image/*', 'light'],
                            ];
                        @endphp
                        @foreach($files as [$key, $label, $url, $accept, $bg])
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold">{{ $label }}</label>
                                <div class="border rounded p-3 text-center mb-2" style="background: {{ $bg === 'dark' ? '#1f2a44' : '#f8f9fa' }}">
                                    <img src="{{ $url }}" alt="{{ $label }}" id="prev_{{ $key }}" style="max-height:70px;max-width:100%">
                                </div>
                                <input type="file" name="{{ $key }}" accept="{{ $accept }}" class="form-control-file" data-preview="prev_{{ $key }}">
                                @if(!empty($s[$key]))
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input" name="remove_{{ $key }}" value="1" id="rm_{{ $key }}">
                                        <label class="form-check-label" for="rm_{{ $key }}">Remove uploaded file (use default)</label>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">PNG / JPG / SVG / WEBP up to 2 MB. Favicon: ICO or PNG up to 1 MB.</small>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><strong>Company details</strong> <small class="text-muted">(used on invoices)</small></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label class="font-weight-bold">Company name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $s['company_name'] ?? '') }}"></div>
                        <div class="col-md-6 form-group"><label class="font-weight-bold">Email</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $s['company_email'] ?? '') }}"></div>
                        <div class="col-md-6 form-group"><label class="font-weight-bold">Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $s['company_phone'] ?? '') }}"></div>
                        <div class="col-md-6 form-group"><label class="font-weight-bold">Address</label>
                            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $s['company_address'] ?? '') }}"></div>
                    </div>
                </div>
            </div>

            @can('edit-settings')
                <div class="mt-3 mb-4">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Save settings</button>
                </div>
            @endcan
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
        var f = inp.files && inp.files[0], img = document.getElementById(inp.dataset.preview);
        if (f && img && f.type.indexOf('image') === 0) img.src = URL.createObjectURL(f);
    });
});
</script>
@endsection
