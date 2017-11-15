<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = array(
    'autoType' => 'class',
    'loadFile' => 'observers/class.ceon_uri_mapping_link_build_observer.php'	
    );
$autoLoadConfig[165][] = array(
    'autoType' => 'classInstantiate',
    'className' => 'CeonUriMappingLinkBuild',
    'objectName' => 'ceon_uri_mapping_link_build_observe'
);
