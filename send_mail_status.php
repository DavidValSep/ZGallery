<?php
header('Content-Type: application/json');
$job = $_GET['job'] ?? '';
if (!$job) { echo json_encode(['success'=>false,'error'=>'no_job']); exit; }
$progressFile = sys_get_temp_dir() . '/sfv_send_' . $job . '.progress';
if (!is_file($progressFile)) { echo json_encode(['success'=>false,'status'=>'pending']); exit; }
$raw = file_get_contents($progressFile);
if (!$raw) { echo json_encode(['success'=>true,'status'=>'running']); exit; }
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'invalid_progress']); exit; }
echo json_encode(['success'=>true,'status'=>$data['status'] ?? 'running','progress'=>$data['progress'] ?? 0,'message'=>$data['message'] ?? '', 'detail'=>$data]);
