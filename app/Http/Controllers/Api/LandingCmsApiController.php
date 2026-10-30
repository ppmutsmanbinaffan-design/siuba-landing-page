<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingCmsApiController extends Controller
{
    public static function defaultProfile(): array
    {
        return [
            'pesantren_name' => 'Pondok Pesantren Modern Utsman Bin Affan',
            'hero_badge' => 'Pondok Pesantren Modern Utsman Bin Affan',
            'hero_title' => 'MEMBANGUN GENERASI RABBANI, HAFIDZ QUR’AN & UNGGUL DALAM SAINS TEKNOLOGI',
            'hero_subtitle' => 'Memadukan kurikulum Tahfidzul Qur’an bersanad, penguasaan Bahasa Arab & Inggris, serta ekosistem manajemen pesantren digital terpadu (SIUBA Smart Card RFID & QR).',
            'founded_year' => 'Sejak 2012',
            'vision' => 'Menjadi Lembaga Pendidikan Islam Terbaik dalam Mencetak Generasi Penghafal Al-Qur’an yang Berakhlak Mulia, Cerdas, Mandiri, dan Berwawasan Global.',
            'motto' => 'Beriman • Berilmu • Beramal • Berprestasi',
            'missions' => [
                'Menyelenggarakan pendidikan Tahfidzul Qur’an yang mutqin dan berakhlak Qur’ani sesuai pemahaman Ahlussunnah wal Jama’ah.',
                'Mengembangkan kompetensi akademik, sains, dan penguasaan Bahasa Arab & Inggris secara aktif.',
                'Membina kemandirian, jiwa kepemimpinan (leadership), dan kepedulian sosial santri melalui pembiasaan asrama 24 jam.',
                'Menerapkan tata kelola pesantren modern yang transparan dan akuntabel berbasis teknologi SIUBA Digital.',
            ],
            'mudir_name' => 'KH. Abdullah Gymnastiar, Lc.',
            'mudir_title' => 'Pimpinan Pondok',
            'mudir_quote' => 'Kami berkomitmen menjaga amanah orang tua dengan mendidik santri sepenuh hati serta pelaporan kehadiran real-time.',
            'stat_students' => '850+ Santri',
            'stat_teachers' => '95+ Ustadz/ah',
            'stat_graduation' => '96.8%',
            'contact_phone' => '(021) 890-1234 / 0812-3456-7890',
            'contact_email' => 'info@utsman.sch.id • admin@utsman.sch.id',
            'contact_address' => 'Pondok Pesantren Utsman Bin Affan — Indonesia',
        ];
    }

    public static function defaultPpdb(): array
    {
        return [
            'academic_year' => '2026 / 2027',
            'status_badge' => 'GELOMBANG 1 BUKA',
            'total_quota' => '240 Kursi',
            'quota_description' => 'Terdiri dari 120 kursi jenjang SMP IT (Putra & Putri) dan 120 kursi jenjang SMA IT / Takhassus (Putra & Putri).',
            'wave_1' => 'Okt – Des 2026',
            'wave_2' => 'Jan – Mar 2027',
            'test_schedule' => 'Setiap Sabtu',
            'scholarship_note' => 'Tersedia jalur Beasiswa Tahfidz (Hafal min. 5 Juz) & Prestasi Akademik.',
            'smp_title' => 'JENJANG SMP IT (BOARDING)',
            'smp_desc' => 'Fondasi Tahsin, Tahfidz 10–15 Juz, Bahasa Arab Dasar & Kurikulum Nasional.',
            'smp_quota' => '120 Santri',
            'sma_title' => 'JENJANG SMA IT & TAKHASSUS',
            'sma_desc' => 'Tahfidz 30 Juz Bersanad, Kajian Kitab Kuning, Sains Olimpiade & Persiapan PTN/Timteng.',
            'sma_quota' => '120 Santri',
            'whatsapp_contact' => '0812-3456-7890',
        ];
    }

    public static function seedDefaultNewsIfEmpty(): void
    {
        if (DB::table('landing_news')->count() === 0) {
            $now = now();
            DB::table('landing_news')->insert([
                [
                    'title' => 'Wisuda Akbar Tahfidzul Qur’an: 48 Santri Tuntaskan Setoran 30 Juz Sekali Duduk',
                    'category' => 'Tahfidz Al-Qur’an',
                    'published_date' => '25 Oktober 2026',
                    'excerpt' => 'Alhamdulillah, sebanyak 48 santri putra dan putri jenjang SMP IT dan SMA IT berhasil menyelesaikan ujian tasmi’ hafalan 30 juz Al-Qur’an tahun ini.',
                    'image_url' => 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&w=800&q=80',
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title' => 'Implementasi Penuh SIUBA v1.2: Presensi RFID & QR Terintegrasi di Masjid, Kelas & Asrama',
                    'category' => 'Inovasi Digital',
                    'published_date' => '18 Oktober 2026',
                    'excerpt' => 'Pesantren Utsman Bin Affan resmi mengoperasikan sistem presensi pintar real-time untuk memantau kehadiran santri dan ustadz secara akurat.',
                    'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title' => 'Delegasi Santri Utsman Bin Affan Raih Medali Emas Olimpiade Sains & Bahasa Arab Nasional',
                    'category' => 'Prestasi Akademik',
                    'published_date' => '10 Oktober 2026',
                    'excerpt' => 'Tim olimpiade pesantren kembali mengharumkan nama lembaga dengan memborong 3 medali emas cabang Matematika, Fisika, dan Debat Bahasa Arab.',
                    'image_url' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80',
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function getPublicData(): JsonResponse
    {
        self::seedDefaultNewsIfEmpty();

        $profileRow = DB::table('landing_settings')->where('key', 'profile')->first();
        $ppdbRow = DB::table('landing_settings')->where('key', 'ppdb')->first();

        $profile = $profileRow ? array_merge(self::defaultProfile(), json_decode($profileRow->value, true) ?? []) : self::defaultProfile();
        $ppdb = $ppdbRow ? array_merge(self::defaultPpdb(), json_decode($ppdbRow->value, true) ?? []) : self::defaultPpdb();

        $news = DB::table('landing_news')
            ->where('is_published', true)
            ->orderByDesc('id')
            ->get();

        $registrations = DB::table('ppdb_registrations')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'ppdb' => $ppdb,
            'news' => $news,
            'registrations' => $registrations,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $existingRow = DB::table('landing_settings')->where('key', 'profile')->first();
        $existing = $existingRow ? (json_decode($existingRow->value, true) ?? []) : self::defaultProfile();
        $merged = array_merge($existing, $request->all());

        DB::table('landing_settings')->updateOrInsert(
            ['key' => 'profile'],
            ['value' => json_encode($merged, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil Pesantren, Visi & Misi berhasil diperbarui.',
            'profile' => $merged,
        ]);
    }

    public function updatePpdb(Request $request): JsonResponse
    {
        $existingRow = DB::table('landing_settings')->where('key', 'ppdb')->first();
        $existing = $existingRow ? (json_decode($existingRow->value, true) ?? []) : self::defaultPpdb();
        $merged = array_merge($existing, $request->all());

        DB::table('landing_settings')->updateOrInsert(
            ['key' => 'ppdb'],
            ['value' => json_encode($merged, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Informasi PPDB berhasil diperbarui.',
            'ppdb' => $merged,
        ]);
    }

    public function storeNews(Request $request): JsonResponse
    {
        $id = DB::table('landing_news')->insertGetId([
            'title' => (string) $request->input('title', 'Berita Baru Pesantren'),
            'category' => (string) $request->input('category', 'Kegiatan Pondok'),
            'published_date' => (string) $request->input('published_date', date('d F Y')),
            'excerpt' => (string) $request->input('excerpt', ''),
            'image_url' => (string) $request->input('image_url', 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&w=800&q=80'),
            'is_published' => (bool) $request->input('is_published', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditambahkan.',
            'news' => DB::table('landing_news')->find($id),
        ]);
    }

    public function updateNews(Request $request, int $id): JsonResponse
    {
        DB::table('landing_news')->where('id', $id)->update([
            'title' => (string) $request->input('title'),
            'category' => (string) $request->input('category', 'Kegiatan Pondok'),
            'published_date' => (string) $request->input('published_date'),
            'excerpt' => (string) $request->input('excerpt', ''),
            'image_url' => (string) $request->input('image_url'),
            'is_published' => (bool) $request->input('is_published', true),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diperbarui.',
            'news' => DB::table('landing_news')->find($id),
        ]);
    }

    public function deleteNews(int $id): JsonResponse
    {
        DB::table('landing_news')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    public function submitPpdbRegistration(Request $request): JsonResponse
    {
        $studentName = $request->input('student_name') ?? $request->input('nama_lengkap') ?? 'Calon Santri';
        $level = $request->input('level') ?? $request->input('jenjang') ?? 'SMP IT';
        $track = $request->input('track') ?? $request->input('jalur') ?? 'Reguler';
        $parentName = $request->input('parent_name') ?? $request->input('nama_wali') ?? '-';
        $whatsapp = $request->input('whatsapp') ?? $request->input('no_whatsapp') ?? '-';
        $originSchool = $request->input('origin_school') ?? $request->input('asal_sekolah') ?? '-';

        $id = DB::table('ppdb_registrations')->insertGetId([
            'student_name' => (string) $studentName,
            'level' => (string) $level,
            'track' => (string) $track,
            'parent_name' => (string) $parentName,
            'whatsapp' => (string) $whatsapp,
            'origin_school' => (string) ($originSchool ?: '-'),
            'status' => (string) $request->input('status', 'Baru'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran PPDB berhasil disimpan.',
            'registration' => DB::table('ppdb_registrations')->find($id),
        ]);
    }

    public function updatePpdbRegistrationStatus(Request $request, int $id): JsonResponse
    {
        DB::table('ppdb_registrations')->where('id', $id)->update([
            'status' => (string) $request->input('status', 'Diverifikasi'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pendaftar PPDB diperbarui.',
        ]);
    }

    public function deletePpdbRegistration(int $id): JsonResponse
    {
        DB::table('ppdb_registrations')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pendaftar PPDB dihapus.',
        ]);
    }
}
