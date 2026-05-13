<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'status',
        'recorded_at',
        'action_note',
        'consecutive_unsafe_count',
        'is_emergency',
        'unsafe_detected_at',
        'action_taken_at',
        'response_time_minutes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'unsafe_detected_at' => 'datetime',
        'action_taken_at' => 'datetime',
        'is_emergency' => 'boolean',
    ];

    protected $appends = ['recommendation', 'action_required'];

    /**
     * Get the device that owns this monitoring record.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get incident markers for this monitoring record.
     */
    public function incidentMarkers()
    {
        return $this->hasMany(IncidentMarker::class);
    }

    /**
     * Get recommendation text based on current temperature and humidity
     */
    public function getRecommendationAttribute()
    {
        $recommendations = [];
        
        if ($this->temperature >= 31) {
            $recommendations[] = "🌡️ Suhu terlalu tinggi (>=31°C) - Turunkan AC";
        } elseif ($this->temperature <= 29) {
            $recommendations[] = "❄️ Suhu terlalu rendah (<=29°C) - Naikkan suhu AC / tutup ventilasi";
        }

        return !empty($recommendations) ? implode(' | ', $recommendations) : "✅ Kondisi normal, tidak perlu tindakan";
    }

    /**
     * Check if action is required
     */
    public function getActionRequiredAttribute()
    {
        return in_array($this->status, ['Panas', 'Dingin']);
    }

    /**
     * Get summary of recommendations in safe format
     */
    public function getRecommendationListAttribute()
    {
        $recommendations = [];
        
        if ($this->temperature >= 31) {
            $recommendations[] = "Turunkan suhu AC";
        } elseif ($this->temperature <= 29) {
            $recommendations[] = "Naikkan suhu AC / tutup ventilasi";
        }

        return $recommendations;
    }

    /**
     * Check if this monitoring data represents an emergency condition
     * (unsafe condition for more than 5 minutes)
     */
    public static function checkEmergencyCondition($deviceId)
    {
        // Ambil data terbaru terlebih dahulu
        $latest = self::where('device_id', $deviceId)->latest('recorded_at')->first();
        
        // Jika data terbaru sudah "Aman", maka kondisi darurat dianggap selesai (banner hilang)
        if (!$latest || $latest->status === 'Aman') {
            return false;
        }

        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        
        $unsafeCount = self::where('device_id', $deviceId)
            ->whereIn('status', ['Panas', 'Dingin'])
            ->where('recorded_at', '>=', $fiveMinutesAgo)
            ->count();
        
        // Tetap anggap darurat jika ada 5+ data "Tidak Aman" dalam 5 menit terakhir
        return $unsafeCount >= 5;
    }

    /**
     * Get the latest unsafe condition details
     */
    public static function getLatestUnsafeDetails($deviceId)
    {
        return self::where('device_id', $deviceId)
            ->whereIn('status', ['Panas', 'Dingin'])
            ->latest('recorded_at')
            ->first();
    }

    /**
     * Count unsafe conditions in the last hour
     */
    public static function countUnsafeToday($deviceId)
    {
        $today = Carbon::today();
        
        return self::where('device_id', $deviceId)
            ->whereIn('status', ['Panas', 'Dingin'])
            ->whereDate('recorded_at', $today)
            ->count();
    }

    /**
     * Get hourly data for chart
     */
    public static function getHourlyData($deviceId, $date = null)
    {
        if (!$date) {
            $date = Carbon::today();
        }

        return self::where('device_id', $deviceId)
            ->whereDate('recorded_at', $date)
            ->selectRaw('HOUR(recorded_at) as hour')
            ->selectRaw('ROUND(AVG(temperature), 2) as avg_temp')
            ->selectRaw('ROUND(AVG(humidity), 2) as avg_humidity')
            ->selectRaw('MAX(temperature) as max_temp')
            ->selectRaw('MIN(temperature) as min_temp')
            ->selectRaw('MAX(humidity) as max_humidity')
            ->selectRaw('MIN(humidity) as min_humidity')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
