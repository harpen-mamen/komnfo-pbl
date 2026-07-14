<?php if (isset($component)) { $__componentOriginalcb9b2192b9152278a357ab8b3656b740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb9b2192b9152278a357ab8b3656b740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.page','data' => ['class' => 'filament-dashboard-page']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-dashboard-page']); ?>
    <?php if (isset($component)) { $__componentOriginal3302e7d8fc13452ff11e26177b225137 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3302e7d8fc13452ff11e26177b225137 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.widgets','data' => ['widgets' => $this->getWidgets(),'columns' => $this->getColumns()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::widgets'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['widgets' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getWidgets()),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getColumns())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3302e7d8fc13452ff11e26177b225137)): ?>
<?php $attributes = $__attributesOriginal3302e7d8fc13452ff11e26177b225137; ?>
<?php unset($__attributesOriginal3302e7d8fc13452ff11e26177b225137); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3302e7d8fc13452ff11e26177b225137)): ?>
<?php $component = $__componentOriginal3302e7d8fc13452ff11e26177b225137; ?>
<?php unset($__componentOriginal3302e7d8fc13452ff11e26177b225137); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb9b2192b9152278a357ab8b3656b740)): ?>
<?php $attributes = $__attributesOriginalcb9b2192b9152278a357ab8b3656b740; ?>
<?php unset($__attributesOriginalcb9b2192b9152278a357ab8b3656b740); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb9b2192b9152278a357ab8b3656b740)): ?>
<?php $component = $__componentOriginalcb9b2192b9152278a357ab8b3656b740; ?>
<?php unset($__componentOriginalcb9b2192b9152278a357ab8b3656b740); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\pages\dashboard.blade.php ENDPATH**/ ?>