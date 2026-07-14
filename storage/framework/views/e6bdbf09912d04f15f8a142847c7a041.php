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
    <?php
        $containers = $getChildComponentContainers();

        $isCollapsible = $isCollapsible();
        $isCloneable = $isCloneable();
        $isReorderableWithButtons = $isReorderableWithButtons();
        $isItemCreationDisabled = $isItemCreationDisabled();
        $isItemDeletionDisabled = $isItemDeletionDisabled();
        $isItemMovementDisabled = $isItemMovementDisabled();
        $hasItemLabels = $hasItemLabels();
    ?>

    <div>
        <?php if((count($containers) > 1) && $isCollapsible): ?>
            <div class="space-x-2 rtl:space-x-reverse" x-data="{}">
                <?php if (isset($component)) { $__componentOriginal6afa4c11877d2c8634a52f57f7f26617 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6afa4c11877d2c8634a52f57f7f26617 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.link','data' => ['xOn:click' => '$dispatch(\'repeater-collapse\', \''.e($getStatePath()).'\')','tag' => 'button','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => '$dispatch(\'repeater-collapse\', \''.e($getStatePath()).'\')','tag' => 'button','size' => 'sm']); ?>
                    <?php echo e(__('forms::components.repeater.buttons.collapse_all.label')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6afa4c11877d2c8634a52f57f7f26617)): ?>
<?php $attributes = $__attributesOriginal6afa4c11877d2c8634a52f57f7f26617; ?>
<?php unset($__attributesOriginal6afa4c11877d2c8634a52f57f7f26617); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6afa4c11877d2c8634a52f57f7f26617)): ?>
<?php $component = $__componentOriginal6afa4c11877d2c8634a52f57f7f26617; ?>
<?php unset($__componentOriginal6afa4c11877d2c8634a52f57f7f26617); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal6afa4c11877d2c8634a52f57f7f26617 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6afa4c11877d2c8634a52f57f7f26617 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.link','data' => ['xOn:click' => '$dispatch(\'repeater-expand\', \''.e($getStatePath()).'\')','tag' => 'button','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => '$dispatch(\'repeater-expand\', \''.e($getStatePath()).'\')','tag' => 'button','size' => 'sm']); ?>
                    <?php echo e(__('forms::components.repeater.buttons.expand_all.label')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6afa4c11877d2c8634a52f57f7f26617)): ?>
<?php $attributes = $__attributesOriginal6afa4c11877d2c8634a52f57f7f26617; ?>
<?php unset($__attributesOriginal6afa4c11877d2c8634a52f57f7f26617); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6afa4c11877d2c8634a52f57f7f26617)): ?>
<?php $component = $__componentOriginal6afa4c11877d2c8634a52f57f7f26617; ?>
<?php unset($__componentOriginal6afa4c11877d2c8634a52f57f7f26617); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div
        <?php echo e($attributes
                ->merge($getExtraAttributes())
                ->class([
                    'filament-forms-repeater-component space-y-6 rounded-xl',
                    'bg-gray-50 p-6' => $isInset(),
                    'dark:bg-gray-500/10' => $isInset() && config('forms.dark_mode'),
                ])); ?>

    >
        <?php if(count($containers)): ?>
            <ul>
                <?php if (isset($component)) { $__componentOriginal619b5702a4dba2316335176c43ec06a2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal619b5702a4dba2316335176c43ec06a2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.grid.index','data' => ['default' => $getGridColumns('default'),'sm' => $getGridColumns('sm'),'md' => $getGridColumns('md'),'lg' => $getGridColumns('lg'),'xl' => $getGridColumns('xl'),'twoXl' => $getGridColumns('2xl'),'wire:sortable' => true,'wire:end.stop' => 'dispatchFormEvent(\'repeater::moveItems\', \''.e($getStatePath()).'\', $event.target.sortable.toArray())','class' => 'gap-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['default' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('default')),'sm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('sm')),'md' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('md')),'lg' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('lg')),'xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('xl')),'two-xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getGridColumns('2xl')),'wire:sortable' => true,'wire:end.stop' => 'dispatchFormEvent(\'repeater::moveItems\', \''.e($getStatePath()).'\', $event.target.sortable.toArray())','class' => 'gap-6']); ?>
                    <?php $__currentLoopData = $containers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uuid => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li
                            x-data="{
                                isCollapsed: <?php echo \Illuminate\Support\Js::from($isCollapsed($item))->toHtml() ?>,
                            }"
                            x-on:repeater-collapse.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = true)"
                            x-on:repeater-expand.window="$event.detail === '<?php echo e($getStatePath()); ?>' && (isCollapsed = false)"
                            wire:key="<?php echo e($this->id); ?>.<?php echo e($item->getStatePath()); ?>.<?php echo e($field::class); ?>.item"
                            wire:sortable.item="<?php echo e($uuid); ?>"
                            x-on:expand-concealing-component.window="
                                error = $el.querySelector('[data-validation-error]')

                                if (! error) {
                                    return
                                }

                                isCollapsed = false

                                if (document.body.querySelector('[data-validation-error]') !== error) {
                                    return
                                }

                                setTimeout(
                                    () =>
                                        $el.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'start',
                                            inline: 'start',
                                        }),
                                    200,
                                )
                            "
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'filament-forms-repeater-component-item relative rounded-xl border border-gray-300 bg-white shadow-sm',
                                'dark:border-gray-600 dark:bg-gray-800' => config('forms.dark_mode'),
                            ]); ?>"
                        >
                            <?php if((! $isItemMovementDisabled) || (! $isItemDeletionDisabled) || $isCloneable || $isCollapsible || $hasItemLabels): ?>
                                <header
                                    <?php if($isCollapsible): ?> x-on:click.stop="isCollapsed = ! isCollapsed" <?php endif; ?>
                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'flex h-10 items-center overflow-hidden rounded-t-xl border-b bg-gray-50',
                                        'dark:border-gray-700 dark:bg-gray-800' => config('forms.dark_mode'),
                                        'cursor-pointer' => $isCollapsible,
                                    ]); ?>"
                                >
                                    <?php if (! ($isItemMovementDisabled)): ?>
                                        <button
                                            title="<?php echo e(__('forms::components.repeater.buttons.move_item.label')); ?>"
                                            x-on:click.stop
                                            wire:sortable.handle
                                            wire:keydown.prevent.arrow-up="dispatchFormEvent('repeater::moveItemUp', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                            wire:keydown.prevent.arrow-down="dispatchFormEvent('repeater::moveItemDown', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                            type="button"
                                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'flex h-10 w-10 flex-none items-center justify-center border-r text-gray-400 outline-none transition hover:text-gray-500 focus:bg-gray-500/5',
                                                'dark:border-gray-700 dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                            ]); ?>"
                                        >
                                            <span class="sr-only">
                                                <?php echo e(__('forms::components.repeater.buttons.move_item.label')); ?>

                                            </span>

                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-switch-vertical'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
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
                                        </button>
                                    <?php endif; ?>

                                    <p
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'flex-none truncate px-4 text-xs font-medium text-gray-600',
                                            'dark:text-gray-400' => config('forms.dark_mode'),
                                        ]); ?>"
                                    >
                                        <?php echo e($getItemLabel($uuid)); ?>

                                    </p>

                                    <div class="flex-1"></div>

                                    <ul
                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'flex divide-x rtl:divide-x-reverse',
                                            'dark:divide-gray-700' => config('forms.dark_mode'),
                                        ]); ?>"
                                    >
                                        <?php if($isReorderableWithButtons): ?>
                                            <?php if (! ($loop->first)): ?>
                                                <li>
                                                    <button
                                                        title="<?php echo e(__('forms::components.repeater.buttons.move_item_up.label')); ?>"
                                                        type="button"
                                                        wire:click.stop="dispatchFormEvent('repeater::moveItemUp', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                        wire:target="dispatchFormEvent('repeater::moveItemUp', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                        wire:loading.attr="disabled"
                                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                            'flex h-10 w-10 flex-none items-center justify-center text-gray-400 outline-none transition hover:text-gray-500 focus:bg-gray-500/5',
                                                            'dark:border-gray-700 dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                                        ]); ?>"
                                                    >
                                                        <span class="sr-only">
                                                            <?php echo e(__('forms::components.repeater.buttons.move_item_up.label')); ?>

                                                        </span>

                                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','wire:loading.remove.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemUp\', \''.e($getStatePath()).'\', \''.e($uuid).'\')']); ?>
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

                                                        <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemUp\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemUp\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
                                                    </button>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (! ($loop->last)): ?>
                                                <li>
                                                    <button
                                                        title="<?php echo e(__('forms::components.repeater.buttons.move_item_down.label')); ?>"
                                                        type="button"
                                                        wire:click.stop="dispatchFormEvent('repeater::moveItemDown', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                        wire:target="dispatchFormEvent('repeater::moveItemDown', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                        wire:loading.attr="disabled"
                                                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                            'flex h-10 w-10 flex-none items-center justify-center text-gray-400 outline-none transition hover:text-gray-500 focus:bg-gray-500/5',
                                                            'dark:border-gray-700 dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                                        ]); ?>"
                                                    >
                                                        <span class="sr-only">
                                                            <?php echo e(__('forms::components.repeater.buttons.move_item_down.label')); ?>

                                                        </span>

                                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','wire:loading.remove.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemDown\', \''.e($getStatePath()).'\', \''.e($uuid).'\')']); ?>
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

                                                        <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemDown\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::moveItemDown\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if($isCloneable): ?>
                                            <li>
                                                <button
                                                    title="<?php echo e(__('forms::components.repeater.buttons.clone_item.label')); ?>"
                                                    wire:click.stop="dispatchFormEvent('repeater::cloneItem', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                    wire:target="dispatchFormEvent('repeater::cloneItem', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                    wire:loading.attr="disabled"
                                                    type="button"
                                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'flex h-10 w-10 flex-none items-center justify-center text-gray-400 outline-none transition hover:text-gray-500 focus:bg-gray-500/5',
                                                        'dark:border-gray-700 dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                                    ]); ?>"
                                                >
                                                    <span class="sr-only">
                                                        <?php echo e(__('forms::components.repeater.buttons.clone_item.label')); ?>

                                                    </span>

                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-duplicate'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','wire:loading.remove.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::cloneItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')']); ?>
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

                                                    <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::cloneItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::cloneItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
                                                </button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (! ($isItemDeletionDisabled)): ?>
                                            <li>
                                                <button
                                                    title="<?php echo e(__('forms::components.repeater.buttons.delete_item.label')); ?>"
                                                    wire:click.stop="dispatchFormEvent('repeater::deleteItem', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                    wire:target="dispatchFormEvent('repeater::deleteItem', '<?php echo e($getStatePath()); ?>', '<?php echo e($uuid); ?>')"
                                                    wire:loading.attr="disabled"
                                                    type="button"
                                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'flex h-10 w-10 flex-none items-center justify-center text-danger-600 outline-none transition hover:text-danger-500 focus:bg-gray-500/5',
                                                        'dark:text-danger-500 dark:hover:text-danger-400 dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                                    ]); ?>"
                                                >
                                                    <span class="sr-only">
                                                        <?php echo e(__('forms::components.repeater.buttons.delete_item.label')); ?>

                                                    </span>

                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','wire:loading.remove.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::deleteItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')']); ?>
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

                                                    <?php if (isset($component)) { $__componentOriginal68a3024fa61352b757a05bc2899e1852 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68a3024fa61352b757a05bc2899e1852 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.loading-indicator','data' => ['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::deleteItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4 text-primary-500','wire:loading.delay' => true,'wire:target' => 'dispatchFormEvent(\'repeater::deleteItem\', \''.e($getStatePath()).'\', \''.e($uuid).'\')','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $attributes = $__attributesOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__attributesOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68a3024fa61352b757a05bc2899e1852)): ?>
<?php $component = $__componentOriginal68a3024fa61352b757a05bc2899e1852; ?>
<?php unset($__componentOriginal68a3024fa61352b757a05bc2899e1852); ?>
<?php endif; ?>
                                                </button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if($isCollapsible): ?>
                                            <li>
                                                <button
                                                    x-bind:title="
                                                        ! isCollapsed
                                                            ? '<?php echo e(__('forms::components.repeater.buttons.collapse_item.label')); ?>'
                                                            : '<?php echo e(__('forms::components.repeater.buttons.expand_item.label')); ?>'
                                                    "
                                                    x-on:click.stop="isCollapsed = ! isCollapsed"
                                                    type="button"
                                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'flex h-10 w-10 flex-none items-center justify-center text-gray-400 outline-none transition hover:text-gray-500 focus:bg-gray-500/5',
                                                        'dark:focus:bg-gray-600/20' => config('forms.dark_mode'),
                                                    ]); ?>"
                                                >
                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-minus-sm'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','x-show' => '! isCollapsed']); ?>
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

                                                    <span
                                                        class="sr-only"
                                                        x-show="! isCollapsed"
                                                    >
                                                        <?php echo e(__('forms::components.repeater.buttons.collapse_item.label')); ?>

                                                    </span>

                                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-s-plus-sm'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4','x-show' => 'isCollapsed','x-cloak' => true]); ?>
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

                                                    <span
                                                        class="sr-only"
                                                        x-show="isCollapsed"
                                                        x-cloak
                                                    >
                                                        <?php echo e(__('forms::components.repeater.buttons.expand_item.label')); ?>

                                                    </span>
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </header>
                            <?php endif; ?>

                            <div
                                x-bind:class="{
                                    'invisible h-0 !m-0 overflow-y-hidden': isCollapsed,
                                    'p-6': ! isCollapsed,
                                }"
                            >
                                <?php echo e($item); ?>

                            </div>

                            <div
                                class="p-2 text-center text-xs text-gray-400"
                                x-show="isCollapsed"
                                x-cloak
                            >
                                <?php echo e(__('forms::components.repeater.collapsed')); ?>

                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal619b5702a4dba2316335176c43ec06a2)): ?>
<?php $attributes = $__attributesOriginal619b5702a4dba2316335176c43ec06a2; ?>
<?php unset($__attributesOriginal619b5702a4dba2316335176c43ec06a2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal619b5702a4dba2316335176c43ec06a2)): ?>
<?php $component = $__componentOriginal619b5702a4dba2316335176c43ec06a2; ?>
<?php unset($__componentOriginal619b5702a4dba2316335176c43ec06a2); ?>
<?php endif; ?>
            </ul>
        <?php endif; ?>

        <?php if(! $isItemCreationDisabled): ?>
            <div class="relative flex justify-center">
                <?php if (isset($component)) { $__componentOriginal3405418bfc981ae89c307c0905f99f8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3405418bfc981ae89c307c0905f99f8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.button','data' => ['wire:click' => 'dispatchFormEvent(\'repeater::createItem\', \'' . $getStatePath() . '\')','size' => 'sm','outlined' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('dispatchFormEvent(\'repeater::createItem\', \'' . $getStatePath() . '\')'),'size' => 'sm','outlined' => true]); ?>
                    <?php echo e($getCreateItemButtonLabel()); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3405418bfc981ae89c307c0905f99f8b)): ?>
<?php $attributes = $__attributesOriginal3405418bfc981ae89c307c0905f99f8b; ?>
<?php unset($__attributesOriginal3405418bfc981ae89c307c0905f99f8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3405418bfc981ae89c307c0905f99f8b)): ?>
<?php $component = $__componentOriginal3405418bfc981ae89c307c0905f99f8b; ?>
<?php unset($__componentOriginal3405418bfc981ae89c307c0905f99f8b); ?>
<?php endif; ?>
            </div>
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
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\repeater.blade.php ENDPATH**/ ?>