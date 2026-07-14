@extends('laporan.layout')

@section('content')
<h1>Laporan Data Pendidikan Bulanan</h1>
<table>
    <thead>
        <tr>
            <th>Periode</th><th>Bulan</th><th>Tahun</th><th>Sekolah</th><th>Kecamatan</th><th>Desa</th><th>Siswa</th><th>L</th><th>P</th><th>Putus</th><th>Lulus</th><th>Naik</th><th>Tidak Naik</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row->periodePelaporan?->nama_periode }}</td><td>{{ $row->periode_bulan }}</td><td>{{ $row->periode_tahun }}</td><td>{{ $row->sekolah }}</td><td>{{ $row->kecamatan?->nama }}</td><td>{{ $row->desa?->nama }}</td><td>{{ $row->jumlah_siswa }}</td><td>{{ $row->laki_laki }}</td><td>{{ $row->perempuan }}</td><td>{{ $row->putus_sekolah }}</td><td>{{ $row->lulus }}</td><td>{{ $row->naik_kelas }}</td><td>{{ $row->tidak_naik }}</td><td>{{ $row->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
