<?php
namespace App\Services;

use App\Models\Finance;
use App\Models\Journal;

class JurnalService
{
    /** Membuat (atau menimpa) jurnal debit-kredit untuk satu transaksi finance. */
    public static function catat(Finance $f, ?string $akunDebitPemasukan = null): Journal
    {
        Journal::where('finance_id', $f->id)->delete();

        if ($f->jenis === 'pemasukan') {
            $debit  = $akunDebitPemasukan ?? 'Kas';
            $kredit = 'Pendapatan '.$f->kategori;
        } else {
            $debit  = 'Beban '.$f->kategori;
            $kredit = 'Kas';
        }

        return Journal::create([
            'finance_id' => $f->id, 'akun_debit' => $debit, 'akun_kredit' => $kredit,
            'nominal' => $f->nominal, 'tanggal' => $f->tanggal, 'keterangan' => $f->deskripsi,
        ]);
    }
}