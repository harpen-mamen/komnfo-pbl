<?php
    $state = $getTags();
?>

<div
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class([
                'filament-tables-tags-column flex flex-wrap items-center gap-1',
                'px-4 py-3' => ! $isInline(),
                match ($getAlignment()) {
                    'start' => 'justify-start',
                    'center' => 'justify-center',
                    'end' => 'justify-end',
                    'left' => 'justify-start rtl:flex-row-reverse',
                    'center' => 'justify-center',
                    'right' => 'justify-end rtl:flex-row-reverse',
                    default => null,
                },
            ])); ?>

>
    <?php $__currentLoopData = array_slice($getTags(), 0, $getLimit()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'min-h-6 inline-flex items-center justify-center whitespace-normal rounded-xl bg-primary-500/10 px-2 py-0.5 text-sm font-medium tracking-tight text-primary-700',
                'dark:text-primary-500' => config('tables.dark_mode'),
            ]); ?>"
        >
            <?php echo e($tag); ?>

        </span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($hasActiveLimit()): ?>
        <span class="ml-1 text-xs">
            <?php echo e(trans_choice('tables::table.columns.tags.more', count($getTags()) - $getLimit())); ?>

        </span>
    <?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\tags-column.blade.php ENDPATH**/ ?>