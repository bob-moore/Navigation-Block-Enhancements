const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/e2e/specs',
	fullyParallel: false,
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
		trace: 'on-first-retry',
	},
} );
