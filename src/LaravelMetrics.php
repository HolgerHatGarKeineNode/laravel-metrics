<?php

namespace Eliseekn\LaravelMetrics;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * LaravelMetrics
 * 
 * Generate metrics and trends data from your database
 */
class LaravelMetrics
{
    public const TODAY = 'today';
    public const DAY = 'day';
    public const WEEK = 'week';
    public const MONTH = 'month';
    public const YEAR = 'year';
    public const QUATER_YEAR = 'quater_year';
    public const HALF_YEAR = 'half_year';

    public const COUNT = 'COUNT';
    public const AVERAGE = 'AVG';
    public const SUM = 'SUM';
    public const MAX = 'MAX';
    public const MIN = 'MIN';

    private static function getMetricsData(string $table, string $column, mixed $period, string $type, ?string $whereRaw = null)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $week = Carbon::now()->weekOfYear;

        if (is_array($period)) {
            list($start_date, $end_date) = array_values($period);

            return DB::table($table)
                ->selectRaw("$type($column) as data")
                ->whereBetween(DB::raw('date(created_at)'), [$start_date, $end_date])
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first();
        }

        if (!is_string($period)) return null;

        return match ($period) {
            self::TODAY => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->where(DB::raw('date(created_at)'), Carbon::now()->toDateString())
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::DAY => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(DB::raw('month(created_at)'), $month)
                ->where(DB::raw('week(created_at)'), $week)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::WEEK => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(DB::raw('month(created_at)'), $month)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::MONTH => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::YEAR => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::HALF_YEAR => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->whereBetween(DB::raw('month(created_at)'), [Carbon::now()->subMonths(6)->month, $month])
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            self::QUATER_YEAR => DB::table($table)
                ->selectRaw("$type($column) as data")
                ->whereBetween(DB::raw('month(created_at)'), [Carbon::now()->subMonths(3)->month, $month])
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->first(),
            default => null,
        };
    }

    private static function getTrendsData(string $table, string $column, mixed $period, string $type, ?string $whereRaw = null)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $week = Carbon::now()->weekOfYear;

        if (is_array($period)) {
            list($start_date, $end_date) = array_values($period);

            return DB::table($table)
                ->selectRaw("$type($column) as data, date(created_at) as label")
                ->whereBetween(DB::raw('date(created_at)'), [$start_date, $end_date])
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        if (!is_string($period)) return [];

        return match ($period) {
            self::TODAY => DB::table($table)
                ->selectRaw("$type($column) as data, dayname(created_at) as label, weekday(created_at) as week_day")
                ->where(DB::raw('date(created_at)'), Carbon::now()->toDateString())
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'week_day')
                ->orderBy('week_day')
                ->get(),
            self::DAY => DB::table($table)
                ->selectRaw("$type($column) as data, dayname(created_at) as label, weekday(created_at) as week_day")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(DB::raw('month(created_at)'), $month)
                ->where(DB::raw('week(created_at)'), $week)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'week_day')
                ->orderBy('week_day')
                ->get(),
            self::WEEK => DB::table($table)
                ->selectRaw("$type($column) as data, dayname(created_at) as label, weekday(created_at) as week_day")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(DB::raw('month(created_at)'), $month)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'week_day')
                ->orderBy('week_day')
                ->get(),
            self::MONTH => DB::table($table)
                ->selectRaw("$type($column) as data, monthname(created_at) as label, month(created_at) as month")
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'month')
                ->orderBy('month')
                ->get(),
            self::YEAR => DB::table($table)
                ->selectRaw("$type($column) as data, year(created_at) as label")
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
            self::HALF_YEAR => DB::table($table)
                ->selectRaw("$type($column) as data, monthname(created_at) as label, month(created_at) as month")
                ->whereBetween(DB::raw('month(created_at)'), [Carbon::now()->subMonths(6)->month, $month])
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'month')
                ->orderBy('month')
                ->get(),
            self::QUATER_YEAR => DB::table($table)
                ->selectRaw("$type($column) as data, monthname(created_at) as label, month(created_at) as month")
                ->whereBetween(DB::raw('month(created_at)'), [Carbon::now()->subMonths(3)->month, $month])
                ->where(DB::raw('year(created_at)'), $year)
                ->where(function ($q) use ($whereRaw) {
                    if (!is_null($whereRaw)) {
                        $q->whereRaw($whereRaw);
                    }
                })
                ->groupBy('label', 'month')
                ->orderBy('month')
                ->get(),
            default => [],
        };
    }
    
    /**
     * Generate metrics data
     *
     * @param  string $table
     * @param  string $column
     * @param  string|array $period
     * @param  string $type
     * @param  string|null $whereRaw
     * @return int
     */
    public static function getMetrics(string $table, string $column, mixed $period, string $type, ?string $whereRaw = null): int
    {
        $metricsData = self::getMetricsData($table, $column, $period, $type, $whereRaw);

        return is_null($metricsData) ? 0 : (int) $metricsData->data;
    }
    
    /**
     * Generate trends data for charts
     *
     * @param  string $table
     * @param  string $column
     * @param  string|array $period
     * @param  string $type
     * @param  string|null $whereRaw
     * @return array
     */
    public static function getTrends(string $table, string $column, mixed $period, string $type, ?string $whereRaw = null): array
    {
        $trendsData = self::getTrendsData($table, $column, $period, $type, $whereRaw);
        $result = [];

        foreach ($trendsData as $data) {
            $result['labels'][] = $data->label;
            $result['data'][] = (int) $data->data;
        }

        return $result;
    }
}
