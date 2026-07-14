<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'actions' => null,
    'columnSearches' => false,
    'description' => null,
    'heading',
    'icon',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'actions' => null,
    'columnSearches' => false,
    'description' => null,
    'heading',
    'icon',
]); ?>
<?php foreach (array_filter(([
    'actions' => null,
    'columnSearches' => false,
    'description' => null,
    'heading',
    'icon',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div
    <?php echo e($attributes->class([
            'filament-tables-empty-state mx-auto flex flex-1 flex-col items-center justify-center space-y-6 bg-white p-6 text-center',
            'dark:bg-gray-800' => config('tables.dark_mode'),
        ])); ?>

>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-primary-500',
            'dark:bg-gray-700' => config('tables.dark_mode'),
        ]); ?>"
    >
        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6','wire:loading.remove.delay' => true,'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'']); ?>
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

        <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-6 w-6','wire:loading.delay' => true,'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6','wire:loading.delay' => true,'wire:target' => ''.e(implode(',', \Filament\Tables\Table::LOADING_TARGETS)).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
    </div>

    <div class="max-w-md space-y-1">
        <?php if (isset($component)) { $__componentOriginal3a47edca5cfdbec7223a479baefe8ffa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a47edca5cfdbec7223a479baefe8ffa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.empty-state.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::empty-state.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php echo e($heading); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a47edca5cfdbec7223a479baefe8ffa)): ?>
<?php $attributes = $__attributesOriginal3a47edca5cfdbec7223a479baefe8ffa; ?>
<?php unset($__attributesOriginal3a47edca5cfdbec7223a479baefe8ffa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a47edca5cfdbec7223a479baefe8ffa)): ?>
<?php $component = $__componentOriginal3a47edca5cfdbec7223a479baefe8ffa; ?>
<?php unset($__componentOriginal3a47edca5cfdbec7223a479baefe8ffa); ?>
<?php endif; ?>

        <?php if($description): ?>
            <?php if (isset($component)) { $__componentOriginal5fca776d8f90795e89ea34d84cbdc3a6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fca776d8f90795e89ea34d84cbdc3a6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.empty-state.description','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::empty-state.description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e($description); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fca776d8f90795e89ea34d84cbdc3a6)): ?>
<?php $attributes = $__attributesOriginal5fca776d8f90795e89ea34d84cbdc3a6; ?>
<?php unset($__attributesOriginal5fca776d8f90795e89ea34d84cbdc3a6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fca776d8f90795e89ea34d84cbdc3a6)): ?>
<?php $component = $__componentOriginal5fca776d8f90795e89ea34d84cbdc3a6; ?>
<?php unset($__componentOriginal5fca776d8f90795e89ea34d84cbdc3a6); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if($actions): ?>
        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'alignment' => 'center','wrap' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'alignment' => 'center','wrap' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $attributes = $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94)): ?>
<?php $component = $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94; ?>
<?php unset($__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($columnSearches): ?>
        <?php if (isset($component)) { $__componentOriginal8088337a56406b46fecf9bd6589edc2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8088337a56406b46fecf9bd6589edc2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.link','data' => ['wire:click' => '$set(\'tableColumnSearchQueries\', [])','color' => 'danger','tag' => 'button','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'tableColumnSearchQueries\', [])','color' => 'danger','tag' => 'button','size' => 'sm']); ?>
            <?php echo e(__('tables::table.empty.buttons.reset_column_searches.label')); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8088337a56406b46fecf9bd6589edc2f)): ?>
<?php $attributes = $__attributesOriginal8088337a56406b46fecf9bd6589edc2f; ?>
<?php unset($__attributesOriginal8088337a56406b46fecf9bd6589edc2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8088337a56406b46fecf9bd6589edc2f)): ?>
<?php $component = $__componentOriginal8088337a56406b46fecf9bd6589edc2f; ?>
<?php unset($__componentOriginal8088337a56406b46fecf9bd6589edc2f); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\empty-state\index.blade.php ENDPATH**/ ?>