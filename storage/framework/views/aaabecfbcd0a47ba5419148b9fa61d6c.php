<?php
    $gridDirection = $getGridDirection();
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
<?php $component->withAttributes(['id' => $getId(),'label' => $getLabel(),'label-sr-only' => $isLabelHidden(),'has-nested-recursive-validation-rules' => true,'helper-text' => $getHelperText(),'hint' => $getHint(),'hint-action' => $getHintAction(),'hint-color' => $getHintColor(),'hint-icon' => $getHintIcon(),'required' => $isRequired(),'state-path' => $getStatePath()]); ?>
    <div
        x-data="{
            areAllCheckboxesChecked: false,

            checkboxListOptions: Array.from(
                $root.querySelectorAll(
                    '.filament-forms-checkbox-list-component-option-label',
                ),
            ),

            search: '',

            visibleCheckboxListOptions: [],

            init: function () {
                this.updateVisibleCheckboxListOptions()

                $nextTick(() => {
                    this.checkIfAllCheckboxesAreChecked()
                })

                Livewire.hook('message.processed', () => {
                    this.updateVisibleCheckboxListOptions()

                    this.checkIfAllCheckboxesAreChecked()
                })

                $watch('search', () => {
                    this.updateVisibleCheckboxListOptions()
                    this.checkIfAllCheckboxesAreChecked()
                })
            },

            checkIfAllCheckboxesAreChecked: function () {
                this.areAllCheckboxesChecked =
                    this.visibleCheckboxListOptions.length ===
                    this.visibleCheckboxListOptions.filter((checkboxLabel) =>
                        checkboxLabel.querySelector('input[type=checkbox]:checked'),
                    ).length
            },

            toggleAllCheckboxes: function () {
                updatedState = []

                state = ! this.areAllCheckboxesChecked

                this.visibleCheckboxListOptions.forEach((checkboxLabel) => {
                    checkbox = checkboxLabel.querySelector('input[type=checkbox]')

                    checkbox.checked = state

                    if (state) {
                        updatedState.push(checkbox.value)
                    }
                })

                this.checkIfAllCheckboxesAreChecked()

                $wire.set(<?php echo \Illuminate\Support\Js::from($getStatePath())->toHtml() ?>, updatedState)

                this.areAllCheckboxesChecked = state
            },

            updateVisibleCheckboxListOptions: function () {
                this.visibleCheckboxListOptions = this.checkboxListOptions.filter(
                    (checkboxListItem) => {
                        return checkboxListItem
                            .querySelector(
                                '.filament-forms-checkbox-list-component-option-label-text',
                            )
                            .innerText.toLowerCase()
                            .includes(this.search.toLowerCase())
                    },
                )
            },
        }"
    >
        <?php if(! $isDisabled()): ?>
            <?php if($isSearchable()): ?>
                <input
                    x-model.debounce.<?php echo e($getSearchDebounce()); ?>="search"
                    type="search"
                    placeholder="<?php echo e($getSearchPrompt()); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'mb-2 block h-7 w-full rounded-lg border-gray-300 px-2 text-sm text-gray-700 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500',
                        'dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200' => config('forms.dark_mode'),
                    ]); ?>"
                />
            <?php endif; ?>

            <?php if($isBulkToggleable() && count($getOptions())): ?>
                <div
                    x-cloak
                    class="mb-2"
                    wire:key="<?php echo e($this->id); ?>.<?php echo e($getStatePath()); ?>.<?php echo e($field::class); ?>.buttons"
                >
                    <?php if (isset($component)) { $__componentOriginal6afa4c11877d2c8634a52f57f7f26617 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6afa4c11877d2c8634a52f57f7f26617 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.link','data' => ['tag' => 'button','size' => 'sm','xShow' => '! areAllCheckboxesChecked','xOn:click' => 'toggleAllCheckboxes()','wire:key' => ''.e($this->id).'.'.e($getStatePath()).'.'.e($field::class).'.buttons.select_all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'button','size' => 'sm','x-show' => '! areAllCheckboxesChecked','x-on:click' => 'toggleAllCheckboxes()','wire:key' => ''.e($this->id).'.'.e($getStatePath()).'.'.e($field::class).'.buttons.select_all']); ?>
                        <?php echo e(__('forms::components.checkbox_list.buttons.select_all.label')); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.link','data' => ['tag' => 'button','size' => 'sm','xShow' => 'areAllCheckboxesChecked','xOn:click' => 'toggleAllCheckboxes()','wire:key' => ''.e($this->id).'.'.e($getStatePath()).'.'.e($field::class).'.buttons.deselect_all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'button','size' => 'sm','x-show' => 'areAllCheckboxesChecked','x-on:click' => 'toggleAllCheckboxes()','wire:key' => ''.e($this->id).'.'.e($getStatePath()).'.'.e($field::class).'.buttons.deselect_all']); ?>
                        <?php echo e(__('forms::components.checkbox_list.buttons.deselect_all.label')); ?>

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
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal619b5702a4dba2316335176c43ec06a2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal619b5702a4dba2316335176c43ec06a2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-support::components.grid.index','data' => ['default' => $getColumns('default'),'sm' => $getColumns('sm'),'md' => $getColumns('md'),'lg' => $getColumns('lg'),'xl' => $getColumns('xl'),'twoXl' => $getColumns('2xl'),'direction' => $gridDirection ?? 'column','xShow' => $isSearchable() ? 'visibleCheckboxListOptions.length' : null,'attributes' => 
                \Filament\Support\prepare_inherited_attributes($attributes->class([
                    'filament-forms-checkbox-list-component gap-1',
                    'space-y-2' => $gridDirection !== 'row',
                ]))
            ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-support::grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['default' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('default')),'sm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('sm')),'md' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('md')),'lg' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('lg')),'xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('xl')),'two-xl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getColumns('2xl')),'direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gridDirection ?? 'column'),'x-show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isSearchable() ? 'visibleCheckboxListOptions.length' : null),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                \Filament\Support\prepare_inherited_attributes($attributes->class([
                    'filament-forms-checkbox-list-component gap-1',
                    'space-y-2' => $gridDirection !== 'row',
                ]))
            )]); ?>
            <?php $__empty_1 = true; $__currentLoopData = $getOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionValue => $optionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    wire:key="<?php echo e($this->id); ?>.<?php echo e($getStatePath()); ?>.<?php echo e($field::class); ?>.options.<?php echo e($optionValue); ?>"
                >
                    <label
                        class="filament-forms-checkbox-list-component-option-label flex items-center space-x-3 rtl:space-x-reverse"
                        <?php if($isSearchable()): ?>
                            x-show="
                                $el.querySelector('.filament-forms-checkbox-list-component-option-label-text')
                                    .innerText.toLowerCase()
                                    .includes(search.toLowerCase())
                            "
                        <?php endif; ?>
                    >
                        <input
                            wire:loading.attr="disabled"
                            type="checkbox"
                            value="<?php echo e($optionValue); ?>"
                            dusk="filament.forms.<?php echo e($getStatePath()); ?>"
                            <?php echo e($applyStateBindingModifiers('wire:model')); ?>="<?php echo e($getStatePath()); ?>"
                            <?php echo e($getExtraAttributeBag()
                                    ->class([
                                        'text-primary-600 transition duration-75 rounded shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70',
                                        'dark:bg-gray-700 dark:checked:bg-primary-500' => config('forms.dark_mode'),
                                        'border-gray-300' => ! $errors->has($getStatePath()),
                                        'dark:border-gray-600' => (! $errors->has($getStatePath())) && config('forms.dark_mode'),
                                        'border-danger-300 ring-danger-500' => $errors->has($getStatePath()),
                                        'dark:border-danger-400 dark:ring-danger-400' => $errors->has($getStatePath()) && config('forms.dark_mode'),
                                    ])
                                    ->merge([
                                        'disabled' => $isDisabled(),
                                    ])); ?>

                        />

                        <span
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'filament-forms-checkbox-list-component-option-label-text text-sm font-medium text-gray-700',
                                'dark:text-gray-200' => config('forms.dark_mode'),
                            ]); ?>"
                        >
                            <?php echo e($optionLabel); ?>

                        </span>
                    </label>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div
                    wire:key="<?php echo e($this->id); ?>.<?php echo e($getStatePath()); ?>.<?php echo e($field::class); ?>.empty"
                ></div>
            <?php endif; ?>
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

        <?php if($isSearchable()): ?>
            <div
                x-cloak
                x-show="! visibleCheckboxListOptions.length"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'filament-forms-checkbox-list-component-no-search-results-message text-sm text-gray-700',
                    'dark:text-gray-200' => config('forms.dark_mode'),
                ]); ?>"
            >
                <?php echo e($getNoSearchResultsMessage()); ?>

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
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\checkbox-list.blade.php ENDPATH**/ ?>