<?php
/**
 * Meeting Booking Admin Notification Email Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Meeting/Event Inquiry</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fafaf9;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #0E8040; padding: 30px 40px; text-align: center;">
                            <?php if (!empty($logoUrl)): ?>
                                <div style="display: inline-block; background: radial-gradient(circle, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0.2) 100%); background-color: rgba(255, 255, 255, 0.4); padding: 12px 28px; border-radius: 9999px; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); margin-bottom: 15px; vertical-align: middle;">
                                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Azzeman Hotel Logo" width="140" style="max-width: 140px; height: auto; display: block;" />
                                </div>
                            <?php endif; ?>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Azzeman Hotel</h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">New Meeting/Event Inquiry</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px; background-color: #ffffff;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 22px; font-weight: bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Inquiry Details</h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafaf9; border-left: 4px solid #0E8040; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Inquiry ID:</strong> <?= htmlspecialchars($booking['id']) ?></td></tr>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Contact Name:</strong> <?= htmlspecialchars($booking['contactName']) ?></td></tr>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></td></tr>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Phone:</strong> <?= htmlspecialchars($booking['phoneNumber']) ?></td></tr>
                                <?php if (!empty($booking['companyName'])): ?>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Company:</strong> <?= htmlspecialchars($booking['companyName']) ?></td></tr>
                                <?php endif; ?>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Venue:</strong> <?= htmlspecialchars($booking['venueName']) ?></td></tr>
                                <tr><td style="padding: 5px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><strong>Date:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($booking['date']))) ?></td></tr>
                            </table>
                            <p style="margin: 20px 0 0 0; color: #666666; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Please review and respond to this inquiry in the admin dashboard.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
                                <tr>
                                    <td style="background-color: #0E8040; border-radius: 4px;">
                                        <a href="<?= defined('APP_URL') ? APP_URL : '#' ?>/admin?tab=meetings" style="display: inline-block; padding: 12px 30px; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 14px;">View in Admin Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #fafaf9; padding: 20px 40px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #666666; font-size: 12px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Azzeman Hotel - Unparalleled Hospitality</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

