<?php $__env->startSection('content'); ?>
<h1>Laporan Data Pendidikan Bulanan</h1>
<table>
    <thead>
        <tr>
            <th>Periode</th><th>Bulan</th><th>Tahun</th><th>Sekolah</th><th>Kecamatan</th><th>Desa</th><th>Siswa</th><th>L</th><th>P</th><th>Putus</th><th>Lulus</th><th>Naik</th><th>Tidak Naik</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($row->periodePelaporan?->nama_periode); ?></td><td><?php echo e($row->periode_bulan); ?></td><td><?php echo e($row->periode_tahun); ?></td><td><?php echo e($row->sekolah); ?></td><td><?php echo e($row->kecamatan?->nama); ?></td><td><?php echo e($row->desa?->nama); ?></td><td><?php echo e($row->jumlah_siswa); ?></td><td><?php echo e($row->laki_laki); ?></td><td><?php echo e($row->perempuan); ?></td><td><?php echo e($row->putus_sekolah); ?></td><td><?php echo e($row->lulus); ?></td><td><?php echo e($row->naik_kelas); ?></td><td><?php echo e($row->tidak_naik); ?></td><td><?php echo e($row->status); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\pbl\resources\views\laporan\pendidikan-bulanan.blade.php ENDPATH**/ ?>