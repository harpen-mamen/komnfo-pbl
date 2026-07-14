<?php if (isset($component)) { $__componentOriginal5bd3f35a44884c19a3f2833e83df6f31 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5bd3f35a44884c19a3f2833e83df6f31 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.actions.action','data' => ['action' => $action,'component' => 'notifications::dropdown.list.item','iconPosition' => $getIconPosition(),'class' => 'filament-notifications-grouped-action']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::actions.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'component' => 'notifications::dropdown.list.item','icon-position' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getIconPosition()),'class' => 'filament-notifications-grouped-action']); ?>
    <?php echo e($getLabel()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5bd3f35a44884c19a3f2833e83df6f31)): ?>
<?php $attributes = $__attributesOriginal5bd3f35a44884c19a3f2833e83df6f31; ?>
<?php unset($__attributesOriginal5bd3f35a44884c19a3f2833e83df6f31); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5bd3f35a44884c19a3f2833e83df6f31)): ?>
<?php $component = $__componentOriginal5bd3f35a44884c19a3f2833e83df6f31; ?>
<?php unset($__componentOriginal5bd3f35a44884c19a3f2833e83df6f31); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\actions\grouped-action.blade.php ENDPATH**/ ?>