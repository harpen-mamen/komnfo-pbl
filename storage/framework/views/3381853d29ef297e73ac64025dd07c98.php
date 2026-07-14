<?php $__env->startSection('content'); ?>
<h1>Laporan Penduduk</h1>
<p>SIPDT Kabupaten Kepulauan Sangihe</p>
<table>
    <thead><tr><th>NIK</th><th>Nama</th><th>JK</th><th>Nomor KK</th><th>Kode Rumah</th><th>Tanggal Lahir</th><th>Agama</th><th>Pendidikan</th><th>Pekerjaan</th></tr></thead>
    <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($row->nik); ?></td><td><?php echo e($row->nama_lengkap); ?></td><td><?php echo e($row->jenis_kelamin); ?></td><td><?php echo e($row->keluarga?->nomor_kk); ?></td><td><?php echo e($row->keluarga?->rumah?->kode_rumah); ?></td><td><?php echo e($row->tanggal_lahir?->format('Y-m-d')); ?></td><td><?php echo e($row->agama); ?></td><td><?php echo e($row->pendidikan); ?></td><td><?php echo e($row->pekerjaan); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\pbl\resources\views\laporan\penduduk.blade.php ENDPATH**/ ?>