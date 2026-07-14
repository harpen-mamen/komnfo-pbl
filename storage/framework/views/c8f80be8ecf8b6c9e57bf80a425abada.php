<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount($name, $params)->html();
} elseif ($_instance->childHasBeenRendered('pxMyB70')) {
    $componentId = $_instance->getRenderedChildComponentId('pxMyB70');
    $componentTag = $_instance->getRenderedChildComponentTagName('pxMyB70');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('pxMyB70');
} else {
    $response = \Livewire\Livewire::mount($name, $params);
    $html = $response->html();
    $_instance->logRenderedChild('pxMyB70', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php /**PATH C:\pbl\vendor\livewire\livewire\src\Testing/../views/mount-component.blade.php ENDPATH**/ ?>