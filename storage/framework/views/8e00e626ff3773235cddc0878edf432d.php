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
    <?php
        $moduleLookup = collect($modules ?? [])->keyBy('key');
        $previewLookup = collect($preview['modules'] ?? [])->keyBy('key');
    ?>

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                <h2 class="text-xl font-semibold text-slate-900">Kirim Data ke Kominfo</h2>
                <p class="mt-1 text-sm text-slate-600">Pilih modul yang ingin dikirim. Setiap modul akan dibuat menjadi batch terpisah.</p>
            </div>

            <div class="space-y-6 px-5 py-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-medium text-slate-500">Total Rumah</div>
                        <div class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($total_rumah); ?></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-medium text-slate-500">Total KK</div>
                        <div class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($total_keluarga); ?></div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-medium text-slate-500">Total Penduduk</div>
                        <div class="mt-2 text-3xl font-bold text-slate-900"><?php echo e($total_penduduk); ?></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="text-sm font-semibold text-slate-900">Pengiriman Terakhir</div>

                    <?php if(($last_submissions ?? collect())->isNotEmpty()): ?>
                        <div class="mt-3 space-y-3">
                            <?php $__currentLoopData = $last_submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900"><?php echo e($submission->submission_number); ?></div>
                                            <div class="text-xs uppercase tracking-[0.18em] text-slate-500"><?php echo e(\App\Models\SubmissionBatch::moduleLabel($submission->module)); ?></div>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700">
                                            <?php echo e(\App\Models\SubmissionBatch::statusLabel($submission->status)); ?>

                                        </span>
                                    </div>
                                    <div class="mt-3 grid gap-3 text-sm text-slate-700 md:grid-cols-3">
                                        <div>
                                            <div class="font-medium text-slate-500">Waktu</div>
                                            <div><?php echo e($submission->submitted_at?->format('d M Y H:i') ?? '-'); ?></div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-500">Periode</div>
                                            <div>
                                                <?php if($submission->module === \App\Models\SubmissionBatch::MODULE_KESEHATAN): ?>
                                                    Bulan <?php echo e($submission->periode_bulan); ?>/<?php echo e($submission->periode_tahun); ?>

                                                <?php elseif($submission->module === \App\Models\SubmissionBatch::MODULE_PENDIDIKAN): ?>
                                                    Semester <?php echo e(ucfirst($submission->semester)); ?> <?php echo e($submission->periode_tahun); ?>

                                                <?php else: ?>
                                                    Master Data
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-500">Catatan</div>
                                            <div><?php echo e($submission->notes ?? '-'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-3 text-sm text-slate-600">Belum pernah mengirim data.</p>
                    <?php endif; ?>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Bulan Kesehatan</label>
                            <input wire:model.defer="periodeBulan" type="number" min="1" max="12" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Semester Pendidikan</label>
                            <select wire:model.defer="semester" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tahun Periode</label>
                            <input wire:model.defer="periodeTahun" type="number" min="2000" max="2100" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Modul Pengiriman</h3>
                                <p class="text-sm text-slate-500">Centang modul yang ingin dikirim. Setiap modul akan menjadi batch sendiri.</p>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                            <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($modulePreview = $previewLookup->get($key)); ?>
                                <label class="group block cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo e($module['badge']); ?></div>
                                            <div class="mt-1 text-lg font-semibold text-slate-900"><?php echo e($module['label']); ?></div>
                                            <p class="mt-2 text-sm text-slate-600"><?php echo e($module['description']); ?></p>
                                        </div>
                                        <input
                                            type="checkbox"
                                            wire:model.defer="selectedModules"
                                            value="<?php echo e($key); ?>"
                                            class="mt-1 h-5 w-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        />
                                    </div>

                                    <div class="mt-4 grid gap-2 rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Jumlah data</span>
                                            <strong class="font-semibold text-slate-900"><?php echo e(number_format((int) ($modulePreview['count'] ?? 0), 0, ',', '.')); ?></strong>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Status terakhir</span>
                                            <strong class="font-semibold text-slate-900"><?php echo e($modulePreview['latest_status_label'] ?? '-'); ?></strong>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-500">Pengiriman terakhir</span>
                                            <strong class="font-semibold text-slate-900"><?php echo e($modulePreview['latest_submission'] ?? '-'); ?></strong>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model.defer="notes" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Opsional: catatan untuk Kominfo."></textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button','data' => ['type' => 'submit','icon' => 'heroicon-o-paper-airplane']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'heroicon-o-paper-airplane']); ?>
                            Kirim Modul Terpilih
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
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
<?php /**PATH C:\pbl\resources\views\filament\pages\kirim-data-kominfo.blade.php ENDPATH**/ ?>