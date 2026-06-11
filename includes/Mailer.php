<?php
/**
 * Mailer simple: soporta mail() y SendGrid via HTTP API
 * Lee configuración desde la tabla `settings` o recibe parámetros.
 */

class Mailer
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function getSetting($key, $default = null)
    {
        $row = $this->db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', [$key]);
        return $row ? $row['svalue'] : $default;
    }

    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $method = $options['method'] ?? $this->getSetting('mail_method', 'mail');
        if ($method === 'sendgrid') {
            return $this->sendSendGrid($to, $subject, $body, $options);
        }

        if ($method === 'phpmailer') {
            // intentar usar PHPMailer si está disponible (sin composer)
            $phpMailerPath = __DIR__ . '/phpmailer/PHPMailer.php';
            if (file_exists(__DIR__ . '/phpmailer/PHPMailer.php') && file_exists(__DIR__ . '/phpmailer/SMTP.php')) {
                require_once __DIR__ . '/phpmailer/Exception.php';
                require_once __DIR__ . '/phpmailer/PHPMailer.php';
                require_once __DIR__ . '/phpmailer/SMTP.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->setFrom($options['from'] ?? $this->getSetting('mail_from', 'no-reply@example.com'));
                    $mail->addAddress($to);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    // usar SMTP si se configuran settings smtp_host
                    $smtpHost = $this->getSetting('smtp_host', '');
                    if ($smtpHost) {
                        $mail->isSMTP();
                        $mail->Host = $smtpHost;
                        $mail->SMTPAuth = $this->getSetting('smtp_auth', '1') === '1';
                        $mail->Username = $this->getSetting('smtp_user', '');
                        $mail->Password = $this->getSetting('smtp_pass', '');
                        $mail->SMTPSecure = $this->getSetting('smtp_secure', '');
                        $mail->Port = (int)$this->getSetting('smtp_port', 587);
                    }
                    $mail->send();
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            } else {
                // PHPMailer no instalado, fallback a mail()
                return $this->sendMailNative($to, $subject, $body, $options);
            }
        }

        // default to mail()
        return $this->sendMailNative($to, $subject, $body, $options);
    }

    private function sendMailNative(string $to, string $subject, string $body, array $options = []): bool
    {
        $from = $options['from'] ?? $this->getSetting('mail_from', 'no-reply@example.com');
        $headers = [];
        $headers[] = 'From: ' . $from;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        $hdr = implode("\r\n", $headers);
        // Use mail() but set additional parameters carefully
        return (bool) mail($to, $subject, $body, $hdr);
    }

    private function sendSendGrid(string $to, string $subject, string $body, array $options = []): bool
    {
        $apiKey = $options['sendgrid_key'] ?? $this->getSetting('sendgrid_key', '');
        if (empty($apiKey)) return false;

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $to]],
                'subject' => $subject
            ]],
            'from' => ['email' => $options['from'] ?? $this->getSetting('mail_from', 'no-reply@example.com')],
            'content' => [[ 'type' => 'text/html', 'value' => $body ]]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_POST, true);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }
}
