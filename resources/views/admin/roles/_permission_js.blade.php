{{-- Select all / deselect all / per-group mark all. Plain JS (no jQuery needed). --}}
<script>
(function () {
    function boxes(scope) { return Array.prototype.slice.call((scope || document).querySelectorAll('.permission-checkbox')); }

    function refreshGroups() {
        document.querySelectorAll('.group-permissions').forEach(function (g) {
            var b = boxes(g), on = b.filter(function (x) { return x.checked; }).length;
            var btn = document.querySelector('.toggle-group[data-group="' + g.dataset.group + '"]');
            if (btn) btn.textContent = (b.length && on === b.length) ? 'Unmark all' : 'Mark all';
            var cnt = document.querySelector('.group-count[data-group="' + g.dataset.group + '"]');
            if (cnt) cnt.textContent = on + '/' + b.length;
        });
        var all = boxes(), allOn = all.filter(function (x) { return x.checked; }).length;
        var c = document.getElementById('permTotalCount');
        if (c) c.textContent = allOn + '/' + all.length + ' selected';
    }

    function init() {
        var selAll = document.getElementById('selectAllPermissions'),
            desAll = document.getElementById('deselectAllPermissions');
        if (selAll) selAll.addEventListener('click', function () { boxes().forEach(function (x) { x.checked = true; }); refreshGroups(); });
        if (desAll) desAll.addEventListener('click', function () { boxes().forEach(function (x) { x.checked = false; }); refreshGroups(); });

        document.querySelectorAll('.toggle-group').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var g = document.querySelector('.group-permissions[data-group="' + btn.dataset.group + '"]');
                if (!g) return;
                var b = boxes(g), allOn = b.length && b.every(function (x) { return x.checked; });
                b.forEach(function (x) { x.checked = !allOn; });
                refreshGroups();
            });
        });
        boxes().forEach(function (x) { x.addEventListener('change', refreshGroups); });
        refreshGroups();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
