<?php
    $affixLabelClasses = [
        'whitespace-nowrap group-focus-within:text-primary-500',
        'text-gray-400' => ! $errors->has($getStatePath()),
        'text-danger-400' => $errors->has($getStatePath()),
        'dark:text-danger-400' => $errors->has($getStatePath()) && config('forms.dark_mode'),
    ];
?>

<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => $getId(),'label' => $getLabel(),'label-sr-only' => $isLabelHidden(),'helper-text' => $getHelperText(),'hint' => $getHint(),'hint-action' => $getHintAction(),'hint-color' => $getHintColor(),'hint-icon' => $getHintIcon(),'required' => $isRequired(),'state-path' => $getStatePath()]); ?>
    <div
        <?php echo e($attributes
                ->merge($getExtraAttributes())
                ->class(['filament-forms-color-picker-component group flex items-center space-x-1 rtl:space-x-reverse'])); ?>

    >
        <?php if(($prefixAction = $getPrefixAction()) && (! $prefixAction->isHidden())): ?>
            <?php echo e($prefixAction); ?>

        <?php endif; ?>

        <?php if($icon = $getPrefixIcon()): ?>
            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if(filled($label = $getPrefixLabel())): ?>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses($affixLabelClasses); ?>">
                <?php echo e($label); ?>

            </span>
        <?php endif; ?>

        <div
            x-data="colorPickerFormComponent({
                        isAutofocused: <?php echo \Illuminate\Support\Js::from($isAutofocused())->toHtml() ?>,
                        isDisabled: <?php echo \Illuminate\Support\Js::from($isDisabled())->toHtml() ?>,
                        state: $wire.<?php echo e($applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')')); ?>,
                    })"
            x-on:keydown.esc="isOpen() && $event.stopPropagation()"
            <?php echo e($getExtraAlpineAttributeBag()->class(['relative flex-1'])); ?>

        >
            <input
                x-ref="input"
                type="text"
                dusk="filament.forms.<?php echo e($getStatePath()); ?>"
                id="<?php echo e($getId()); ?>"
                x-model="state"
                x-on:click="togglePanelVisibility()"
                x-on:keydown.enter.stop.prevent="togglePanelVisibility()"
                autocomplete="off"
                <?php echo $isDisabled() ? 'disabled' : null; ?>

                <?php echo ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null; ?>

                <?php if(! $isConcealed()): ?>
                    <?php echo $isRequired() ? 'required' : null; ?>

                <?php endif; ?>
                <?php echo e($getExtraInputAttributeBag()->class([
                        'filament-forms-input block w-full rounded-lg text-gray-900 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                        'dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('forms.dark_mode'),
                        'border-gray-300' => ! $errors->has($getStatePath()),
                        'dark:border-gray-600' => (! $errors->has($getStatePath())) && config('forms.dark_mode'),
                        'border-danger-600 ring-danger-600' => $errors->has($getStatePath()),
                        'dark:border-danger-400 dark:ring-danger-400' => $errors->has($getStatePath()) && config('forms.dark_mode'),
                    ])); ?>

            />

            <span
                x-cloak
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 rtl:left-0 rtl:right-auto rtl:pl-2"
            >
                <span
                    x-bind:style="{ 'background-color': state }"
                    class="filament-forms-color-picker-component-preview relative h-7 w-7 overflow-hidden rounded-md"
                ></span>
            </span>

            <div
                x-cloak
                x-ref="panel"
                x-float.placement.bottom-start.offset.flip.shift="{ offset: 8 }"
                wire:ignore.self
                wire:key="<?php echo e($this->id); ?>.<?php echo e($getStatePath()); ?>.<?php echo e($field::class); ?>.panel"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'absolute z-10 hidden shadow-lg',
                    'pointer-events-none opacity-70' => $isDisabled(),
                ]); ?>"
            >
                <?php
                    $tag = match ($getFormat()) {
                        'hsl' => 'hsl-string',
                        'rgb' => 'rgb-string',
                        'rgba' => 'rgba-string',
                        default => 'hex',
                    } . '-color-picker';
                ?>

                <<?php echo e($tag); ?> color="<?php echo e($getState()); ?>" />
            </div>
        </div>

        <?php if(filled($label = $getSuffixLabel())): ?>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses($affixLabelClasses); ?>">
                <?php echo e($label); ?>

            </span>
        <?php endif; ?>

        <?php if($icon = $getSuffixIcon()): ?>
            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if(($suffixAction = $getSuffixAction()) && (! $suffixAction->isHidden())): ?>
            <?php echo e($suffixAction); ?>

        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\color-picker.blade.php ENDPATH**/ ?>