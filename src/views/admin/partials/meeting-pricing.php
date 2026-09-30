<?php
$meeting_venues = $meeting_venues ?? [];
$currencies = $currencies ?? [];
$csrf_token = $csrf_token ?? '';
$symbol_map = [];
foreach ($currencies as $curr) {
    $symbol_map[$curr['code']] = $curr['symbol'];
}
?>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="presentation" class="w-6 h-6 text-brand-green"></i>
                Meeting &amp; Event Venue Pricing
            </h2>
            <p class="text-sm text-gray-500 mt-1">Set prices guests see when booking a venue.</p>
        </div>
        <button type="button" onclick="openMeetingVenueModal()" class="inline-flex items-center px-4 py-2 rounded-lg text-white bg-brand-green font-semibold hover:brightness-110">
            <i data-lucide="plus" class="w-5 h-5 mr-1.5"></i> Add Venue
        </button>
    </div>
    <div class="p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Venue</th>
                    <th class="px-4 py-3 text-left">Prices</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($meeting_venues)): ?>
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No venues yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($meeting_venues as $venue): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900"><?= ViewHelper::e($venue['name']) ?></div>
                                <?php if (!empty($venue['capacity_note'])): ?>
                                    <div class="text-xs text-gray-500"><?= ViewHelper::e($venue['capacity_note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                <?php foreach ($currencies as $curr):
                                    $code = $curr['code'];
                                    $price = $venue['currency_prices'][$code] ?? null;
                                    if ($price === null) continue;
                                    $sym = $symbol_map[$code] ?? '';
                                ?>
                                    <div><?= ViewHelper::e($code) ?>: <?= ViewHelper::e($sym) . number_format((float)$price, 2) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?= !empty($venue['is_active']) ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' ?>">
                                    <?= !empty($venue['is_active']) ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button type="button" class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-xs font-semibold" onclick='editMeetingVenue(<?= json_encode($venue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>Edit</button>
                                <button type="button" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold" onclick="deleteMeetingVenue(<?= (int)$venue['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="meetingVenueModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 id="meetingVenueModalTitle" class="font-semibold text-lg">Add Venue</h3>
            <button type="button" onclick="closeMeetingVenueModal()" class="text-gray-500 hover:text-gray-800">&times;</button>
        </div>
        <form id="meetingVenueForm" class="p-5 space-y-4">
            <input type="hidden" name="id" id="meetingVenueId" value="">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Venue name</label>
                <input type="text" name="name" id="meetingVenueName" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g. Tiya">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Capacity / note</label>
                <input type="text" name="capacity_note" id="meetingVenueNote" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g. (40-60 Pax) - 2nd Floor">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" id="meetingVenueActive" value="1" checked>
                Active (shown in booking form)
            </label>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($currencies as $curr): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Price (<?= ViewHelper::e($curr['code']) ?>)</label>
                        <input type="number" step="0.01" min="0" name="prices[<?= ViewHelper::e($curr['code']) ?>]" class="meeting-price-input w-full border border-gray-300 rounded-lg px-3 py-2" data-currency="<?= ViewHelper::e($curr['code']) ?>" value="">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeMeetingVenueModal()" class="px-4 py-2 rounded-lg border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-brand-green text-white font-semibold">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('meetingVenueModal');
    const form = document.getElementById('meetingVenueForm');
    const csrfToken = <?= json_encode($csrf_token) ?>;
    const csrfName = <?= json_encode(CSRF_TOKEN_NAME) ?>;
    const base = window.APP_BASE_PATH || '';

    window.openMeetingVenueModal = () => {
        form.reset();
        document.getElementById('meetingVenueId').value = '';
        document.getElementById('meetingVenueActive').checked = true;
        document.getElementById('meetingVenueModalTitle').textContent = 'Add Venue';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    window.closeMeetingVenueModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };
    window.editMeetingVenue = (venue) => {
        document.getElementById('meetingVenueId').value = venue.id || '';
        document.getElementById('meetingVenueName').value = venue.name || '';
        document.getElementById('meetingVenueNote').value = venue.capacity_note || '';
        document.getElementById('meetingVenueActive').checked = !!Number(venue.is_active);
        document.getElementById('meetingVenueModalTitle').textContent = 'Edit Venue';
        document.querySelectorAll('.meeting-price-input').forEach((input) => {
            const code = input.dataset.currency;
            input.value = (venue.currency_prices && venue.currency_prices[code] != null)
                ? venue.currency_prices[code]
                : '';
        });
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    window.deleteMeetingVenue = async (id) => {
        if (!confirm('Delete this venue?')) return;
        const fd = new FormData();
        fd.append('id', id);
        fd.append(csrfName, csrfToken);
        const res = await fetch(base + '/admin/meeting-pricing/delete', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json().catch(() => null);
        if (res.ok && data?.success) location.reload();
        else alert(data?.error || 'Delete failed');
    };
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        fd.append(csrfName, csrfToken);
        if (!document.getElementById('meetingVenueActive').checked) {
            fd.delete('is_active');
        }
        const res = await fetch(base + '/admin/meeting-pricing/save', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json().catch(() => null);
        if (res.ok && data?.success) location.reload();
        else alert(data?.error || 'Save failed');
    });
})();
</script>
