<?php
// Meeting Bookings Table
$meeting_bookings = isset($meeting_bookings) ? $meeting_bookings : [];
$bookings = $meeting_bookings;
?>
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">
                        <span id="emptyMessage">No meeting or event bookings found.</span>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr class="booking-row">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= ViewHelper::e($booking['contact_name']) ?></div>
                            <div class="text-sm text-gray-500"><?= ViewHelper::e($booking['email']) ?></div>
                            <div class="text-sm text-gray-500"><?= ViewHelper::e($booking['phone_number']) ?></div>
                            <?php if (!empty($booking['company_name'])): ?>
                                <div class="text-xs text-gray-400"><?= ViewHelper::e($booking['company_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div><?= ViewHelper::e($booking['venue_name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($booking['date'])) ?>
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
                                <button onclick="updateBooking('meeting', '<?= $booking['id'] ?>', 'confirmed')" class="text-green-600 hover:text-green-900">Confirm</button>
                            <?php endif; ?>
                            <?php if ($status !== 'cancelled'): ?>
                                <button onclick="updateBooking('meeting', '<?= $booking['id'] ?>', 'cancelled')" class="text-yellow-600 hover:text-yellow-900">Cancel</button>
                            <?php endif; ?>
                            <button onclick="deleteBooking('meeting', '<?= $booking['id'] ?>')" class="text-red-600 hover:text-red-900">
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

