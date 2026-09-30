<?php
/**
 * Brevo Transactional Email Service
 *
 * Sends booking status notification emails (approved/rejected) to customers
 * via the Brevo v3 SMTP API using cURL. No external SDK required.
 */

class BrevoService {
    private $apiKey;
    private $senderEmail;
    private $senderName;

    public function __construct() {
        $this->apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
        $this->senderEmail = defined('BREVO_SENDER_EMAIL') ? BREVO_SENDER_EMAIL : 'reservation@azzemanhotel.com';
        $this->senderName = defined('BREVO_SENDER_NAME') ? BREVO_SENDER_NAME : 'Azzeman Hotel';
    }

    /**
     * Send a booking-approved email to the customer.
     *
     * @param string $bookingType  'room', 'spa', or 'meeting'
     * @param array  $bookingData  Associative array of booking fields from the DB
     */
    public function sendBookingApproved($bookingType, $bookingData) {
        $toEmail = $this->getCustomerEmail($bookingType, $bookingData);
        $toName  = $this->getCustomerName($bookingType, $bookingData);

        if (!$toEmail) {
            error_log("BrevoService: No customer email found for {$bookingType} booking {$bookingData['id']}");
            return false;
        }

        $subject = $this->getApprovedSubject($bookingType, $bookingData);
        $html    = $this->renderTemplate('booking-approved', [
            'booking'     => $bookingData,
            'bookingType' => $bookingType,
            'logoUrl'     => $this->getLogoUrl(),
        ]);

        return $this->sendEmail($toEmail, $toName, $subject, $html);
    }

    /**
     * Send a booking-rejected/cancelled email to the customer.
     *
     * @param string $bookingType  'room', 'spa', or 'meeting'
     * @param array  $bookingData  Associative array of booking fields from the DB
     */
    public function sendBookingRejected($bookingType, $bookingData) {
        $toEmail = $this->getCustomerEmail($bookingType, $bookingData);
        $toName  = $this->getCustomerName($bookingType, $bookingData);

        if (!$toEmail) {
            error_log("BrevoService: No customer email found for {$bookingType} booking {$bookingData['id']}");
            return false;
        }

        $subject = $this->getRejectedSubject($bookingType, $bookingData);
        $html    = $this->renderTemplate('booking-rejected', [
            'booking'     => $bookingData,
            'bookingType' => $bookingType,
            'logoUrl'     => $this->getLogoUrl(),
        ]);

        return $this->sendEmail($toEmail, $toName, $subject, $html);
    }

    // ─── Private helpers ───────────────────────────────────────────

    private function sendEmail($toEmail, $toName, $subject, $htmlContent) {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_BREVO_API_KEY_HERE') {
            error_log('BrevoService: API key not configured. Skipping email to ' . $toEmail);
            return false;
        }

        $payload = [
            'sender'      => [
                'name'  => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to'          => [
                [
                    'email' => $toEmail,
                    'name'  => $toName ?: $toEmail,
                ],
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'api-key: ' . $this->apiKey,
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
            error_log("BrevoService cURL error: {$curlErr}");
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            error_log("BrevoService: Email sent successfully to {$toEmail} (HTTP {$httpCode})");
            return true;
        }

        error_log("BrevoService: Failed to send email to {$toEmail}. HTTP {$httpCode}. Response: {$response}");
        return false;
    }

    private function getCustomerEmail($type, $data) {
        return $data['email'] ?? null;
    }

    private function getCustomerName($type, $data) {
        if ($type === 'meeting') {
            return $data['contact_name'] ?? $data['contactName'] ?? '';
        }
        return $data['guest_name'] ?? $data['guestName'] ?? '';
    }

    private function getApprovedSubject($type, $data) {
        $id = $data['id'] ?? '';
        switch ($type) {
            case 'room':
                return "Booking Confirmed! - Azzeman Hotel ({$id})";
            case 'spa':
                return "Spa Appointment Confirmed! - Azzeman Hotel ({$id})";
            case 'meeting':
                return "Event Booking Confirmed! - Azzeman Hotel ({$id})";
            default:
                return "Booking Confirmed - Azzeman Hotel ({$id})";
        }
    }

    private function getRejectedSubject($type, $data) {
        $id = $data['id'] ?? '';
        switch ($type) {
            case 'room':
                return "Booking Update - Azzeman Hotel ({$id})";
            case 'spa':
                return "Spa Appointment Update - Azzeman Hotel ({$id})";
            case 'meeting':
                return "Event Booking Update - Azzeman Hotel ({$id})";
            default:
                return "Booking Update - Azzeman Hotel ({$id})";
        }
    }

    private function renderTemplate($templateName, $data) {
        $templatePath = BASE_PATH . '/src/views/emails/' . $templateName . '.php';

        if (!file_exists($templatePath)) {
            error_log("BrevoService: Template not found: {$templatePath}");
            return $this->buildFallbackHtml($data);
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private function buildFallbackHtml($data) {
        $booking = $data['booking'] ?? [];
        $type    = $data['bookingType'] ?? 'booking';
        $id      = $booking['id'] ?? 'N/A';

        return "<html><body>
            <h2>Azzeman Hotel - Booking Update</h2>
            <p>Your {$type} booking (ID: {$id}) status has been updated.</p>
            <p>Please contact us for details: +251 116 393 131</p>
        </body></html>";
    }

    private function getLogoUrl() {
        $appUrl = defined('APP_URL') ? APP_URL : '';
        if (!empty($appUrl) && strpos($appUrl, 'localhost') === false && strpos($appUrl, '127.0.0.1') === false) {
            return rtrim($appUrl, '/') . '/assets/images/logo.png';
        }
        return 'https://azzemanhotel.com/assets/images/logo.png';
    }
}
