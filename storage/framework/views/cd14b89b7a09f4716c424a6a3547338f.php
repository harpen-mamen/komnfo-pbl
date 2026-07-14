<?php
    $size = $getSize() ?? 'lg';
    $stateColor = $getStateColor();
    $stateIcon = $getStateIcon();

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        match ($stateColor) {
            'danger' => 'text-danger-500',
            'primary' => 'text-primary-500',
            'success' => 'text-success-500',
            'warning' => 'text-warning-500',
            null => \Illuminate\Support\Arr::toCssClasses(['text-gray-700', 'dark:text-gray-200' => config('tables.dark_mode')]),
            default => $stateColor,
        },
        match ($size) {
            'xs' => 'h-3 w-3',
            'sm' => 'h-4 w-4',
            'md' => 'h-5 w-5',
            'lg' => 'h-6 w-6',
            'xl' => 'h-7 w-7',
            default => null,
        },
    ]);
?>

<div
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class([
                "filament-tables-icon-column filament-tables-icon-column-size-{$size}",
                'px-4 py-3' => ! $isInline(),
            ])); ?>

>
    <?php if($stateIcon): ?>
        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $stateIcon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => $iconClasses]); ?>
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
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\icon-column.blade.php ENDPATH**/ ?>