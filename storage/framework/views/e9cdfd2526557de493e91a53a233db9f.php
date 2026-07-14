<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'action' => null,
    'color' => null,
    'icon' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'action' => null,
    'color' => null,
    'icon' => null,
]); ?>
<?php foreach (array_filter(([
    'action' => null,
    'color' => null,
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div
    <?php echo e($attributes->class(array_merge(
            ['filament-forms-field-wrapper-hint flex items-center space-x-2 rtl:space-x-reverse'],
            match ($color) {
                'danger' => [
                    'text-danger-500',
                    'dark:text-danger-300' => config('tables.dark_mode'),
                ],
                'success' => [
                    'text-success-500',
                    'dark:text-success-300' => config('tables.dark_mode'),
                ],
                'warning' => [
                    'text-warning-500',
                    'dark:text-warning-300' => config('filament.dark_mode'),
                ],
                'primary' => [
                    'text-primary-500',
                    'dark:text-primary-300' => config('tables.dark_mode'),
                ],
                default => [
                    'text-gray-500',
                    'dark:text-gray-300' => config('tables.dark_mode'),
                ],
            },
        ))); ?>

>
    <?php if($slot->isNotEmpty()): ?>
        <span class="text-xs leading-tight">
            <?php echo e($slot); ?>

        </span>
    <?php endif; ?>

    <?php if($icon): ?>
        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($action && (! $action->isHidden())): ?>
        <div class="filament-forms-field-wrapper-hint-action">
            <?php echo e($action); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\field-wrapper\hint.blade.php ENDPATH**/ ?>