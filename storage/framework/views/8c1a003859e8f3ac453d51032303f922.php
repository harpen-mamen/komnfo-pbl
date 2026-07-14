<?php if (isset($component)) { $__componentOriginal0a6087579beaae9feec0056cadcfb42f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a6087579beaae9feec0056cadcfb42f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.widget','data' => ['class' => 'filament-account-widget']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'filament-account-widget']); ?>
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
        <?php
            $user = \Filament\Facades\Filament::auth()->user();
        ?>

        <div class="flex h-12 items-center space-x-4 rtl:space-x-reverse">
            <?php if (isset($component)) { $__componentOriginal962ee38a63c629995b25d23a161167b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal962ee38a63c629995b25d23a161167b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.user-avatar','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal962ee38a63c629995b25d23a161167b9)): ?>
<?php $attributes = $__attributesOriginal962ee38a63c629995b25d23a161167b9; ?>
<?php unset($__attributesOriginal962ee38a63c629995b25d23a161167b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal962ee38a63c629995b25d23a161167b9)): ?>
<?php $component = $__componentOriginal962ee38a63c629995b25d23a161167b9; ?>
<?php unset($__componentOriginal962ee38a63c629995b25d23a161167b9); ?>
<?php endif; ?>

            <div>
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">
                    <?php echo e(__('filament::widgets/account-widget.welcome', ['user' => \Filament\Facades\Filament::getUserName($user)])); ?>

                </h2>

                <form
                    action="<?php echo e(route('filament.auth.logout')); ?>"
                    method="post"
                    class="text-sm"
                >
                    <?php echo csrf_field(); ?>

                    <button
                        type="submit"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'text-gray-600 outline-none hover:text-primary-500 focus:underline',
                            'dark:text-gray-300 dark:hover:text-primary-500' => config('filament.dark_mode'),
                        ]); ?>"
                    >
                        <?php echo e(__('filament::widgets/account-widget.buttons.logout.label')); ?>

                    </button>
                </form>
            </div>
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
<?php /**PATH C:\pbl\vendor\filament\filament\resources\views\widgets\account-widget.blade.php ENDPATH**/ ?>