@extends('layouts.main')
@section('title', 'ERP - Payroll')
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><input type="month" name="periode" value="{{ request('periode') }}" class="form-control"></div>
    <div class="col"><button class="btn btn-secondary">Filter</button> <a href="{{ route('payroll.create') }}" class="btn btn-primary">+ Penggajian</a></div>
</form>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>Periode</th><th>Karyawan</th><th class="text-end">Gaji Pokok</th><th class="text-end">Tunjangan</th><th class="text-end">Potongan</th><th class="text-end">Total</th><th></th></tr></thead>
    @foreach($data as $p)
    <tr><td>{{ $p->periode }}</td><td>{{ $p->employee->nama }}</td>
        <td class="text-end">{{ number_format($p->gaji_pokok,0,',','.') }}</td>
        <td class="text-end">{{ number_format($p->tunjangan,0,',','.') }}</td>
        <td class="text-end">{{ number_format($p->potongan,0,',','.') }}</td>
        <td class="text-end"><strong>{{ number_format($p->total_gaji,0,',','.') }}</strong></td>
        <td><form action="{{ route('payroll.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus data gaji ini?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus</button></form></td></tr>
    @endforeach
</table>
{{ $data->links() }}
@endsection