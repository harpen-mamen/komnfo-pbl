<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'label',
    'results',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'label',
    'results',
]); ?>
<?php foreach (array_filter(([
    'label',
    'results',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<ul
    <?php echo e($attributes->class([
            'filament-global-search-result-group divide-y',
            'dark:divide-gray-700' => config('filament.dark_mode'),
        ])); ?>

>
    <li class="sticky top-0 z-10">
        <header
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'bg-gray-50/80 px-6 py-2 backdrop-blur-xl backdrop-saturate-150',
                'dark:bg-gray-700' => config('filament.dark_mode'),
            ]); ?>"
        >
            <p
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'text-xs font-bold uppercase tracking-wider text-gray-500',
                    'dark:text-gray-400' => config('filament.dark_mode'),
                ]); ?>"
            >
                <?php echo e($label); ?>

            </p>
        </header>
    </li>

    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginale5b89e6ce3652594239f75381d01bc2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5b89e6ce3652594239f75381d01bc2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.result','data' => ['actions' => $result->actions,'details' => $result->details,'title' => $result->title,'url' => $result->url]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.result'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($result->actions),'details' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($result->details),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($result->title),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($result->url)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5b89e6ce3652594239f75381d01bc2b)): ?>
<?php $attributes = $__attributesOriginale5b89e6ce3652594239f75381d01bc2b; ?>
<?php unset($__attributesOriginale5b89e6ce3652594239f75381d01bc2b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5b89e6ce3652594239f75381d01bc2b)): ?>
<?php $component = $__componentOriginale5b89e6ce3652594239f75381d01bc2b; ?>
<?php unset($__componentOriginale5b89e6ce3652594239f75381d01bc2b); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\global-search\result-group.blade.php ENDPATH**/ ?>