<?php $slotContents = get_defined_vars(); $slots = collect([
    'detail',
])->mapWithKeys(fn (string $slot): array => [$slot => $slotContents[$slot] ?? null])->all(); unset($slotContents) ?>

<?php if (isset($component)) { $__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.dropdown.list.item','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes)->merge($slots)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)->merge($slots))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb)): ?>
<?php $attributes = $__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb; ?>
<?php unset($__attributesOriginalce50feef1bd7e44483f7c3246bfd3bbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb)): ?>
<?php $component = $__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb; ?>
<?php unset($__componentOriginalce50feef1bd7e44483f7c3246bfd3bbb); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\dropdown\item.blade.php ENDPATH**/ ?>