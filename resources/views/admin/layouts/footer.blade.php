</main>

    <footer class="tl-footer">
        <span>&copy; {{ date('Y') }} Techlab33 - Custom Software</span>
        <span>All rights reserved</span>
    </footer>

</div><!-- /.tl-main -->

<!-- Select2 JS -->
<script src="{{ asset('admin/vendors/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('admin/vendors/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('admin/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Layout behaviour: plain JavaScript, independent of the Bootstrap/jQuery versions --}}
<script>
(function () {
    var root = document.documentElement, body = document.body;
    var isMobile = function () { return window.matchMedia('(max-width: 991.98px)').matches; };

    // Sidebar: collapse on desktop, slide-in on mobile
    document.getElementById('tlToggle').addEventListener('click', function () {
        if (isMobile()) { body.classList.toggle('tl-open'); return; }
        var on = root.classList.toggle('tl-collapsed');
        try { localStorage.setItem('tl-collapsed', on ? '1' : '0'); } catch (e) {}
    });
    document.getElementById('tlOverlay').addEventListener('click', function () { body.classList.remove('tl-open'); });

    // User menu
    var btn = document.getElementById('tlUserBtn'), menu = document.getElementById('tlUserMenu');
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = menu.classList.toggle('show');
        btn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function (e) { if (!menu.contains(e.target)) { menu.classList.remove('show'); btn.setAttribute('aria-expanded', false); } });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { menu.classList.remove('show'); body.classList.remove('tl-open'); } });

    // Fullscreen
    document.getElementById('tlFullscreen').addEventListener('click', function () {
        if (!document.fullscreenElement) { root.requestFullscreen && root.requestFullscreen(); }
        else { document.exitFullscreen && document.exitFullscreen(); }
    });

    // Top progress bar when navigating or submitting
    var bar = document.getElementById('tlProgress');
    var start = function () { bar.classList.add('run'); };
    document.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('a[href]');
        if (!a || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
        var h = a.getAttribute('href');
        if (!h || h.charAt(0) === '#' || h.indexOf('javascript:') === 0 || a.hasAttribute('data-toggle') || a.hasAttribute('data-bs-toggle')) return;
        start();
    });
    document.addEventListener('submit', start);
    window.addEventListener('pageshow', function () { bar.classList.remove('run'); });
})();
</script>

<script>
// searchable dropdowns: customers, agents, partners, shipments (or any select with .js-search)
(function () {
    var $ = window.jQuery; if (!$ || !$.fn.select2) return;
    $(function () {
        $('select').each(function () {
            var el = this, n = (el.name || el.id || '').toLowerCase();
            if (el.multiple || $(el).hasClass('select2-hidden-accessible') || $(el).hasClass('no-search')) return;
            if (!($(el).hasClass('js-search') || /customer|agent|partner|party|shipment_id|office/.test(n))) return;
            var modal = $(el).closest('.modal');
            $(el).select2({ width: '100%', dropdownParent: modal.length ? modal : $(document.body) });
        });
    });
})();
</script>

@if(\App\Models\Setting::get('footer_text'))
<div style="text-align:center;color:#8a94a6;font-size:12px;padding:8px 0 16px">{{ \App\Models\Setting::get('footer_text') }}</div>
@endif

@php($__flash = collect(['error'=>'danger','success'=>'success','warning'=>'warning','info'=>'info'])->filter(fn($c,$k)=>session($k)))
@if($__flash->isNotEmpty() || $errors->any())
<div id="globalFlash" style="display:none;position:fixed;top:70px;right:20px;z-index:99999;max-width:420px">
    @foreach($__flash as $k=>$c)
        <div class="alert alert-{{ $c }} alert-dismissible" role="alert">{{ session($k) }}<button type="button" class="close" onclick="this.parentNode.remove()" style="margin-left:10px;background:none;border:0;font-size:20px;float:right">&times;</button></div>
    @endforeach
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul style="margin:0;padding-left:18px">@foreach(array_slice($errors->all(),0,5) as $__e)<li>{{ $__e }}</li>@endforeach</ul></div>
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var g=document.getElementById('globalFlash');if(!g)return;
    // only show the fallback toast when the page doesn't already render its own alert
    var own=document.querySelectorAll('.alert:not(#globalFlash .alert)');
    if(own.length===0){g.style.display='block';setTimeout(function(){g.style.display='none'},8000);}
});
</script>
@endif
@stack('scripts')
@yield('scripts')

</body>

</html>