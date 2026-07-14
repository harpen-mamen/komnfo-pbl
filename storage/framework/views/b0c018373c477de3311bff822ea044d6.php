<div
    <?php echo e($attributes
            ->merge($getExtraAttributes())
            ->class([
                'rounded-lg bg-gray-100 px-4 py-3',
                'dark:bg-gray-900' => config('forms.dark_mode'),
            ])); ?>

>
    <?php if (isset($component)) { $__componentOriginald1b6d0c0005f6495fd81d3884781d290 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1b6d0c0005f6495fd81d3884781d290 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.columns.layout','data' => ['components' => $getComponents(),'record' => $getRecord(),'recordKey' => $recordKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::columns.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['components' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getComponents()),'record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getRecord()),'record-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recordKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $attributes = $__attributesOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__attributesOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1b6d0c0005f6495fd81d3884781d290)): ?>
<?php $component = $__componentOriginald1b6d0c0005f6495fd81d3884781d290; ?>
<?php unset($__componentOriginald1b6d0c0005f6495fd81d3884781d290); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\layout\panel.blade.php ENDPATH**/ ?>