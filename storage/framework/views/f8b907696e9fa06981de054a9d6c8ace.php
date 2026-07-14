<?php if (isset($component)) { $__componentOriginal0622c8b3c15d7f7d75145ec32e5af7ec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0622c8b3c15d7f7d75145ec32e5af7ec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.actions.action','data' => ['action' => $action,'label' => $getLabel(),'component' => 'forms::icon-button','class' => 'filament-forms-icon-button-action -my-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::actions.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getLabel()),'component' => 'forms::icon-button','class' => 'filament-forms-icon-button-action -my-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0622c8b3c15d7f7d75145ec32e5af7ec)): ?>
<?php $attributes = $__attributesOriginal0622c8b3c15d7f7d75145ec32e5af7ec; ?>
<?php unset($__attributesOriginal0622c8b3c15d7f7d75145ec32e5af7ec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0622c8b3c15d7f7d75145ec32e5af7ec)): ?>
<?php $component = $__componentOriginal0622c8b3c15d7f7d75145ec32e5af7ec; ?>
<?php unset($__componentOriginal0622c8b3c15d7f7d75145ec32e5af7ec); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\actions\icon-button-action.blade.php ENDPATH**/ ?>