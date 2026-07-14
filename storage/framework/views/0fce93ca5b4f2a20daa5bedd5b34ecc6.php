<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'actions',
    'color' => null,
    'darkMode' => false,
    'icon' => 'heroicon-o-dots-vertical',
    'label' => __('filament-support::actions/group.trigger.label'),
    'size' => null,
    'tooltip' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'actions',
    'color' => null,
    'darkMode' => false,
    'icon' => 'heroicon-o-dots-vertical',
    'label' => __('filament-support::actions/group.trigger.label'),
    'size' => null,
    'tooltip' => null,
]); ?>
<?php foreach (array_filter(([
    'actions',
    'color' => null,
    'darkMode' => false,
    'icon' => 'heroicon-o-dots-vertical',
    'label' => __('filament-support::actions/group.trigger.label'),
    'size' => null,
    'tooltip' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginal7d097d642da69788bf899d5dad94dd25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7d097d642da69788bf899d5dad94dd25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.dropdown.index','data' => ['darkMode' => $darkMode,'placement' => 'bottom-end','teleport' => true,'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($darkMode),'placement' => 'bottom-end','teleport' => true,'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginale0bf1e6727c4b30910826fa306b06fc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0bf1e6727c4b30910826fa306b06fc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.icon-button','data' => ['color' => $color,'darkMode' => $darkMode,'icon' => $icon,'size' => $size,'tooltip' => $tooltip]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($color),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($darkMode),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($size),'tooltip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltip)]); ?>
             <?php $__env->slot('label', null, []); ?> 
                <?php echo e($label); ?>

             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0bf1e6727c4b30910826fa306b06fc2)): ?>
<?php $attributes = $__attributesOriginale0bf1e6727c4b30910826fa306b06fc2; ?>
<?php unset($__attributesOriginale0bf1e6727c4b30910826fa306b06fc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0bf1e6727c4b30910826fa306b06fc2)): ?>
<?php $component = $__componentOriginale0bf1e6727c4b30910826fa306b06fc2; ?>
<?php unset($__componentOriginale0bf1e6727c4b30910826fa306b06fc2); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <?php if (isset($component)) { $__componentOriginalac68a8978c824bb8f9627fd627b8a5d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac68a8978c824bb8f9627fd627b8a5d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.dropdown.list.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::dropdown.list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(! $action->isHidden()): ?>
                <?php echo e($action); ?>

            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7d097d642da69788bf899d5dad94dd25)): ?>
<?php $attributes = $__attributesOriginal7d097d642da69788bf899d5dad94dd25; ?>
<?php unset($__attributesOriginal7d097d642da69788bf899d5dad94dd25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7d097d642da69788bf899d5dad94dd25)): ?>
<?php $component = $__componentOriginal7d097d642da69788bf899d5dad94dd25; ?>
<?php unset($__componentOriginal7d097d642da69788bf899d5dad94dd25); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\support\resources\views\components\actions\group.blade.php ENDPATH**/ ?>