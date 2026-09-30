<?php
/**
 * Background booking email sender.
 * Usage: php cli_send_booking_email.php /path/to/payload.json
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$file = $argv[1] ?? '';
if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "Missing payload file\n");
    exit(1);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/services/EmailService.php';

$raw = file_get_contents($file);
@unlink($file);

$data = json_decode((string) $raw, true);
if (!is_array($data) || empty($data['kind']) || empty($data['payload']) || !is_array($data['payload'])) {
    fwrite(STDERR, "Invalid payload\n");
    exit(1);
}

try {
    $email = new EmailService();
    switch ($data['kind']) {
        case 'room':
            $email->sendRoomBookingConfirmation($data['payload']);
            break;
        case 'spa':
            $email->sendSpaBookingConfirmation($data['payload']);
            break;
        case 'meeting':
            $email->sendMeetingBookingConfirmation($data['payload']);
            break;
        default:
            fwrite(STDERR, "Unknown kind\n");
            exit(1);
    }
} catch (Throwable $e) {
    error_log('cli_send_booking_email: ' . $e->getMessage());
    exit(1);
}
