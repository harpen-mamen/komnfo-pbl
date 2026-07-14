<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['id','label','labelSrOnly','helperText','hint','hintAction','hintColor','hintIcon','required','statePath']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['id','label','labelSrOnly','helperText','hint','hintAction','hintColor','hintIcon','required','statePath']); ?>
<?php foreach (array_filter((['id','label','labelSrOnly','helperText','hint','hintAction','hintColor','hintIcon','required','statePath']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if (isset($component)) { $__componentOriginal52bb83441a2ed252b874ca1dc505d4b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal52bb83441a2ed252b874ca1dc505d4b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.field-wrapper.index','data' => ['id' => $id,'label' => $label,'labelSrOnly' => $labelSrOnly,'helperText' => $helperText,'hint' => $hint,'hintAction' => $hintAction,'hintColor' => $hintColor,'hintIcon' => $hintIcon,'required' => $required,'statePath' => $statePath]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::field-wrapper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($id),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'label-sr-only' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($labelSrOnly),'helper-text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($helperText),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hint),'hint-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintAction),'hint-color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintColor),'hint-icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintIcon),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'state-path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statePath)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal52bb83441a2ed252b874ca1dc505d4b8)): ?>
<?php $attributes = $__attributesOriginal52bb83441a2ed252b874ca1dc505d4b8; ?>
<?php unset($__attributesOriginal52bb83441a2ed252b874ca1dc505d4b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal52bb83441a2ed252b874ca1dc505d4b8)): ?>
<?php $component = $__componentOriginal52bb83441a2ed252b874ca1dc505d4b8; ?>
<?php unset($__componentOriginal52bb83441a2ed252b874ca1dc505d4b8); ?>
<?php endif; ?><?php /**PATH C:\pbl\storage\framework\views/5c15fe415597335aee7b6c740abf5e8e.blade.php ENDPATH**/ ?>