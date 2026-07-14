<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'id',
    'label' => null,
    'labelPrefix' => null,
    'labelSrOnly' => false,
    'labelSuffix' => null,
    'hasNestedRecursiveValidationRules' => false,
    'helperText' => null,
    'hint' => null,
    'hintColor' => null,
    'hintIcon' => null,
    'hintAction' => null,
    'required' => false,
    'statePath',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'id',
    'label' => null,
    'labelPrefix' => null,
    'labelSrOnly' => false,
    'labelSuffix' => null,
    'hasNestedRecursiveValidationRules' => false,
    'helperText' => null,
    'hint' => null,
    'hintColor' => null,
    'hintIcon' => null,
    'hintAction' => null,
    'required' => false,
    'statePath',
]); ?>
<?php foreach (array_filter(([
    'id',
    'label' => null,
    'labelPrefix' => null,
    'labelSrOnly' => false,
    'labelSuffix' => null,
    'hasNestedRecursiveValidationRules' => false,
    'helperText' => null,
    'hint' => null,
    'hintColor' => null,
    'hintIcon' => null,
    'hintAction' => null,
    'required' => false,
    'statePath',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div <?php echo e($attributes->class(['filament-forms-field-wrapper'])); ?>>
    <?php if($label && $labelSrOnly): ?>
        <label for="<?php echo e($id); ?>" class="sr-only">
            <?php echo e($label); ?>

        </label>
    <?php endif; ?>

    <div class="space-y-2">
        <?php if(($label && (! $labelSrOnly)) || $labelPrefix || $labelSuffix || $hint || $hintIcon || $hintAction): ?>
            <div
                class="flex items-center justify-between space-x-2 rtl:space-x-reverse"
            >
                <?php if($label && (! $labelSrOnly)): ?>
                    <?php if (isset($component)) { $__componentOriginale7437716cbf30e9a2a88737089ff908f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale7437716cbf30e9a2a88737089ff908f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.field-wrapper.label','data' => ['for' => $id,'error' => $errors->has($statePath),'prefix' => $labelPrefix,'required' => $required,'suffix' => $labelSuffix]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::field-wrapper.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($id),'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->has($statePath)),'prefix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($labelPrefix),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($labelSuffix)]); ?>
                        <?php echo e($label); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale7437716cbf30e9a2a88737089ff908f)): ?>
<?php $attributes = $__attributesOriginale7437716cbf30e9a2a88737089ff908f; ?>
<?php unset($__attributesOriginale7437716cbf30e9a2a88737089ff908f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale7437716cbf30e9a2a88737089ff908f)): ?>
<?php $component = $__componentOriginale7437716cbf30e9a2a88737089ff908f; ?>
<?php unset($__componentOriginale7437716cbf30e9a2a88737089ff908f); ?>
<?php endif; ?>
                <?php elseif($labelPrefix): ?>
                    <?php echo e($labelPrefix); ?>

                <?php elseif($labelSuffix): ?>
                    <?php echo e($labelSuffix); ?>

                <?php endif; ?>

                <?php if($hint || $hintIcon || $hintAction): ?>
                    <?php if (isset($component)) { $__componentOriginale474803f4b4a9e464b8e9e12941eb87b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale474803f4b4a9e464b8e9e12941eb87b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.field-wrapper.hint','data' => ['action' => $hintAction,'color' => $hintColor,'icon' => $hintIcon]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::field-wrapper.hint'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintAction),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintColor),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hintIcon)]); ?>
                        <?php echo e(filled($hint) ? ($hint instanceof \Illuminate\Support\HtmlString ? $hint : \Illuminate\Support\Str::of($hint)->markdown()->sanitizeHtml()->toHtmlString()) : null); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale474803f4b4a9e464b8e9e12941eb87b)): ?>
<?php $attributes = $__attributesOriginale474803f4b4a9e464b8e9e12941eb87b; ?>
<?php unset($__attributesOriginale474803f4b4a9e464b8e9e12941eb87b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale474803f4b4a9e464b8e9e12941eb87b)): ?>
<?php $component = $__componentOriginale474803f4b4a9e464b8e9e12941eb87b; ?>
<?php unset($__componentOriginale474803f4b4a9e464b8e9e12941eb87b); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php echo e($slot); ?>


        <?php if($errors->has($statePath) || ($hasNestedRecursiveValidationRules && $errors->has("{$statePath}.*"))): ?>
            <?php if (isset($component)) { $__componentOriginald965c3e53f868cf0cc90fb08cf29001a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald965c3e53f868cf0cc90fb08cf29001a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.field-wrapper.error-message','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::field-wrapper.error-message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e($errors->first($statePath) ?: ($hasNestedRecursiveValidationRules ? $errors->first("{$statePath}.*") : null)); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald965c3e53f868cf0cc90fb08cf29001a)): ?>
<?php $attributes = $__attributesOriginald965c3e53f868cf0cc90fb08cf29001a; ?>
<?php unset($__attributesOriginald965c3e53f868cf0cc90fb08cf29001a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald965c3e53f868cf0cc90fb08cf29001a)): ?>
<?php $component = $__componentOriginald965c3e53f868cf0cc90fb08cf29001a; ?>
<?php unset($__componentOriginald965c3e53f868cf0cc90fb08cf29001a); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($helperText): ?>
            <?php if (isset($component)) { $__componentOriginalc76dcc15b0d51a52c8bfa983cb53a08f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc76dcc15b0d51a52c8bfa983cb53a08f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.field-wrapper.helper-text','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::field-wrapper.helper-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e($helperText instanceof \Illuminate\Support\HtmlString ? $helperText : \Illuminate\Support\Str::of($helperText)->markdown()->sanitizeHtml()->toHtmlString()); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc76dcc15b0d51a52c8bfa983cb53a08f)): ?>
<?php $attributes = $__attributesOriginalc76dcc15b0d51a52c8bfa983cb53a08f; ?>
<?php unset($__attributesOriginalc76dcc15b0d51a52c8bfa983cb53a08f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc76dcc15b0d51a52c8bfa983cb53a08f)): ?>
<?php $component = $__componentOriginalc76dcc15b0d51a52c8bfa983cb53a08f; ?>
<?php unset($__componentOriginalc76dcc15b0d51a52c8bfa983cb53a08f); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views/components/field-wrapper/index.blade.php ENDPATH**/ ?>