<?php $slotContents = get_defined_vars(); $slots = collect([
    'detail',
])->mapWithKeys(fn (string $slot): array => [$slot => $slotContents[$slot] ?? null])->all(); unset($slotContents) ?>

<?php if (isset($component)) { $__componentOriginal9793da841d26febbc9fa3fa836322138 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9793da841d26febbc9fa3fa836322138 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.dropdown.list.item','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes)->merge($slots)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::dropdown.list.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)->merge($slots))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9793da841d26febbc9fa3fa836322138)): ?>
<?php $attributes = $__attributesOriginal9793da841d26febbc9fa3fa836322138; ?>
<?php unset($__attributesOriginal9793da841d26febbc9fa3fa836322138); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9793da841d26febbc9fa3fa836322138)): ?>
<?php $component = $__componentOriginal9793da841d26febbc9fa3fa836322138; ?>
<?php unset($__componentOriginal9793da841d26febbc9fa3fa836322138); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\support\resources\views\components\dropdown\item.blade.php ENDPATH**/ ?>