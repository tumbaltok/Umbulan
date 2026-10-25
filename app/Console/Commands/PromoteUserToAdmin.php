<?php

namespace App\Console\Commands;

use App\Models\User\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promote-admin 
                            {email : Email akun pengguna yang akan diubah hak aksesnya} 
                            {--demote : Turunkan hak akses kembali menjadi Staf Biasa (Level 3)}
                            {--level= : Tentukan level spesifik (1 = Administrator, 2 = Approver/Atasan, 3 = Staff)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kelola hak akses administrator sistem (Level 1 / Level 3) untuk akun pengguna';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email '{$email}' tidak ditemukan dalam database.");
            return self::FAILURE;
        }

        $targetLevel = 1;

        if ($this->option('demote')) {
            $targetLevel = 3;
        } elseif ($this->option('level')) {
            $targetLevel = (int) $this->option('level');
            if (!in_array($targetLevel, [1, 2, 3])) {
                $this->error("Level tidak valid. Gunakan level 1 (Admin), 2 (Approver), atau 3 (Staff).");
                return self::FAILURE;
            }
        }

        $levelNames = [
            1 => 'Administrator Sistem (Level 1 - Akses Penuh)',
            2 => 'Atasan / Penyetuju (Level 2 - Monitoring & Persetujuan)',
            3 => 'Karyawan / Staf Biasa (Level 3 - Portal Standar)',
        ];

        $user->level = $targetLevel;
        $user->save();

        $this->info("Berhasil! Hak akses untuk {$user->name} ({$user->email}) kini diatur sebagai:");
        $this->line("-> {$levelNames[$targetLevel]}");

        return self::SUCCESS;
    }
}
