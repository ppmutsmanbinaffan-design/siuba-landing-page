<?php

namespace App\Http\Controllers\Api;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Models\AttendanceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotDeviceApiController extends Controller
{
    /**
     * Heartbeat / Ping dari ESP32 untuk mendeteksi status Online/Offline perangkat di dashboard.
     * Route: GET/POST /api/v1/iot/ping
     */
    public function ping(Request $request): JsonResponse
    {
        $deviceCode = (string) ($request->input('device_code') ?? 'ESP32-DEVICE-01');
        $deviceName = (string) ($request->input('device_name') ?? 'ESP32 Terminal');

        $device = AttendanceDevice::firstOrCreate(
            ['device_code' => $deviceCode],
            [
                'device_name' => $deviceName,
                'device_type' => 'kiosk',
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
}
