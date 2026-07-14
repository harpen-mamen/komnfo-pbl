<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'blocks',
    'createAfterItem' => null,
    'statePath',
    'trigger',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'blocks',
    'createAfterItem' => null,
    'statePath',
    'trigger',
]); ?>
<?php foreach (array_filter(([
    'blocks',
    'createAfterItem' => null,
    'statePath',
    'trigger',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginale6f9d477a58e58646474f7b2c2055766 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale6f9d477a58e58646474f7b2c2055766 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.dropdown.index','data' => ['attributes' => $attributes->class(['filament-forms-builder-component-block-picker'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-forms-builder-component-block-picker']))]); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php echo e($trigger); ?>

     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginal5b4d35a3a39441092d6e834de9e92ca6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b4d35a3a39441092d6e834de9e92ca6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.dropdown.list.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::dropdown.list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.dropdown.list.item','data' => ['wire:click' => 'dispatchFormEvent(\'builder::createItem\', \'' . $statePath . '\', \'' . $block->getName() . '\'' . ($createAfterItem ? ', \'' . $createAfterItem . '\'' : '') . ')','icon' => $block->getIcon(),'xOn:click' => 'close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('dispatchFormEvent(\'builder::createItem\', \'' . $statePath . '\', \'' . $block->getName() . '\'' . ($createAfterItem ? ', \'' . $createAfterItem . '\'' : '') . ')'),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($block->getIcon()),'x-on:click' => 'close']); ?>
                <?php echo e($block->getLabel()); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb)): ?>
<?php $attributes = $__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb; ?>
<?php unset($__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb)): ?>
<?php $component = $__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb; ?>
<?php unset($__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b4d35a3a39441092d6e834de9e92ca6)): ?>
<?php $attributes = $__attributesOriginal5b4d35a3a39441092d6e834de9e92ca6; ?>
<?php unset($__attributesOriginal5b4d35a3a39441092d6e834de9e92ca6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b4d35a3a39441092d6e834de9e92ca6)): ?>
<?php $component = $__componentOriginal5b4d35a3a39441092d6e834de9e92ca6; ?>
<?php unset($__componentOriginal5b4d35a3a39441092d6e834de9e92ca6); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale6f9d477a58e58646474f7b2c2055766)): ?>
<?php $attributes = $__attributesOriginale6f9d477a58e58646474f7b2c2055766; ?>
<?php unset($__attributesOriginale6f9d477a58e58646474f7b2c2055766); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale6f9d477a58e58646474f7b2c2055766)): ?>
<?php $component = $__componentOriginale6f9d477a58e58646474f7b2c2055766; ?>
<?php unset($__componentOriginale6f9d477a58e58646474f7b2c2055766); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\builder\block-picker.blade.php ENDPATH**/ ?>