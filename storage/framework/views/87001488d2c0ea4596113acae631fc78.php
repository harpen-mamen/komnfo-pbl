<div <?php echo e($attributes->class(['filament-global-search-input'])); ?>>
    <label for="globalSearchInput" class="sr-only">
        <?php echo e(__('filament::global-search.field.label')); ?>

    </label>

    <div class="group relative max-w-md">
        <span
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'pointer-events-none absolute inset-y-0 left-0 flex h-10 w-10 items-center justify-center text-gray-500 group-focus-within:text-primary-500',
                'dark:text-gray-400' => config('filament.dark_mode'),
            ]); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-o-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5','wire:loading.remove.delay' => true,'wire:target' => 'search']); ?>
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

            <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-5 w-5','wire:loading.delay' => true,'wire:target' => 'search']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5','wire:loading.delay' => true,'wire:target' => 'search']); ?>
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
        </span>

        <input
            wire:model.debounce.500ms="search"
            id="globalSearchInput"
            placeholder="<?php echo e(__('filament::global-search.field.placeholder')); ?>"
            type="search"
            autocomplete="off"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'block h-10 w-full rounded-lg border-transparent bg-gray-400/10 pl-10 placeholder-gray-500 outline-none transition duration-75 focus:border-primary-500 focus:bg-white focus:placeholder-gray-400 focus:ring-1 focus:ring-inset focus:ring-primary-500',
                'dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400' => config('filament.dark_mode'),
            ]); ?>"
        />
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\global-search\input.blade.php ENDPATH**/ ?>