<?php

function customize_php_scoper_config( array $config = [] ): array {
	$config['exclude-namespaces'] = array_merge(
		$config['exclude-namespaces'] ?? [],
		[ 'Composer' ]
	);

	return $config;
}
