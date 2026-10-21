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

<script>
    @stack('scripts')
</script>

</body>

</html>