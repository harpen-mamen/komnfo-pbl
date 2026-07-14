@extends('laporan.layout')

@section('content')
<h1>Laporan Penduduk</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>NIK</th><th>Nama</th><th>JK</th><th>Nomor KK</th><th>Kode Rumah</th><th>Tanggal Lahir</th><th>Agama</th><th>Pendidikan</th><th>Pekerjaan</th></tr></thead>
    <tbody>
        @foreach ($rows as $row)
            <tr><td>{{ $row->nik }}</td><td>{{ $row->nama_lengkap }}</td><td>{{ $row->jenis_kelamin }}</td><td>{{ $row->keluarga?->nomor_kk }}</td><td>{{ $row->keluarga?->rumah?->kode_rumah }}</td><td>{{ $row->tanggal_lahir?->format('Y-m-d') }}</td><td>{{ $row->agama }}</td><td>{{ $row->pendidikan }}</td><td>{{ $row->pekerjaan }}</td></tr>
        @endforeach
    </tbody>
</table>
@endsection
