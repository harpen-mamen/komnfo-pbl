<?php
    $state = $getState();
    $isCopyable = $isCopyable();
?>

<div
    <?php if($state): ?>
        style="background-color: <?php echo e($state); ?>"
        <?php if($isCopyable): ?>
            x-on:click="
                window.navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from($getCopyableState())->toHtml() ?>)
                $tooltip(<?php echo \Illuminate\Support\Js::from($getCopyMessage())->toHtml() ?>, { timeout: <?php echo \Illuminate\Support\Js::from($getCopyMessageDuration())->toHtml() ?> })
            "
        <?php endif; ?>
    <?php endif; ?>
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class([
                'filament-tables-color-column relative ml-4 flex h-6 w-6 rounded-md',
                'cursor-pointer' => $isCopyable,
            ])); ?>

></div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\color-column.blade.php ENDPATH**/ ?>