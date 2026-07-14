<html>
<body>
    <p>Halo Tim Kominfo,</p>
    <p>Data baru dari Kecamatan <strong>{{ $kecamatan }}</strong> menunggu verifikasi. Detail snapshot:</p>
    <pre>{{ json_encode($snapshot, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    <p>Silakan cek panel verifikasi untuk detail lebih lanjut.</p>
</body>
</html>
