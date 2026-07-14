<?php
    $state = $getState();
?>

<div
    wire:key="<?php echo e($this->id); ?>.table.record.<?php echo e($recordKey); ?>.column.<?php echo e($getName()); ?>.toggle-column.<?php echo e($state ? 'true' : 'false'); ?>"
>
    <div
        x-data="{
            error: undefined,
            state: <?php echo \Illuminate\Support\Js::from((bool) $state)->toHtml() ?>,
            isLoading: false,
        }"
        <?php echo e($attributes
                ->merge($getExtraAttributes())
                ->class(['filament-tables-toggle-column'])); ?>

        wire:ignore
    >
        <button
            role="switch"
            aria-checked="false"
            x-bind:aria-checked="state.toString()"
            x-on:click="
                if (isLoading) {
                    return
                }

                state = ! state

                isLoading = true
                response = await $wire.updateTableColumnState(<?php echo \Illuminate\Support\Js::from($getName())->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($recordKey)->toHtml() ?>, state)
                error = response?.error ?? undefined

                if (error) {
                    state = ! state
                }

                isLoading = false
            "
            x-tooltip="error"
            x-bind:class="{
                'opacity-70 pointer-events-none': isLoading,
                '<?php echo e(match ($getOnColor()) {
                        'danger' => 'bg-danger-500',
                        'secondary' => 'bg-gray-500',
                        'success' => 'bg-success-500',
                        'warning' => 'bg-warning-500',
                        default => 'bg-primary-600',
                    }); ?>': state,
                '<?php echo e(match ($getOffColor()) {
                        'danger' => 'bg-danger-500',
                        'primary' => 'bg-primary-500',
                        'success' => 'bg-success-500',
                        'warning' => 'bg-warning-500',
                        default => 'bg-gray-200',
                    }); ?> <?php if(config('forms.dark_mode')): ?> dark:bg-white/10 <?php endif; ?>': ! state,
            }"
            <?php echo $isDisabled() ? 'disabled' : null; ?>

            type="button"
            class="relative ml-4 inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent outline-none transition-colors duration-200 ease-in-out focus:ring-1 focus:ring-primary-500 focus:ring-offset-1 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span
                class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                x-bind:class="{
                    'translate-x-5 rtl:-translate-x-5': state,
                    'translate-x-0': ! state,
                }"
            >
                <span
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                    aria-hidden="true"
                    x-bind:class="{
                        'opacity-0 ease-out duration-100': state,
                        'opacity-100 ease-in duration-200': ! state,
                    }"
                >
                    <?php if($hasOffIcon()): ?>
                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getOffIcon()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 
                                \Illuminate\Support\Arr::toCssClasses([
                                    'h-3 w-3',
                                    match ($getOffColor()) {
                                        'danger' => 'text-danger-500',
                                        'primary' => 'text-primary-500',
                                        'success' => 'text-success-500',
                                        'warning' => 'text-warning-500',
                                        default => 'text-gray-400',
                                    },
                                ])
                            ]); ?>
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
                </span>

                <span
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                    aria-hidden="true"
                    x-bind:class="{
                        'opacity-100 ease-in duration-200': state,
                        'opacity-0 ease-out duration-100': ! state,
                    }"
                >
                    <?php if($hasOnIcon()): ?>
                        <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getOnIcon()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-cloak' => true,'class' => 
                                \Illuminate\Support\Arr::toCssClasses([
                                    'h-3 w-3',
                                    match ($getOnColor()) {
                                        'danger' => 'text-danger-500',
                                        'secondary' => 'text-gray-400',
                                        'success' => 'text-success-500',
                                        'warning' => 'text-warning-500',
                                        default => 'text-primary-500',
                                    },
                                ])
                            ]); ?>
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
                </span>
            </span>
        </button>
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\tables\resources\views\columns\toggle-column.blade.php ENDPATH**/ ?>