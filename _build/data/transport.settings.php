<?php

$s = array(
    'translit' => false,
    'check_resid' => true,
    'random_lenght' => 6
);

$settings = array();

foreach ($s as $key => $value) {
    if (is_string($value) || is_int($value)) { $type = 'textfield'; }
    elseif (is_bool($value)) { $type = 'combo-boolean'; }
    else { $type = 'textfield'; }

    $parts = explode('.',$key);
    if (count($parts) == 1) { $area = 'Default'; }
    else { $area = $parts[0]; }
    
    $settings[PKG_NAME_LOWER.'.'.$key] = $modx->newObject('modSystemSetting');
    $settings[PKG_NAME_LOWER.'.'.$key]->set('key', PKG_NAME_LOWER.'.'.$key);
    $settings[PKG_NAME_LOWER.'.'.$key]->fromArray(array(
        'value' => $value,
        'xtype' => $type,
        'namespace' => PKG_NAME_LOWER,
        'area' => $area
    ));
}

return $settings;