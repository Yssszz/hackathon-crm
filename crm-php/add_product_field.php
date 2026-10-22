<?php
// Adds a "Product" text field to Opportunities (the video shows a product/vehicle per opportunity) and puts it on the detail and edit views.
require __DIR__ . '/bootstrap.php';
require_once 'modules/DynamicFields/DynamicField.php';
require_once 'modules/DynamicFields/FieldCases.php';
require_once 'modules/ModuleBuilder/parsers/ParserFactory.php';

$bean = BeanFactory::newBean('Opportunities');
if (!isset($bean->field_defs['product_c'])) {
    $df = new DynamicField('Opportunities');
    $df->setup($bean);
    $field = get_widget('varchar');
    $field->populateFromPost = false;
    $field->name = 'product_c';
    $field->label = 'LBL_PRODUCT_C';
    $field->type = 'varchar';
    $field->len = 100;
    $field->max_size = 100;
    $field->required = false;
    $field->reportable = true;
    $field->audited = false;
    $field->importable = 'true';
    $field->massupdate = false;
    $field->help = 'Vehicle the customer is interested in';
    $field->comments = 'Vehicle the customer is interested in';
    $field->supports_inline_edit = true;
    $field->label_value = 'Product';
    $field->save($df);
    echo "field added\n";
} else {
    echo "field already exists\n";
}

foreach (array(MB_DETAILVIEW, MB_EDITVIEW) as $view) {
    $parser = ParserFactory::getParser($view, 'Opportunities');
    $file = 'custom/modules/Opportunities/metadata/' . ($view === MB_DETAILVIEW ? 'detailviewdefs.php' : 'editviewdefs.php');
    if (!file_exists($file) || strpos(file_get_contents($file), 'product_c') === false) {
        $parser->addField(array('name' => 'product_c', 'label' => 'LBL_PRODUCT_C'));
        $parser->handleSave(false);
        echo "added to $view\n";
    }
}

require_once 'modules/Administration/QuickRepairAndRebuild.php';
$r = new RepairAndClear();
$r->repairAndClearAll(array('clearAll'), array('Opportunities'), false, false);
echo "rebuilt\n";
