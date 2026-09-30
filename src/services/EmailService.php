<?php
/**
 * Email Service
 *
 * Sends booking notifications via PHPMailer when available, otherwise falls
 * back to PHP's native mail() so API endpoints never fatal.
 */

// Attempt to load PHPMailer automatically if it isn't already available.
if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
    $phpmailerPath = BASE_PATH . '/vendor/autoload.php';
    if (file_exists($phpmailerPath)) {
        require_once $phpmailerPath;
    }
}

if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
    /**
     * PHPMailer-backed implementation.
     */
    class EmailService {
        private $mailer;

        public function __construct() {
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        }

        public function sendRoomBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'room-booking-admin', "New Room Booking Request: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Booking Confirmation - Azzeman Hotel',
                $this->renderTemplate('room-booking-customer', $booking)
            );
        }

        public function sendSpaBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'spa-booking-admin', "New Spa Booking Request: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Spa Booking Confirmation - Azzeman Hotel',
                $this->renderTemplate('spa-booking-customer', $booking)
            );
        }

        public function sendMeetingBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'meeting-booking-admin', "New Meeting/Event Inquiry: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Event Inquiry Confirmation - Azzeman Hotel',
                $this->renderTemplate('meeting-booking-customer', $booking)
            );
        }

        private function sendAdminNotifications($booking, $template, $subject, $replyTo) {
            foreach ($this->getAdminEmails() as $adminEmail) {
                $this->sendEmail(
                    $adminEmail,
                    $subject,
                    $this->renderTemplate($template, $booking),
                    $replyTo
                );
            }
        }

        private function sendEmail($to, $subject, $message, $replyTo = null) {
            $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
            $senderEmail = defined('BREVO_SENDER_EMAIL') ? BREVO_SENDER_EMAIL : 'reservation@azzemanhotel.com';
            $senderName = defined('BREVO_SENDER_NAME') ? BREVO_SENDER_NAME : 'Azzeman Hotel';

            if (empty($apiKey) || $apiKey === 'YOUR_BREVO_API_KEY_HERE') {
                error_log('EmailService (Brevo API): API key not configured. Skipping email to ' . $to);
                return false;
            }

            $payload = [
                'sender'      => [
                    'name'  => $senderName,
                    'email' => $senderEmail,
                ],
                'to'          => [
                    [
                        'email' => $to,
                        'name'  => $to,
                    ],
                ],
                'subject'     => $subject,
                'htmlContent' => $message,
            ];

            if ($replyTo) {
                $payload['replyTo'] = [
                    'email' => $replyTo,
                ];
            }

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'accept: application/json',
                    'api-key: ' . $apiKey,
                    'content-type: application/json',
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                error_log("EmailService (Brevo API) cURL error: {$curlErr}");
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                error_log("EmailService (Brevo API): Email sent successfully to {$to} (HTTP {$httpCode})");
                return true;
            }

            error_log("EmailService (Brevo API): Failed to send email to {$to}. HTTP {$httpCode}. Response: {$response}");
            return false;
        }

        private function getSmtpConfig($recipientEmail): array {
            $domain = strtolower(substr(strrchr($recipientEmail, '@') ?: '', 1));

            if ($domain === 'azzemanhotel.com' && defined('SMTP2_HOST') && !empty(SMTP2_HOST)) {
                return [
                    'host' => SMTP2_HOST,
                    'port' => SMTP2_PORT,
                    'user' => SMTP2_USER,
                    'pass' => SMTP2_PASS,
                    'from_email' => SMTP2_FROM_EMAIL,
                    'from_name' => SMTP2_FROM_NAME,
                    'secure' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS,
                ];
            }

            return [
                'host' => SMTP_HOST,
                'port' => SMTP_PORT,
                'user' => SMTP_USER,
                'pass' => SMTP_PASS,
                'from_email' => SMTP_FROM_EMAIL,
                'from_name' => SMTP_FROM_NAME,
                'secure' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
            ];
        }

        private function configureMailer($smtp) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->clearAttachments();

                $this->mailer->isSMTP();
                $this->mailer->Host = $smtp['host'];
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $smtp['user'];
                $this->mailer->Password = $smtp['pass'];
                $this->mailer->SMTPSecure = $smtp['secure'];
                $this->mailer->Port = $smtp['port'];
                $this->mailer->setFrom($smtp['from_email'], $smtp['from_name']);
                $this->mailer->CharSet = 'UTF-8';
                $this->mailer->isHTML(true);
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                error_log('EmailService configuration error: ' . $e->getMessage());
            }
        }

        private function getAdminEmails(): array {
            $emails = [];

            if (defined('ADMIN_EMAILS') && !empty(ADMIN_EMAILS)) {
                foreach (explode(',', ADMIN_EMAILS) as $email) {
                    $email = trim($email);
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $email;
                    }
                }
            }

            if (defined('ADMIN_EMAIL') && !empty(ADMIN_EMAIL)) {
                $adminEmail = trim(ADMIN_EMAIL);
                if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL) && !in_array($adminEmail, $emails)) {
                    $emails[] = $adminEmail;
                }
            }

            if (empty($emails) && defined('SMTP_USER') && !empty(SMTP_USER)) {
                $emails[] = SMTP_USER;
            }

            return $emails;
        }

        private function renderTemplate($templateName, $data): string {
            $templatePath = BASE_PATH . '/src/views/emails/' . $templateName . '.php';

            if (!file_exists($templatePath)) {
                error_log("Email template not found: {$templatePath}");
                return $this->buildBasicEmail($data, $templateName);
            }

            if (!isset($data['booking']) || !is_array($data['booking'])) {
                $data['booking'] = $data;
            }

            $data['logoUrl'] = $this->getLogoUrl();
            extract($data);

            ob_start();
            include $templatePath;
            return ob_get_clean();
        }

        private function buildBasicEmail($booking, $templateName): string {
            $type = str_replace(['-booking-admin', '-booking-customer'], '', $templateName);
            $isCustomer = strpos($templateName, 'customer') !== false;
            $title = ucfirst($type) . ($type === 'meeting' ? '/Event' : '') . ($isCustomer ? ' Booking Confirmation' : ' Booking Request');
            $details = $this->getBookingDetails($booking, $type);

            return "<html><body><h2>{$title}</h2>{$details}</body></html>";
        }

        private function getBookingDetails($booking, $type): string {
            $html = "<p><strong>Booking ID:</strong> {$booking['id']}</p>";

            if ($type === 'room') {
                $html .= "<p><strong>Guest Name:</strong> {$booking['guestName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Check-in:</strong> {$booking['checkInDate']}</p>";
                $html .= "<p><strong>Check-out:</strong> {$booking['checkOutDate']}</p>";
                $html .= "<p><strong>Room Type:</strong> {$booking['roomType']}</p>";
                $html .= "<p><strong>Number of Guests:</strong> {$booking['numberOfGuests']}</p>";
            } elseif ($type === 'spa') {
                $html .= "<p><strong>Guest Name:</strong> {$booking['guestName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Service:</strong> {$booking['service']}</p>";
                $html .= "<p><strong>Date:</strong> {$booking['date']}</p>";
                $html .= "<p><strong>Time:</strong> {$booking['time']}</p>";
            } else {
                $companyName = $booking['companyName'] ?? 'N/A';
                $html .= "<p><strong>Contact Name:</strong> {$booking['contactName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Company:</strong> {$companyName}</p>";
                $html .= "<p><strong>Venue:</strong> {$booking['venueName']}</p>";
                $html .= "<p><strong>Date:</strong> {$booking['date']}</p>";
            }

            return $html;
        }

        private function getLogoUrl(): string {
            $appUrl = defined('APP_URL') ? APP_URL : '';
            if (!empty($appUrl) && strpos($appUrl, 'localhost') === false && strpos($appUrl, '127.0.0.1') === false) {
                return rtrim($appUrl, '/') . '/assets/images/logo.png';
            }
            return 'https://azzemanhotel.com/assets/images/logo.png';
        }
    }
} else {
    /**
     * Basic fallback using PHP's mail() function.
     */
    class EmailService {
        public function sendRoomBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'room-booking-admin', "New Room Booking Request: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Booking Confirmation - Azzeman Hotel',
                $this->renderTemplate('room-booking-customer', $booking)
            );
        }

        public function sendSpaBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'spa-booking-admin', "New Spa Booking Request: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Spa Booking Confirmation - Azzeman Hotel',
                $this->renderTemplate('spa-booking-customer', $booking)
            );
        }

        public function sendMeetingBookingConfirmation($booking) {
            $this->sendAdminNotifications($booking, 'meeting-booking-admin', "New Meeting/Event Inquiry: {$booking['id']}", $booking['email']);
            $this->sendEmail(
                $booking['email'],
                'Event Inquiry Confirmation - Azzeman Hotel',
                $this->renderTemplate('meeting-booking-customer', $booking)
            );
        }

        private function sendAdminNotifications($booking, $template, $subject, $replyTo) {
            foreach ($this->getAdminEmails() as $adminEmail) {
                $this->sendEmail(
                    $adminEmail,
                    $subject,
                    $this->renderTemplate($template, $booking),
                    $replyTo
                );
            }
        }

        private function getAdminEmails(): array {
            $emails = [];

            if (defined('ADMIN_EMAILS') && !empty(ADMIN_EMAILS)) {
                foreach (explode(',', ADMIN_EMAILS) as $email) {
                    $email = trim($email);
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $email;
                    }
                }
            }

            if (defined('ADMIN_EMAIL') && !empty(ADMIN_EMAIL)) {
                $adminEmail = trim(ADMIN_EMAIL);
                if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL) && !in_array($adminEmail, $emails)) {
                    $emails[] = $adminEmail;
                }
            }

            if (empty($emails) && defined('SMTP_USER') && !empty(SMTP_USER)) {
                $emails[] = SMTP_USER;
            }

            return $emails;
        }

        private function sendEmail($to, $subject, $message, $replyTo = null) {
            $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
            $senderEmail = defined('BREVO_SENDER_EMAIL') ? BREVO_SENDER_EMAIL : 'reservation@azzemanhotel.com';
            $senderName = defined('BREVO_SENDER_NAME') ? BREVO_SENDER_NAME : 'Azzeman Hotel';

            if (empty($apiKey) || $apiKey === 'YOUR_BREVO_API_KEY_HERE') {
                error_log('EmailService fallback (Brevo API): API key not configured. Skipping email to ' . $to);
                return false;
            }

            $payload = [
                'sender'      => [
                    'name'  => $senderName,
                    'email' => $senderEmail,
                ],
                'to'          => [
                    [
                        'email' => $to,
                        'name'  => $to,
                    ],
                ],
                'subject'     => $subject,
                'htmlContent' => $message,
            ];

            if ($replyTo) {
                $payload['replyTo'] = [
                    'email' => $replyTo,
                ];
            }

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'accept: application/json',
                    'api-key: ' . $apiKey,
                    'content-type: application/json',
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                error_log("EmailService fallback (Brevo API) cURL error: {$curlErr}");
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                error_log("EmailService fallback (Brevo API): Email sent successfully to {$to} (HTTP {$httpCode})");
                return true;
            }

            error_log("EmailService fallback (Brevo API): Failed to send email to {$to}. HTTP {$httpCode}. Response: {$response}");
            return false;
        }

        private function renderTemplate($templateName, $data): string {
            $templatePath = BASE_PATH . '/src/views/emails/' . $templateName . '.php';

            if (!file_exists($templatePath)) {
                error_log("Email template not found: {$templatePath}");
                return $this->buildBasicEmail($data, $templateName);
            }

            if (!isset($data['booking']) || !is_array($data['booking'])) {
                $data['booking'] = $data;
            }

            $data['logoUrl'] = $this->getLogoUrl();
            extract($data);

            ob_start();
            include $templatePath;
            return ob_get_clean();
        }

        private function buildBasicEmail($booking, $templateName): string {
            $type = str_replace(['-booking-admin', '-booking-customer'], '', $templateName);
            $isCustomer = strpos($templateName, 'customer') !== false;
            $title = ucfirst($type) . ($type === 'meeting' ? '/Event' : '') . ($isCustomer ? ' Booking Confirmation' : ' Booking Request');
            $details = $this->getBookingDetails($booking, $type);

            return "<html><body><h2>{$title}</h2>{$details}</body></html>";
        }

        private function getBookingDetails($booking, $type): string {
            $html = "<p><strong>Booking ID:</strong> {$booking['id']}</p>";

            if ($type === 'room') {
                $html .= "<p><strong>Guest Name:</strong> {$booking['guestName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Check-in:</strong> {$booking['checkInDate']}</p>";
                $html .= "<p><strong>Check-out:</strong> {$booking['checkOutDate']}</p>";
                $html .= "<p><strong>Room Type:</strong> {$booking['roomType']}</p>";
                $html .= "<p><strong>Number of Guests:</strong> {$booking['numberOfGuests']}</p>";
            } elseif ($type === 'spa') {
                $html .= "<p><strong>Guest Name:</strong> {$booking['guestName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Service:</strong> {$booking['service']}</p>";
                $html .= "<p><strong>Date:</strong> {$booking['date']}</p>";
                $html .= "<p><strong>Time:</strong> {$booking['time']}</p>";
            } else {
                $companyName = $booking['companyName'] ?? 'N/A';
                $html .= "<p><strong>Contact Name:</strong> {$booking['contactName']}</p>";
                $html .= "<p><strong>Email:</strong> {$booking['email']}</p>";
                $html .= "<p><strong>Phone:</strong> {$booking['phoneNumber']}</p>";
                $html .= "<p><strong>Company:</strong> {$companyName}</p>";
                $html .= "<p><strong>Venue:</strong> {$booking['venueName']}</p>";
                $html .= "<p><strong>Date:</strong> {$booking['date']}</p>";
            }

            return $html;
        }

        private function getLogoUrl(): string {
            $appUrl = defined('APP_URL') ? APP_URL : '';
            if (!empty($appUrl) && strpos($appUrl, 'localhost') === false && strpos($appUrl, '127.0.0.1') === false) {
                return rtrim($appUrl, '/') . '/assets/images/logo.png';
            }
            return 'https://azzemanhotel.com/assets/images/logo.png';
        }
    }
}
