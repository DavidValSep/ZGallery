<?php
require_once 'config.php';
require_once 'includes/dbcon.php';

// New behavior: spawn background worker and return job id. Client polls status.
header('Content-Type: application/json');
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) { echo json_encode(['success'=>false,'error'=>'invalid_json']); exit; }
$files = $payload['files'] ?? [];
$email = $payload['email'] ?? '';
$name = $payload['name'] ?? '';
$message = $payload['message'] ?? '';
$useZip = !empty($payload['zip']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'error'=>'invalid_email']); exit; }
if (!is_array($files) || count($files) === 0) { echo json_encode(['success'=>false,'error'=>'no_files']); exit; }

// job id
$jobId = uniqid();
$jobFile = sys_get_temp_dir() . '/sfv_job_' . $jobId . '.json';
$jobData = ['files'=>$files,'email'=>$email,'name'=>$name,'message'=>$message,'zip'=> $useZip ? 1 : 0];
file_put_contents($jobFile, json_encode($jobData));

// spawn worker in background
$cmd = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/send_mail_worker.php') . ' ' . escapeshellarg($jobId) . ' > /dev/null 2>&1 &';

// intentar varios mecanismos de spawn del worker para mayor robustez
@exec($cmd);
// si exec no inició el worker (no hay archivo de progreso en breve), probar alternativas
$progressFile = sys_get_temp_dir() . '/sfv_send_' . $jobId . '.progress';
usleep(200000); // 200ms
if (!is_file($progressFile)) {
	// intentar popen
	if (function_exists('popen')) {
		@pclose(@popen($cmd, 'r'));
		usleep(200000);
	}
}
if (!is_file($progressFile)) {
	// intentar shell_exec con nohup
	if (function_exists('shell_exec')) {
		@shell_exec('nohup ' . $cmd);
		usleep(200000);
	}
}

echo json_encode(['success'=>true,'job'=>$jobId]);
exit;

