<?php if (isset($component)) { $__componentOriginal0a6087579beaae9feec0056cadcfb42f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a6087579beaae9feec0056cadcfb42f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.widget','data' => ['class' => 'filament-widgets-table-widget']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-widgets-table-widget']); ?>
    <?php echo e($this->table); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0a6087579beaae9feec0056cadcfb42f)): ?>
<?php $attributes = $__attributesOriginal0a6087579beaae9feec0056cadcfb42f; ?>
<?php unset($__attributesOriginal0a6087579beaae9feec0056cadcfb42f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0a6087579beaae9feec0056cadcfb42f)): ?>
<?php $component = $__componentOriginal0a6087579beaae9feec0056cadcfb42f; ?>
<?php unset($__componentOriginal0a6087579beaae9feec0056cadcfb42f); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\widgets\table-widget.blade.php ENDPATH**/ ?>