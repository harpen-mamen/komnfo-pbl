<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'form',
    'maxHeight' => null,
    'width' => 'xs',
    'indicatorsCount' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'form',
    'maxHeight' => null,
    'width' => 'xs',
    'indicatorsCount' => null,
]); ?>
<?php foreach (array_filter(([
    'form',
    'maxHeight' => null,
    'width' => 'xs',
    'indicatorsCount' => null,
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.index','data' => ['attributes' => $attributes->class(['filament-tables-filters']),'maxHeight' => $maxHeight,'placement' => 'bottom-end','shift' => true,'width' => $width,'wire:key' => ''.e($this->id).'.table.filters']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-tables-filters'])),'max-height' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($maxHeight),'placement' => 'bottom-end','shift' => true,'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($width),'wire:key' => ''.e($this->id).'.table.filters']); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginal7917fea7221db3198ac6d08be6e10451 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7917fea7221db3198ac6d08be6e10451 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.trigger','data' => ['indicatorsCount' => $indicatorsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['indicators-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($indicatorsCount)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7917fea7221db3198ac6d08be6e10451)): ?>
<?php $attributes = $__attributesOriginal7917fea7221db3198ac6d08be6e10451; ?>
<?php unset($__attributesOriginal7917fea7221db3198ac6d08be6e10451); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7917fea7221db3198ac6d08be6e10451)): ?>
<?php $component = $__componentOriginal7917fea7221db3198ac6d08be6e10451; ?>
<?php unset($__componentOriginal7917fea7221db3198ac6d08be6e10451); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.filters.index','data' => ['class' => 'p-4','form' => $form]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'p-4','form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($form)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $attributes = $__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__attributesOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e)): ?>
<?php $component = $__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e; ?>
<?php unset($__componentOriginal3b3fa8ec7d71109f7bdf00def1db315e); ?>
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
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\filters\popover.blade.php ENDPATH**/ ?>