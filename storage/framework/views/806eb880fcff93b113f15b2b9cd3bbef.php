<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'results',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'results',
]); ?>
<?php foreach (array_filter(([
    'results',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div
    x-data="{ isOpen: true }"
    x-show="isOpen"
    x-on:keydown.escape.window="isOpen = false"
    x-on:click.away="isOpen = false"
    x-on:open-global-search-results.window="isOpen = true"
    <?php echo e($attributes->class(['filament-global-search-results-container absolute right-0 top-auto z-10 mt-2 w-screen max-w-xs overflow-hidden rounded-xl shadow-xl rtl:left-0 rtl:right-auto sm:max-w-lg'])); ?>

>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'max-h-96 overflow-x-hidden rounded-xl bg-white shadow',
            'dark:bg-gray-800' => config('filament.dark_mode'),
        ]); ?>"
    >
        <?php $__empty_1 = true; $__currentLoopData = $results->getCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupedResults): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if (isset($component)) { $__componentOriginalb1d0261ba6cf56df76266f1b00bffc64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb1d0261ba6cf56df76266f1b00bffc64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.result-group','data' => ['label' => $group,'results' => $groupedResults]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.result-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group),'results' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groupedResults)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb1d0261ba6cf56df76266f1b00bffc64)): ?>
<?php $attributes = $__attributesOriginalb1d0261ba6cf56df76266f1b00bffc64; ?>
<?php unset($__attributesOriginalb1d0261ba6cf56df76266f1b00bffc64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb1d0261ba6cf56df76266f1b00bffc64)): ?>
<?php $component = $__componentOriginalb1d0261ba6cf56df76266f1b00bffc64; ?>
<?php unset($__componentOriginalb1d0261ba6cf56df76266f1b00bffc64); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal0d775592038cfa46c935aec92e235e65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d775592038cfa46c935aec92e235e65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.no-results-message','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.no-results-message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d775592038cfa46c935aec92e235e65)): ?>
<?php $attributes = $__attributesOriginal0d775592038cfa46c935aec92e235e65; ?>
<?php unset($__attributesOriginal0d775592038cfa46c935aec92e235e65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d775592038cfa46c935aec92e235e65)): ?>
<?php $component = $__componentOriginal0d775592038cfa46c935aec92e235e65; ?>
<?php unset($__componentOriginal0d775592038cfa46c935aec92e235e65); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\global-search\results-container.blade.php ENDPATH**/ ?>