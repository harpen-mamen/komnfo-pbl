<?php if (isset($component)) { $__componentOriginal80841467f8736e9883f654c5d18af672 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80841467f8736e9883f654c5d18af672 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.actions.action','data' => ['action' => $action,'label' => $getLabel(),'component' => 'filament::icon-button','class' => 'filament-global-search-icon-button-action']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.actions.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getLabel()),'component' => 'filament::icon-button','class' => 'filament-global-search-icon-button-action']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80841467f8736e9883f654c5d18af672)): ?>
<?php $attributes = $__attributesOriginal80841467f8736e9883f654c5d18af672; ?>
<?php unset($__attributesOriginal80841467f8736e9883f654c5d18af672); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80841467f8736e9883f654c5d18af672)): ?>
<?php $component = $__componentOriginal80841467f8736e9883f654c5d18af672; ?>
<?php unset($__componentOriginal80841467f8736e9883f654c5d18af672); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\global-search\actions\icon-button-action.blade.php ENDPATH**/ ?>