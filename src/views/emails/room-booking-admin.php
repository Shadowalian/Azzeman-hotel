<?php
/**
 * Room Booking Admin Notification Email Template
 * 
 * This template is sent to the admin when a new room booking is made.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Room Booking Request</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fafaf9;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0E8040; padding: 30px 40px; text-align: center;">
                            <?php if (!empty($logoUrl)): ?>
                                <div style="display: inline-block; background: radial-gradient(circle, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0.2) 100%); background-color: rgba(255, 255, 255, 0.4); padding: 12px 28px; border-radius: 9999px; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); margin-bottom: 15px; vertical-align: middle;">
                                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Azzeman Hotel Logo" width="140" style="max-width: 140px; height: auto; display: block;" />
                                </div>
                            <?php endif; ?>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Azzeman Hotel</h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">New Room Booking Request</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px; background-color: #ffffff;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 22px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Booking Details</h2>
                            
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9; border-left: 4px solid #0E8040; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Booking ID:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['id']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Guest Name:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['guestName']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Email:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['email']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Phone:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['phoneNumber']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Check-in Date:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars(date('F j, Y', strtotime($booking['checkInDate']))) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Check-out Date:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars(date('F j, Y', strtotime($booking['checkOutDate']))) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Room Type:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['roomType']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Rooms Booked:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['roomsCount'] ?? $booking['rooms_count'] ?? '1') ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Guest Breakdown:</strong> 
                                        <span style="color: #666666;">
                                            <?= htmlspecialchars($booking['adultsCount'] ?? $booking['adults_count'] ?? $booking['numberOfGuests'] ?? '1') ?> Adult<?= ($booking['adultsCount'] ?? $booking['adults_count'] ?? $booking['numberOfGuests'] ?? 1) > 1 ? 's' : '' ?>
                                            <?php if (($booking['childrenCount'] ?? $booking['children_count'] ?? 0) > 0): ?>
                                                , <?= htmlspecialchars($booking['childrenCount'] ?? $booking['children_count'] ?? 0) ?> Child<?= ($booking['childrenCount'] ?? $booking['children_count'] ?? 0) > 1 ? 'ren' : '' ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php if (!empty($booking['roomDetails'] ?? $booking['room_details'] ?? '')): ?>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Allocation:</strong> 
                                        <span style="color: #666666;"><?= htmlspecialchars($booking['roomDetails'] ?? $booking['room_details'] ?? '') ?></span>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php
                                $cIn = $booking['checkInDate'] ?? $booking['check_in_date'] ?? null;
                                $cOut = $booking['checkOutDate'] ?? $booking['check_out_date'] ?? null;
                                $nights = 1;
                                if ($cIn && $cOut) {
                                    $checkInObj = new DateTime($cIn);
                                    $checkOutObj = new DateTime($cOut);
                                    $nights = $checkInObj->diff($checkOutObj)->days;
                                }
                                ?>
                                <tr>
                                    <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333333;">Number of Nights:</strong> 
                                        <span style="color: #666666;"><?= $nights ?></span>
                                    </td>
                                </tr>
                            </table>
                            
                             <?php 
                             $dbTotalPrice = $booking['total_price'] ?? $booking['totalPrice'] ?? null;
                             $dbPricePerNight = $booking['price_per_night'] ?? $booking['pricePerNight'] ?? null;
                             $dbPriceAdditionalAdult = $booking['price_additional_adult'] ?? $booking['priceAdditionalAdult'] ?? null;
                             $dbPricePerChild = $booking['price_per_child'] ?? $booking['pricePerChild'] ?? null;
                             $dbCurrency = $booking['currency'] ?? 'USD';
                             
                             if ($dbTotalPrice !== null && $dbPricePerNight !== null): 
                                 $curr = $dbCurrency;
                                 // Use the symbol passed from BookingController, fall back to known map
                                 $knownSymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ETB' => 'Br '];
                                 $symbol = $booking['currencySymbol'] ?? $knownSymbols[$curr] ?? '';
                             ?>
                             <h2 style="margin: 30px 0 20px 0; color: #333333; font-size: 20px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Billing Summary</h2>
                             <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9; border-left: 4px solid #7A6960; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                                 <tr>
                                     <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                         <strong style="color: #333333;">Adult Rate per night:</strong> 
                                         <span style="color: #666666;"><?= $symbol . number_format($dbPricePerNight, 2) . ' ' . $curr ?></span>
                                     </td>
                                 </tr>
                                 <?php if ($dbPriceAdditionalAdult !== null && $dbPriceAdditionalAdult > 0): ?>
                                 <tr>
                                     <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                         <strong style="color: #333333;">Extra Adult Rate per night:</strong> 
                                         <span style="color: #666666;"><?= $symbol . number_format($dbPriceAdditionalAdult, 2) . ' ' . $curr ?></span>
                                     </td>
                                 </tr>
                                 <?php endif; ?>
                                 <?php if ($dbPricePerChild !== null && $dbPricePerChild > 0): ?>
                                 <tr>
                                     <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                         <strong style="color: #333333;">Child Rate per night:</strong> 
                                         <span style="color: #666666;"><?= $symbol . number_format($dbPricePerChild, 2) . ' ' . $curr ?></span>
                                     </td>
                                 </tr>
                                 <?php endif; ?>
                                 <tr>
                                     <td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                         <strong style="color: #333333;">Number of Nights:</strong> 
                                         <span style="color: #666666;"><?= $nights ?></span>
                                     </td>
                                 </tr>
                                 <tr>
                                     <td style="padding: 10px 0 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 18px; border-top: 1px solid #e0e0e0; margin-top: 5px;">
                                         <strong style="color: #333333;">Total Price:</strong> 
                                         <strong style="color: #0E8040;"><?= $symbol . number_format($dbTotalPrice, 2) . ' ' . $curr ?></strong>
                                     </td>
                                 </tr>
                             </table>
                             <?php endif; ?>
                            
                            <p style="margin: 20px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                Please review this booking request and confirm it in the admin dashboard.
                            </p>
                            
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
                                <tr>
                                    <td style="background-color: #0E8040; border-radius: 4px;">
                                        <a href="<?= defined('ADMIN_DASHBOARD_URL') ? ADMIN_DASHBOARD_URL : (defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL . '/admin' : '#') ?>?tab=rooms" style="display: inline-block; padding: 12px 30px; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 14px;">View in Admin Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fafaf9; padding: 20px 40px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #666666; font-size: 12px; line-height: 1.6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                Azzeman Hotel - Unparalleled Hospitality<br>
                                Bole Sub-City, Woreda 03, Addis Ababa<br>
                                Phone: +251 116 393 131 | Email: reservation@azzemanhotel.com
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

