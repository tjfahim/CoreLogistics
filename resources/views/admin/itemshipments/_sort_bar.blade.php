{{-- Sort bar (server side, no <form> so it can sit inside other forms): ?sort=customer|goods|mark|ctn|id&dir=asc|desc --}}
<div class="form-inline mb-2 no-print">
    <label class="mr-2 font-weight-bold"><i class="fa fa-sort"></i> Sort by:</label>
    <select id="sortKey" class="form-control form-control-sm mr-2">
        <option value="">Default</option>
        <option value="customer" {{ request('sort') === 'customer' ? 'selected' : '' }}>Customer name</option>
        <option value="goods" {{ request('sort') === 'goods' ? 'selected' : '' }}>Goods name</option>
        <option value="mark" {{ request('sort') === 'mark' ? 'selected' : '' }}>Shipping mark</option>
        <option value="ctn" {{ request('sort') === 'ctn' ? 'selected' : '' }}>Printed CTN</option>
        <option value="id" {{ request('sort') === 'id' ? 'selected' : '' }}>ID</option>
    </select>
    <select id="sortDirKey" class="form-control form-control-sm">
        <option value="asc" {{ request('dir') !== 'desc' ? 'selected' : '' }}>A &rarr; Z / 1 &rarr; 9</option>
        <option value="desc" {{ request('dir') === 'desc' ? 'selected' : '' }}>Z &rarr; A / 9 &rarr; 1</option>
    </select>
</div>
<script>
(function () {
    function go() {
        var u = new URL(window.location.href);
        var k = document.getElementById('sortKey').value, d = document.getElementById('sortDirKey').value;
        if (k) { u.searchParams.set('sort', k); u.searchParams.set('dir', d); } else { u.searchParams.delete('sort'); u.searchParams.delete('dir'); }
        window.location.href = u.toString();
    }
    document.getElementById('sortKey').addEventListener('change', go);
    document.getElementById('sortDirKey').addEventListener('change', go);
})();
</script>
