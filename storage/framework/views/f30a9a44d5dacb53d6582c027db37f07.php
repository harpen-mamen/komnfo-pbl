<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'parentGroup' => null,
    'collapsible' => true,
    'icon' => null,
    'items' => [],
    'label' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'parentGroup' => null,
    'collapsible' => true,
    'icon' => null,
    'items' => [],
    'label' => null,
]); ?>
<?php foreach (array_filter(([
    'parentGroup' => null,
    'collapsible' => true,
    'icon' => null,
    'items' => [],
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<li
    x-data="{
        label: <?php echo e(\Illuminate\Support\Js::from((filled($parentGroup) ? "{$parentGroup}." : null) . $label)); ?>,
    }"
    class="filament-sidebar-group"
    <?php if(filled($parentGroup)): ?>
        x-bind:class="
            <?php echo e(config('filament.layout.sidebar.is_collapsible_on_desktop') ? '$store.sidebar.isOpen' : 'true'); ?>

                ? 'ml-11 pr-3 pt-3'
                : 'hidden'
        "
    <?php endif; ?>
>
    <?php if($label): ?>
        <button
            <?php if($collapsible): ?>
                x-on:click.prevent="$store.sidebar.toggleCollapsedGroup(label)"
            <?php endif; ?>
            <?php if(config('filament.layout.sidebar.is_collapsible_on_desktop')): ?>
                x-show="$store.sidebar.isOpen"
            <?php endif; ?>
            class="flex w-full items-center justify-between"
        >
            <div
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'flex items-center gap-4 text-gray-600',
                    'dark:text-gray-300' => config('filament.dark_mode'),
                ]); ?>"
            >
                <?php if($icon): ?>
                    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'ml-1 h-3 w-3 flex-shrink-0']); ?>
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

                <p class="flex-1 text-xs font-bold uppercase tracking-wider">
                    <?php echo e($label); ?>

                </p>
            </div>

            <?php if($collapsible): ?>
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(BladeUI\Icons\Components\Svg::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
                        \Illuminate\Support\Arr::toCssClasses([
                            'w-3 h-3 text-gray-600 transition',
                            'dark:text-gray-300' => config('filament.dark_mode'),
                        ])
                    ),'x-bind:class' => '$store.sidebar.groupIsCollapsed(label) || \'-rotate-180\'','x-cloak' => true]); ?>
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
    <?php endif; ?>

    <ul
        x-show="
            ! (
                $store.sidebar.groupIsCollapsed(label) &&
                <?php echo e(config('filament.layout.sidebar.is_collapsible_on_desktop') ? '$store.sidebar.isOpen' : 'true'); ?>

            )
        "
        x-collapse.duration.200ms
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            '-mx-3 space-y-1 text-sm',
            'mt-2' => $label,
        ]); ?>"
    >
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($item instanceof \Filament\Navigation\NavigationItem): ?>
                <?php if($item->isVisible()): ?>
                    <?php if (isset($component)) { $__componentOriginalc3d74403a5408f42553fd772497b251d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc3d74403a5408f42553fd772497b251d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.layouts.app.sidebar.item','data' => ['active' => $item->isActive(),'icon' => $item->getIcon(),'iconColor' => $item->getIconColor(),'activeIcon' => $item->getActiveIcon(),'url' => $item->getUrl(),'badge' => $item->getBadge(),'badgeColor' => $item->getBadgeColor(),'shouldOpenUrlInNewTab' => $item->shouldOpenUrlInNewTab()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::layouts.app.sidebar.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->isActive()),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getIcon()),'iconColor' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getIconColor()),'active-icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getActiveIcon()),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getUrl()),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getBadge()),'badgeColor' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getBadgeColor()),'shouldOpenUrlInNewTab' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->shouldOpenUrlInNewTab())]); ?>
                        <?php echo e($item->getLabel()); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc3d74403a5408f42553fd772497b251d)): ?>
<?php $attributes = $__attributesOriginalc3d74403a5408f42553fd772497b251d; ?>
<?php unset($__attributesOriginalc3d74403a5408f42553fd772497b251d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc3d74403a5408f42553fd772497b251d)): ?>
<?php $component = $__componentOriginalc3d74403a5408f42553fd772497b251d; ?>
<?php unset($__componentOriginalc3d74403a5408f42553fd772497b251d); ?>
<?php endif; ?>
                <?php endif; ?>
            <?php elseif($item instanceof \Filament\Navigation\NavigationGroup): ?>
                <?php if (isset($component)) { $__componentOriginal69820f77c9c841d8e235b39e6f4303bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69820f77c9c841d8e235b39e6f4303bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.layouts.app.sidebar.group','data' => ['label' => $item->getLabel(),'icon' => $item->getIcon(),'collapsible' => $item->isCollapsible(),'items' => $item->getItems(),'parentGroup' => (filled($parentGroup) ? ('$parentGroup' . '.') : null) . $label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::layouts.app.sidebar.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getLabel()),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getIcon()),'collapsible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->isCollapsible()),'items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->getItems()),'parentGroup' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((filled($parentGroup) ? ('$parentGroup' . '.') : null) . $label)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69820f77c9c841d8e235b39e6f4303bb)): ?>
<?php $attributes = $__attributesOriginal69820f77c9c841d8e235b39e6f4303bb; ?>
<?php unset($__attributesOriginal69820f77c9c841d8e235b39e6f4303bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69820f77c9c841d8e235b39e6f4303bb)): ?>
<?php $component = $__componentOriginal69820f77c9c841d8e235b39e6f4303bb; ?>
<?php unset($__componentOriginal69820f77c9c841d8e235b39e6f4303bb); ?>
<?php endif; ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</li>
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\components\layouts\app\sidebar\group.blade.php ENDPATH**/ ?>