@extends('laporan.layout')

@section('content')
<h1>Laporan Rumah</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>Kode Rumah</th><th>Alamat</th><th>Koordinat</th><th>Status</th><th>Jumlah KK</th></tr></thead>
    <tbody>
        @foreach ($rows as $row)
            <tr><td>{{ $row->kode_rumah }}</td><td>{{ $row->alamat_lengkap }}</td><td>{{ $row->latitude }}, {{ $row->longitude }}</td><td>{{ $row->status_rumah }}</td><td>{{ $row->keluarga_count }}</td></tr>
        @endforeach
    </tbody>
</table>
@endsection
