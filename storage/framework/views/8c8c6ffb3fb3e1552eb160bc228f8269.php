<?php if (isset($component)) { $__componentOriginal0a6087579beaae9feec0056cadcfb42f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a6087579beaae9feec0056cadcfb42f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.widget','data' => ['class' => 'filament-stats-overview-widget']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-stats-overview-widget']); ?>
    <div
        <?php echo ($pollingInterval = $this->getPollingInterval()) ? "wire:poll.{$pollingInterval}" : ''; ?>

    >
        <?php if (isset($component)) { $__componentOriginalbc41fac53ab5698e54f641cdfa6d87f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbc41fac53ab5698e54f641cdfa6d87f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.stats.index','data' => ['columns' => $this->getColumns()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::stats'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getColumns())]); ?>
            <?php $__currentLoopData = $this->getCachedCards(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($card); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbc41fac53ab5698e54f641cdfa6d87f3)): ?>
<?php $attributes = $__attributesOriginalbc41fac53ab5698e54f641cdfa6d87f3; ?>
<?php unset($__attributesOriginalbc41fac53ab5698e54f641cdfa6d87f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbc41fac53ab5698e54f641cdfa6d87f3)): ?>
<?php $component = $__componentOriginalbc41fac53ab5698e54f641cdfa6d87f3; ?>
<?php unset($__componentOriginalbc41fac53ab5698e54f641cdfa6d87f3); ?>
<?php endif; ?>
    </div>
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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\widgets\stats-overview-widget.blade.php ENDPATH**/ ?>