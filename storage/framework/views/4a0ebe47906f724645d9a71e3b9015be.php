<?php if (isset($component)) { $__componentOriginal39c847cc520ad031b1ffccd96a83b24d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal39c847cc520ad031b1ffccd96a83b24d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.pages.actions.action','data' => ['action' => $action,'component' => 'filament::button','outlined' => $isOutlined(),'iconPosition' => $getIconPosition(),'class' => 'filament-page-button-action']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::pages.actions.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'component' => 'filament::button','outlined' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isOutlined()),'icon-position' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getIconPosition()),'class' => 'filament-page-button-action']); ?>
    <?php echo e($getLabel()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal39c847cc520ad031b1ffccd96a83b24d)): ?>
<?php $attributes = $__attributesOriginal39c847cc520ad031b1ffccd96a83b24d; ?>
<?php unset($__attributesOriginal39c847cc520ad031b1ffccd96a83b24d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal39c847cc520ad031b1ffccd96a83b24d)): ?>
<?php $component = $__componentOriginal39c847cc520ad031b1ffccd96a83b24d; ?>
<?php unset($__componentOriginal39c847cc520ad031b1ffccd96a83b24d); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views/pages/actions/button-action.blade.php ENDPATH**/ ?>