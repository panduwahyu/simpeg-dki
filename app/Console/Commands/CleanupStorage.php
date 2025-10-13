<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CleanupStorage extends Command
{
    /**
     * Nama command yang dipakai di terminal
     *
     * Contoh: php artisan storage:cleanup
     */
    protected $signature = 'storage:cleanup';

    /**
     * Deskripsi command
     */
    protected $description = 'Hapus file di storage/app/private/uploads yang tidak terdaftar di tabel dokumen';

    /**
     * Jalankan command
     */
    public function handle()
    {
        $this->info('Memeriksa file di storage...');

        // Ambil semua path file dari tabel dokumen
        $dbPaths = DB::table('dokumen')->pluck('path')->toArray();

        // Ambil semua file di storage/app/private/uploads
        $storageFiles = Storage::disk('private')->files('uploads');

        $deletedCount = 0;

        foreach ($storageFiles as $file) {
            if (!in_array($file, $dbPaths)) {
                Storage::disk('private')->delete($file);
                $this->line("🗑️  Dihapus: {$file}");
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            $this->info('Tidak ada file yang perlu dihapus. Penyimpanan sudah bersih.');
        } else {
            $this->info("Selesai. Total file dihapus: {$deletedCount}");
        }

        return Command::SUCCESS;
    }
}
