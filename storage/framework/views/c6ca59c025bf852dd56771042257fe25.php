<?php if (isset($component)) { $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'tables::components.icon-button','data' => ['icon' => 'heroicon-o-dots-vertical','label' => __('tables::table.buttons.open_actions.label'),'xCloak' => true,'attributes' => $attributes->class(['filament-tables-bulk-actions-trigger'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('tables::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-dots-vertical','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('tables::table.buttons.open_actions.label')),'x-cloak' => true,'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class(['filament-tables-bulk-actions-trigger']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $attributes = $__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__attributesOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b)): ?>
<?php $component = $__componentOriginalefe22f8cc3ec165cd36d597b30b8510b; ?>
<?php unset($__componentOriginalefe22f8cc3ec165cd36d597b30b8510b); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\components\bulk-actions\trigger.blade.php ENDPATH**/ ?>