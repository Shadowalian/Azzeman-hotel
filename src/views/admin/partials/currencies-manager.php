<?php
// Currencies Manager - Beautiful and matching Tailwind style
$currencies = isset($currencies) ? $currencies : [];
$basePath = isset($basePath) ? $basePath : '';
$csrf_token = isset($csrf_token) ? $csrf_token : '';
?>
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center bg-white">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i data-lucide="coins" class="w-6 h-6 mr-3 text-brand-green"></i> Currency Management
            </h2>
            <p class="text-sm text-gray-500 mt-1">Manage currencies accepted by the booking engine.</p>
        </div>
        <button
            onclick="openAddCurrencyModal()"
            class="bg-brand-green text-white font-semibold py-2.5 px-4 rounded-lg hover:brightness-110 flex items-center transition-all text-sm"
        >
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Currency
        </button>
    </div>

    <div class="p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Symbol</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="currenciesTableBody">
                <?php if (empty($currencies)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No currencies configured.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($currencies as $curr): ?>
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?= ViewHelper::e($curr['code']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono"><?= ViewHelper::e($curr['symbol']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= ViewHelper::e($curr['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($curr['is_default']): ?>
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Default
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <?php if (!$curr['is_default']): ?>
                                    <button
                                        onclick="setDefaultCurrency('<?= ViewHelper::e($curr['code']) ?>')"
                                        class="text-brand-green hover:brightness-110 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded transition-all text-xs"
                                    >
                                        Set Default
                                    </button>
                                    <button
                                        onclick="deleteCurrency('<?= ViewHelper::e($curr['code']) ?>')"
                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition-all text-xs"
                                    >
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 font-medium italic">Primary Currency</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Currency Modal -->
<div id="addCurrencyModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4" onclick="closeAddCurrencyModal()">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
        <button onclick="closeAddCurrencyModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700" aria-label="Close modal">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
        <div class="p-8">
            <h2 class="text-2xl font-bold font-sans text-brand-green mb-1">Add Currency</h2>
            <p class="text-gray-500 mb-6">Introduce a new accepted currency for room pricing.</p>

            <form id="addCurrencyForm" onsubmit="submitAddCurrency(event)" class="space-y-4">
                <div>
                    <label for="currencyCode" class="block text-sm font-semibold text-gray-700 mb-1">Currency Code (ISO)</label>
                    <input
                        type="text"
                        id="currencyCode"
                        name="code"
                        placeholder="e.g., EUR"
                        maxlength="10"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green uppercase bg-white text-gray-900"
                    />
                </div>
                <div>
                    <label for="currencySymbol" class="block text-sm font-semibold text-gray-700 mb-1">Symbol</label>
                    <input
                        type="text"
                        id="currencySymbol"
                        name="symbol"
                        placeholder="e.g., €"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green bg-white text-gray-900"
                    />
                </div>
                <div>
                    <label for="currencyName" class="block text-sm font-semibold text-gray-700 mb-1">Currency Name</label>
                    <input
                        type="text"
                        id="currencyName"
                        name="name"
                        placeholder="e.g., Euro"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green bg-white text-gray-900"
                    />
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeAddCurrencyModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="saveCurrencyBtn"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-green hover:brightness-110"
                    >
                        Add Currency
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddCurrencyModal() {
        document.getElementById('addCurrencyModal').classList.remove('hidden');
        document.getElementById('addCurrencyModal').classList.add('flex');
        document.getElementById('currencyCode').focus();
    }

    function closeAddCurrencyModal() {
        document.getElementById('addCurrencyModal').classList.remove('flex');
        document.getElementById('addCurrencyModal').classList.add('hidden');
        document.getElementById('addCurrencyForm').reset();
    }

    async function submitAddCurrency(event) {
        event.preventDefault();
        const code = document.getElementById('currencyCode').value.trim();
        const symbol = document.getElementById('currencySymbol').value.trim();
        const name = document.getElementById('currencyName').value.trim();

        if (!code || !symbol || !name) {
            showToast('All fields are required.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('code', code);
        formData.append('symbol', symbol);
        formData.append('name', name);
        formData.append(csrfTokenName, csrfToken);

        const btn = document.getElementById('saveCurrencyBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Adding...';

        try {
            const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/currencies/create', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast('Currency added successfully!', 'success');
                closeAddCurrencyModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to add currency.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (err) {
            console.error(err);
            showToast('Error occurred while adding currency.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    async function setDefaultCurrency(code) {
        if (!confirm(`Are you sure you want to set ${code} as the default currency?`)) return;

        const formData = new FormData();
        formData.append('code', code);
        formData.append(csrfTokenName, csrfToken);

        try {
            const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/currencies/set-default', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast('Default currency updated!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to update default currency.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error occurred while setting default.', 'error');
        }
    }

    async function deleteCurrency(code) {
        if (!confirm(`Are you sure you want to delete the currency ${code}? This will delete all custom prices configured for this currency.`)) return;

        const formData = new FormData();
        formData.append('code', code);
        formData.append(csrfTokenName, csrfToken);

        try {
            const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/currencies/delete', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast('Currency deleted successfully.', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to delete currency.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error occurred while deleting currency.', 'error');
        }
    }
</script>
