<p
    <?php echo e($attributes->class([
            'filament-notifications-date text-xs text-gray-500',
            'dark:text-gray-300' => config('notifications.dark_mode'),
        ])); ?>

>
    <?php echo e($slot); ?>

</p>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\date.blade.php ENDPATH**/ ?>