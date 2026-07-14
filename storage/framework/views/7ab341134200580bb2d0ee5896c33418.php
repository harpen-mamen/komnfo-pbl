<?php
    $isRtl = __('filament::layout.direction') === 'rtl';
    $previousArrowIcon = $isRtl ? 'heroicon-o-chevron-right' : 'heroicon-o-chevron-left';
    $nextArrowIcon = $isRtl ? 'heroicon-o-chevron-left' : 'heroicon-o-chevron-right';
?>

<div
    x-data="{
        step: null,

        init: function () {
            this.$watch('step', () => this.updateQueryString())

            this.step = this.getSteps().at(<?php echo e($getStartStep() - 1); ?>)
        },

        nextStep: function () {
            let nextStepIndex = this.getStepIndex(this.step) + 1

            if (nextStepIndex >= this.getSteps().length) {
                return
            }

            this.step = this.getSteps()[nextStepIndex]

            this.autofocusFields()
            this.scrollToTop()
        },

        previousStep: function () {
            let previousStepIndex = this.getStepIndex(this.step) - 1

            if (previousStepIndex < 0) {
                return
            }

            this.step = this.getSteps()[previousStepIndex]

            this.autofocusFields()
            this.scrollToTop()
        },

        scrollToTop: function () {
            this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' })
        },

        autofocusFields: function () {
            $nextTick(() =>
                this.$refs[`step-${this.step}`]
                    .querySelector('[autofocus]')
                    ?.focus(),
            )
        },

        getStepIndex: function (step) {
            return this.getSteps().findIndex((indexedStep) => indexedStep === step)
        },

        getSteps: function () {
            return JSON.parse(this.$refs.stepsData.value)
        },

        isFirstStep: function () {
            return this.getStepIndex(this.step) <= 0
        },

        isLastStep: function () {
            return this.getStepIndex(this.step) + 1 >= this.getSteps().length
        },

        isStepAccessible: function (step, index) {
            return <?php echo \Illuminate\Support\Js::from($isSkippable())->toHtml() ?> || this.getStepIndex(step) > index
        },

        updateQueryString: function () {
            if (! <?php echo \Illuminate\Support\Js::from($isStepPersistedInQueryString())->toHtml() ?>) {
                return
            }

            const url = new URL(window.location.href)
            url.searchParams.set(<?php echo \Illuminate\Support\Js::from($getStepQueryStringKey())->toHtml() ?>, this.step)

            history.pushState(null, document.title, url.toString())
        },
    }"
    x-on:next-wizard-step.window="if ($event.detail.statePath === '<?php echo e($getStatePath()); ?>') nextStep()"
    x-cloak
    wire:ignore.self
    <?php echo $getId() ? "id=\"{$getId()}\"" : null; ?>

    <?php echo e($attributes->merge($getExtraAttributes())->class(['filament-forms-wizard-component grid gap-y-6'])); ?>

    <?php echo e($getExtraAlpineAttributeBag()); ?>

>
    <input
        type="hidden"
        value="<?php echo e(collect($getChildComponentContainer()->getComponents())
                ->filter(static fn (\Filament\Forms\Components\Wizard\Step $step): bool => ! $step->isHidden())
                ->map(static fn (\Filament\Forms\Components\Wizard\Step $step) => $step->getId())
                ->values()
                ->toJson()); ?>"
        x-ref="stepsData"
    />

    <ol
        <?php echo $getLabel() ? 'aria-label="' . $getLabel() . '"' : null; ?>

        role="list"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'filament-forms-wizard-component-header divide-y divide-gray-300 overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm md:flex md:divide-y-0',
            'dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-800' => config('forms.dark_mode'),
        ]); ?>"
    >
        <?php $__currentLoopData = $getChildComponentContainer()->getComponents(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li
                class="filament-forms-wizard-component-header-step group relative overflow-hidden md:flex-1"
            >
                <button
                    type="button"
                    x-on:click="if (isStepAccessible(step, <?php echo e($loop->index); ?>)) step = '<?php echo e($step->getId()); ?>'"
                    x-bind:aria-current="getStepIndex(step) === <?php echo e($loop->index); ?> ? 'step' : null"
                    x-bind:class="{
                        'cursor-not-allowed pointer-events-none': ! isStepAccessible(
                            step,
                            <?php echo e($loop->index); ?>,
                        ),
                    }"
                    role="step"
                    class="flex h-full w-full items-center text-start"
                >
                    <div
                        x-bind:class="{
                            'bg-primary-600': getStepIndex(step) === <?php echo e($loop->index); ?>,
                            'bg-transparent group-hover:bg-gray-200 <?php if(config('forms.dark_mode')): ?> dark:group-hover:bg-gray-600 <?php endif; ?>': getStepIndex(step) > <?php echo e($loop->index); ?>,
                        }"
                        class="absolute left-0 top-0 h-full w-1 md:bottom-0 md:top-auto md:h-1 md:w-full"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="flex items-center gap-3 px-5 py-4 text-sm font-medium"
                    >
                        <div class="flex-shrink-0">
                            <div
                                x-bind:class="{
                                    'bg-primary-600': getStepIndex(step) > <?php echo e($loop->index); ?>,
                                    'border-2': getStepIndex(step) <= <?php echo e($loop->index); ?>,
                                    'border-primary-500': getStepIndex(step) === <?php echo e($loop->index); ?>,
                                    'border-gray-300 <?php if(config('forms.dark_mode')): ?> dark:border-gray-500 <?php endif; ?>': getStepIndex(step) < <?php echo e($loop->index); ?>,
                                }"
                                class="filament-forms-wizard-component-header-step-icon flex h-10 w-10 items-center justify-center rounded-full"
                            >
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-o-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'getStepIndex(step) > '.e($loop->index).'','x-cloak' => true,'class' => 'h-5 w-5 text-white']); ?>
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

                                <?php if($step->getIcon()): ?>
                                    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $step->getIcon()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'getStepIndex(step) <= '.e($loop->index).'','x-cloak' => true,'x-bind:class' => '{
                                            \'text-gray-500 @if (config(\'forms.dark_mode\')) dark:text-gray-400 @endif\': getStepIndex(step) !== '.e($loop->index).',
                                            \'text-primary-500\': getStepIndex(step) === '.e($loop->index).',
                                        }','class' => 'h-5 w-5']); ?>
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
                                <?php else: ?>
                                    <span
                                        x-show="getStepIndex(step) <= <?php echo e($loop->index); ?>"
                                        x-bind:class="{
                                            'text-gray-500 <?php if(config('forms.dark_mode')): ?> dark:text-gray-400 <?php endif; ?>': getStepIndex(step) !== <?php echo e($loop->index); ?>,
                                            'text-primary-500': getStepIndex(step) === <?php echo e($loop->index); ?>,
                                        }"
                                    >
                                        <?php echo e(str_pad($loop->index + 1, 2, '0', STR_PAD_LEFT)); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex flex-col items-start justify-center">
                            <div
                                class="filament-forms-wizard-component-header-step-label text-sm font-semibold uppercase tracking-wide"
                            >
                                <?php echo e($step->getLabel()); ?>

                            </div>

                            <?php if(filled($description = $step->getDescription())): ?>
                                <div
                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'filament-forms-wizard-component-header-step-description text-sm font-medium leading-4 text-gray-500',
                                        'dark:text-gray-400' => config('forms.dark_mode'),
                                    ]); ?>"
                                >
                                    <?php echo e($description); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </button>

                <?php if(! $loop->first): ?>
                    <div
                        class="absolute inset-0 left-0 top-0 hidden w-3 md:block"
                        aria-hidden="true"
                    >
                        <svg
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'h-full w-full text-gray-300 rtl:rotate-180',
                                'dark:text-gray-700' => config('forms.dark_mode'),
                            ]); ?>"
                            viewBox="0 0 12 82"
                            fill="none"
                            preserveAspectRatio="none"
                        >
                            <path
                                d="M0.5 0V31L10.5 41L0.5 51V82"
                                stroke="currentcolor"
                                vector-effect="non-scaling-stroke"
                            />
                        </svg>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>

    <div>
        <?php $__currentLoopData = $getChildComponentContainer()->getComponents(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($step); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <?php if (isset($component)) { $__componentOriginal3405418bfc981ae89c307c0905f99f8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3405418bfc981ae89c307c0905f99f8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.button','data' => ['icon' => $previousArrowIcon,'xShow' => '! isFirstStep()','xCloak' => true,'xOn:click' => 'previousStep','color' => 'secondary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($previousArrowIcon),'x-show' => '! isFirstStep()','x-cloak' => true,'x-on:click' => 'previousStep','color' => 'secondary','size' => 'sm']); ?>
                <?php echo e(__('forms::components.wizard.buttons.previous_step.label')); ?>

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

            <div x-show="isFirstStep()">
                <?php echo e($getCancelAction()); ?>

            </div>
        </div>

        <div>
            <?php if (isset($component)) { $__componentOriginal3405418bfc981ae89c307c0905f99f8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3405418bfc981ae89c307c0905f99f8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'forms::components.button','data' => ['icon' => $nextArrowIcon,'iconPosition' => 'after','xShow' => '! isLastStep()','xCloak' => true,'xOn:click' => '$wire.dispatchFormEvent(\'wizard::nextStep\', \''.e($getStatePath()).'\', getStepIndex(step))','wire:loading.class.delay' => 'opacity-70 cursor-wait','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nextArrowIcon),'icon-position' => 'after','x-show' => '! isLastStep()','x-cloak' => true,'x-on:click' => '$wire.dispatchFormEvent(\'wizard::nextStep\', \''.e($getStatePath()).'\', getStepIndex(step))','wire:loading.class.delay' => 'opacity-70 cursor-wait','size' => 'sm']); ?>
                <?php echo e(__('forms::components.wizard.buttons.next_step.label')); ?>

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

            <div x-show="isLastStep()">
                <?php echo e($getSubmitAction()); ?>

            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\pbl\vendor\filament\forms\resources\views\components\wizard.blade.php ENDPATH**/ ?>