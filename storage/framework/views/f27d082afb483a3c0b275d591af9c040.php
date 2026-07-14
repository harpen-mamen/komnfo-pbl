<?php
    $action = $this->getMountedFormComponentAction();
?>

<form wire:submit.prevent="callMountedFormComponentAction">
    <?php if (isset($component)) { $__componentOriginal53d3b1a50fb89901bb4da6500a00869b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53d3b1a50fb89901bb4da6500a00869b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.modal.index','data' => ['id' => $this->id . '-form-component-action','wire:key' => $action ? $this->id . '.' . $action->getComponent()->getStatePath() . '.actions.' . $action->getName() . '.modal' : null,'visible' => filled($action),'width' => $action?->getModalWidth(),'slideOver' => $action?->isModalSlideOver(),'closeByClickingAway' => $action?->isModalClosedByClickingAway(),'displayClasses' => 'block','xInit' => 'livewire = $wire.__instance','xOn:modalClosed.stop' => 'if (\'mountedFormComponentAction\' in livewire?.serverMemo.data) livewire.set(\'mountedFormComponentAction\', null)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->id . '-form-component-action'),'wire:key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action ? $this->id . '.' . $action->getComponent()->getStatePath() . '.actions.' . $action->getName() . '.modal' : null),'visible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($action)),'width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->getModalWidth()),'slide-over' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalSlideOver()),'close-by-clicking-away' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action?->isModalClosedByClickingAway()),'display-classes' => 'block','x-init' => 'livewire = $wire.__instance','x-on:modal-closed.stop' => 'if (\'mountedFormComponentAction\' in livewire?.serverMemo.data) livewire.set(\'mountedFormComponentAction\', null)']); ?>
        <?php if($action): ?>
            <?php if($action->isModalCentered()): ?>
                <?php if($heading = $action->getModalHeading()): ?>
                     <?php $__env->slot('heading', null, []); ?> 
                        <?php echo e($heading); ?>

                     <?php $__env->endSlot(); ?>
                <?php endif; ?>

                <?php if($subheading = $action->getModalSubheading()): ?>
                     <?php $__env->slot('subheading', null, []); ?> 
                        <?php echo e($subheading); ?>

                     <?php $__env->endSlot(); ?>
                <?php endif; ?>
            <?php else: ?>
                 <?php $__env->slot('header', null, []); ?> 
                    <?php if($heading = $action->getModalHeading()): ?>
                        <?php if (isset($component)) { $__componentOriginalbb6c7f530e6b14de1d802b641f19bcbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb6c7f530e6b14de1d802b641f19bcbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.modal.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::modal.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php echo e($heading); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb6c7f530e6b14de1d802b641f19bcbc)): ?>
<?php $attributes = $__attributesOriginalbb6c7f530e6b14de1d802b641f19bcbc; ?>
<?php unset($__attributesOriginalbb6c7f530e6b14de1d802b641f19bcbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb6c7f530e6b14de1d802b641f19bcbc)): ?>
<?php $component = $__componentOriginalbb6c7f530e6b14de1d802b641f19bcbc; ?>
<?php unset($__componentOriginalbb6c7f530e6b14de1d802b641f19bcbc); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if($subheading = $action->getModalSubheading()): ?>
                        <?php if (isset($component)) { $__componentOriginal6556dfd423d0a210297e853e50b7f8ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6556dfd423d0a210297e853e50b7f8ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.modal.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::modal.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <?php echo e($subheading); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6556dfd423d0a210297e853e50b7f8ee)): ?>
<?php $attributes = $__attributesOriginal6556dfd423d0a210297e853e50b7f8ee; ?>
<?php unset($__attributesOriginal6556dfd423d0a210297e853e50b7f8ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6556dfd423d0a210297e853e50b7f8ee)): ?>
<?php $component = $__componentOriginal6556dfd423d0a210297e853e50b7f8ee; ?>
<?php unset($__componentOriginal6556dfd423d0a210297e853e50b7f8ee); ?>
<?php endif; ?>
                    <?php endif; ?>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>

            <?php echo e($action->getModalContent()); ?>


            <?php if($action->hasFormSchema()): ?>
                <?php echo e($this->getMountedFormComponentActionForm()); ?>

            <?php endif; ?>

            <?php echo e($action->getModalFooter()); ?>


            <?php if(count($action->getModalActions())): ?>
                 <?php $__env->slot('footer', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginalea296b60bae671dcbebb36375da131d9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea296b60bae671dcbebb36375da131d9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.modal.actions','data' => ['fullWidth' => $action->isModalCentered()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::modal.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($action->isModalCentered())]); ?>
                        <?php $__currentLoopData = $action->getModalActions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalAction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($modalAction); ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea296b60bae671dcbebb36375da131d9)): ?>
<?php $attributes = $__attributesOriginalea296b60bae671dcbebb36375da131d9; ?>
<?php unset($__attributesOriginalea296b60bae671dcbebb36375da131d9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea296b60bae671dcbebb36375da131d9)): ?>
<?php $component = $__componentOriginalea296b60bae671dcbebb36375da131d9; ?>
<?php unset($__componentOriginalea296b60bae671dcbebb36375da131d9); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53d3b1a50fb89901bb4da6500a00869b)): ?>
<?php $attributes = $__attributesOriginal53d3b1a50fb89901bb4da6500a00869b; ?>
<?php unset($__attributesOriginal53d3b1a50fb89901bb4da6500a00869b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53d3b1a50fb89901bb4da6500a00869b)): ?>
<?php $component = $__componentOriginal53d3b1a50fb89901bb4da6500a00869b; ?>
<?php unset($__componentOriginal53d3b1a50fb89901bb4da6500a00869b); ?>
<?php endif; ?>
</form>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views/components/actions/modal/index.blade.php ENDPATH**/ ?>