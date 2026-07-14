<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'form',
    'maxHeight' => null,
    'width' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'form',
    'maxHeight' => null,
    'width' => null,
]); ?>
<?php foreach (array_filter(([
    'form',
    'maxHeight' => null,
    'width' => null,
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.index','data' => ['attributes' => $attributes->class(['filament-tables-column-toggling']),'maxHeight' => $maxHeight,'placement' => 'bottom-end','shift' => true,'width' => $width,'wire:key' => ''.e($this->id).'.table.toggle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-tables-column-toggling'])),'max-height' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($maxHeight),'placement' => 'bottom-end','shift' => true,'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($width),'wire:key' => ''.e($this->id).'.table.toggle']); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalb6288c0101ddb386d04134194b7330a6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6288c0101ddb386d04134194b7330a6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.toggleable.trigger','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::toggleable.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6288c0101ddb386d04134194b7330a6)): ?>
<?php $attributes = $__attributesOriginalb6288c0101ddb386d04134194b7330a6; ?>
<?php unset($__attributesOriginalb6288c0101ddb386d04134194b7330a6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6288c0101ddb386d04134194b7330a6)): ?>
<?php $component = $__componentOriginalb6288c0101ddb386d04134194b7330a6; ?>
<?php unset($__componentOriginalb6288c0101ddb386d04134194b7330a6); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="p-4">
        <?php echo e($form); ?>

    </div>
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
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\toggleable\index.blade.php ENDPATH**/ ?>