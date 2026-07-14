<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'actions' => null,
    'heading',
    'subheading' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'actions' => null,
    'heading',
    'subheading' => null,
]); ?>
<?php foreach (array_filter(([
    'actions' => null,
    'heading',
    'subheading' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<header
    <?php echo e($attributes->class(['filament-header items-start justify-between space-y-2 sm:flex sm:space-x-4 sm:space-y-0 sm:py-4 sm:rtl:space-x-reverse'])); ?>

>
    <div>
        <?php if (isset($component)) { $__componentOriginal907d10c93bd84b4968d35caa0513baa5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal907d10c93bd84b4968d35caa0513baa5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.header.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::header.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <?php echo e($heading); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal907d10c93bd84b4968d35caa0513baa5)): ?>
<?php $attributes = $__attributesOriginal907d10c93bd84b4968d35caa0513baa5; ?>
<?php unset($__attributesOriginal907d10c93bd84b4968d35caa0513baa5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal907d10c93bd84b4968d35caa0513baa5)): ?>
<?php $component = $__componentOriginal907d10c93bd84b4968d35caa0513baa5; ?>
<?php unset($__componentOriginal907d10c93bd84b4968d35caa0513baa5); ?>
<?php endif; ?>

        <?php if($subheading): ?>
            <?php if (isset($component)) { $__componentOriginalb03337650572b4ee6c4904c6d2c345e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb03337650572b4ee6c4904c6d2c345e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.header.subheading','data' => ['class' => 'mt-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::header.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-1']); ?>
                <?php echo e($subheading); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb03337650572b4ee6c4904c6d2c345e8)): ?>
<?php $attributes = $__attributesOriginalb03337650572b4ee6c4904c6d2c345e8; ?>
<?php unset($__attributesOriginalb03337650572b4ee6c4904c6d2c345e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb03337650572b4ee6c4904c6d2c345e8)): ?>
<?php $component = $__componentOriginalb03337650572b4ee6c4904c6d2c345e8; ?>
<?php unset($__componentOriginalb03337650572b4ee6c4904c6d2c345e8); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginal1723d855bd2fed2e2f3e1b80e97c34fe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1723d855bd2fed2e2f3e1b80e97c34fe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.pages.actions.index','data' => ['actions' => $actions,'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::pages.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions),'class' => 'shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1723d855bd2fed2e2f3e1b80e97c34fe)): ?>
<?php $attributes = $__attributesOriginal1723d855bd2fed2e2f3e1b80e97c34fe; ?>
<?php unset($__attributesOriginal1723d855bd2fed2e2f3e1b80e97c34fe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1723d855bd2fed2e2f3e1b80e97c34fe)): ?>
<?php $component = $__componentOriginal1723d855bd2fed2e2f3e1b80e97c34fe; ?>
<?php unset($__componentOriginal1723d855bd2fed2e2f3e1b80e97c34fe); ?>
<?php endif; ?>
</header>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views/components/header/index.blade.php ENDPATH**/ ?>