<?php if (isset($component)) { $__componentOriginalee86f3cd96e06d117377a49a72e656d1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee86f3cd96e06d117377a49a72e656d1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.notification','data' => ['notification' => $notification,'class' => 
        \Illuminate\Support\Arr::toCssClasses([
            'flex gap-3 w-full transition duration-300',
            'shadow-lg max-w-sm bg-white rounded-xl p-4 border border-gray-200' => ! $isInline(),
            'dark:border-gray-700 dark:bg-gray-800' => (! $isInline()) && config('notifications.dark_mode'),
        ])
    ,'xTransition:enterStart' => 
        \Illuminate\Support\Arr::toCssClasses([
            'opacity-0',
            match (config('notifications.layout.alignment.horizontal')) {
                'left' => '-translate-x-12',
                'right' => 'translate-x-12',
                'center' => match (config('notifications.layout.alignment.vertical')) {
                    'top' => '-translate-y-12',
                    'bottom' => 'translate-y-12',
                    'center' => null,
                },
            },
        ])
    ,'xTransition:leaveEnd' => 'scale-95 opacity-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['notification' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notification),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
        \Illuminate\Support\Arr::toCssClasses([
            'flex gap-3 w-full transition duration-300',
            'shadow-lg max-w-sm bg-white rounded-xl p-4 border border-gray-200' => ! $isInline(),
            'dark:border-gray-700 dark:bg-gray-800' => (! $isInline()) && config('notifications.dark_mode'),
        ])
    ),'x-transition:enter-start' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
        \Illuminate\Support\Arr::toCssClasses([
            'opacity-0',
            match (config('notifications.layout.alignment.horizontal')) {
                'left' => '-translate-x-12',
                'right' => 'translate-x-12',
                'center' => match (config('notifications.layout.alignment.vertical')) {
                    'top' => '-translate-y-12',
                    'bottom' => 'translate-y-12',
                    'center' => null,
                },
            },
        ])
    ),'x-transition:leave-end' => 'scale-95 opacity-0']); ?>
    <?php if($icon = $getIcon()): ?>
        <?php if (isset($component)) { $__componentOriginal45dfc869e1cd090414a1d0321fe2a02b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45dfc869e1cd090414a1d0321fe2a02b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.icon','data' => ['icon' => $icon,'color' => $getIconColor()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($getIconColor())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45dfc869e1cd090414a1d0321fe2a02b)): ?>
<?php $attributes = $__attributesOriginal45dfc869e1cd090414a1d0321fe2a02b; ?>
<?php unset($__attributesOriginal45dfc869e1cd090414a1d0321fe2a02b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45dfc869e1cd090414a1d0321fe2a02b)): ?>
<?php $component = $__componentOriginal45dfc869e1cd090414a1d0321fe2a02b; ?>
<?php unset($__componentOriginal45dfc869e1cd090414a1d0321fe2a02b); ?>
<?php endif; ?>
    <?php endif; ?>

    <div class="grid flex-1">
        <?php if($title = $getTitle()): ?>
            <?php if (isset($component)) { $__componentOriginal836fff01f2f0f3cb6d8b113808975365 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal836fff01f2f0f3cb6d8b113808975365 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.title','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e(\Illuminate\Support\Str::of($title)->markdown()->sanitizeHtml()->toHtmlString()); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal836fff01f2f0f3cb6d8b113808975365)): ?>
<?php $attributes = $__attributesOriginal836fff01f2f0f3cb6d8b113808975365; ?>
<?php unset($__attributesOriginal836fff01f2f0f3cb6d8b113808975365); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal836fff01f2f0f3cb6d8b113808975365)): ?>
<?php $component = $__componentOriginal836fff01f2f0f3cb6d8b113808975365; ?>
<?php unset($__componentOriginal836fff01f2f0f3cb6d8b113808975365); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($date = $getDate()): ?>
            <?php if (isset($component)) { $__componentOriginal2c972f30046c5e226eeeab6d2179dfb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c972f30046c5e226eeeab6d2179dfb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.date','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::date'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e($date); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c972f30046c5e226eeeab6d2179dfb0)): ?>
<?php $attributes = $__attributesOriginal2c972f30046c5e226eeeab6d2179dfb0; ?>
<?php unset($__attributesOriginal2c972f30046c5e226eeeab6d2179dfb0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c972f30046c5e226eeeab6d2179dfb0)): ?>
<?php $component = $__componentOriginal2c972f30046c5e226eeeab6d2179dfb0; ?>
<?php unset($__componentOriginal2c972f30046c5e226eeeab6d2179dfb0); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($body = $getBody()): ?>
            <?php if (isset($component)) { $__componentOriginala1f3fd8ee53691255e560038212b3a80 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1f3fd8ee53691255e560038212b3a80 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.body','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::body'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php echo e(\Illuminate\Support\Str::of($body)->markdown()->sanitizeHtml()->toHtmlString()); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1f3fd8ee53691255e560038212b3a80)): ?>
<?php $attributes = $__attributesOriginala1f3fd8ee53691255e560038212b3a80; ?>
<?php unset($__attributesOriginala1f3fd8ee53691255e560038212b3a80); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1f3fd8ee53691255e560038212b3a80)): ?>
<?php $component = $__componentOriginala1f3fd8ee53691255e560038212b3a80; ?>
<?php unset($__componentOriginala1f3fd8ee53691255e560038212b3a80); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($actions = $getActions()): ?>
            <?php if (isset($component)) { $__componentOriginale66e58e95f6545d8ec4be0232684c317 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale66e58e95f6545d8ec4be0232684c317 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.actions.index','data' => ['actions' => $actions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($actions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale66e58e95f6545d8ec4be0232684c317)): ?>
<?php $attributes = $__attributesOriginale66e58e95f6545d8ec4be0232684c317; ?>
<?php unset($__attributesOriginale66e58e95f6545d8ec4be0232684c317); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale66e58e95f6545d8ec4be0232684c317)): ?>
<?php $component = $__componentOriginale66e58e95f6545d8ec4be0232684c317; ?>
<?php unset($__componentOriginale66e58e95f6545d8ec4be0232684c317); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalfc5184578ce15b2218cc0b9a8a6e10e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc5184578ce15b2218cc0b9a8a6e10e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.close-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notifications::close-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc5184578ce15b2218cc0b9a8a6e10e2)): ?>
<?php $attributes = $__attributesOriginalfc5184578ce15b2218cc0b9a8a6e10e2; ?>
<?php unset($__attributesOriginalfc5184578ce15b2218cc0b9a8a6e10e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc5184578ce15b2218cc0b9a8a6e10e2)): ?>
<?php $component = $__componentOriginalfc5184578ce15b2218cc0b9a8a6e10e2; ?>
<?php unset($__componentOriginalfc5184578ce15b2218cc0b9a8a6e10e2); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee86f3cd96e06d117377a49a72e656d1)): ?>
<?php $attributes = $__attributesOriginalee86f3cd96e06d117377a49a72e656d1; ?>
<?php unset($__attributesOriginalee86f3cd96e06d117377a49a72e656d1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee86f3cd96e06d117377a49a72e656d1)): ?>
<?php $component = $__componentOriginalee86f3cd96e06d117377a49a72e656d1; ?>
<?php unset($__componentOriginalee86f3cd96e06d117377a49a72e656d1); ?>
<?php endif; ?>
<?php /**PATH C:\pbl\vendor\filament\notifications\resources\views\notification.blade.php ENDPATH**/ ?>