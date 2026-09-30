<?php
// Room Bookings Table
$room_bookings = isset($room_bookings) ? $room_bookings : [];
$bookings = $room_bookings;
?>
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room & Guests</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price Details</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">
                        <span id="emptyMessage">No room bookings found.</span>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr class="booking-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= ViewHelper::e($booking['guest_name']) ?></div>
                            <div class="text-sm text-gray-500"><?= ViewHelper::e($booking['email']) ?></div>
                            <div class="text-sm text-gray-500"><?= ViewHelper::e($booking['phone_number']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($booking['check_in_date'])) ?> - <?= date('M d, Y', strtotime($booking['check_out_date'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= ViewHelper::e($booking['room_type']) ?></div>
                            <div class="text-xs text-gray-500 font-semibold mt-0.5">
                                <?= intval($booking['rooms_count'] ?? 1) ?> Room<?= intval($booking['rooms_count'] ?? 1) > 1 ? 's' : '' ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= intval($booking['adults_count'] ?? $booking['number_of_guests'] ?? 1) ?> Adult<?= intval($booking['adults_count'] ?? $booking['number_of_guests'] ?? 1) > 1 ? 's' : '' ?>,
                                <?= intval($booking['children_count'] ?? 0) ?> Child<?= intval($booking['children_count'] ?? 0) > 1 ? 'ren' : '' ?>
                            </div>
                            <?php if (!empty($booking['room_details'])): ?>
                                <div class="text-[10px] text-gray-400 mt-1 max-w-[200px] truncate" title="<?= ViewHelper::e($booking['room_details']) ?>">
                                    <?= ViewHelper::e($booking['room_details']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (isset($booking['total_price']) && $booking['total_price'] !== null): ?>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php
                                    $curr = $booking['currency'] ?: 'USD';
                                    // Build symbol map from $currencies if available, else use known fallbacks
                                    $symbolMap = [];
                                    if (!empty($currencies) && is_array($currencies)) {
                                        foreach ($currencies as $c) {
                                            $symbolMap[$c['code']] = $c['symbol'];
                                        }
                                    }
                                    $knownSymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ETB' => 'Br '];
                                    $symbol = $symbolMap[$curr] ?? $knownSymbols[$curr] ?? '';
                                    echo $symbol . number_format($booking['total_price'], 2) . ' ' . $curr;
                                    ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Rate: <?= $symbol . number_format($booking['price_per_night'], 2) ?>/nt
                                </div>
                                <?php if (isset($booking['discount_amount']) && floatval($booking['discount_amount']) > 0): ?>
                                    <div class="text-xs text-green-600 font-medium">
                                        Discount: -<?= $symbol . number_format($booking['discount_amount'], 2) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php
                            $status = $booking['status'] ?? 'pending';
                            $statusClasses = [
                                'confirmed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $statusClass = $statusClasses[$status] ?? $statusClasses['pending'];
                            $statusIcon = [
                                'confirmed' => 'check-circle',
                                'cancelled' => 'x-circle',
                                'pending' => 'clock',
                            ][$status] ?? 'clock';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1 <?= $statusClass ?>">
                                <i data-lucide="<?= $statusIcon ?>" class="w-3 h-3"></i>
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <?php if ($status === 'pending'): ?>
                                <button onclick="updateBooking('room', '<?= $booking['id'] ?>', 'confirmed')" class="text-green-600 hover:text-green-900">Confirm</button>
                            <?php endif; ?>
                            <?php if ($status !== 'cancelled'): ?>
                                <button onclick="updateBooking('room', '<?= $booking['id'] ?>', 'cancelled')" class="text-yellow-600 hover:text-yellow-900">Cancel</button>
                            <?php endif; ?>
                            <button onclick="deleteBooking('room', '<?= $booking['id'] ?>')" class="text-red-600 hover:text-red-900">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

