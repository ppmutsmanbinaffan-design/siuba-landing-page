<?php

namespace App\Http\Controllers\Api;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotDeviceApiController extends Controller
{
    /**
     * Endpoint Sinkronisasi Tap Kartu RFID dari Hardware ESP32.
     * Route: POST /api/v1/iot/rfid-tap
     */
    public function handleRfidTap(Request $request): JsonResponse
    {
        $rawUid = $request->input('rfid_uid') ?? $request->input('uid') ?? $request->input('card_uid');

        if (!$rawUid || trim((string) $rawUid) === '') {
            return response()->json([
                'success' => false,
                'code' => 'UID_REQUIRED',
                'message' => 'Parameter rfid_uid / uid wajib dikirimkan oleh perangkat.',
            ], 400);
        }

        // Verifikasi opsional Global API Key (Nexabsen V.4)
        $apiKey = $request->header('X-GLOBAL-KEY') ?? $request->header('x-global-key');
        $configuredKey = env('NEXABSEN_GLOBAL_KEY');
        if (!empty($configuredKey) && $apiKey !== $configuredKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key Salah!',
            ], 401);
        }

        $normalizedUid = Student::normalizeRfidUid((string) $rawUid);

        // 1. Catat atau Perbarui Perangkat Terminal ESP32 / Nexabsen
        $deviceCode = (string) ($request->input('device_code') ?? $request->input('device_id') ?? 'NEXABSEN-01');
        $deviceName = (string) ($request->input('device_name') ?? 'Nexabsen RFID Terminal');
        $deviceLocation = (string) ($request->input('location') ?? 'Gerbang / Pintu Masuk');

        $device = AttendanceDevice::firstOrCreate(
            ['device_code' => $deviceCode],
            [
                'device_name' => $deviceName,
                'device_type' => 'rfid_reader',
                'location' => $deviceLocation,
                'is_active' => true,
            ]
        );

        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // 2. Cari Identitas Santri Berdasarkan UID RFID
        $student = Student::with('classroom')->where('rfid_uid', $normalizedUid)->first();

        if (!$student) {
            // Simpan UID terakhir yang di-tap agar mudah diambil / didaftarkan dari dashboard
            \Illuminate\Support\Facades\Cache::put('last_scanned_rfid', [
                'uid' => $normalizedUid,
                'device' => $deviceCode,
                'tapped_at' => now()->format('H:i:s') . ' WIB',
            ], 600);

            return response()->json([
                'success' => false,
                'code' => 'CARD_NOT_REGISTERED',
                'message' => 'UID: ' . $normalizedUid,
                'data' => [
                    'name' => 'UID: ' . $normalizedUid,
                ],
                'rfid_uid' => $normalizedUid,
                'device' => $deviceCode,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 404);
        }

        // 3. Tentukan Sesi Presensi Aktif
        $session = null;
        if ($request->filled('session_id')) {
            $session = AttendanceSession::find($request->input('session_id'));
        }

        // Cari Sesi yang Berstatus OPEN Hari Ini
        if (!$session) {
            $session = AttendanceSession::where('status', SessionStatus::OPEN)
                ->whereDate('session_date', today())
                ->latest('opened_at')
                ->first();
        }

        // Jika tidak ada yang OPEN eksplisit, cari jadwal yang sedang berjalan pada jam ini
        if (!$session) {
            $session = AttendanceSession::whereDate('session_date', today())
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                ->first();
        }

        // Fallback ke sesi terbaru hari ini jika ada
        if (!$session) {
            $session = AttendanceSession::whereDate('session_date', today())
                ->latest('id')
                ->first();
        }

        if (!$session) {
            return response()->json([
                'success' => false,
                'code' => 'NO_ACTIVE_SESSION',
                'message' => 'Tidak ada sesi presensi aktif.',
                'data' => [
                    'name' => $student->name,
                ],
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                ],
                'rfid_uid' => $normalizedUid,
            ], 422);
        }

        // 4. Periksa Apakah Santri Sudah Melakukan Tap Pada Sesi Ini
        $existingRecord = AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingRecord) {
            $statusLabel = match ($existingRecord->status) {
                'present' => 'Hadir Tepat Waktu',
                'late' => 'Terlambat',
                default => (string) $existingRecord->status,
            };

            return response()->json([
                'success' => true,
                'code' => 'ALREADY_RECORDED',
                'message' => 'Sudah Absen',
                'data' => [
                    'name' => $student->name,
                ],
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                    'gender' => $student->gender?->value ?? $student->gender,
                    'classroom' => $student->classroom?->name ?? 'Semua Santri',
                ],
                'session' => [
                    'id' => $session->id,
                    'code' => $session->session_code,
                    'title' => $session->activity?->name ?? $session->session_code,
                ],
                'status' => $existingRecord->status,
                'status_label' => $statusLabel,
                'check_in_at' => $existingRecord->check_in_at?->format('H:i:s') . ' WIB',
            ]);
        }

        // Waktu tap nyata (mendukung Timestamp Recovery mode offline Nexabsen)
        $checkInTime = $request->filled('tapped_at') ? Carbon::parse($request->input('tapped_at')) : now();

        // 5. Hitung Status Hadir / Terlambat
        $status = 'present';
        $lateMinutes = 0;

        if ($session->present_until && $checkInTime->gt($session->present_until)) {
            $status = 'late';
            $lateMinutes = (int) $checkInTime->diffInMinutes($session->present_until);
        }

        // 6. Simpan Rekam Presensi
        $record = AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $status,
            'method' => 'rfid',
            'check_in_at' => $checkInTime,
            'late_minutes' => $lateMinutes,
            'attendance_device_id' => $device->id,
            'notes' => 'Tap via Hardware [' . $deviceCode . ']',
        ]);

        $statusText = $status === 'late' ? "Terlambat ({$lateMinutes} mnt)" : 'Hadir Tepat Waktu';

        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Absen Sukses',
            'data' => [
                'name' => $student->name,
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'gender' => $student->gender?->value ?? $student->gender,
                'classroom' => $student->classroom?->name ?? 'Semua Santri',
            ],
            'session' => [
                'id' => $session->id,
                'code' => $session->session_code,
                'title' => $session->activity?->name ?? $session->session_code,
            ],
            'status' => $status,
            'status_label' => $statusText,
            'late_minutes' => $lateMinutes,
            'check_in_at' => $checkInTime->format('H:i:s') . ' WIB',
            'device' => $deviceCode,
        ]);
    }

    /**
     * Heartbeat / Ping dari ESP32 untuk mendeteksi status Online/Offline perangkat di dashboard.
     * Route: GET/POST /api/v1/iot/ping
     */
    public function ping(Request $request): JsonResponse
    {
        $deviceCode = (string) ($request->input('device_code') ?? 'ESP32-RFID-01');
        $deviceName = (string) ($request->input('device_name') ?? 'ESP32 RFID Reader');

        $device = AttendanceDevice::firstOrCreate(
            ['device_code' => $deviceCode],
            [
                'device_name' => $deviceName,
                'device_type' => 'rfid_reader',
                'location' => 'Pintu Masuk',
                'is_active' => true,
            ]
        );

        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $activeSession = AttendanceSession::where('status', SessionStatus::OPEN)
            ->whereDate('session_date', today())
            ->latest('opened_at')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'ESP32 Ping OK - Server Online',
            'device' => $device->device_code,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'active_session' => $activeSession ? [
                'id' => $activeSession->id,
                'title' => $activeSession->activity?->name ?? $activeSession->session_code,
                'code' => $activeSession->session_code,
            ] : null,
        ]);
    }

    /**
     * Info Sesi Presensi Aktif untuk ditampilkan di Layar LCD/OLED ESP32 (Opsional).
     * Route: GET /api/v1/iot/active-sessions
     */
    public function getActiveSessions(): JsonResponse
    {
        $sessions = AttendanceSession::with('activity')
            ->whereDate('session_date', today())
            ->where('status', SessionStatus::OPEN)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $sessions->count(),
            'sessions' => $sessions->map(fn($s) => [
                'id' => $s->id,
                'code' => $s->session_code,
                'title' => $s->activity?->name ?? $s->session_code,
                'start_at' => $s->start_at?->format('H:i'),
                'end_at' => $s->end_at?->format('H:i'),
            ]),
        ]);
    }

    /**
     * Ambil UID Kartu Terakhir yang di-tap pada mesin (untuk auto-fill di form Santri).
     * Route: GET /api/v1/iot/last-scan
     */
    public function getLastScan(): JsonResponse
    {
        $last = \Illuminate\Support\Facades\Cache::get('last_scanned_rfid');
        return response()->json([
            'success' => true,
            'data' => $last,
        ]);
    }
}
