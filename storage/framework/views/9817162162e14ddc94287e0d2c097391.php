<?php if (isset($component)) { $__componentOriginalcb9b2192b9152278a357ab8b3656b740 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb9b2192b9152278a357ab8b3656b740 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.page','data' => ['widgetData' => ['record' => $record],'class' => 
        \Illuminate\Support\Arr::toCssClasses([
            'filament-resources-edit-record-page',
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
            'filament-resources-edit-record-page',
            'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
            'filament-resources-record-' . $record->getKey(),
        ])
    )]); ?>
    
            <?php $form = (function ($args) {
                return function () use ($args) {
                    extract($args, EXTR_SKIP);
                    ob_start(); ?>
        
        <?php if (isset($component)) { $__componentOriginal0292b4b9c99199315c59438a3c176bad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0292b4b9c99199315c59438a3c176bad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.form.index','data' => ['wire:submit.prevent' => 'save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:submit.prevent' => 'save']); ?>
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
    
            <?php return new \Illuminate\Support\HtmlString(ob_get_clean()); };
                })(get_defined_vars()); ?>
        

    <?php
        $relationManagers = $this->getRelationManagers();
    ?>

    <?php if((! $this->hasCombinedRelationManagerTabsWithForm()) || (! count($relationManagers))): ?>
        <?php echo e($form()); ?>

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
                    <?php echo e($form()); ?>

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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\resources\pages\edit-record.blade.php ENDPATH**/ ?>