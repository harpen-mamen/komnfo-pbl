<?php if (isset($component)) { $__componentOriginalc0842e5bca8f5c9c3236b0e725e63b3e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc0842e5bca8f5c9c3236b0e725e63b3e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.actions.group','data' => ['actions' => $getActions(),'darkMode' => config('tables.dark_mode'),'color' => $getColor(),'icon' => $getIcon(),'label' => $getLabel(),'size' => $getSize(),'tooltip' => $getTooltip()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::actions.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getActions()),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('tables.dark_mode')),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColor()),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getIcon()),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getLabel()),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getSize()),'tooltip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getTooltip())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc0842e5bca8f5c9c3236b0e725e63b3e)): ?>
<?php $attributes = $__attributesOriginalc0842e5bca8f5c9c3236b0e725e63b3e; ?>
<?php unset($__attributesOriginalc0842e5bca8f5c9c3236b0e725e63b3e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc0842e5bca8f5c9c3236b0e725e63b3e)): ?>
<?php $component = $__componentOriginalc0842e5bca8f5c9c3236b0e725e63b3e; ?>
<?php unset($__componentOriginalc0842e5bca8f5c9c3236b0e725e63b3e); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\actions\group.blade.php ENDPATH**/ ?>