<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'enabled' => false,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'enabled' => false,
]); ?>
<?php foreach (array_filter(([
    'enabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.icon-button','data' => ['wire:click' => 'toggleTableReordering','icon' => $enabled ? 'heroicon-o-check' : 'heroicon-o-selector','label' => $enabled ? __('tables::table.buttons.disable_reordering.label') : __('tables::table.buttons.enable_reordering.label'),'attributes' => $attributes->class(['filament-tables-reordering-trigger'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'toggleTableReordering','icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enabled ? 'heroicon-o-check' : 'heroicon-o-selector'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enabled ? __('tables::table.buttons.disable_reordering.label') : __('tables::table.buttons.enable_reordering.label')),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-tables-reordering-trigger']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $attributes = $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $component = $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\reorder\trigger.blade.php ENDPATH**/ ?>