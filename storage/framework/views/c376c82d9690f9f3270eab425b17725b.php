<?php $__env->startSection('content'); ?>
<h1>Laporan KK</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>Nomor KK</th><th>Kepala Keluarga</th><th>Kode Rumah</th><th>Alamat</th><th>Jumlah Anggota</th><th>Status Ekonomi</th></tr></thead>
    <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($row->nomor_kk); ?></td><td><?php echo e($row->nama_kepala_keluarga); ?></td><td><?php echo e($row->rumah?->kode_rumah); ?></td><td><?php echo e($row->rumah?->alamat_lengkap); ?></td><td><?php echo e($row->jumlah_anggota); ?></td><td><?php echo e($row->status_ekonomi); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\pbl\resources\views\laporan\keluarga.blade.php ENDPATH**/ ?>