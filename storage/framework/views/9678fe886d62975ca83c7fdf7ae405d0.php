<?php if (isset($component)) { $__componentOriginalcf37a5fc7652aa4e2ed3a4891150be58 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcf37a5fc7652aa4e2ed3a4891150be58 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.hr','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'darkMode' => config('forms.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('forms.dark_mode'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcf37a5fc7652aa4e2ed3a4891150be58)): ?>
<?php $attributes = $__attributesOriginalcf37a5fc7652aa4e2ed3a4891150be58; ?>
<?php unset($__attributesOriginalcf37a5fc7652aa4e2ed3a4891150be58); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcf37a5fc7652aa4e2ed3a4891150be58)): ?>
<?php $component = $__componentOriginalcf37a5fc7652aa4e2ed3a4891150be58; ?>
<?php unset($__componentOriginalcf37a5fc7652aa4e2ed3a4891150be58); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\components\hr.blade.php ENDPATH**/ ?>