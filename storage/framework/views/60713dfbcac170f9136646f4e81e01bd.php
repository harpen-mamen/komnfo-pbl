<?php if (isset($component)) { $__componentOriginale0bf1e6727c4b30910826fa306b06fc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0bf1e6727c4b30910826fa306b06fc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.icon-button','data' => ['attributes' => \Filament\Support\prepare_inherited_attributes($attributes),'darkMode' => config('forms.dark_mode')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Filament\Support\prepare_inherited_attributes($attributes)),'dark-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('forms.dark_mode'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0bf1e6727c4b30910826fa306b06fc2)): ?>
<?php $attributes = $__attributesOriginale0bf1e6727c4b30910826fa306b06fc2; ?>
<?php unset($__attributesOriginale0bf1e6727c4b30910826fa306b06fc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0bf1e6727c4b30910826fa306b06fc2)): ?>
<?php $component = $__componentOriginale0bf1e6727c4b30910826fa306b06fc2; ?>
<?php unset($__componentOriginale0bf1e6727c4b30910826fa306b06fc2); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\icon-button.blade.php ENDPATH**/ ?>