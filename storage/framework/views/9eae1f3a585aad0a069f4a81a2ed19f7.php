<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'colspan',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'colspan',
]); ?>
<?php foreach (array_filter(([
    'colspan',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div
    wire:key="<?php echo e($this->id); ?>.table.reorder.indicator"
    x-cloak
    <?php echo e($attributes->class(['filament-tables-reorder-indicator whitespace-nowrap bg-primary-500/10 px-4 py-2 text-sm'])); ?>

>
    <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['wire:loading.delay' => true,'wire:target' => 'reorderTable','class' => 'mr-3 h-4 w-4 text-primary-500 rtl:ml-3 rtl:mr-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:loading.delay' => true,'wire:target' => 'reorderTable','class' => 'mr-3 h-4 w-4 text-primary-500 rtl:ml-3 rtl:mr-0']); ?>
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

    <span>
        <?php echo e(__('tables::table.reorder_indicator')); ?>

    </span>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\reorder\indicator.blade.php ENDPATH**/ ?>