<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'title' => null,
    'width' => 'md',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'title' => null,
    'width' => 'md',
]); ?>
<?php foreach (array_filter(([
    'title' => null,
    'width' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if (isset($component)) { $__componentOriginala69fc323a31b8e7793aaa16493bca576 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala69fc323a31b8e7793aaa16493bca576 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.layouts.base','data' => ['title' => $title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title)]); ?>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'filament-login-page flex min-h-screen items-center justify-center bg-gray-100 py-12 text-gray-900',
            'dark:bg-gray-900 dark:text-white' => config('filament.dark_mode'),
        ]); ?>"
    >
        <div
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                '-mt-16 w-screen space-y-8 px-6 md:mt-0 md:px-2',
                match ($width) {
                    'xs' => 'max-w-xs',
                    'sm' => 'max-w-sm',
                    'md' => 'max-w-md',
                    'lg' => 'max-w-lg',
                    'xl' => 'max-w-xl',
                    '2xl' => 'max-w-2xl',
                    '3xl' => 'max-w-3xl',
                    '4xl' => 'max-w-4xl',
                    '5xl' => 'max-w-5xl',
                    '6xl' => 'max-w-6xl',
                    '7xl' => 'max-w-7xl',
                    default => $width,
                },
            ]); ?>"
        >
            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'relative space-y-4 rounded-2xl border border-gray-200 bg-white/50 p-8 shadow-2xl backdrop-blur-xl',
                    'dark:border-gray-700 dark:bg-gray-900/50' => config('filament.dark_mode'),
                ]); ?>"
            >
                <div class="flex w-full justify-center">
                    <?php if (isset($component)) { $__componentOriginal28b2b10ffb063c347a7502183d14b0ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal28b2b10ffb063c347a7502183d14b0ae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.brand','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal28b2b10ffb063c347a7502183d14b0ae)): ?>
<?php $attributes = $__attributesOriginal28b2b10ffb063c347a7502183d14b0ae; ?>
<?php unset($__attributesOriginal28b2b10ffb063c347a7502183d14b0ae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal28b2b10ffb063c347a7502183d14b0ae)): ?>
<?php $component = $__componentOriginal28b2b10ffb063c347a7502183d14b0ae; ?>
<?php unset($__componentOriginal28b2b10ffb063c347a7502183d14b0ae); ?>
<?php endif; ?>
                </div>

                <?php if(filled($title)): ?>
                    <h2 class="text-center text-2xl font-bold tracking-tight">
                        <?php echo e($title); ?>

                    </h2>
                <?php endif; ?>

                <div <?php echo e($attributes); ?>>
                    <?php echo e($slot); ?>

                </div>
            </div>
        </div>
    </div>

    <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('notifications')->html();
} elseif ($_instance->childHasBeenRendered('FlWnljh')) {
    $componentId = $_instance->getRenderedChildComponentId('FlWnljh');
    $componentTag = $_instance->getRenderedChildComponentTagName('FlWnljh');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('FlWnljh');
} else {
    $response = \Livewire\Livewire::mount('notifications');
    $html = $response->html();
    $_instance->logRenderedChild('FlWnljh', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala69fc323a31b8e7793aaa16493bca576)): ?>
<?php $attributes = $__attributesOriginala69fc323a31b8e7793aaa16493bca576; ?>
<?php unset($__attributesOriginala69fc323a31b8e7793aaa16493bca576); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala69fc323a31b8e7793aaa16493bca576)): ?>
<?php $component = $__componentOriginala69fc323a31b8e7793aaa16493bca576; ?>
<?php unset($__componentOriginala69fc323a31b8e7793aaa16493bca576); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\layouts\card.blade.php ENDPATH**/ ?>