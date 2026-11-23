@props(['s'])
@php
$warna = ['pemasukan'=>'success','lunas'=>'success','aktif'=>'success','diterima'=>'success','deal'=>'success',
 'diterbitkan'=>'primary','dikonfirmasi'=>'primary','dipesan'=>'primary','negotiation'=>'warning','diproses'=>'warning',
 'jatuh_tempo'=>'warning','prospect'=>'info','dikirim'=>'info','pengeluaran'=>'danger','dibatalkan'=>'danger',
 'lost'=>'danger','menunggu'=>'secondary','draft'=>'secondary','nonaktif'=>'secondary','masuk'=>'success','keluar'=>'danger'];
@endphp
<span class="badge rounded-pill text-bg-{{ $warna[$s] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</span>