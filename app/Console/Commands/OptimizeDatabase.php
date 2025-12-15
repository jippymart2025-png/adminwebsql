<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database tables and clean up old data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting database optimization...');

        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = 'Tables_in_' . $databaseName;

            $optimized = 0;
            $errors = 0;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                try {
                    // Optimize table
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                    $optimized++;
                    $this->line("✓ Optimized: {$tableName}");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("✗ Failed to optimize {$tableName}: " . $e->getMessage());
                    Log::warning("Failed to optimize table {$tableName}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Clean up old cache entries (older than 24 hours)
            try {
                $deleted = DB::table('cache')
                    ->where('expiration', '<', now()->timestamp)
                    ->delete();
                $this->info("Cleaned up {$deleted} expired cache entries");
            } catch (\Exception $e) {
                $this->warn("Could not clean cache table: " . $e->getMessage());
            }

            // Clean up old failed jobs (older than 7 days)
            try {
                $deleted = DB::table('failed_jobs')
                    ->where('failed_at', '<', now()->subDays(7))
                    ->delete();
                $this->info("Cleaned up {$deleted} old failed jobs");
            } catch (\Exception $e) {
                $this->warn("Could not clean failed_jobs table: " . $e->getMessage());
            }

            $this->info("\n✅ Database optimization complete!");
            $this->info("Optimized tables: {$optimized}");
            if ($errors > 0) {
                $this->warn("Errors: {$errors}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Database optimization failed: " . $e->getMessage());
            Log::error("Database optimization error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}





