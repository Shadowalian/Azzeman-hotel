<?php
/**
 * Booking Rejected/Cancelled Email Template
 *
 * Sent to the customer when their booking is cancelled by admin.
 * Variables available: $booking (array), $bookingType (string), $logoUrl (string)
 */
$guestName = '';
$bookingDetails = '';

if ($bookingType === 'room') {
    $guestName = htmlspecialchars($booking['guest_name'] ?? $booking['guestName'] ?? 'Valued Guest');
    $checkIn  = isset($booking['check_in_date']) ? date('F j, Y', strtotime($booking['check_in_date'])) : (isset($booking['checkInDate']) ? date('F j, Y', strtotime($booking['checkInDate'])) : 'N/A');
    $checkOut = isset($booking['check_out_date']) ? date('F j, Y', strtotime($booking['check_out_date'])) : (isset($booking['checkOutDate']) ? date('F j, Y', strtotime($booking['checkOutDate'])) : 'N/A');
    $roomType = htmlspecialchars($booking['room_type'] ?? $booking['roomType'] ?? 'N/A');

    $bookingDetails = '
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Check-in:</strong>
            <span style="color: #666;">' . htmlspecialchars($checkIn) . '</span>
        </td></tr>
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Check-out:</strong>
            <span style="color: #666;">' . htmlspecialchars($checkOut) . '</span>
        </td></tr>
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Room Type:</strong>
            <span style="color: #666;">' . $roomType . '</span>
        </td></tr>';

} elseif ($bookingType === 'spa') {
    $guestName = htmlspecialchars($booking['guest_name'] ?? $booking['guestName'] ?? 'Valued Guest');
    $service   = htmlspecialchars($booking['service'] ?? 'N/A');
    $date      = isset($booking['date']) ? date('F j, Y', strtotime($booking['date'])) : 'N/A';
    $time      = htmlspecialchars($booking['time'] ?? 'N/A');

    $bookingDetails = '
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Service:</strong>
            <span style="color: #666;">' . $service . '</span>
        </td></tr>
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Date:</strong>
            <span style="color: #666;">' . htmlspecialchars($date) . '</span>
        </td></tr>
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Time:</strong>
            <span style="color: #666;">' . $time . '</span>
        </td></tr>';

} else { // meeting
    $guestName = htmlspecialchars($booking['contact_name'] ?? $booking['contactName'] ?? 'Valued Guest');
    $venue     = htmlspecialchars($booking['venue_name'] ?? $booking['venueName'] ?? 'N/A');
    $date      = isset($booking['date']) ? date('F j, Y', strtotime($booking['date'])) : 'N/A';
    $company   = htmlspecialchars($booking['company_name'] ?? $booking['companyName'] ?? '');

    $bookingDetails = '
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Venue:</strong>
            <span style="color: #666;">' . $venue . '</span>
        </td></tr>
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Date:</strong>
            <span style="color: #666;">' . htmlspecialchars($date) . '</span>
        </td></tr>';
    if ($company) {
        $bookingDetails .= '
        <tr><td style="padding: 8px 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <strong style="color: #333;">Company:</strong>
            <span style="color: #666;">' . $company . '</span>
        </td></tr>';
    }
}

$bookingId = htmlspecialchars($booking['id'] ?? 'N/A');
$typeLabel = $bookingType === 'room' ? 'Room Reservation' : ($bookingType === 'spa' ? 'Spa Appointment' : 'Event Booking');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Update - Azzeman Hotel</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fafaf9;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #7c2d12, #b45309); padding: 30px 40px; text-align: center;">
                            <?php if (!empty($logoUrl)): ?>
                                <div style="display: inline-block; background: radial-gradient(circle, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0.2) 100%); background-color: rgba(255, 255, 255, 0.4); padding: 12px 28px; border-radius: 9999px; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); margin-bottom: 15px; vertical-align: middle;">
                                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Azzeman Hotel Logo" width="140" style="max-width: 140px; height: auto; display: block;" />
                                </div>
                            <?php endif; ?>
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: bold;">Booking Update</h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 15px;"><?= $typeLabel ?></p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px 0; color: #333; font-size: 16px; line-height: 1.6;">
                                Dear <?= $guestName ?>,
                            </p>

                            <p style="margin: 0 0 20px 0; color: #333; font-size: 16px; line-height: 1.6;">
                                Thank you for your interest in Azzeman Hotel. Unfortunately, we are unable to accommodate
                                your <?= strtolower($typeLabel) ?> at this time. We sincerely apologize for any inconvenience.
                            </p>

                            <!-- Booking Details -->
                            <h2 style="margin: 30px 0 15px 0; color: #333; font-size: 18px; font-weight: bold;">Booking Details</h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 8px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333;">Booking ID:</strong>
                                        <span style="color: #666;"><?= $bookingId ?></span>
                                    </td>
                                </tr>
                                <?= $bookingDetails ?>
                                <tr>
                                    <td style="padding: 8px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                        <strong style="color: #333;">Status:</strong>
                                        <span style="color: #dc2626; font-weight: bold;">Cancelled</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternatives -->
                            <div style="margin-top: 25px; background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 15px 20px; border-radius: 4px;">
                                <p style="margin: 0; color: #1e40af; font-size: 14px; line-height: 1.6;">
                                    <strong>We'd love to help!</strong><br>
                                    Please contact our reservations team to explore alternative dates or options.
                                    We are committed to making your experience with us a memorable one.
                                </p>
                            </div>

                            <!-- Contact Info -->
                            <p style="margin: 25px 0 0 0; color: #333; font-size: 14px; line-height: 1.6;">
                                Please don't hesitate to reach out to us:
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 12px; background-color: #fafaf9; padding: 15px; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 5px 0; color: #666; font-size: 14px;">
                                        <strong>Phone:</strong> +251 116 393 131<br>
                                        <strong>Email:</strong> reservation@azzemanhotel.com<br>
                                        <strong>WhatsApp:</strong> +251 939 767676
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 30px 0 0 0; color: #333; font-size: 14px; line-height: 1.6;">
                                We hope to serve you in the future and apologize again for the inconvenience.
                            </p>
                            <p style="margin: 15px 0 0 0; color: #333; font-size: 14px;">
                                Warm regards,<br>
                                <strong>The Azzeman Hotel Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fafaf9; padding: 20px 40px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #666; font-size: 12px; line-height: 1.6;">
                                Azzeman Hotel - Unparalleled Hospitality<br>
                                Bole Sub-City, Woreda 03, Addis Ababa<br>
                                <a href="https://azzemanhotel.com" style="color: #0E8040; text-decoration: none;">Visit our website</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
