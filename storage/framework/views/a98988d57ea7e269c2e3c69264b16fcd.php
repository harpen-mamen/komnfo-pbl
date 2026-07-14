<?php if (isset($component)) { $__componentOriginalcb9b2192b9152278a357ab8b3656b740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb9b2192b9152278a357ab8b3656b740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.page','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Jenis Data</div>
                <div class="mt-2 text-lg font-semibold text-gray-900"><?php echo e($this->getEntityTypeLabel()); ?></div>
                <div class="mt-1 text-sm text-gray-600">ID <?php echo e($record->entity_id); ?></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Workflow Status</div>
                <div class="mt-2 text-lg font-semibold text-gray-900"><?php echo e($record->status?->nama ?? '-'); ?></div>
                <div class="mt-4 text-sm text-gray-500">Kecamatan</div>
                <div class="mt-1 text-base font-medium text-gray-800"><?php echo e($record->kecamatan?->nama ?? '-'); ?></div>
                <div class="mt-3 text-sm text-gray-500">Desa</div>
                <div class="mt-1 text-base font-medium text-gray-800"><?php echo e($record->desa?->nama ?? '-'); ?></div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Submitted By</div>
                <div class="mt-2 text-lg font-semibold text-gray-900"><?php echo e($record->dibuatOleh?->name ?? '-'); ?></div>
                <div class="mt-4 text-sm text-gray-500">Submitted Date</div>
                <div class="mt-1 text-base font-medium text-gray-800"><?php echo e($record->tanggal_submit?->format('d M Y H:i') ?? '-'); ?></div>
                <div class="mt-4 text-sm text-gray-500">Verification Date</div>
                <div class="mt-1 text-base font-medium text-gray-800"><?php echo e($record->tanggal_verifikasi?->format('d M Y H:i') ?? '-'); ?></div>
            </div>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Entity Details</h2>
            <p class="mt-2 text-sm text-gray-500">Data <?php echo e($this->getEntityTypeLabel()); ?></p>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php $__empty_1 = true; $__currentLoopData = $this->getEntitySummary(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="text-sm text-gray-500"><?php echo e($item['label']); ?></div>
                        <div class="mt-1 text-base font-semibold text-gray-900"><?php echo e($item['value'] ?? '-'); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                        Detail data tidak tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Workflow Summary</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php $__currentLoopData = $this->getWorkflowSummary(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="text-sm text-gray-500"><?php echo e($item['label']); ?></div>
                        <div class="mt-1 text-base font-semibold text-gray-900"><?php echo e($item['value'] ?? '-'); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Approve History</h2>
            <div class="mt-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $this->getTimeline(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                <?php echo e($item['jenis'] === 'history' ? 'Riwayat' : 'Catatan'); ?>

                            </span>
                            <span class="text-sm text-gray-500"><?php echo e(optional($item['waktu'])->format('d M Y H:i')); ?></span>
                        </div>
                        <div class="mt-2 text-sm text-gray-900">
                            <?php echo e($item['status_baru'] ?? $item['tipe'] ?? '-'); ?>

                        </div>
                        <div class="mt-1 text-sm text-gray-600">
                            <?php echo e($item['catatan'] ?? $item['komentar'] ?? '-'); ?>

                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            <?php echo e($item['dilakukan_oleh'] ?? '-'); ?><?php echo e(! empty($item['role']) ? ' • '.$item['role'] : ''); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                        Belum ada riwayat workflow.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb9b2192b9152278a357ab8b3656b740)): ?>
<?php $attributes = $__attributesOriginalcb9b2192b9152278a357ab8b3656b740; ?>
<?php unset($__attributesOriginalcb9b2192b9152278a357ab8b3656b740); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb9b2192b9152278a357ab8b3656b740)): ?>
<?php $component = $__componentOriginalcb9b2192b9152278a357ab8b3656b740; ?>
<?php unset($__componentOriginalcb9b2192b9152278a357ab8b3656b740); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\resources\views\filament\pages\workflow-review.blade.php ENDPATH**/ ?>