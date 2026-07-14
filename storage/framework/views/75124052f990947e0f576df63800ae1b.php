<?php if (isset($component)) { $__componentOriginal1367eb6d0af6726772b12e195c4476c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1367eb6d0af6726772b12e195c4476c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.modal.subheading','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'darkMode' => config('notifications.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::modal.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('notifications.dark_mode'))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1367eb6d0af6726772b12e195c4476c3)): ?>
<?php $attributes = $__attributesOriginal1367eb6d0af6726772b12e195c4476c3; ?>
<?php unset($__attributesOriginal1367eb6d0af6726772b12e195c4476c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1367eb6d0af6726772b12e195c4476c3)): ?>
<?php $component = $__componentOriginal1367eb6d0af6726772b12e195c4476c3; ?>
<?php unset($__componentOriginal1367eb6d0af6726772b12e195c4476c3); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\modal\subheading.blade.php ENDPATH**/ ?>