<div class="filament-global-search ml-4 flex items-center rtl:ml-0 rtl:mr-4">
    <?php if (isset($component)) { $__componentOriginalb3750a1b394165197414cd9cec7b65d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3750a1b394165197414cd9cec7b65d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.start','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.start'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3750a1b394165197414cd9cec7b65d9)): ?>
<?php $attributes = $__attributesOriginalb3750a1b394165197414cd9cec7b65d9; ?>
<?php unset($__attributesOriginalb3750a1b394165197414cd9cec7b65d9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3750a1b394165197414cd9cec7b65d9)): ?>
<?php $component = $__componentOriginalb3750a1b394165197414cd9cec7b65d9; ?>
<?php unset($__componentOriginalb3750a1b394165197414cd9cec7b65d9); ?>
<?php endif; ?>
    <?php echo e(\Filament\Facades\Filament::renderHook('global-search.start')); ?>


    <?php if($this->isEnabled()): ?>
        <div class="relative">
            <?php if (isset($component)) { $__componentOriginalfef7f4dd02ecf662a1e0e192177ec1cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfef7f4dd02ecf662a1e0e192177ec1cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.input','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfef7f4dd02ecf662a1e0e192177ec1cf)): ?>
<?php $attributes = $__attributesOriginalfef7f4dd02ecf662a1e0e192177ec1cf; ?>
<?php unset($__attributesOriginalfef7f4dd02ecf662a1e0e192177ec1cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfef7f4dd02ecf662a1e0e192177ec1cf)): ?>
<?php $component = $__componentOriginalfef7f4dd02ecf662a1e0e192177ec1cf; ?>
<?php unset($__componentOriginalfef7f4dd02ecf662a1e0e192177ec1cf); ?>
<?php endif; ?>

            <?php if($results !== null): ?>
                <?php if (isset($component)) { $__componentOriginal5224eb71ac00648469a482cea3a272c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5224eb71ac00648469a482cea3a272c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.results-container','data' => ['results' => $results]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.results-container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['results' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($results)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5224eb71ac00648469a482cea3a272c8)): ?>
<?php $attributes = $__attributesOriginal5224eb71ac00648469a482cea3a272c8; ?>
<?php unset($__attributesOriginal5224eb71ac00648469a482cea3a272c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5224eb71ac00648469a482cea3a272c8)): ?>
<?php $component = $__componentOriginal5224eb71ac00648469a482cea3a272c8; ?>
<?php unset($__componentOriginal5224eb71ac00648469a482cea3a272c8); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal8f335ae43254ed5ec6038b78f2efc0f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f335ae43254ed5ec6038b78f2efc0f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.global-search.end','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::global-search.end'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f335ae43254ed5ec6038b78f2efc0f0)): ?>
<?php $attributes = $__attributesOriginal8f335ae43254ed5ec6038b78f2efc0f0; ?>
<?php unset($__attributesOriginal8f335ae43254ed5ec6038b78f2efc0f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f335ae43254ed5ec6038b78f2efc0f0)): ?>
<?php $component = $__componentOriginal8f335ae43254ed5ec6038b78f2efc0f0; ?>
<?php unset($__componentOriginal8f335ae43254ed5ec6038b78f2efc0f0); ?>
<?php endif; ?>
    <?php echo e(\Filament\Facades\Filament::renderHook('global-search.end')); ?>

</div>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\global-search\index.blade.php ENDPATH**/ ?>