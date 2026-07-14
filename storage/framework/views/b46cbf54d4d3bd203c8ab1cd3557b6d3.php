<?php $slotContents = get_defined_vars(); $slots = collect([
    'detail',
])->mapWithKeys(fn (string $slot): array => [$slot => $slotContents[$slot] ?? null])->all(); unset($slotContents) ?>

<?php if (isset($component)) { $__componentOriginala8158f47bd437a3cd13f72a187d9b82e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8158f47bd437a3cd13f72a187d9b82e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.dropdown.list.item','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes)->merge($slots)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)->merge($slots))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8158f47bd437a3cd13f72a187d9b82e)): ?>
<?php $attributes = $__attributesOriginala8158f47bd437a3cd13f72a187d9b82e; ?>
<?php unset($__attributesOriginala8158f47bd437a3cd13f72a187d9b82e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8158f47bd437a3cd13f72a187d9b82e)): ?>
<?php $component = $__componentOriginala8158f47bd437a3cd13f72a187d9b82e; ?>
<?php unset($__componentOriginala8158f47bd437a3cd13f72a187d9b82e); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\dropdown\item.blade.php ENDPATH**/ ?>