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
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Batch</div>
                <div class="mt-2 text-lg font-semibold text-slate-900"><?php echo e($this->record->submission_number); ?></div>
                <div class="mt-1 text-sm text-slate-600"><?php echo e(\App\Models\SubmissionBatch::moduleLabel($this->record->module)); ?></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Status</div>
                <div class="mt-2 text-lg font-semibold text-slate-900"><?php echo e(\App\Models\SubmissionBatch::statusLabel($this->record->status)); ?></div>
                <div class="mt-4 text-sm text-slate-500">Periode</div>
                <div class="mt-1 text-base font-medium text-slate-800">
                    <?php if($this->record->module === \App\Models\SubmissionBatch::MODULE_KESEHATAN): ?>
                        Bulan <?php echo e($this->record->periode_bulan ?? '-'); ?> / <?php echo e($this->record->periode_tahun ?? '-'); ?>

                    <?php elseif($this->record->module === \App\Models\SubmissionBatch::MODULE_PENDIDIKAN): ?>
                        Semester <?php echo e(ucfirst($this->record->semester ?? '-')); ?> / <?php echo e($this->record->periode_tahun ?? '-'); ?>

                    <?php else: ?>
                        Master Data
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Pengirim</div>
                <div class="mt-2 text-lg font-semibold text-slate-900"><?php echo e($this->record->createdBy?->name ?? '-'); ?></div>
                <div class="mt-4 text-sm text-slate-500">Tanggal Kirim</div>
                <div class="mt-1 text-base font-medium text-slate-800"><?php echo e($this->record->submitted_at?->format('d M Y H:i') ?? '-'); ?></div>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Ringkasan Batch</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php $__currentLoopData = $this->getEntitySummary(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="text-sm text-slate-500"><?php echo e($item['label']); ?></div>
                        <div class="mt-1 text-base font-semibold text-slate-900"><?php echo e($item['value'] ?? '-'); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Isi Batch</h2>
                    <p class="mt-1 text-sm text-slate-500">Kominfo melihat seluruh data di dalam batch modul ini.</p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                    <?php echo e(count($this->getBatchItems())); ?> item
                </div>
            </div>

            <div class="mt-4 grid gap-4 xl:grid-cols-2">
                <?php $__empty_1 = true; $__currentLoopData = $this->getBatchItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo e($item['type']); ?></div>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900"><?php echo e($item['title']); ?></h3>
                            <p class="mt-1 text-sm text-slate-600"><?php echo e($item['subtitle'] ?? '-'); ?></p>
                        </div>

                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            <?php $__currentLoopData = $item['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-xl bg-white px-3 py-2">
                                    <dt class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400"><?php echo e($field['label']); ?></dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800"><?php echo e($field['value'] ?? '-'); ?></dd>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </dl>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        Belum ada item pada batch ini.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Riwayat Verifikasi</h2>
            <div class="mt-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $this->getTimeline(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                <?php echo e($item['status_baru'] ?? '-'); ?>

                            </span>
                            <span class="text-sm text-slate-500"><?php echo e(optional($item['waktu'])->format('d M Y H:i')); ?></span>
                        </div>
                        <div class="mt-2 text-sm text-slate-900">
                            <?php echo e($item['catatan'] ?? '-'); ?>

                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            <?php echo e($item['dilakukan_oleh'] ?? '-'); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
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
<?php /**PATH C:\pbl\resources\views\filament\pages\submission-batch-review.blade.php ENDPATH**/ ?>