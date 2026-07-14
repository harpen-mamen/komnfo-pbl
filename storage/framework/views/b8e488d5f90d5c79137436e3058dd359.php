<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'maxContentWidth' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'maxContentWidth' => null,
]); ?>
<?php foreach (array_filter(([
    'maxContentWidth' => null,
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
    <div class="filament-app-layout flex h-full w-full overflow-x-clip">
        <div
            x-data="{}"
            x-cloak
            x-show="$store.sidebar.isOpen"
            x-transition.opacity.500ms
            x-on:click="$store.sidebar.close()"
            class="filament-sidebar-close-overlay fixed inset-0 z-20 h-full w-full bg-gray-900/50 lg:hidden"
        ></div>

        <?php if (isset($component)) { $__componentOriginal4c75ac6166c661b3c0e941989a1cfe9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4c75ac6166c661b3c0e941989a1cfe9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.layouts.app.sidebar.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::layouts.app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4c75ac6166c661b3c0e941989a1cfe9f)): ?>
<?php $attributes = $__attributesOriginal4c75ac6166c661b3c0e941989a1cfe9f; ?>
<?php unset($__attributesOriginal4c75ac6166c661b3c0e941989a1cfe9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4c75ac6166c661b3c0e941989a1cfe9f)): ?>
<?php $component = $__componentOriginal4c75ac6166c661b3c0e941989a1cfe9f; ?>
<?php unset($__componentOriginal4c75ac6166c661b3c0e941989a1cfe9f); ?>
<?php endif; ?>

        <div
            <?php if(config('filament.layout.sidebar.is_collapsible_on_desktop')): ?>
                x-data="{}"
                x-bind:class="{
                    'lg:pl-[var(--collapsed-sidebar-width)] rtl:lg:pr-[var(--collapsed-sidebar-width)]':
                        ! $store.sidebar.isOpen,
                    'filament-main-sidebar-open lg:pl-[var(--sidebar-width)] rtl:lg:pr-[var(--sidebar-width)]':
                        $store.sidebar.isOpen,
                }"
                x-bind:style="'display: flex'" 
            <?php endif; ?>
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'filament-main w-screen flex-1 flex-col gap-y-6 rtl:lg:pl-0',
                'hidden h-full transition-all' => config('filament.layout.sidebar.is_collapsible_on_desktop'),
                'flex lg:pl-[var(--sidebar-width)] rtl:lg:pr-[var(--sidebar-width)]' => ! config('filament.layout.sidebar.is_collapsible_on_desktop'),
            ]); ?>"
        >
            <?php if (isset($component)) { $__componentOriginald0661fbaa13e43d08b4ff49478996148 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0661fbaa13e43d08b4ff49478996148 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.topbar','data' => ['breadcrumbs' => $breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0661fbaa13e43d08b4ff49478996148)): ?>
<?php $attributes = $__attributesOriginald0661fbaa13e43d08b4ff49478996148; ?>
<?php unset($__attributesOriginald0661fbaa13e43d08b4ff49478996148); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0661fbaa13e43d08b4ff49478996148)): ?>
<?php $component = $__componentOriginald0661fbaa13e43d08b4ff49478996148; ?>
<?php unset($__componentOriginald0661fbaa13e43d08b4ff49478996148); ?>
<?php endif; ?>

            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'filament-main-content mx-auto w-full flex-1 px-4 md:px-6 lg:px-8',
                    match ($maxContentWidth ??= config('filament.layout.max_content_width')) {
                        null, '7xl', '' => 'max-w-7xl',
                        'xl' => 'max-w-xl',
                        '2xl' => 'max-w-2xl',
                        '3xl' => 'max-w-3xl',
                        '4xl' => 'max-w-4xl',
                        '5xl' => 'max-w-5xl',
                        '6xl' => 'max-w-6xl',
                        'full' => 'max-w-full',
                        default => $maxContentWidth,
                    },
                ]); ?>"
            >
                <?php echo e(\Filament\Facades\Filament::renderHook('content.start')); ?>


                <?php echo e($slot); ?>


                <?php echo e(\Filament\Facades\Filament::renderHook('content.end')); ?>

            </div>

            <div class="filament-main-footer shrink-0 py-4">
                <?php if (isset($component)) { $__componentOriginalef63468e7819851a174368595bff4271 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalef63468e7819851a174368595bff4271 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalef63468e7819851a174368595bff4271)): ?>
<?php $attributes = $__attributesOriginalef63468e7819851a174368595bff4271; ?>
<?php unset($__attributesOriginalef63468e7819851a174368595bff4271); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalef63468e7819851a174368595bff4271)): ?>
<?php $component = $__componentOriginalef63468e7819851a174368595bff4271; ?>
<?php unset($__componentOriginalef63468e7819851a174368595bff4271); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\layouts\app.blade.php ENDPATH**/ ?>