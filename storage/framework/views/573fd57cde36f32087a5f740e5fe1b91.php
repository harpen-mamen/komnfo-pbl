<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'actions',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'actions',
]); ?>
<?php foreach (array_filter(([
    'actions',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginal49135787211a7c86d1cc2813cdd72191 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49135787211a7c86d1cc2813cdd72191 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.index','data' => ['attributes' => $attributes->class(['filament-tables-bulk-actions']),'placement' => 'bottom-start']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-tables-bulk-actions'])),'placement' => 'bottom-start']); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginal3245761d7eb941c271c8dc0f578eb01b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3245761d7eb941c271c8dc0f578eb01b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.bulk-actions.trigger','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::bulk-actions.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3245761d7eb941c271c8dc0f578eb01b)): ?>
<?php $attributes = $__attributesOriginal3245761d7eb941c271c8dc0f578eb01b; ?>
<?php unset($__attributesOriginal3245761d7eb941c271c8dc0f578eb01b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3245761d7eb941c271c8dc0f578eb01b)): ?>
<?php $component = $__componentOriginal3245761d7eb941c271c8dc0f578eb01b; ?>
<?php unset($__componentOriginal3245761d7eb941c271c8dc0f578eb01b); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginal9a6ab0144257793b5dc0d9ea3f6a1173 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a6ab0144257793b5dc0d9ea3f6a1173 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.list.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown.list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($action); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a6ab0144257793b5dc0d9ea3f6a1173)): ?>
<?php $attributes = $__attributesOriginal9a6ab0144257793b5dc0d9ea3f6a1173; ?>
<?php unset($__attributesOriginal9a6ab0144257793b5dc0d9ea3f6a1173); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a6ab0144257793b5dc0d9ea3f6a1173)): ?>
<?php $component = $__componentOriginal9a6ab0144257793b5dc0d9ea3f6a1173; ?>
<?php unset($__componentOriginal9a6ab0144257793b5dc0d9ea3f6a1173); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49135787211a7c86d1cc2813cdd72191)): ?>
<?php $attributes = $__attributesOriginal49135787211a7c86d1cc2813cdd72191; ?>
<?php unset($__attributesOriginal49135787211a7c86d1cc2813cdd72191); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49135787211a7c86d1cc2813cdd72191)): ?>
<?php $component = $__componentOriginal49135787211a7c86d1cc2813cdd72191; ?>
<?php unset($__componentOriginal49135787211a7c86d1cc2813cdd72191); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\bulk-actions\index.blade.php ENDPATH**/ ?>