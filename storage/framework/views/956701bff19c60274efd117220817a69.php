<div
    <?php echo e($attributes->class([
            'mx-auto my-6 flex flex-col items-center justify-center space-y-4 bg-white text-center',
            'dark:bg-gray-800' => config('notifications.dark_mode'),
        ])); ?>

>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-500',
            'dark:bg-gray-700' => config('notifications.dark_mode'),
        ]); ?>"
    >
        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-o-bell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
    </div>

    <div class="max-w-md space-y-1">
        <h2
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'text-lg font-bold tracking-tight',
                'dark:text-white' => config('notifications.dark_mode'),
            ]); ?>"
        >
            <?php echo e(__('notifications::database.modal.empty.heading')); ?>

        </h2>

        <p
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'whitespace-normal text-sm font-medium text-gray-500',
                'dark:text-gray-400' => config('notifications.dark_mode'),
            ]); ?>"
        >
            <?php echo e(__('notifications::database.modal.empty.description')); ?>

        </p>
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\database\modal\empty-state.blade.php ENDPATH**/ ?>