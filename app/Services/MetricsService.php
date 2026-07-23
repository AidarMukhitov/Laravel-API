<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * MetricsService tracks contact form submission counts.
 *
 * Data is stored in storage/app/metrics.json with the structure:
 * {
 *   "today": 5,
 *   "week": 12,
 *   "total": 42,
 *   "last_reset_daily": "2025-01-15",
 *   "last_reset_weekly": "2025-01-13"
 * }
 */
class MetricsService
{
    protected string $metricsPath;

    public function __construct()
    {
        $this->metricsPath = storage_path('app/metrics.json');
    }

    /**
     * Increment all counters, performing date-based resets as needed.
     */
    public function increment(): void
    {
        $metrics = $this->load();
        $now = Carbon::now();

        // Reset daily counter if a new day has started
        if ($metrics['last_reset_daily'] !== $now->format('Y-m-d')) {
            $metrics['today'] = 0;
            $metrics['last_reset_daily'] = $now->format('Y-m-d');
        }

        // Reset weekly counter if a new week (Monday) has started
        if ($metrics['last_reset_weekly'] !== $now->format('Y-m-d')) {
            $metrics['week'] = 0;
            $metrics['last_reset_weekly'] = $now->format('Y-m-d');
        }

        $metrics['today']++;
        $metrics['week']++;
        $metrics['total']++;

        $this->save($metrics);
    }

    /**
     * Return the current metrics snapshot.
     *
     * @return array<string, int>
     */
    public function get(): array
    {
        return $this->load();
    }

    /**
     * Load metrics from disk, initializing if the file doesn't exist.
     *
     * @return array<string, mixed>
     */
    protected function load(): array
    {
        if (!File::exists($this->metricsPath)) {
            return $this->initialize();
        }

        $data = json_decode(File::get($this->metricsPath), true);

        if (!is_array($data)) {
            Log::warning('metrics.json is corrupted, reinitializing.');

            return $this->initialize();
        }

        // Ensure all keys exist
        $defaults = $this->defaults();

        return array_merge($defaults, $data);
    }

    /**
     * Save metrics to disk.
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function save(array $metrics): void
    {
        $dir = dirname($this->metricsPath);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($this->metricsPath, json_encode($metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Create a fresh metrics file with zeroed counters.
     *
     * @return array<string, mixed>
     */
    protected function initialize(): array
    {
        $now = Carbon::now();

        $metrics = $this->defaults();
        $metrics['last_reset_daily'] = $now->format('Y-m-d');
        $metrics['last_reset_weekly'] = $this->currentWeekMonday($now)->format('Y-m-d');

        $this->save($metrics);

        return $metrics;
    }

    /**
     * Default metrics structure.
     *
     * @return array<string, int|string>
     */
    protected function defaults(): array
    {
        return [
            'today' => 0,
            'week' => 0,
            'total' => 0,
            'last_reset_daily' => '',
            'last_reset_weekly' => '',
        ];
    }

    /**
     * Get the Monday of the current week.
     */
    protected function currentWeekMonday(Carbon $date): Carbon
    {
        return $date->copy()->startOfWeek(Carbon::MONDAY);
    }
}
