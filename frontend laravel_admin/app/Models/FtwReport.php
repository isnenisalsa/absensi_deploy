<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FtwReport extends Model
{
    use HasFactory;

    protected $table = 'ftw_reports';

    public $timestamps = false; // Prisma uses custom created_at

    protected $fillable = [
        'nrp',
        'ftw_date',
        'kehadiran_onsite',
        'unit_dioperasikan',
        'pola_shift',
        'shift',
        'jam_tidur_12_jam',
        'jam_tidur_36_jam',
        'jam_bangun',
        'konsumsi_obat',
        'punya_masalah',
        'gejala_kesehatan',
        'status_ftw',
        'created_at'
    ];

    protected $casts = [
        'ftw_date' => 'date',
        'kehadiran_onsite' => 'boolean',
        'konsumsi_obat' => 'boolean',
        'punya_masalah' => 'boolean',
        'gejala_kesehatan' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the ftw report.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }
}
