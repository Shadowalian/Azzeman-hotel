<?php
// Room Pricing CMS Partial - Beautiful and multi-currency enabled
$rooms = isset($rooms) ? $rooms : [];
$currencies = isset($currencies) ? $currencies : [];
$basePath = isset($basePath) ? $basePath : '';
$csrf_token = isset($csrf_token) ? $csrf_token : '';

// Identify default currency
$default_currency_code = 'USD';
foreach ($currencies as $curr) {
    if ($curr['is_default']) {
        $default_currency_code = $curr['code'];
        break;
    }
}

$symbol_map = [];
foreach ($currencies as $curr) {
    $symbol_map[$curr['code']] = $curr['symbol'];
}
?>
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i data-lucide="dollar-sign" class="w-6 h-6 mr-3 text-brand-green"></i> Room Configurations &amp; Pricing
            </h2>
            <p class="text-sm text-gray-500 mt-1">Configure room types, max capacity, base prices, and per-guest pricing tiers by currency.</p>
        </div>
        <button
            onclick="openAddRoomModal()"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-lg text-white bg-brand-green hover:brightness-110 shadow-sm focus:outline-none transition-colors"
        >
            <i data-lucide="plus" class="w-5 h-5 mr-1.5"></i> Add Room Type
        </button>
    </div>
    
    <div class="p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room Type &amp; Details</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rates (Default: <?= ViewHelper::e($default_currency_code) ?>)</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Accepted Currency Rates</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Max Guests</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            No room configurations found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr class="room-pricing-row hover:bg-stone-50 transition-colors" id="room-row-<?= $room['id'] ?>">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900"><?= ViewHelper::e($room['type']) ?></div>
                                <div class="text-xs text-gray-500 mt-0.5">Quantity: <?= intval($room['quantity']) ?> | Booked: <?= intval($room['booked']) ?></div>
                                <div class="text-xs text-gray-400 mt-1 max-w-md truncate" title="<?= ViewHelper::e($room['facilities']) ?>">
                                    Facilities: <?= ViewHelper::e($room['facilities']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $cPrices = $room['currency_prices'] ?? [];
                                $defaultRates = $cPrices[$default_currency_code] ?? [
                                    'adult' => $room['price_per_adult'],
                                    'additional_adult' => $room['price_additional_adult'],
                                    'child' => $room['price_per_child']
                                ];
                                $defSym = $symbol_map[$default_currency_code] ?? '';
                                ?>
                                <div class="text-xs text-gray-700 space-y-1">
                                    <div><span class="font-semibold text-gray-500">1st Adult:</span> <?= $defSym . number_format($defaultRates['adult'], 2) ?></div>
                                    <div><span class="font-semibold text-gray-500">Extra Adult:</span> <?= $defSym . number_format($defaultRates['additional_adult'], 2) ?></div>
                                    <div><span class="font-semibold text-gray-500">Child:</span> <?= $defSym . number_format($defaultRates['child'], 2) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-700 space-y-1 max-w-xs">
                                    <?php foreach ($cPrices as $cCode => $rates): ?>
                                        <?php if ($cCode !== $default_currency_code): ?>
                                            <?php $sym = $symbol_map[$cCode] ?? ''; ?>
                                            <div>
                                                <span class="font-bold text-gray-600"><?= ViewHelper::e($cCode) ?>:</span>
                                                <span>A: <?= $sym . number_format($rates['adult'], 1) ?></span> |
                                                <span>+A: <?= $sym . number_format($rates['additional_adult'], 1) ?></span> |
                                                <span>C: <?= $sym . number_format($rates['child'], 1) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i data-lucide="users" class="w-3 h-3 mr-1"></i>
                                    <?= intval($room['max_guests'] ?? 2) ?> max
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    onclick='openEditRoomModal(<?= json_encode($room) ?>)'
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-semibold rounded text-gray-700 bg-white hover:bg-gray-50 shadow-sm focus:outline-none transition-colors"
                                    title="Edit Details &amp; Pricing"
                                >
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5 mr-1"></i> Edit
                                </button>
                                <button
                                    onclick="deleteRoomType(<?= $room['id'] ?>, '<?= ViewHelper::e($room['type']) ?>')"
                                    id="delete-btn-<?= $room['id'] ?>"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded text-white bg-red-600 hover:bg-red-700 shadow-sm focus:outline-none transition-colors"
                                    title="Delete Room Type"
                                >
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Room Modal -->
<div id="addRoomModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeAddRoomModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i data-lucide="plus-circle" class="w-5 h-5 mr-2 text-brand-green"></i> Add New Room Type
                </h3>
                <button onclick="closeAddRoomModal()" class="text-gray-400 hover:text-gray-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="addRoomForm" onsubmit="submitAddRoom(event)" class="px-6 py-4 space-y-4 max-h-[80vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Room Type Name</label>
                        <input type="text" name="type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900" placeholder="e.g. Deluxe Suite">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Quantity</label>
                        <input type="number" name="quantity" min="0" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900" placeholder="10">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Base Currency</label>
                        <select name="currency" id="addRoomCurrencySelect" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                            <?php foreach ($currencies as $curr): ?>
                                <option value="<?= ViewHelper::e($curr['code']) ?>"><?= ViewHelper::e($curr['name']) ?> (<?= ViewHelper::e($curr['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Max Guests</label>
                        <input type="number" name="max_guests" min="1" max="20" value="2" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Facilities (comma-separated)</label>
                    <textarea name="facilities" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900" placeholder="e.g. King size bed, Mini bar, writing table"></textarea>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold text-sm text-brand-green mb-3 flex items-center">
                        <i data-lucide="coins" class="w-4 h-4 mr-1.5"></i> Pricing by Currency
                    </h4>
                    <div class="space-y-4">
                        <?php foreach ($currencies as $curr): ?>
                            <?php 
                            $code = $curr['code'];
                            $symbol = $curr['symbol'];
                            $name = $curr['name'];
                            ?>
                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="font-bold text-xs text-brand-gold mb-2 flex justify-between items-center">
                                    <span><?= ViewHelper::e($name) ?> (<?= ViewHelper::e($code) ?> - <?= ViewHelper::e($symbol) ?>)</span>
                                    <?php if ($curr['is_default']): ?>
                                        <span class="text-[10px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-semibold">Base / Default</span>
                                    <?php endif; ?>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">1st Adult Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="adult"
                                                class="add-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="100.00"
                                                value="100.00"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">Extra Adult Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="additional_adult"
                                                class="add-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="50.00"
                                                value="50.00"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">Child Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="child"
                                                class="add-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="40.00"
                                                value="40.00"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="pt-4 border-t flex justify-end space-x-3">
                    <button type="button" onclick="closeAddRoomModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">Cancel</button>
                    <button type="submit" id="addRoomSubmitBtn" class="px-4 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-brand-green hover:brightness-110 focus:outline-none transition-colors flex items-center">
                        Save Room Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div id="editRoomModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeEditRoomModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i data-lucide="edit" class="w-5 h-5 mr-2 text-brand-green"></i> Edit Room Type
                </h3>
                <button onclick="closeEditRoomModal()" class="text-gray-400 hover:text-gray-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="editRoomForm" onsubmit="submitEditRoom(event)" class="px-6 py-4 space-y-4 max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="id" id="editRoomId">
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Room Type Name</label>
                        <input type="text" name="type" id="editRoomType" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="editRoomQuantity" min="0" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Base Currency</label>
                        <select name="currency" id="editRoomCurrency" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                            <?php foreach ($currencies as $curr): ?>
                                <option value="<?= ViewHelper::e($curr['code']) ?>"><?= ViewHelper::e($curr['name']) ?> (<?= ViewHelper::e($curr['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Max Guests</label>
                        <input type="number" name="max_guests" id="editRoomMaxGuests" min="1" max="20" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Facilities (comma-separated)</label>
                    <textarea name="facilities" id="editRoomFacilities" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold text-sm bg-white text-gray-900"></textarea>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold text-sm text-brand-green mb-3 flex items-center">
                        <i data-lucide="coins" class="w-4 h-4 mr-1.5"></i> Pricing by Currency
                    </h4>
                    <div class="space-y-4">
                        <?php foreach ($currencies as $curr): ?>
                            <?php 
                            $code = $curr['code'];
                            $symbol = $curr['symbol'];
                            $name = $curr['name'];
                            ?>
                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="font-bold text-xs text-brand-gold mb-2 flex justify-between items-center">
                                    <span><?= ViewHelper::e($name) ?> (<?= ViewHelper::e($code) ?> - <?= ViewHelper::e($symbol) ?>)</span>
                                    <?php if ($curr['is_default']): ?>
                                        <span class="text-[10px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-semibold">Base / Default</span>
                                    <?php endif; ?>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">1st Adult Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="adult"
                                                class="edit-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="100.00"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">Extra Adult Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="additional_adult"
                                                class="edit-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="50.00"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500">Child Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-xs font-mono"><?= ViewHelper::e($symbol) ?></span>
                                            </div>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                data-currency="<?= ViewHelper::e($code) ?>"
                                                data-rate-type="child"
                                                class="edit-currency-price-input block w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-green focus:border-brand-green text-xs bg-white text-gray-900"
                                                placeholder="40.00"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="pt-4 border-t flex justify-end space-x-3">
                    <button type="button" onclick="closeEditRoomModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">Cancel</button>
                    <button type="submit" id="editRoomSubmitBtn" class="px-4 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-brand-green hover:brightness-110 focus:outline-none transition-colors flex items-center">
                        Update Room Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Modal Control functions
function openAddRoomModal() {
    const modal = document.getElementById('addRoomModal');
    modal.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Close Add Room Modal
function closeAddRoomModal() {
    const modal = document.getElementById('addRoomModal');
    modal.classList.add('hidden');
    document.getElementById('addRoomForm').reset();
}

function openEditRoomModal(room) {
    document.getElementById('editRoomId').value = room.id;
    document.getElementById('editRoomType').value = room.type;
    document.getElementById('editRoomQuantity').value = room.quantity;
    document.getElementById('editRoomMaxGuests').value = room.max_guests || 2;
    document.getElementById('editRoomCurrency').value = room.currency || 'USD';
    document.getElementById('editRoomFacilities').value = room.facilities || '';
    
    // Populate currency pricing inputs for edit
    document.querySelectorAll('.edit-currency-price-input').forEach(input => {
        const currency = input.getAttribute('data-currency');
        const rateType = input.getAttribute('data-rate-type');
        let val = '';
        if (room.currency_prices && room.currency_prices[currency]) {
            val = room.currency_prices[currency][rateType];
        }
        if (val === undefined || val === '') {
            if (currency === room.currency) {
                if (rateType === 'adult') val = room.price_per_adult;
                else if (rateType === 'additional_adult') val = room.price_additional_adult;
                else if (rateType === 'child') val = room.price_per_child;
            } else {
                val = '0.00';
            }
        }
        input.value = parseFloat(val || 0).toFixed(2);
    });
    
    const modal = document.getElementById('editRoomModal');
    modal.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeEditRoomModal() {
    const modal = document.getElementById('editRoomModal');
    modal.classList.add('hidden');
    document.getElementById('editRoomForm').reset();
}

// Submit new Room Type
async function submitAddRoom(e) {
    e.preventDefault();
    const form = document.getElementById('addRoomForm');
    const submitBtn = document.getElementById('addRoomSubmitBtn');
    const originalHtml = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-1.5 animate-spin"></i>Saving...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    const formData = new FormData(form);
    
    // Parse currency rates
    const baseCurrency = document.getElementById('addRoomCurrencySelect').value;
    const currencyPrices = {};
    let baseAdult = 0, baseAdditionalAdult = 0, baseChild = 0;
    
    document.querySelectorAll('.add-currency-price-input').forEach(input => {
        const currency = input.getAttribute('data-currency');
        const rateType = input.getAttribute('data-rate-type');
        const val = parseFloat(input.value) || 0;
        
        if (!currencyPrices[currency]) {
            currencyPrices[currency] = {};
        }
        currencyPrices[currency][rateType] = val;
        
        if (currency === baseCurrency) {
            if (rateType === 'adult') baseAdult = val;
            if (rateType === 'additional_adult') baseAdditionalAdult = val;
            if (rateType === 'child') baseChild = val;
        }
    });
    
    formData.append('currency_prices', JSON.stringify(currencyPrices));
    formData.append('price_per_adult', baseAdult);
    formData.append('price_additional_adult', baseAdditionalAdult);
    formData.append('price_per_child', baseChild);
    formData.append(csrfTokenName, csrfToken);
    
    try {
        const response = await fetch(`${window.APP_BASE_PATH || ''}/admin/room-pricing/create`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (response.ok && data.success) {
            showToast('Room type created successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to create room type.', 'error');
        }
    } catch (error) {
        console.error('Create room error:', error);
        showToast('An error occurred.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// Submit Edited details
async function submitEditRoom(e) {
    e.preventDefault();
    const form = document.getElementById('editRoomForm');
    const submitBtn = document.getElementById('editRoomSubmitBtn');
    const originalHtml = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-1.5 animate-spin"></i>Updating...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    const formData = new FormData(form);
    
    const baseCurrency = document.getElementById('editRoomCurrency').value;
    const currencyPrices = {};
    let baseAdult = 0, baseAdditionalAdult = 0, baseChild = 0;
    
    document.querySelectorAll('.edit-currency-price-input').forEach(input => {
        const currency = input.getAttribute('data-currency');
        const rateType = input.getAttribute('data-rate-type');
        const val = parseFloat(input.value) || 0;
        
        if (!currencyPrices[currency]) {
            currencyPrices[currency] = {};
        }
        currencyPrices[currency][rateType] = val;
        
        if (currency === baseCurrency) {
            if (rateType === 'adult') baseAdult = val;
            if (rateType === 'additional_adult') baseAdditionalAdult = val;
            if (rateType === 'child') baseChild = val;
        }
    });
    
    formData.append('currency_prices', JSON.stringify(currencyPrices));
    formData.append('price_per_adult', baseAdult);
    formData.append('price_additional_adult', baseAdditionalAdult);
    formData.append('price_per_child', baseChild);
    formData.append(csrfTokenName, csrfToken);
    
    try {
        const response = await fetch(`${window.APP_BASE_PATH || ''}/admin/room-pricing/update`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (response.ok && data.success) {
            showToast('Room updated successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to update room.', 'error');
        }
    } catch (error) {
        console.error('Update details error:', error);
        showToast('An error occurred.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// Delete Room Type
async function deleteRoomType(roomId, roomTypeName) {
    if (!confirm(`Are you sure you want to delete the room type "${roomTypeName}"? This cannot be undone.`)) {
        return;
    }
    
    const deleteBtn = document.getElementById(`delete-btn-${roomId}`);
    const originalHtml = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 mr-1 animate-spin"></i>Deleting...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    const formData = new FormData();
    formData.append('id', roomId);
    formData.append(csrfTokenName, csrfToken);
    
    try {
        const response = await fetch(`${window.APP_BASE_PATH || ''}/admin/room-pricing/delete`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (response.ok && data.success) {
            showToast('Room type deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete room type.', 'error');
        }
    } catch (error) {
        console.error('Delete room type error:', error);
        showToast('An error occurred.', 'error');
    } finally {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalHtml;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}
</script>
