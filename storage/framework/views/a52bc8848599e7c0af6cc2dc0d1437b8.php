<?php
    $heading = $this->getHeading();
    $filters = $this->getFilters();
?>

<?php if (isset($component)) { $__componentOriginal0a6087579beaae9feec0056cadcfb42f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a6087579beaae9feec0056cadcfb42f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.widget','data' => ['class' => 'filament-widgets-chart-widget']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-widgets-chart-widget']); ?>
    <?php if (isset($component)) { $__componentOriginal9b945b32438afb742355861768089b04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b945b32438afb742355861768089b04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.card.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if($heading || $filters): ?>
            <div class="flex items-center justify-between gap-8">
                <?php if($heading): ?>
                    <?php if (isset($component)) { $__componentOriginal2d57e9a4f94e1d74974fd1cc8bf41e90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d57e9a4f94e1d74974fd1cc8bf41e90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.card.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::card.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php echo e($heading); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d57e9a4f94e1d74974fd1cc8bf41e90)): ?>
<?php $attributes = $__attributesOriginal2d57e9a4f94e1d74974fd1cc8bf41e90; ?>
<?php unset($__attributesOriginal2d57e9a4f94e1d74974fd1cc8bf41e90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d57e9a4f94e1d74974fd1cc8bf41e90)): ?>
<?php $component = $__componentOriginal2d57e9a4f94e1d74974fd1cc8bf41e90; ?>
<?php unset($__componentOriginal2d57e9a4f94e1d74974fd1cc8bf41e90); ?>
<?php endif; ?>
                <?php endif; ?>

                <?php if($filters): ?>
                    <select
                        wire:model="filter"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'block h-10 rounded-lg border-gray-300 text-gray-900 shadow-sm outline-none transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500',
                            'dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500' => config('filament.dark_mode'),
                        ]); ?>"
                        wire:loading.class="animate-pulse"
                    >
                        <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>">
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal3bb406532a5c335331f97685b7527de1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3bb406532a5c335331f97685b7527de1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.hr','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::hr'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3bb406532a5c335331f97685b7527de1)): ?>
<?php $attributes = $__attributesOriginal3bb406532a5c335331f97685b7527de1; ?>
<?php unset($__attributesOriginal3bb406532a5c335331f97685b7527de1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3bb406532a5c335331f97685b7527de1)): ?>
<?php $component = $__componentOriginal3bb406532a5c335331f97685b7527de1; ?>
<?php unset($__componentOriginal3bb406532a5c335331f97685b7527de1); ?>
<?php endif; ?>
        <?php endif; ?>

        <div
            <?php echo ($pollingInterval = $this->getPollingInterval()) ? "wire:poll.{$pollingInterval}=\"updateChartData\"" : ''; ?>

        >
            <canvas
                x-data="{
                    chart: null,

                    init: function () {
                        let chart = this.initChart()

                        $wire.on('updateChartData', async ({ data }) => {
                            chart.data = this.applyColorToData(data)
                            chart.update('resize')
                        })

                        $wire.on('filterChartData', async ({ data }) => {
                            chart.destroy()
                            chart = this.initChart(data)
                        })
                    },

                    initChart: function (data = null) {
                        data = data ?? <?php echo e(json_encode($this->getCachedData())); ?>


                        return (this.chart = new Chart($el, {
                            type: '<?php echo e($this->getType()); ?>',
                            data: this.applyColorToData(data),
                            options: <?php echo e(json_encode($this->getOptions())); ?> ?? {},
                        }))
                    },

                    applyColorToData: function (data) {
                        data.datasets.forEach((dataset, datasetIndex) => {
                            if (! dataset.backgroundColor) {
                                data.datasets[datasetIndex].backgroundColor = getComputedStyle(
                                    $refs.backgroundColorElement,
                                ).color
                            }

                            if (! dataset.borderColor) {
                                data.datasets[datasetIndex].borderColor = getComputedStyle(
                                    $refs.borderColorElement,
                                ).color
                            }
                        })

                        return data
                    },
                }"
                wire:ignore
                <?php if($maxHeight = $this->getMaxHeight()): ?>
                    style="max-height: <?php echo e($maxHeight); ?>"
                <?php endif; ?>
            >
                <span
                    x-ref="backgroundColorElement"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-gray-50',
                        'dark:text-gray-300' => config('filament.dark_mode'),
                    ]); ?>"
                ></span>

                <span
                    x-ref="borderColorElement"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-gray-500',
                        'dark:text-gray-200' => config('filament.dark_mode'),
                    ]); ?>"
                ></span>
            </canvas>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9b945b32438afb742355861768089b04)): ?>
<?php $attributes = $__attributesOriginal9b945b32438afb742355861768089b04; ?>
<?php unset($__attributesOriginal9b945b32438afb742355861768089b04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9b945b32438afb742355861768089b04)): ?>
<?php $component = $__componentOriginal9b945b32438afb742355861768089b04; ?>
<?php unset($__componentOriginal9b945b32438afb742355861768089b04); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0a6087579beaae9feec0056cadcfb42f)): ?>
<?php $attributes = $__attributesOriginal0a6087579beaae9feec0056cadcfb42f; ?>
<?php unset($__attributesOriginal0a6087579beaae9feec0056cadcfb42f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0a6087579beaae9feec0056cadcfb42f)): ?>
<?php $component = $__componentOriginal0a6087579beaae9feec0056cadcfb42f; ?>
<?php unset($__componentOriginal0a6087579beaae9feec0056cadcfb42f); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\widgets\chart-widget.blade.php ENDPATH**/ ?>