<nav
    <?php echo e($attributes->class([
            'filament-tabs flex items-center space-x-1 overflow-x-auto rounded-xl bg-gray-500/5 p-1 text-sm text-gray-600 rtl:space-x-reverse',
            'dark:bg-gray-500/20' => config('filament.dark_mode'),
        ])); ?>

>
    <?php echo e($slot); ?>

</nav>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\tabs\index.blade.php ENDPATH**/ ?>