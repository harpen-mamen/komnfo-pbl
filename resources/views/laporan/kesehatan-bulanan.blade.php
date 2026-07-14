@extends('laporan.layout')

@section('content')
<h1>Laporan Data Kesehatan Bulanan</h1>
<table>
    <thead>
        <tr>
            <th>Periode</th><th>Bulan</th><th>Tahun</th><th>Puskesmas</th><th>Kecamatan</th><th>Desa</th><th>Balita</th><th>Ibu Hamil</th><th>Stunting</th><th>Gizi Buruk</th><th>Disabilitas</th><th>Kematian</th><th>Kelahiran</th><th>BPJS Aktif</th><th>BPJS Tidak Aktif</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row->periodePelaporan?->nama_periode }}</td><td>{{ $row->periode_bulan }}</td><td>{{ $row->periode_tahun }}</td><td>{{ $row->puskesmas }}</td><td>{{ $row->kecamatan?->nama }}</td><td>{{ $row->desa?->nama }}</td><td>{{ $row->jumlah_balita }}</td><td>{{ $row->jumlah_ibu_hamil }}</td><td>{{ $row->stunting }}</td><td>{{ $row->gizi_buruk }}</td><td>{{ $row->disabilitas }}</td><td>{{ $row->kematian }}</td><td>{{ $row->kelahiran }}</td><td>{{ $row->bpjs_aktif }}</td><td>{{ $row->bpjs_tidak_aktif }}</td><td>{{ $row->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
