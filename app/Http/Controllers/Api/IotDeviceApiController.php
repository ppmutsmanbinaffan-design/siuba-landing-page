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
        $rawContent = $request->getContent();
        $jsonData = json_decode($rawContent, true);
        if (!is_array($jsonData)) {
            $jsonData = [];
        }

        $rawUid = $request->input('rfid_uid') 
            ?? $request->input('uid') 
            ?? $request->input('card_uid')
            ?? ($jsonData['rfid_uid'] ?? null)
            ?? ($jsonData['uid'] ?? null)
            ?? ($jsonData['card_uid'] ?? null);

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
        $deviceCode = (string) (
            $request->input('device_code') 
            ?? $request->input('device_id') 
            ?? ($jsonData['device_code'] ?? null)
            ?? ($jsonData['device_id'] ?? null)
            ?? 'NEXABSEN-01'
        );
        $deviceName = (string) ($request->input('device_name') ?? ($jsonData['device_name'] ?? 'Nexabsen RFID Terminal'));
        $deviceLocation = (string) ($request->input('location') ?? ($jsonData['location'] ?? 'Gerbang / Pintu Masuk'));

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
                'success' => true,
                'code' => 'NO_ACTIVE_SESSION',
                'message' => 'Sesi Belum Buka',
                'data' => [
                    'name' => $student->name,
                ],
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                ],
                'rfid_uid' => $normalizedUid,
            ], 200);
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

    /**
     * Ambil Daftar Santri & Kepemilikan RFID untuk Pusat Registrasi Kartu.
     * Route: GET /api/v1/iot/students
     */
    public function getStudentsWithRfid(Request $request): JsonResponse
    {
        $query = Student::with(['classroom', 'dormitoryRoom'])
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('student_number', 'like', "%{$s}%")
                  ->orWhere('rfid_uid', 'like', "%{$s}%");
            });
        }

        if ($request->input('filter') === 'without_rfid') {
            $query->where(function ($q) {
                $q->whereNull('rfid_uid')->orWhere('rfid_uid', '');
            });
        } elseif ($request->input('filter') === 'with_rfid') {
            $query->whereNotNull('rfid_uid')->where('rfid_uid', '!=', '');
        }

        $allStudents = $query->get();
        $totalInDb = Student::count();
        $withRfidCount = Student::whereNotNull('rfid_uid')->where('rfid_uid', '!=', '')->count();

        $students = $allStudents->map(function ($stu) {
            return [
                'id' => $stu->id,
                'name' => $stu->name,
                'student_number' => $stu->student_number,
                'nisn' => $stu->nisn,
                'gender' => $stu->gender?->value ?? $stu->gender,
                'classroom' => $stu->classroom?->name ?? 'Belum ada kelas',
                'dormitory' => $stu->dormitoryRoom?->name ?? 'Non-Asrama',
                'rfid_uid' => $stu->rfid_uid,
                'has_rfid' => !empty($stu->rfid_uid),
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $totalInDb,
            'with_rfid' => $withRfidCount,
            'without_rfid' => $totalInDb - $withRfidCount,
            'data' => $students,
        ]);
    }

    /**
     * Tautkan Kartu RFID ke Santri.
     * Route: POST /api/v1/iot/register-rfid
     */
    public function registerRfid(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'rfid_uid' => 'required|string',
        ]);

        $normalizedUid = Student::normalizeRfidUid((string) $request->input('rfid_uid'));
        if (!$normalizedUid) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor UID RFID tidak boleh kosong atau tidak valid.',
            ], 422);
        }

        // Cek apakah UID sudah digunakan oleh santri lain
        $existing = Student::where('rfid_uid', $normalizedUid)
            ->where('id', '!=', $request->input('student_id'))
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "Kartu RFID [{$normalizedUid}] sudah terdaftar pada santri: {$existing->name} (NIS: {$existing->student_number}).",
            ], 422);
        }

        $student = Student::findOrFail($request->input('student_id'));
        $oldUid = $student->rfid_uid;
        $student->rfid_uid = $normalizedUid;
        $student->save();

        // Bersihkan cache kartu terakhir agar tidak tertinggal
        \Illuminate\Support\Facades\Cache::forget('last_scanned_rfid');

        return response()->json([
            'success' => true,
            'message' => "Alhamdulillah, kartu RFID [{$normalizedUid}] berhasil didaftarkan untuk santri {$student->name}.",
            'data' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'rfid_uid' => $student->rfid_uid,
                'old_rfid_uid' => $oldUid,
            ],
        ]);
    }

    /**
     * Lepas / Hapus Tautan Kartu RFID dari Santri.
     * Route: POST /api/v1/iot/unregister-rfid
     */
    public function unregisterRfid(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->input('student_id'));
        $oldUid = $student->rfid_uid;
        $student->rfid_uid = null;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => "Kartu RFID [{$oldUid}] berhasil dilepas dari santri {$student->name}.",
        ]);
    }
}
