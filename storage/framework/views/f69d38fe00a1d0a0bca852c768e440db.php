<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'actions' => [],
    'description' => null,
    'heading',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'actions' => [],
    'description' => null,
    'heading',
]); ?>
<?php foreach (array_filter(([
    'actions' => [],
    'description' => null,
    'heading',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div <?php echo e($attributes->class(['filament-tables-header px-4 py-2'])); ?>>
    <div
        class="flex flex-col gap-4 md:-mr-2 md:flex-row md:items-start md:justify-between"
    >
        <div>
            <?php if($heading): ?>
                <?php if (isset($component)) { $__componentOriginala9cac6f782d0e6f25030d902c9c98392 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9cac6f782d0e6f25030d902c9c98392 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php echo e($heading); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9cac6f782d0e6f25030d902c9c98392)): ?>
<?php $attributes = $__attributesOriginala9cac6f782d0e6f25030d902c9c98392; ?>
<?php unset($__attributesOriginala9cac6f782d0e6f25030d902c9c98392); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9cac6f782d0e6f25030d902c9c98392)): ?>
<?php $component = $__componentOriginala9cac6f782d0e6f25030d902c9c98392; ?>
<?php unset($__componentOriginala9cac6f782d0e6f25030d902c9c98392); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if($description): ?>
                <?php if (isset($component)) { $__componentOriginal4d7c017cc92c84bf23f36e2980ae5bb1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d7c017cc92c84bf23f36e2980ae5bb1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.header.description','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::header.description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php echo e($description); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d7c017cc92c84bf23f36e2980ae5bb1)): ?>
<?php $attributes = $__attributesOriginal4d7c017cc92c84bf23f36e2980ae5bb1; ?>
<?php unset($__attributesOriginal4d7c017cc92c84bf23f36e2980ae5bb1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d7c017cc92c84bf23f36e2980ae5bb1)): ?>
<?php $component = $__componentOriginal4d7c017cc92c84bf23f36e2980ae5bb1; ?>
<?php unset($__componentOriginal4d7c017cc92c84bf23f36e2980ae5bb1); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($component)) { $__componentOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a8b72bf300c2fa6dda80f8a829adc94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.index','data' => ['actions' => $actions,'alignment' => 'right','wrap' => true,'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'alignment' => 'right','wrap' => true,'class' => 'shrink-0']); ?>
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
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\header\index.blade.php ENDPATH**/ ?>