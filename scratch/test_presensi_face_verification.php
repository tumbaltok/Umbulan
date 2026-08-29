<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User\User;
use App\Models\Absen\Kehadiran;
use App\Http\Controllers\Absen\KehadiranController;
use App\Http\Controllers\Absen\JadwalController;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

$passed = 0;
$failed = 0;

function assertTest($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo " [\033[32mPASS\033[0m] " . $message . "\n";
        $passed++;
    } else {
        echo " [\033[31mFAIL\033[0m] " . $message . "\n";
        $failed++;
    }
}

echo "====================================================\n";
echo "   PRESENSI & BIOMETRIC FACE VERIFICATION TEST SUITE \n";
echo "====================================================\n\n";

// Reset table kehadirans for testing
DB::table('kehadirans')->truncate();

// Set simulated time to a Friday at 06:55 WIB (Before shift start, strictly on-time)
Carbon::setTestNow(Carbon::parse('2026-08-28 06:55:00', 'Asia/Jakarta'));

// 0. TEST SECURITY GUARD: BLOCK UNREGISTERED USER FROM ATTENDANCE
echo "--- TEST 0: Security Guard - Block Unregistered / Null Biometric User ---\n";
$unregisteredUser = User::where('email', 'herta@meta.com')->first();
$unregisteredUser->update(['face_descriptor' => null]);
$unregisteredUser->refresh();

$kehadiranController = app(KehadiranController::class);

// Direct request trying to bypass with is_face_verified = true
$reqBypass = Request::create('/attendance/check-in', 'POST', [
    'latitude' => -7.2575,
    'longitude' => 112.7521,
    'is_face_verified' => true,
]);
$reqBypass->headers->set('Accept', 'application/json');
$reqBypass->setUserResolver(fn() => $unregisteredUser);

$resBypass = $kehadiranController->checkIn($reqBypass);
assertTest($resBypass->getStatusCode() === 422, "Check-in blocked with HTTP 422 when face_descriptor is NULL");
$bypassData = json_decode($resBypass->getContent(), true);
assertTest(str_contains($bypassData['message'], 'Data biometrik wajah belum terdaftar'), "Error message states biometric not registered");

// Direct request to check-out when face_descriptor is null
$reqBypassOut = Request::create('/attendance/check-out', 'POST', [
    'latitude' => -7.2575,
    'longitude' => 112.7521,
    'is_face_verified' => true,
]);
$reqBypassOut->headers->set('Accept', 'application/json');
$reqBypassOut->setUserResolver(fn() => $unregisteredUser);
$resBypassOut = $kehadiranController->checkOut($reqBypassOut);
assertTest($resBypassOut->getStatusCode() === 422, "Check-out blocked with HTTP 422 when face_descriptor is NULL");


// 1. TEST FACE REGISTRATION (128-float descriptor)
echo "\n--- TEST 1: Face Registration & Embedding Storage ---\n";
$user = $unregisteredUser;
assertTest($user !== null, "User Herta Eridani found");

// Generate a dummy 128-float face descriptor
$dummy128Descriptor = [];
for ($i = 0; $i < 128; $i++) {
    $dummy128Descriptor[] = round((sin($i) + 1) / 2, 6);
}

$jadwalController = app(JadwalController::class);
$reqRegister = Request::create('/user/face/register', 'POST', [
    'face_descriptor' => $dummy128Descriptor,
]);
$reqRegister->setUserResolver(fn() => $user);

$resRegister = $jadwalController->registerFace($reqRegister);
$resData = json_decode($resRegister->getContent(), true);

assertTest($resRegister->getStatusCode() === 200, "Register face returns HTTP 200");
assertTest($resData['success'] === true, "Register face response has success=true");

$user->refresh();
assertTest(is_array($user->face_descriptor), "User face_descriptor is cast to array");
assertTest(count($user->face_descriptor) === 128, "User face_descriptor contains 128 floats");

// Test: Registered user but is_face_verified = false
$reqUnverified = Request::create('/attendance/check-in', 'POST', [
    'latitude' => -7.2575,
    'longitude' => 112.7521,
    'is_face_verified' => false,
]);
$reqUnverified->headers->set('Accept', 'application/json');
$reqUnverified->setUserResolver(fn() => $user);
$resUnverified = $kehadiranController->checkIn($reqUnverified);
assertTest($resUnverified->getStatusCode() === 422, "Check-in blocked with HTTP 422 when is_face_verified is false");


// 2. TEST NORMAL ON-TIME CHECK-IN WITHIN RADIUS (NO SELFIE SAVED TO DISK)
echo "\n--- TEST 2: Normal On-Time Check-In within Radius ---\n";
// Stasiun Surabaya coords: lat -7.2575, long 112.7521
$stSurabaya = $user->station;
$stationLat = (float) $stSurabaya->latitude;
$stationLong = (float) $stSurabaya->longitude;

$kehadiranController = app(KehadiranController::class);

$reqCheckIn = Request::create('/attendance/check-in', 'POST', [
    'latitude' => $stationLat,
    'longitude' => $stationLong,
    'is_face_verified' => true,
]);
$reqCheckIn->setUserResolver(fn() => $user);

$resCheckIn = $kehadiranController->checkIn($reqCheckIn);
assertTest($resCheckIn->getStatusCode() === 200, "Check-in on-time within radius returns HTTP 200");

$today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
$savedAttendance = Kehadiran::where('user_id', $user->id)->whereDate('date', $today)->first();

assertTest($savedAttendance !== null, "Kehadiran record successfully created");
assertTest($savedAttendance->is_in_radius_check_in === true, "is_in_radius_check_in is true");
assertTest($savedAttendance->is_face_verified_in === true, "is_face_verified_in is true");
assertTest(empty($savedAttendance->face_photo_in), "Daily selfie face_photo_in is empty/null (No storage waste)");
assertTest(!Storage::disk('public')->exists('foto_absensi'), "No legacy foto_absensi folder created");


// 3. TEST LATE / OUTSIDE RADIUS VALIDATION (MANDATORY REASON)
echo "\n--- TEST 3: Outside Radius / Late Check-In Reason Enforcement ---\n";
$user2 = User::where('email', 'reki@meta.com')->first();
assertTest($user2 !== null, "User Reki M. found");
$user2->update(['face_descriptor' => $dummy128Descriptor]);
$user2->refresh();

// Simulate outside radius: far coordinates
$reqOutsideNoReason = Request::create('/attendance/check-in', 'POST', [
    'latitude' => -1.0000,
    'longitude' => 100.0000,
    'is_face_verified' => true,
    'reason' => '', // Empty reason
]);
$reqOutsideNoReason->setUserResolver(fn() => $user2);

$resOutsideNoReason = $kehadiranController->checkIn($reqOutsideNoReason);
assertTest($resOutsideNoReason->getStatusCode() === 422, "Outside radius without reason rejected with HTTP 422");

// Now check-in with reason filled
$reqOutsideWithReason = Request::create('/attendance/check-in', 'POST', [
    'latitude' => -1.0000,
    'longitude' => 100.0000,
    'is_face_verified' => true,
    'reason' => 'Sedang dinas luar kota meeting dengan supplier pipa',
]);
$reqOutsideWithReason->setUserResolver(fn() => $user2);

$resOutsideWithReason = $kehadiranController->checkIn($reqOutsideWithReason);
assertTest($resOutsideWithReason->getStatusCode() === 200, "Outside radius with reason accepted with HTTP 200");

$savedAttendance2 = Kehadiran::where('user_id', $user2->id)->whereDate('date', $today)->first();
assertTest($savedAttendance2 !== null, "User2 Kehadiran record created");
assertTest($savedAttendance2->is_in_radius_check_in === false, "is_in_radius_check_in is false");
assertTest($savedAttendance2->reason_in === 'Sedang dinas luar kota meeting dengan supplier pipa', "reason_in properly stored");


// 4. TEST AUTOMATIC WATERMARK ON SUPPORTING EVIDENCE
echo "\n--- TEST 4: Automatic Watermark on Supporting Evidence ---\n";
// Create a fake test image file
$tempImgPath = sys_get_temp_dir() . '/test_evidence_' . time() . '.jpg';
$testImg = imagecreatetruecolor(600, 400);
$bgCol = imagecolorallocate($testImg, 100, 150, 200);
imagefilledrectangle($testImg, 0, 0, 600, 400, $bgCol);
imagejpeg($testImg, $tempImgPath, 90);
imagedestroy($testImg);

$uploadedFile = new UploadedFile($tempImgPath, 'surat_tugas.jpg', 'image/jpeg', null, true);

$watermarkedPath = $kehadiranController->processAndWatermarkEvidence(
    $uploadedFile,
    'checkin',
    $user2,
    'LUAR RADIUS (DINAS LUAR)'
);

assertTest(!empty($watermarkedPath), "Watermarked evidence returned valid relative path: " . $watermarkedPath);
assertTest(Storage::disk('public')->exists($watermarkedPath), "Watermarked evidence exists on public storage");

// Verify that the saved image is readable
$savedImgContent = Storage::disk('public')->get($watermarkedPath);
$checkImg = @imagecreatefromstring($savedImgContent);
assertTest($checkImg !== false, "Watermarked file is a valid image");
if ($checkImg) {
    imagedestroy($checkImg);
}
@unlink($tempImgPath);


// 5. TEST CHECK-OUT FLOW (ON-TIME & EARLY REASON ENFORCEMENT)
echo "\n--- TEST 5: Check-Out Flow & Early Departure Validation ---\n";
// Attempt duplicate check-in
$resDuplicateIn = $kehadiranController->checkIn($reqCheckIn);
assertTest($resDuplicateIn->getStatusCode() === 400, "Duplicate check-in blocked with HTTP 400");

// Check-out for user 1
$reqCheckOut = Request::create('/attendance/check-out', 'POST', [
    'latitude' => $stationLat,
    'longitude' => $stationLong,
    'is_face_verified' => true,
    'reason' => 'Pulang kerja shift selesai',
]);
$reqCheckOut->setUserResolver(fn() => $user);

$resCheckOut = $kehadiranController->checkOut($reqCheckOut);
assertTest($resCheckOut->getStatusCode() === 200, "Check-out returns HTTP 200");

$savedAttendance->refresh();
assertTest(!empty($savedAttendance->check_out), "check_out time recorded");
assertTest($savedAttendance->is_face_verified_out === true, "is_face_verified_out is true");
assertTest(empty($savedAttendance->face_photo_out), "Daily selfie face_photo_out is empty/null");

// Duplicate check-out
$resDuplicateOut = $kehadiranController->checkOut($reqCheckOut);
assertTest($resDuplicateOut->getStatusCode() === 400, "Duplicate check-out blocked with HTTP 400");


// 6. TEST MODEL ACCESSORS & RELATIONSHIPS
echo "\n--- TEST 6: Model Accessors & Backward Compatibility ---\n";
assertTest($savedAttendance2->effective_reason_in === 'Sedang dinas luar kota meeting dengan supplier pipa', "effective_reason_in accessor works");
assertTest($savedAttendance->user->name === 'Herta Eridani', "Kehadiran belongsTo User relation works");

echo "\n====================================================\n";
echo "TEST RESULTS: PASS: $passed | FAIL: $failed\n";
echo "====================================================\n";

if ($failed === 0) {
    exit(0);
} else {
    exit(1);
}
