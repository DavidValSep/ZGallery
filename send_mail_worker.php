<?php
// Worker script: takes job JSON file path as first arg OR job id
if ($argc < 2) exit(1);
$jobId = $argv[1];
$jobFile = sys_get_temp_dir() . '/sfv_job_' . $jobId . '.json';
$progressFile = sys_get_temp_dir() . '/sfv_send_' . $jobId . '.progress';

function write_progress($data) {
    global $progressFile;
    @file_put_contents($progressFile, json_encode($data));
}

if (!is_file($jobFile)) {
    write_progress(['status'=>'error','message'=>'job_not_found']);
    exit(1);
}
$payload = json_decode(file_get_contents($jobFile), true);
if (!$payload) {
    write_progress(['status'=>'error','message'=>'invalid_job']);
    exit(1);
}

// start
write_progress(['status'=>'started','progress'=>0,'message'=>'Iniciando envío']);
$files = $payload['files'] ?? [];
$destEmail = $payload['email'] ?? '';
$destName = $payload['name'] ?? '';
$message = $payload['message'] ?? '';
$useZip = !empty($payload['zip']);

require __DIR__ . '/config.php';
require __DIR__ . '/includes/dbcon.php';

$dir = __DIR__ . '/fotos/';
$available = [];
foreach ($files as $f) {
    $safe = basename($f);
    $path = $dir . $safe;
    if (is_file($path) && is_readable($path)) $available[] = $path;
}
if (count($available) === 0) {
    write_progress(['status'=>'error','message'=>'files_not_found']); exit(1);
}

// decide threshold
$ztRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['zip_threshold']);
$zip_threshold = $ztRow ? intval($ztRow['svalue']) : 5;
if (count($available) >= $zip_threshold) $useZip = true;

write_progress(['status'=>'progress','progress'=>10,'message'=>'Preparando adjuntos']);

$tmpFiles = [];
$zipPath = null;
if ($useZip) {
    write_progress(['status'=>'progress','progress'=>20,'message'=>'Creando ZIP']);
    $zipPath = sys_get_temp_dir() . '/sfv_' . uniqid() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        write_progress(['status'=>'error','message'=>'zip_failed']); exit(1);
    }
    foreach ($available as $p) {
        $zip->addFile($p, basename($p));
    }
    $zip->close();
    $tmpFiles[] = $zipPath;
}

// decide sending method
$mailMethodRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['mail_method']);
$mailMethod = $mailMethodRow ? $mailMethodRow['svalue'] : 'mail';
$galleryName = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['gallery_name']);
$galleryName = $galleryName ? $galleryName['svalue'] : 'Galería';
$subject = 'Imagenes desde ' . $galleryName;

try {
    if ($mailMethod === 'sendgrid') {
        write_progress(['status'=>'progress','progress'=>50,'message'=>'Enviando vía SendGrid']);
        $apiKeyRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['sendgrid_key']);
        $fromRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['sendgrid_from']);
        $fromNameRow = $db->fetchOne('SELECT svalue FROM settings WHERE skey = ?', ['sendgrid_from_name']);
        $apiKey = $apiKeyRow ? $apiKeyRow['svalue'] : null;
        $fromEmail = $fromRow ? $fromRow['svalue'] : ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $fromName = $fromNameRow ? $fromNameRow['svalue'] : 'Galería';
        if (!$apiKey) { write_progress(['status'=>'error','message'=>'sendgrid_no_api']); exit(1); }

        $attachments = [];
        $toAttach = $useZip && $zipPath ? [$zipPath] : $available;
        $progressStep = 50;
        foreach ($toAttach as $p) {
            $content = base64_encode(file_get_contents($p));
            $attachments[] = [
                'content' => $content,
                'type' => mime_content_type($p) ?: 'application/octet-stream',
                'filename' => basename($p)
            ];
            $progressStep += 5; write_progress(['status'=>'progress','progress'=>min(95,$progressStep),'message'=>'Preparando adjuntos']);
        }

        $personalizations = [['to'=>[['email'=>$destEmail, 'name'=>$destName]]]];
        $payloadSend = [
            'personalizations' => $personalizations,
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'subject' => $subject,
            'content' => [['type'=>'text/plain','value'=>$message ?: 'Adjunto(s) desde la galería.']],
            'attachments' => $attachments,
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadSend));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($http >= 200 && $http < 300) {
            write_progress(['status'=>'done','progress'=>100,'message'=>'Enviado']);
            // cleanup
            if ($zipPath && is_file($zipPath)) @unlink($zipPath);
            @unlink($jobFile);
            exit(0);
        } else {
            write_progress(['status'=>'error','message'=>'sendgrid_error','http'=>$http,'resp'=>$resp,'err'=>$err]);
            exit(1);
        }

    } elseif ($mailMethod === 'phpmailer' && is_file(__DIR__ . '/includes/phpmailer/PHPMailer.php')) {
        write_progress(['status'=>'progress','progress'=>60,'message'=>'Enviando vía PHPMailer']);
        require_once __DIR__ . '/includes/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/includes/phpmailer/SMTP.php';
        require_once __DIR__ . '/includes/phpmailer/Exception.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->setFrom('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), $galleryName);
        $mail->addAddress($destEmail, $destName);
        $mail->Subject = $subject;
        $mail->Body = $message ?: 'Adjunto(s) desde la galería.';
        if ($useZip && $zipPath) $mail->addAttachment($zipPath, basename($zipPath)); else foreach ($available as $p) $mail->addAttachment($p, basename($p));
        $mail->send();
        write_progress(['status'=>'done','progress'=>100,'message'=>'Enviado']);
        if ($zipPath && is_file($zipPath)) @unlink($zipPath);
        @unlink($jobFile);
        exit(0);
    } else {
        // fallback to mail()
        write_progress(['status'=>'progress','progress'=>60,'message'=>'Enviando vía mail()']);
        $boundary = '=====' . md5(time());
        $headers = "From: $galleryName <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">" . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $body .= ($message ?: 'Adjunto(s) desde la galería.') . "\r\n\r\n";
        $attachFiles = $useZip && $zipPath ? [$zipPath] : $available;
        foreach ($attachFiles as $filePath) {
            if (!is_file($filePath)) continue;
            $data = chunk_split(base64_encode(file_get_contents($filePath)));
            $fname = basename($filePath);
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/octet-stream; name=\"$fname\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$fname\"\r\n\r\n";
            $body .= $data . "\r\n\r\n";
        }
        $body .= "--$boundary--";
        $sent = mail($destEmail, $subject, $body, $headers);
        if ($sent) {
            write_progress(['status'=>'done','progress'=>100,'message'=>'Enviado']);
            if ($zipPath && is_file($zipPath)) @unlink($zipPath);
            @unlink($jobFile);
            exit(0);
        } else {
            write_progress(['status'=>'error','message'=>'mail_failed']); exit(1);
        }
    }
} catch (Throwable $e) {
    write_progress(['status'=>'error','message'=>'exception','detail'=>$e->getMessage()]); exit(1);
}

