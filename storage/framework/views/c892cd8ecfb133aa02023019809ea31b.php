<?php if (isset($component)) { $__componentOriginalcb9b2192b9152278a357ab8b3656b740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb9b2192b9152278a357ab8b3656b740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.page','data' => ['widgetData' => ['record' => $record],'class' => 
        \Illuminate\Support\Arr::toCssClasses([
            'filament-resources-view-record-page',
            'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
            'filament-resources-record-' . $record->getKey(),
        ])
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['widget-data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['record' => $record]),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
        \Illuminate\Support\Arr::toCssClasses([
            'filament-resources-view-record-page',
            'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
            'filament-resources-record-' . $record->getKey(),
        ])
    )]); ?>
    <?php
        $relationManagers = $this->getRelationManagers();
    ?>

    <?php if((! $this->hasCombinedRelationManagerTabsWithForm()) || (! count($relationManagers))): ?>
        <?php echo e($this->form); ?>

    <?php endif; ?>

    <?php if(count($relationManagers)): ?>
        <?php if(! $this->hasCombinedRelationManagerTabsWithForm()): ?>
            <?php if (isset($component)) { $__componentOriginal3bb406532a5c335331f97685b7527de1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3bb406532a5c335331f97685b7527de1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.hr','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3bb406532a5c335331f97685b7527de1)): ?>
<?php $attributes = $__attributesOriginal3bb406532a5c335331f97685b7527de1; ?>
<?php unset($__attributesOriginal3bb406532a5c335331f97685b7527de1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3bb406532a5c335331f97685b7527de1)): ?>
<?php $component = $__componentOriginal3bb406532a5c335331f97685b7527de1; ?>
<?php unset($__componentOriginal3bb406532a5c335331f97685b7527de1); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalf2343a8bb01441eb6d0b96f0c31ee428 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2343a8bb01441eb6d0b96f0c31ee428 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.resources.relation-managers.index','data' => ['activeManager' => $activeRelationManager,'formTabLabel' => $this->getFormTabLabel(),'managers' => $relationManagers,'ownerRecord' => $record,'pageClass' => static::class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::resources.relation-managers'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active-manager' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeRelationManager),'form-tab-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getFormTabLabel()),'managers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relationManagers),'owner-record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($record),'page-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(static::class)]); ?>
            <?php if($this->hasCombinedRelationManagerTabsWithForm()): ?>
                 <?php $__env->slot('form', null, []); ?> 
                    <?php echo e($this->form); ?>

                 <?php $__env->endSlot(); ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2343a8bb01441eb6d0b96f0c31ee428)): ?>
<?php $attributes = $__attributesOriginalf2343a8bb01441eb6d0b96f0c31ee428; ?>
<?php unset($__attributesOriginalf2343a8bb01441eb6d0b96f0c31ee428); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2343a8bb01441eb6d0b96f0c31ee428)): ?>
<?php $component = $__componentOriginalf2343a8bb01441eb6d0b96f0c31ee428; ?>
<?php unset($__componentOriginalf2343a8bb01441eb6d0b96f0c31ee428); ?>
<?php endif; ?>
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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\resources\pages\view-record.blade.php ENDPATH**/ ?>