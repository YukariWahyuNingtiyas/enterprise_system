<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $table = 'ai_analytics_logs';
    protected $fillable = ['modul','jenis_analisis','ref_tabel','ref_id','skor','hasil'];
}