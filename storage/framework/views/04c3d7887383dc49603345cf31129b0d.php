<?php $__env->startSection('content'); ?>
<h1>Laporan Data Kesehatan Bulanan</h1>
<table>
    <thead>
        <tr>
            <th>Periode</th><th>Bulan</th><th>Tahun</th><th>Puskesmas</th><th>Kecamatan</th><th>Desa</th><th>Balita</th><th>Ibu Hamil</th><th>Stunting</th><th>Gizi Buruk</th><th>Disabilitas</th><th>Kematian</th><th>Kelahiran</th><th>BPJS Aktif</th><th>BPJS Tidak Aktif</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($row->periodePelaporan?->nama_periode); ?></td><td><?php echo e($row->periode_bulan); ?></td><td><?php echo e($row->periode_tahun); ?></td><td><?php echo e($row->puskesmas); ?></td><td><?php echo e($row->kecamatan?->nama); ?></td><td><?php echo e($row->desa?->nama); ?></td><td><?php echo e($row->jumlah_balita); ?></td><td><?php echo e($row->jumlah_ibu_hamil); ?></td><td><?php echo e($row->stunting); ?></td><td><?php echo e($row->gizi_buruk); ?></td><td><?php echo e($row->disabilitas); ?></td><td><?php echo e($row->kematian); ?></td><td><?php echo e($row->kelahiran); ?></td><td><?php echo e($row->bpjs_aktif); ?></td><td><?php echo e($row->bpjs_tidak_aktif); ?></td><td><?php echo e($row->status); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\pbl\resources\views\laporan\kesehatan-bulanan.blade.php ENDPATH**/ ?>