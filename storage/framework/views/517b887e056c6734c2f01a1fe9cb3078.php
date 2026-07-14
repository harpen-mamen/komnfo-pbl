<p
    <?php echo e($attributes->class([
            'filament-tables-empty-state-description whitespace-normal text-sm font-medium text-gray-500',
            'dark:text-gray-400' => config('tables.dark_mode'),
        ])); ?>

>
    <?php echo e($slot); ?>

</p>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\empty-state\description.blade.php ENDPATH**/ ?>