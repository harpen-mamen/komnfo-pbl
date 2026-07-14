<?php $slotContents = get_defined_vars(); $slots = collect([
    'detail',
])->mapWithKeys(fn (string $slot): array => [$slot => $slotContents[$slot] ?? null])->all(); unset($slotContents) ?>

<?php if (isset($component)) { $__componentOriginale195ea0bec2a46e3694fd4df94164525 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale195ea0bec2a46e3694fd4df94164525 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.dropdown.list.item','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes)->merge($slots)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)->merge($slots))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale195ea0bec2a46e3694fd4df94164525)): ?>
<?php $attributes = $__attributesOriginale195ea0bec2a46e3694fd4df94164525; ?>
<?php unset($__attributesOriginale195ea0bec2a46e3694fd4df94164525); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale195ea0bec2a46e3694fd4df94164525)): ?>
<?php $component = $__componentOriginale195ea0bec2a46e3694fd4df94164525; ?>
<?php unset($__componentOriginale195ea0bec2a46e3694fd4df94164525); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\dropdown\item.blade.php ENDPATH**/ ?>