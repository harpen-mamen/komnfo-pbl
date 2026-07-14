<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => $getId(),'label' => $getLabel(),'label-sr-only' => $isLabelHidden(),'has-nested-recursive-validation-rules' => true,'helper-text' => $getHelperText(),'hint' => $getHint(),'hint-action' => $getHintAction(),'hint-color' => $getHintColor(),'hint-icon' => $getHintIcon(),'required' => $isRequired(),'state-path' => $getStatePath()]); ?>
    <div
        x-data="tagsInputFormComponent({
                    state: $wire.<?php echo e($applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')')); ?>,
                })"
        id="<?php echo e($getId()); ?>"
        <?php echo e($attributes->merge($getExtraAttributes())->class(['filament-forms-tags-input-component'])); ?>

        <?php echo e($getExtraAlpineAttributeBag()); ?>

    >
        <div
            x-show="state.length || <?php echo e($isDisabled() ? 'false' : 'true'); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'block w-full divide-y overflow-hidden rounded-lg border shadow-sm transition duration-75 focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500',
                'dark:divide-gray-600' => config('forms.dark_mode'),
                'border-gray-300' => ! $errors->has($getStatePath()),
                'dark:border-gray-600' => (! $errors->has($getStatePath())) && config('forms.dark_mode'),
                'border-danger-600 ring-1 ring-inset ring-danger-600' => $errors->has($getStatePath()),
                'dark:border-danger-400 dark:ring-danger-400' => $errors->has($getStatePath()) && config('forms.dark_mode'),
            ]); ?>"
        >
            <?php if (! ($isDisabled())): ?>
                <div>
                    <input
                        autocomplete="off"
                        <?php echo $isAutofocused() ? 'autofocus' : null; ?>

                        id="<?php echo e($getId()); ?>"
                        list="<?php echo e($getId()); ?>-suggestions"
                        <?php echo $getPlaceholder() ? 'placeholder="' . $getPlaceholder() . '"' : null; ?>

                        type="text"
                        dusk="filament.forms.<?php echo e($getStatePath()); ?>"
                        x-on:keydown.enter.stop.prevent="createTag()"
                        x-on:keydown.,.stop.prevent="createTag()"
                        x-on:blur="createTag()"
                        x-on:paste="
                            $nextTick(() => {
                                if (newTag.includes(',')) {
                                    newTag.split(',').forEach((tag) => {
                                        newTag = tag

                                        createTag()
                                    })
                                }
                            })
                        "
                        x-model="newTag"
                        <?php echo e($getExtraInputAttributeBag()->class([
                                'webkit-calendar-picker-indicator:opacity-0 block w-full border-0',
                                'dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400' => config('forms.dark_mode'),
                            ])); ?>

                    />

                    <datalist id="<?php echo e($getId()); ?>-suggestions">
                        <?php $__currentLoopData = $getSuggestions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <template
                                x-if="! state.includes(<?php echo \Illuminate\Support\Js::from($suggestion)->toHtml() ?>)"
                                x-bind:key="<?php echo \Illuminate\Support\Js::from($suggestion)->toHtml() ?>"
                            >
                                <option value="<?php echo e($suggestion); ?>" />
                            </template>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </datalist>
                </div>
            <?php endif; ?>

            <div
                x-show="state.length"
                x-cloak
                class="relative w-full overflow-hidden p-2"
            >
                <div class="flex flex-wrap gap-1">
                    <template
                        class="hidden"
                        x-for="tag in state"
                        x-bind:key="tag"
                    >
                        <button
                            <?php if (! ($isDisabled())): ?>
                                x-on:click="deleteTag(tag)"
                            <?php endif; ?>
                            type="button"
                            x-bind:dusk="'filament.forms.<?php echo e($getStatePath()); ?>' + '.tag.' + tag + '.delete'"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'min-h-6 inline-flex items-center justify-center space-x-1 rounded-xl bg-primary-500/10 px-2 py-0.5 text-sm font-medium tracking-tight text-primary-700 rtl:space-x-reverse',
                                'dark:text-primary-500' => config('forms.dark_mode'),
                                'cursor-default' => $isDisabled(),
                            ]); ?>"
                        >
                            <span class="text-start" x-text="tag"></span>

                            <?php if (! ($isDisabled())): ?>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-x'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-3 w-3 shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </button>
                    </template>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\tags-input.blade.php ENDPATH**/ ?>