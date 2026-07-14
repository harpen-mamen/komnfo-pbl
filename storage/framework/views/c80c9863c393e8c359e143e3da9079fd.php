<?php if (isset($component)) { $__componentOriginalac68a8978c824bb8f9627fd627b8a5d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac68a8978c824bb8f9627fd627b8a5d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.dropdown.list.index','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'darkMode' => config('filament.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::dropdown.list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('filament.dark_mode'))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac68a8978c824bb8f9627fd627b8a5d9)): ?>
<?php $attributes = $__attributesOriginalac68a8978c824bb8f9627fd627b8a5d9; ?>
<?php unset($__attributesOriginalac68a8978c824bb8f9627fd627b8a5d9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac68a8978c824bb8f9627fd627b8a5d9)): ?>
<?php $component = $__componentOriginalac68a8978c824bb8f9627fd627b8a5d9; ?>
<?php unset($__componentOriginalac68a8978c824bb8f9627fd627b8a5d9); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\dropdown\list\index.blade.php ENDPATH**/ ?>