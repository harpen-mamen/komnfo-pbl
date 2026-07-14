<?php if (isset($component)) { $__componentOriginala83d10ce06096c1794c4095e0dec0770 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala83d10ce06096c1794c4095e0dec0770 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.link','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'darkMode' => config('notifications.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('notifications.dark_mode'))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala83d10ce06096c1794c4095e0dec0770)): ?>
<?php $attributes = $__attributesOriginala83d10ce06096c1794c4095e0dec0770; ?>
<?php unset($__attributesOriginala83d10ce06096c1794c4095e0dec0770); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala83d10ce06096c1794c4095e0dec0770)): ?>
<?php $component = $__componentOriginala83d10ce06096c1794c4095e0dec0770; ?>
<?php unset($__componentOriginala83d10ce06096c1794c4095e0dec0770); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\link.blade.php ENDPATH**/ ?>