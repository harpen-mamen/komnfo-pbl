<?php $__env->startSection('content'); ?>
<h1>Laporan Rumah</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>Kode Rumah</th><th>Alamat</th><th>Koordinat</th><th>Status</th><th>Jumlah KK</th></tr></thead>
    <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($row->kode_rumah); ?></td><td><?php echo e($row->alamat_lengkap); ?></td><td><?php echo e($row->latitude); ?>, <?php echo e($row->longitude); ?></td><td><?php echo e($row->status_rumah); ?></td><td><?php echo e($row->keluarga_count); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\pbl\resources\views\laporan\rumah.blade.php ENDPATH**/ ?>