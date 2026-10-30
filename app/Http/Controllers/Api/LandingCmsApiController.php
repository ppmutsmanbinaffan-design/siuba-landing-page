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
            'hero_slides' => [
                [
                    'bg_image' => 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&w=1920&q=80',
                    'card_image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=800&q=80',
                    'card_badge' => 'Akreditasi A • Boarding School',
                    'card_caption' => 'Lingkungan pendidikan berasrama yang asri, kondusif, dan terpantau secara digital 24 jam.',
                ],
                [
                    'bg_image' => 'https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1920&q=80',
                    'card_image' => 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&w=800&q=80',
                    'card_badge' => 'Tahfidz 15–30 Juz Bersanad',
                    'card_caption' => 'Setoran Ziyadah & Muraja’ah harian ba’da Subuh dan Maghrib dibimbing Hafidz/ah bersanad.',
                ],
                [
                    'bg_image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1920&q=80',
                    'card_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
                    'card_badge' => 'Sains, IT & Bilingual Area',
                    'card_caption' => 'Integrasi kurikulum nasional, laboratorium sains & komputer modern, serta Bahasa Arab & Inggris aktif.',
                ],
                [
                    'bg_image' => 'https://images.unsplash.com/photo-1526232761682-d26e03ac148e?auto=format&fit=crop&w=1920&q=80',
                    'card_image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80',
                    'card_badge' => 'SIUBA Smart Card RFID & QR',
                    'card_caption' => 'Presensi shalat berjamaah, kelas, asrama, dan perizinan santri tercatat real-time di sistem SIUBA.',
                ],
            ],
        ];
    }

    public static function defaultPpdb(): array
    {
        return [
            'academic_year' => '2026 / 2027',
            'status_badge' => 'GELOMBANG 1 BUKA',
            'total_quota' => '240 Kursi',
            'quota_description' => 'Terdiri dari 120 kursi jenjang SMP IT (60 Putra & 60 Putri) dan 120 kursi jenjang SMA IT / Takhassus (60 Putra & 60 Putri).',
            'wave_1' => '01 Okt – 31 Des 2026',
            'wave_1_test' => 'Setiap Sabtu (One Day Service)',
            'wave_1_announce' => '3 Hari Setelah Tes',
            'wave_1_rereg' => 'Maks. 14 Hari Setelah Lulus',
            'wave_2' => '01 Jan – 31 Mar 2027',
            'wave_2_test' => 'Setiap Sabtu (Jan – Mar 2027)',
            'wave_2_announce' => '3 Hari Setelah Tes',
            'wave_2_rereg' => '01 – 15 April 2027',
            'boarding_entry_date' => '12 Juli 2027 (Khutbatul Arsy & MATSABA)',
            'test_schedule' => 'Setiap Sabtu (Pukul 08.00 – 14.00 WIB)',
            'scholarship_note' => 'Tersedia jalur Beasiswa Tahfidz (Hafal min. 5–30 Juz), Juara Olimpiade Sains, & Yatim Berprestasi.',
            'smp_title' => 'JENJANG SMP IT (BOARDING)',
            'smp_desc' => 'Fondasi Tahsin bersanad, Target Tahfidz 10–15 Juz, Bahasa Arab & Inggris Aktif, serta Kurikulum Nasional Kemenag/Kemendikbud.',
            'smp_quota' => '120 Santri (60 Pa / 60 Pi)',
            'sma_title' => 'JENJANG SMA IT & TAKHASSUS',
            'sma_desc' => 'Tahfidz 30 Juz Mutqin Bersanad, Kajian Kitab Turats, Olimpiade MIPA/Sains & Persiapan Masuk PTN / Universitas Timur Tengah.',
            'sma_quota' => '120 Santri (60 Pa / 60 Pi)',
            // Rincian Biaya Pendidikan
            'fee_registration' => 'Rp 350.000',
            'fee_dsp' => 'Rp 14.500.000',
            'fee_spp' => 'Rp 1.650.000 / bulan',
            'fee_uniform_books' => 'Rp 3.200.000',
            'fee_includes' => 'Makan 3x sehari bergizi, Asrama ber-AC/nyaman, Laundry pakaian, Layanan Poskestren, SPP Sekolah & Halaqah Tahfidz, serta Kartu Pintar RFID SIUBA.',
            // Beasiswa
            'scholarship_tahfidz' => 'Hafal 5–10 Juz: Potongan DSP 25% • Hafal 15 Juz: Potongan DSP 50% • Hafal 30 Juz Mutqin: Bebas DSP 100% + Beasiswa SPP.',
            'scholarship_academic' => 'Juara 1–3 OSN/KSM/MTQ Tingkat Provinsi/Nasional: Potongan Dana Pengembangan 50% s.d. 100%.',
            'scholarship_orphan' => 'Kuota khusus 15 kursi Beasiswa Penuh bagi santri Yatim/Dhuafa yang lulus seleksi Tahfidz & Akademik.',
            // Syarat & Materi Tes
            'requirements_list' => "1. Mengisi formulir pendaftaran PPDB Online atau Offline di Sekretariat.\n2. Fotokopi Akta Kelahiran & Kartu Keluarga (masing-masing 2 lembar).\n3. Fotokopi Rapor 2 semester terakhir yang telah dilegalisir.\n4. Surat Keterangan Aktif Sekolah & Nomor Induk Siswa Nasional (NISN).\n5. Pas Foto berwarna ukuran 3x4 (4 lembar, putri berjilbab putih/gelap).\n6. Surat Keterangan Sehat dari Dokter/Puskesmas & bebas penyakit menular.",
            'selection_materials' => "1. Tes Membaca & Menghafal Al-Qur'an (Tajwid, kelancaran bacaan, dan tes kecepatan menghafal 1 halaman baru).\n2. Tes Akademik Dasar (Matematika, IPA, Bahasa Indonesia, dan Pengetahuan Agama Islam Dasar).\n3. Tes Psikotes Kesiapan Berasrama & Kemandirian Calon Santri.\n4. Wawancara Komitmen Orang Tua / Wali Santri bersama Dewan Masyayikh.",
            // Kontak & Rekening Resmi
            'whatsapp_contact' => '0812-3456-7890',
            'admin_contact_name' => 'Ustadz H. Ahmad Fauzi, S.Pd.I (Ketua Panitia PPDB)',
            'bank_account' => 'Bank Syariah Indonesia (BSI) — No. Rek: 7123-456-789 a.n. Yayasan Ponpes Utsman Bin Affan',
            'secretariat_hours' => 'Senin – Sabtu, Pukul 08.00 – 16.00 WIB (Gedung Pusat Lt. 1)',
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

    public static function seedDefaultPpdbRegistrationsIfEmpty(): void
    {
        if (DB::table('ppdb_registrations')->count() === 0) {
            $now = now();
            DB::table('ppdb_registrations')->insert([
                [
                    'reg_number' => 'PPDB-2026-0001',
                    'student_name' => 'Muhammad Fatih Al-Ayyubi',
                    'nisn' => '0124589301',
                    'gender' => 'Putra',
                    'birth_info' => 'Jakarta, 14 Mei 2014',
                    'level' => 'SMP IT',
                    'track' => 'Beasiswa Tahfidz',
                    'juz_memorized' => '8 Juz',
                    'parent_name' => 'H. Ridwan Kamilullah',
                    'whatsapp' => '081288990011',
                    'origin_school' => 'SDIT Al-Hikmah Jakarta Selatan',
                    'address' => 'Jl. Kebagusan Raya No. 45, Pasar Minggu, Jakarta Selatan',
                    'status' => 'Diterima',
                    'test_score_quran' => '95 (Jayyid Jiddan)',
                    'test_score_academic' => '88',
                    'notes' => 'Bacaan sangat tartil, hafalan 8 juz mutqin. Direkomendasikan beasiswa potongan DSP 25%.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'reg_number' => 'PPDB-2026-0002',
                    'student_name' => 'Aisyah Humaira Zahra',
                    'nisn' => '0098341209',
                    'gender' => 'Putri',
                    'birth_info' => 'Bandung, 22 Agustus 2011',
                    'level' => 'SMA IT',
                    'track' => 'Prestasi Akademik',
                    'juz_memorized' => '5 Juz',
                    'parent_name' => 'Dr. Irfan Hakim, M.T.',
                    'whatsapp' => '081322334455',
                    'origin_school' => 'SMPIT Nurul Fikri Depok',
                    'address' => 'Perum Griya Asri Blok C2 No. 10, Cimanggis, Depok',
                    'status' => 'Tes Seleksi',
                    'test_score_quran' => '89',
                    'test_score_academic' => '94',
                    'notes' => 'Juara 1 OSN Matematika Tingkat Kota. Dijadwalkan wawancara wali hari Sabtu.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'reg_number' => 'PPDB-2026-0003',
                    'student_name' => 'Zaidan Abdurrahman',
                    'nisn' => '0113490872',
                    'gender' => 'Putra',
                    'birth_info' => 'Bekasi, 03 Januari 2011',
                    'level' => 'Takhassus',
                    'track' => 'Reguler',
                    'juz_memorized' => '15 Juz',
                    'parent_name' => 'Ustadz Lukmanul Hakim',
                    'whatsapp' => '085711223344',
                    'origin_school' => 'MTs Negeri 1 Bekasi',
                    'address' => 'Jl. KH. Noer Ali No. 18, Bekasi Barat',
                    'status' => 'Baru',
                    'test_score_quran' => '-',
                    'test_score_academic' => '-',
                    'notes' => 'Pendaftar baru dari formulir online, menunggu konfirmasi jadwal tes.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function getPublicData(): JsonResponse
    {
        self::seedDefaultNewsIfEmpty();
        self::seedDefaultPpdbRegistrationsIfEmpty();

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

    public function checkPpdbStatus(Request $request): JsonResponse
    {
        self::seedDefaultPpdbRegistrationsIfEmpty();
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['success' => false, 'message' => 'Masukkan Nomor Registrasi, NISN, atau Nomor WhatsApp.'], 422);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $q);

        $item = DB::table('ppdb_registrations')
            ->where('reg_number', 'like', '%' . $q . '%')
            ->orWhere('nisn', $q)
            ->orWhere('whatsapp', 'like', '%' . $q . '%')
            ->orWhere(function ($query) use ($cleanPhone) {
                if (strlen($cleanPhone) >= 6) {
                    $query->where('whatsapp', 'like', '%' . substr($cleanPhone, -8) . '%');
                }
            })
            ->orWhere('student_name', 'like', '%' . $q . '%')
            ->orderByDesc('id')
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftar tidak ditemukan. Pastikan Nomor Registrasi / WhatsApp / Nama sudah benar.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'registration' => $item,
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
            'message' => 'Pengaturan Informasi PPDB berhasil diperbarui.',
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

        $nextNum = DB::table('ppdb_registrations')->max('id') + 1;
        $regNumber = sprintf('PPDB-2026-%04d', $nextNum);

        $id = DB::table('ppdb_registrations')->insertGetId([
            'reg_number' => $regNumber,
            'student_name' => (string) $studentName,
            'nisn' => (string) $request->input('nisn', ''),
            'gender' => (string) $request->input('gender', 'Putra'),
            'birth_info' => (string) $request->input('birth_info', ''),
            'level' => (string) $level,
            'track' => (string) $track,
            'juz_memorized' => (string) $request->input('juz_memorized', '0 Juz'),
            'parent_name' => (string) $parentName,
            'whatsapp' => (string) $whatsapp,
            'origin_school' => (string) ($originSchool ?: '-'),
            'address' => (string) $request->input('address', ''),
            'status' => (string) $request->input('status', 'Baru'),
            'test_score_quran' => (string) $request->input('test_score_quran', '-'),
            'test_score_academic' => (string) $request->input('test_score_academic', '-'),
            'notes' => (string) $request->input('notes', ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran PPDB berhasil disimpan dengan Nomor Registrasi: ' . $regNumber,
            'registration' => DB::table('ppdb_registrations')->find($id),
        ]);
    }

    public function updatePpdbRegistrationStatus(Request $request, int $id): JsonResponse
    {
        $updateData = ['updated_at' => now()];
        foreach (['status', 'test_score_quran', 'test_score_academic', 'notes', 'level', 'track', 'juz_memorized', 'gender', 'nisn', 'birth_info', 'address'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = (string) $request->input($field);
            }
        }

        DB::table('ppdb_registrations')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data & status seleksi calon santri berhasil diperbarui.',
            'registration' => DB::table('ppdb_registrations')->find($id),
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
