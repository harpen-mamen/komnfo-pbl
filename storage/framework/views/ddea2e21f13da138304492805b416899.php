<html>
<body>
    <p>Halo Tim Kominfo,</p>
    <p>Data baru dari Kecamatan <strong><?php echo e($kecamatan); ?></strong> menunggu verifikasi. Detail snapshot:</p>
    <pre><?php echo e(json_encode($snapshot, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
    <p>Silakan cek panel verifikasi untuk detail lebih lanjut.</p>
</body>
</html>
<?php /**PATH C:\pbl\resources\views\emails\submission_snapshot.blade.php ENDPATH**/ ?>