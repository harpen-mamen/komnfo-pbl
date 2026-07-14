@extends('laporan.layout')

@section('content')
<h1>Laporan KK</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>Nomor KK</th><th>Kepala Keluarga</th><th>Kode Rumah</th><th>Alamat</th><th>Jumlah Anggota</th><th>Status Ekonomi</th></tr></thead>
    <tbody>
        @foreach ($rows as $row)
            <tr><td>{{ $row->nomor_kk }}</td><td>{{ $row->nama_kepala_keluarga }}</td><td>{{ $row->rumah?->kode_rumah }}</td><td>{{ $row->rumah?->alamat_lengkap }}</td><td>{{ $row->jumlah_anggota }}</td><td>{{ $row->status_ekonomi }}</td></tr>
        @endforeach
    </tbody>
</table>
@endsection
