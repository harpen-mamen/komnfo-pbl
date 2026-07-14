<?php if (isset($component)) { $__componentOriginalcb9b2192b9152278a357ab8b3656b740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb9b2192b9152278a357ab8b3656b740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.page','data' => ['class' => 
        \Illuminate\Support\Arr::toCssClasses([
            'filament-resources-create-record-page',
            'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
        ])
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
        \Illuminate\Support\Arr::toCssClasses([
            'filament-resources-create-record-page',
            'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
        ])
    )]); ?>
    <?php if (isset($component)) { $__componentOriginal0292b4b9c99199315c59438a3c176bad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0292b4b9c99199315c59438a3c176bad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.form.index','data' => ['wire:submit.prevent' => 'create']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:submit.prevent' => 'create']); ?>
        <?php echo e($this->form); ?>


        <?php if (isset($component)) { $__componentOriginal06fbb85eece357c42d0c3aa5699c7cff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06fbb85eece357c42d0c3aa5699c7cff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.form.actions','data' => ['actions' => $this->getCachedFormActions(),'fullWidth' => $this->hasFullWidthFormActions()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::form.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getCachedFormActions()),'full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->hasFullWidthFormActions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06fbb85eece357c42d0c3aa5699c7cff)): ?>
<?php $attributes = $__attributesOriginal06fbb85eece357c42d0c3aa5699c7cff; ?>
<?php unset($__attributesOriginal06fbb85eece357c42d0c3aa5699c7cff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06fbb85eece357c42d0c3aa5699c7cff)): ?>
<?php $component = $__componentOriginal06fbb85eece357c42d0c3aa5699c7cff; ?>
<?php unset($__componentOriginal06fbb85eece357c42d0c3aa5699c7cff); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0292b4b9c99199315c59438a3c176bad)): ?>
<?php $attributes = $__attributesOriginal0292b4b9c99199315c59438a3c176bad; ?>
<?php unset($__attributesOriginal0292b4b9c99199315c59438a3c176bad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0292b4b9c99199315c59438a3c176bad)): ?>
<?php $component = $__componentOriginal0292b4b9c99199315c59438a3c176bad; ?>
<?php unset($__componentOriginal0292b4b9c99199315c59438a3c176bad); ?>
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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\resources\pages\create-record.blade.php ENDPATH**/ ?>