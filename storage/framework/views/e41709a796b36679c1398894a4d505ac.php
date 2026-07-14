<?php if (isset($component)) { $__componentOriginal80359543710cbb7599320e25473de020 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80359543710cbb7599320e25473de020 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.actions.action','data' => ['action' => $action,'component' => 'tables::dropdown.list.item','icon' => $action->getGroupedIcon(),'class' => 'filament-tables-grouped-action']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::actions.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'component' => 'tables::dropdown.list.item','icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action->getGroupedIcon()),'class' => 'filament-tables-grouped-action']); ?>
    <?php echo e($getLabel()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80359543710cbb7599320e25473de020)): ?>
<?php $attributes = $__attributesOriginal80359543710cbb7599320e25473de020; ?>
<?php unset($__attributesOriginal80359543710cbb7599320e25473de020); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80359543710cbb7599320e25473de020)): ?>
<?php $component = $__componentOriginal80359543710cbb7599320e25473de020; ?>
<?php unset($__componentOriginal80359543710cbb7599320e25473de020); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\actions\grouped-action.blade.php ENDPATH**/ ?>