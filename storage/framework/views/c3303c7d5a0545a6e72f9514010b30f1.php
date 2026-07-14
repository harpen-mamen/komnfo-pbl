<?php if (isset($component)) { $__componentOriginal6fdf9114990bf1716e475d486f86e401 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6fdf9114990bf1716e475d486f86e401 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.modal.actions','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'alignment' => config('forms.components.actions.modal.actions.alignment'),'darkMode' => config('forms.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::modal.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'alignment' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('forms.components.actions.modal.actions.alignment')),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('forms.dark_mode'))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6fdf9114990bf1716e475d486f86e401)): ?>
<?php $attributes = $__attributesOriginal6fdf9114990bf1716e475d486f86e401; ?>
<?php unset($__attributesOriginal6fdf9114990bf1716e475d486f86e401); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6fdf9114990bf1716e475d486f86e401)): ?>
<?php $component = $__componentOriginal6fdf9114990bf1716e475d486f86e401; ?>
<?php unset($__componentOriginal6fdf9114990bf1716e475d486f86e401); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\modal\actions.blade.php ENDPATH**/ ?>