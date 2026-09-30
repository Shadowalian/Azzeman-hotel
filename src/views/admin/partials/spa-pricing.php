<?php
$spa_services = $spa_services ?? [];
$currencies = $currencies ?? [];
$csrf_token = $csrf_token ?? '';
$default_currency_code = 'USD';
foreach ($currencies as $curr) {
    if (!empty($curr['is_default'])) {
        $default_currency_code = $curr['code'];
        break;
    }
}
$symbol_map = [];
foreach ($currencies as $curr) {
    $symbol_map[$curr['code']] = $curr['symbol'];
}
?>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="heart-pulse" class="w-6 h-6 text-brand-green"></i>
                Spa Services &amp; Pricing
            </h2>
            <p class="text-sm text-gray-500 mt-1">Set prices guests see when booking a spa treatment.</p>
        </div>
        <button type="button" onclick="openSpaServiceModal()" class="inline-flex items-center px-4 py-2 rounded-lg text-white bg-brand-green font-semibold hover:brightness-110">
            <i data-lucide="plus" class="w-5 h-5 mr-1.5"></i> Add Service
        </button>
    </div>
    <div class="p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Service</th>
                    <th class="px-4 py-3 text-left">Prices</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($spa_services)): ?>
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No spa services yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($spa_services as $service): ?>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900"><?= ViewHelper::e($service['name']) ?></td>
                            <td class="px-4 py-3 text-gray-700">
                                <?php foreach ($currencies as $curr):
                                    $code = $curr['code'];
                                    $price = $service['currency_prices'][$code] ?? null;
                                    if ($price === null) continue;
                                    $sym = $symbol_map[$code] ?? '';
                                ?>
                                    <div><?= ViewHelper::e($code) ?>: <?= ViewHelper::e($sym) . number_format((float)$price, 2) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?= !empty($service['is_active']) ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' ?>">
                                    <?= !empty($service['is_active']) ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button type="button" class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-xs font-semibold" onclick='editSpaService(<?= json_encode($service, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>Edit</button>
                                <button type="button" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold" onclick="deleteSpaService(<?= (int)$service['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="spaServiceModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 id="spaServiceModalTitle" class="font-semibold text-lg">Add Spa Service</h3>
            <button type="button" onclick="closeSpaServiceModal()" class="text-gray-500 hover:text-gray-800">&times;</button>
        </div>
        <form id="spaServiceForm" class="p-5 space-y-4">
            <input type="hidden" name="id" id="spaServiceId" value="">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Service name</label>
                <input type="text" name="name" id="spaServiceName" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g. Swedish Massage">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" id="spaServiceActive" value="1" checked>
                Active (shown in booking form)
            </label>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($currencies as $curr): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Price (<?= ViewHelper::e($curr['code']) ?>)</label>
                        <input type="number" step="0.01" min="0" name="prices[<?= ViewHelper::e($curr['code']) ?>]" class="spa-price-input w-full border border-gray-300 rounded-lg px-3 py-2" data-currency="<?= ViewHelper::e($curr['code']) ?>" value="">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSpaServiceModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-brand-green text-white font-semibold">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('spaServiceModal');
    const form = document.getElementById('spaServiceForm');
    const csrfToken = <?= json_encode($csrf_token) ?>;
    const csrfName = <?= json_encode(CSRF_TOKEN_NAME) ?>;
    const base = window.APP_BASE_PATH || '';

    window.openSpaServiceModal = () => {
        form.reset();
        document.getElementById('spaServiceId').value = '';
        document.getElementById('spaServiceActive').checked = true;
        document.getElementById('spaServiceModalTitle').textContent = 'Add Spa Service';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    window.closeSpaServiceModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };
    window.editSpaService = (service) => {
        document.getElementById('spaServiceId').value = service.id || '';
        document.getElementById('spaServiceName').value = service.name || '';
        document.getElementById('spaServiceActive').checked = !!Number(service.is_active);
        document.getElementById('spaServiceModalTitle').textContent = 'Edit Spa Service';
        document.querySelectorAll('.spa-price-input').forEach((input) => {
            const code = input.dataset.currency;
            input.value = (service.currency_prices && service.currency_prices[code] != null)
                ? service.currency_prices[code]
                : '';
        });
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    window.deleteSpaService = async (id) => {
        if (!confirm('Delete this spa service?')) return;
        const fd = new FormData();
        fd.append('id', id);
        fd.append(csrfName, csrfToken);
        const res = await fetch(base + '/admin/spa-pricing/delete', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json().catch(() => null);
        if (res.ok && data?.success) location.reload();
        else alert(data?.error || 'Delete failed');
    };
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        fd.append(csrfName, csrfToken);
        if (!document.getElementById('spaServiceActive').checked) {
            fd.delete('is_active');
        }
        const res = await fetch(base + '/admin/spa-pricing/save', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json().catch(() => null);
        if (res.ok && data?.success) location.reload();
        else alert(data?.error || 'Save failed');
    });
})();
</script>
