<?php if (isset($component)) { $__componentOriginale195ea0bec2a46e3694fd4df94164525 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale195ea0bec2a46e3694fd4df94164525 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.list.item','data' => ['xOn:click' => 'mountBulkAction(\'' . $getName() . '\')','icon' => $getIcon(),'color' => $getColor(),'attributes' => \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag()),'class' => 'filament-tables-bulk-action']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('mountBulkAction(\'' . $getName() . '\')'),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getIcon()),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColor()),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())),'class' => 'filament-tables-bulk-action']); ?>
    <?php echo e($getLabel()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale195ea0bec2a46e3694fd4df94164525)): ?>
<?php $attributes = $__attributesOriginale195ea0bec2a46e3694fd4df94164525; ?>
<?php unset($__attributesOriginale195ea0bec2a46e3694fd4df94164525); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale195ea0bec2a46e3694fd4df94164525)): ?>
<?php $component = $__componentOriginale195ea0bec2a46e3694fd4df94164525; ?>
<?php unset($__componentOriginale195ea0bec2a46e3694fd4df94164525); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\actions\bulk-action.blade.php ENDPATH**/ ?>